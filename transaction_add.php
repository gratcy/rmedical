<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='transaction.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$tammount = isset($_POST['tammount']) ? (double) $_POST['tammount'] : 0;
$ttotal = isset($_POST['ttotal']) ? (double) $_POST['ttotal'] : 0;
$tdiscount = isset($_POST['tdiscount']) ? (double) $_POST['tdiscount'] : 0;
$tqty = isset($_POST['tqty']) ? (int) $_POST['tqty'] : 0;
$store_id = isset($_POST['store_id']) ? (int) $_POST['store_id'] : 0;
$customer_id = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
$staff_id = isset($_POST['staff_id']) ? (int) $_POST['staff_id'] : 0;
$tpayment = isset($_POST['tpayment']) ? (int) $_POST['tpayment'] : 0;
$tcardno = isset($_POST['tcardno']) ? $_POST['tcardno'] : '';

$newcust = isset($_POST['newcust']) ? (int) $_POST['newcust'] : 0;

$oname = isset($_POST['oname']) ? $_POST['oname'] : '';
$odd = isset($_POST['odd']) ? (int) $_POST['odd'] : 0;
$omm = isset($_POST['omm']) ? (int) $_POST['omm'] : 0;
$oyyyy = isset($_POST['oyyyy']) ? (int) $_POST['oyyyy'] : 0;
$oemail = isset($_POST['oemail']) ? $_POST['oemail'] : '';
$ophone = isset($_POST['ophone']) ? $_POST['ophone'] : array();

$name = isset($_POST['name']) ? $_POST['name'] : '';
$dd = isset($_POST['dd']) ? (int) $_POST['dd'] : 0;
$mm = isset($_POST['mm']) ? (int) $_POST['mm'] : 0;
$yyyy = isset($_POST['yyyy']) ? (int) $_POST['yyyy'] : 0;
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : array();

$qty = isset($_POST['qty']) ? $_POST['qty'] : array();
$price = isset($_POST['price']) ? $_POST['price'] : array();
$pname = isset($_POST['pname']) ? $_POST['pname'] : array();

$submit = isset($_POST['submit']) ? $_POST['submit'] : '';

if ($submit) {
	$error = null;
	if (!$tammount || !$ttotal || !$tqty || !$staff_id) {
		$error = 'Data you input is incomplete !!!';
	}
	else if ($tammount <= 1 || $ttotal <= 1) {
		$error = 'Total zero !!!';
	}
	else if (!$store_id) {
		$error = 'Unknown Store !!!';
	}
	else if ($tpayment > 1 && !$tcardno) {
		$error = 'Card number must be filled !!!';
	}
	else if ($newcust == 0 && !$customer_id) {
		$error = 'Customer must be filled !!!';
	}
	else {
		if ($newcust == 1) {
			$ck = sql_check("SELECT * FROM customer_tab WHERE cstatus=1 AND cemail='".$email."'");
			if ($ck) {
				$error = 'Email exists !!!';
				echo "<p><font color=red>Error :</font></p>";
				echo "<p>( ".$error." )</p>";
				gotoURL('/transaction_add.php', 3);
				exit;
			}
			else {
				$birthday = $yyyy.'-'.$mm.'-'.$dd;
				
				$custFields['cname'] = $name;
				$custFields['cbirthday'] = strtotime($birthday);
				$custFields['cemail'] = $email;
				$custFields['cphone'] = implode('*',$phone);
				$custFields['cstatus'] = 1;
				$custFields['ccreated'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
				sql_query(sql_insert("customer_tab", $custFields));
				$customer_id = sql_insert_id();
			}
		}
		else {
			$obirthday = $oyyyy.'-'.$omm.'-'.$odd;
			
			$custFields['cname'] = $oname;
			$custFields['cbirthday'] = strtotime($obirthday);
			$custFields['cemail'] = $oemail;
			$custFields['cphone'] = implode('*',$ophone);
			$custFields['cstatus'] = 1;
			$custFields['cmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
			sql_query(sql_update("customer_tab", $custFields, "cid='$customer_id'"));
			$name = $oname;
			$email = $oemail;
		}
		
		$tno = 'SO'.str_pad($store_id, 2, "0", STR_PAD_LEFT).date('Ym').str_pad(get_total_SO($store_id)+1, 4, "0", STR_PAD_LEFT);
		$fields['tdate'] = date('Y-m-d H:i:s');
		$fields['tno'] = $tno;
		$fields['tstore'] = $store_id;
		$fields['tsid'] = $staff_id;
		$fields['tcid'] = $customer_id;
		$fields['tqty'] = $tqty;
		$fields['tammount'] = $tammount;
		$fields['ttotal'] = $ttotal;
		$fields['tdiscount'] = $tdiscount;
		$fields['tpayment'] = $tpayment;
		$fields['tcardno'] = $tcardno;
		$fields['tstatus'] = 1;
		$fields['tcreated'] = json_encode(array('uid' => $user -> id, 'date' => date('Y-m-d H:i:s')));
		sql_query(sql_insert("transaction_tab", $fields));
		$id = sql_insert_id();
		$fields2 = array();
		foreach($qty as $k => $v) {
			$fields2['ttid'] = $id;
			$fields2['tpid'] = $k;
			$fields2['tqty'] = $v;
			$fields2['tprice'] = $price[$k];
			$fields2['tstatus'] = 1;
			$fields2['tcreated'] = json_encode(array('uid' => $user -> id, 'date' => date('Y-m-d H:i:s')));
			sql_query(sql_insert("transaction_detail_tab", $fields2));
			unset($fields2);
		}
	}
	if (!empty($error)) {
		echo "<p><font color=red>Error :</font></p>";
		echo "<p>( ".$error." )</p>";
		gotoURL('/transaction_add.php', 3);
		exit;
	}
	else {
		$wew = sql_getTable("select name FROM staff WHERE id=" . $staff_id);
		$res = '';
		foreach($pname as $k => $v) {
			$warranty = sql_getTable("select warranty FROM item WHERE id=" . $k);
			$res .= '<tr>';
			$res .= '<td>'.$v.'</td>';
			$res .= '<td>'.$qty[$k].'</td>';
			$res .= '<td>'.($warranty[0]['warranty'] == 1 ? date('Y-m-d',strtotime("+1 year")) : 'No Warranty').'</td>';
			$res .= '<td>'.$price[$k].'</td>';
			$res .= '</tr>';
		}
		$res .= '<tr><td><b>Total</b></td><td>'.array_sum($qty).'</td><td></td><td>'.array_sum($price).'</td></tr>';

		$Qdata['dateorder'] = date('Y-m-d H:i:s');
		$Qdata['sales'] = $wew[0]['name'];
		$Qdata['sono'] = $tno;
		$Qdata['table'] = $res;
		$Qdata['cname'] = $name;
		
		__send_email($email, 'Rock Trading Transaction', $Qdata,dirname(__FILE__) . '/tpl/transaction.html');
		
		echo "<p><font color=blue>Sales Order successfully added :</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='transaction.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL('/transaction.php', 3);
		exit;
	}
}
include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'tammount'				, 'text_readonly2'	, 'Amount'		, '30%',
			'tdiscount'				, 'text'	, 'Discount (%)'	, '30%',
			'tcardno'				, 'text'	, 'Card No.'	, '100%',
			'tqty'					, 'text_readonly2'	, 'QTY'			, '20%',
			'ttotal'				, 'text_readonly2'	, 'Total'		, '30%',
			'store_id'				, 'select2'	, 'Store'		, '100%',
			'staff_id'				, 'select2'	, 'Staff'		, '100%',
			'customer_id'			, 'select2'	, 'Customer'	, '100%',
			'tpayment'				, 'radio'	, 'Payment Type'	, '100%',
			'item_id'				, 'select2'	, 'Item'	, '100%'
				);
$inputs->options['staff_id']				= sql_getArray("select name, id from staff WHERE class IN (2,3) order by name asc");
$inputs->options['store_id']				= sql_getArray("select sname, sid from store_tab WHERE sstatus=1 order by sname asc");
$inputs->options['customer_id']				= sql_getArray("select cname, cid from customer_tab order by cname asc");
$inputs->options['tpayment']				= array(lang('現金') => 0,'EPS' => 1,lang('信用咭') => 2);
?>

<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>
<style>
.newcust{display:none}
.oldcust2{display:none}
</style>
<h3><?php echo lang('開單') ?></h3>
<table width="100%" cellpadding="0" border="0" class="noprint">
	<tbody><tr>
		<td align="right"><input class="btn btn-default" style="width:80px;" type="button" value="<?php echo lang('返回'); ?>" onclick="history.go(-1);"></td>
	</tr>
</tbody></table>
<br />
<div class="msg" style="text-align:center;padding:5px;font-weight:bold;color:#982029"></div>
<form name="form" action="/transaction_add.php" method="post" id="transactionform">
<table class="table table-borderless table_form" width="100%" cellpadding="2" cellspacing="5" border="0" style="padding:5px">
	<?php
	if (isset($_SESSION['class_staff']) && $_SESSION['class_staff'] != 1) {
	?>
	<input type="hidden" name="store_id" value="<?php echo $_SESSION['store_id']; ?>">
	<input type="hidden" name="staff_id" value="<?php echo $_SESSION['staff_id']; ?>">
	<?php
	}
	else {
	?>
				<tr>
					<td><?php echo lang('商店'); ?></td>
					<td><span id="manager">
					<?php echo $inputs->store_id; ?>
					</span></td>
				</tr>
				<tr>
					<td><?php echo lang('销售'); ?></td>
					<td><span id="manager">
					<?php echo $inputs->staff_id; ?>
					</span></td>
				</tr>
				<?php
			}
				?>
				<tr>
					<td><?php echo lang('新客戶'); ?></td>
					<td>
					<?php echo lang('是'); ?> <input type="radio" value="1" name="newcust">
					<?php echo lang('不是'); ?> <input type="radio" value="0" name="newcust" checked>
					</td>
				</tr>
				<tr class="newcust">
					<td><?php echo lang('姓名'); ?></td>
					<td>
						<input class='form-control' type="text" name="name" value="">
					</td>
				</tr>
				<tr class="newcust">
					<td><?php echo lang('出生日期'); ?></td>
					<td>
						<?php echo get_date_dropdown(strtotime('2000-01-01'),'');?>
					</td>
				</tr>
				<tr class="newcust">
					<td>Email</td>
					<td>
						<input class='form-control' type="text" name="email" value="">
					</td>
				</tr>
				<tr class="newcust">
					<td><?php echo lang('手機號碼'); ?> I</td>
					<td>
						<input class='form-control' type="text" name="phone[0]" value="">
					</td>
				</tr>
				<tr class="newcust">
					<td><?php echo lang('手機號碼'); ?> II</td>
					<td>
						<input class='form-control' type="text" name="phone[1]" value="">
					</td>
				</tr>
				<tr class="oldcust">
					<td><?php echo lang('顾客'); ?></td>
					<td><span id="manager">
					<?php echo $inputs->customer_id; ?>
					</span></td>
				</tr>
				<tr class="oldcust2">
					<td><?php echo lang('姓名'); ?></td>
					<td>
						<input class='form-control' type="text" name="oname" value="">
					</td>
				</tr>
				<tr class="oldcust2">
					<td><?php echo lang('出生日期'); ?></td>
					<td>
						<?php echo get_date_dropdown('','o');?>
					</td>
				</tr>
				<tr class="oldcust2">
					<td>Email</td>
					<td>
						<input class='form-control' type="text" name="oemail" value="">
					</td>
				</tr>
				<tr class="oldcust2">
					<td><?php echo lang('手機號碼'); ?> I</td>
					<td>
						<input class="form-control" type="text" name="ophone[0]" value="">
					</td>
				</tr>
				<tr class="oldcust2">
					<td><?php echo lang('手機號碼'); ?> II</td>
					<td>
						<input class="form-control" type="text" name="ophone[1]" value="">
					</td>
				</tr>
				<tr>
				<td colspan="2">
					<br />
					<table border="0" width="100%">
						<tr>
						<td width="10%"><?php echo lang('產品'); ?> : </td>
						<td width="80%"><select name="items" class="form-control"><?php echo select_product(0); ?></select></td>
						<td width="10%"><input class="btn btn-default" type="button" id="addItem" name="addItem" value="<?php echo lang('加入'); ?> <?php echo lang('產品'); ?>"></td>
						</tr>
					</table>
					<br />
<table id="ListItem" width="100%" class="table" cellspacing="0" cellpadding="3" border="0" style="border:solid 1px #cccccc">
				<thead>
	<tr height="30" style="font-weight:bold" bgcolor="#dddddd">
					<td><?php echo lang('產品'); ?></td>
					<td><?php echo lang('價錢'); ?></td>
					<td><?php echo lang('數量'); ?></td>
					<td style="display:none;"><?php echo lang('共'); ?></td>
					<td></td>
				</tr>
				</thead>
				<tbody>
				</tbody>
				</table>
				</td>
				</tr>
				<tr>
				<td colspan="2" style="width:100%"><hr></td>
				</tr>
				<tr>
					<td colspan="2"><?php echo lang('數量'); ?> &nbsp; <?php echo $inputs->tqty; ?></td>
				</tr>
				<tr>
					<td colspan="2" align="right"><?php echo lang('量'); ?>  &nbsp; <span id="tammount"><?php echo $inputs->tammount; ?></span></td>
				</tr>
				<tr>
					<td colspan="2" align="right"><?php echo lang('贴现'); ?>  &nbsp; <span id="tdiscount"><?php echo $inputs->tdiscount; ?></span></td>
				</tr>
				<tr>
					<td colspan="2" align="right"><?php echo lang('共'); ?> &nbsp; <span id="phone2"><?php echo $inputs->ttotal; ?></span></td>
				</tr>
				<tr><td colspan="2"><hr width="100%" size="1" align="left"></td></tr>
				<tr>
					<td style="width:40%"><?php echo lang('付款方式'); ?></td>
					<td><span id="tpayment">
					<?php echo $inputs->tpayment; ?>
					</span></td>
				</tr>
				<tr class="tcardno" style="display:none">
					<td>Card No.</td>
					<td><span id="tcardno">
					<table border="0">
					<tr><td><?php echo $inputs->tcardno; ?></td></tr>
					</table>
					</span></td>
				</tr>
				<tr><td colspan="2"><hr width="100%" size="1" align="left"></td></tr>
				<tr>
					<td colspan="2" style="text-align:center" align="center"><span id="input_submit_button"><input name="submit" type="submit" value="<?php echo lang('確定'); ?>" class="btn btn-default"> </span></td>
				</tr>
</tbody></table>
</form>
<script>
function validate(evt) {
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	key = String.fromCharCode( key );
	var regex = /[0-9]|\./;
	if( !regex.test(key) ) {
		theEvent.returnValue = false;
		if(theEvent.preventDefault) theEvent.preventDefault();
	}
}
shortcut.add("Ctrl+B", function () {history.go(-1); });
$('input[name="addItem"]').click(function(){
	var product = $("select[name=\"items\"] option:selected");
	var productId = product.val();
	var productName = product.text();
	var productPrice = product.attr('price');
	if (productId > 0 && productId != '' && $('tr[idnya="'+productId+'"]').length == 0) {
		var res;
		res = '<tr idnya="'+productId+'">';
		res += '<input type="hidden" value="'+productName+'" name="pname['+productId+']">';
		res += '<td>'+productName+'</td>';
		res += '<td><input type="hidden" value="'+productPrice+'" name="price['+productId+']">$'+productPrice+'</td>';
		res += '<td><input type="number" style="width:50px" nextinput="items" idnya="'+productId+'" value="1" name="qty['+productId+']"></td>';
		res += '<td style="display:none;"><input type="text" style="width:100px" readonly name="totalprice['+productId+']" value="'+productPrice+'"></td>';
		res += '<td><a class="delete_items" idnya="'+productId+'" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
		res += '</tr>';
		$('#ListItem > tbody').append(res);

		var tammount = 0;
		var tdiscount = $('input[name="tdiscount"]').val() == '' ? 0 : parseFloat($('input[name="tdiscount"]').val());
		$('input[name^="totalprice"]').each(function(index) {
			tammount += parseInt($(this).val());
		});
		$('input[name="tammount"]').val(tammount);
		$('input[name="ttotal"]').val(tammount - parseFloat(tdiscount));

		var totalqty = 0;
		$('input[name^="qty"]').each(function(index) {
			totalqty += parseInt($(this).val());
		});

		$('input[name="tqty"]').val(totalqty);

		$('input[name^="qty"]').change(function(){
			var qty = $(this).val();
			var price = $('input[name="price['+$(this).attr('idnya')+']"]').val();
			var totalprice = $('input[name="totalprice['+$(this).attr('idnya')+']"]').val(parseFloat(price * qty));

			var totalqty = 0;
			$('input[name^="qty"]').each(function(index) {
				totalqty += parseInt($(this).val());
			});

			$('input[name="tqty"]').val(totalqty);

			var tammount = 0;
			$('input[name^="totalprice"]').each(function(index) {
				tammount += parseInt($(this).val());
			});
			$('input[name="tammount"]').val(tammount);
			$('input[name="ttotal"]').val(tammount - parseFloat(tdiscount));
		});

		$('input[name="tdiscount"]').keypress(function(){
			return validate();
		});

		$('input[name="tdiscount"]').change(function(){
			var tdiscount = parseFloat($(this).val());
			var tammount = parseFloat($('input[name="tammount"]').val());
			$('input[name="ttotal"]').val(tammount - parseFloat(tdiscount));
		});

		$('a.delete_items').click(function(){
			var result = confirm('Are you sure you want to delete this item?');
			if (result) {
				$('tr[idnya="'+$(this).attr('idnya')+'"]').remove();
			}
		});
	}
})
$('input[name="tpayment"]').click(function(){
	if ($(this).val() == 0 || $(this).val() == 1) {
		$('tr.tcardno').hide();
	}
	else {
		$('tr.tcardno').show();
	}
});
$('input[name="newcust"]').click(function(){
	if ($(this).val() == 0) {
		$('tr.newcust').hide();
		$('tr.oldcust').show();
	}
	else {
		$('tr.newcust').show();
		$('tr.oldcust').hide();
		$('tr.oldcust2').hide();
	}
});
$(document).ready(function(){
	$('#transactionform').on('submit', function(e) {
		<?php
		if (isset($_SESSION['class_staff']) && $_SESSION['class_staff'] != 1) {
		?>
		var store_id = $('input[name="store_id"]').val()
		var staff_id = $('input[name="staff_id"]').val()
		<?php } else { ?>
		var store_id = $('select[name="store_id"]').val()
		var staff_id = $('select[name="staff_id"]').val()
		<?php } ?>
		var tqty = $('input[name="tqty"]').val()
		var tammount = $('input[name="tammount"]').val()
		var ttotal = $('input[name="ttotal"]').val()
		var customer_id = $('input[name="customer_id"]').val()
		var newcust = $('input[name="newcust"]:checked').val()
		var name = $('input[name="name"]').val()
		
		error = null
		$('.msg').html('')
		if (!store_id) {
			error = 'Store must be filled !!!'
		}
		else if (!staff_id) {
			error = 'Staff must be filled !!!'
		}
		else if (newcust == 1 && !name) {
			error = 'Customer must be filled !!!'
		}
		else if (!tqty) {
			error = 'QTY zero !!!'
		}
		else if (!tammount) {
			error = 'Ammount zero, please input qty of product !!!'
		}
		else if (!ttotal) {
			error = 'Total zero, please input qty of product !!!'
		}
		else if (!$('input[name="tpayment"]').is(':checked')) {
			error = 'Please choose payment type !!!'
		}
		else {
			
		}
		
		if (error) {
			e.preventDefault();
			$('.msg').html(error)
			var body = $("html, body");
			body.stop().animate({scrollTop:0}, 500, 'swing', function() {});
		}
		else {
			
		}
	})
})
$('input[name="newcust"]').click();
$('select[name="customer_id"]').change(function(){
	
		$.post( "/ajax_customer.php", { cid: $(this).val(), type: 1 }).done(function( data ) {
			var wew = data.cphone;
			var res = wew.split("*");
			$('input[name="oname"]').val(data.cname);
			$('input[name="oemail"]').val(data.cemail);
			$('input[name="ophone[0]"]').val(res[0]);
			$('input[name="ophone[1]"]').val(res[1]);
			var dt = data.cbirthday;
			var dts = dt.split('-');
			$('select#odd').val(parseInt(dts[2])).trigger('change')
			$('select#omm').val(parseInt(dts[1])).trigger('change')
			$('select#oyyyy').val(parseInt(dts[0])).trigger('change')
			$('tr.oldcust2').show();
	});
});
$('select[name="items"]').on('change', function() {
	var val = $(this).val()
	document.addEventListener("keydown", function(event) {
		if (event.which === 13) {
			$('#addItem').click()
			$('input[idnya="'+val+'"]').focus()
			$('input[idnya="'+val+'"]').on('change keyup keydown', function() {
				document.addEventListener("keydown", function(event) {
					if (event.which === 13) {
						$('select[name="items"]').focus()
					}
				})
			})
		}
	})
})
</script>
<?php include_once "footer.php"; ?>
