<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='blasting.php'");
if (empty($privilege->edit))	{	gotoURL("blasting.php"); exit; }

$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$app = isset($_POST['app']) ? (int) $_POST['app'] : 0;
$blasting = isset($_POST['blasting']) ? (int) $_POST['blasting'] : 0;
$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
$msgsms = isset($_POST['msgsms']) ? $_POST['msgsms'] : '';
$schedule = isset($_POST['msg']) ? $_POST['schedule'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';
$testing = isset($_POST['testing']) ? $_POST['testing'] : '';
$rtesting = isset($_POST['testing']) ? $_POST['testing'] : '';
$test = isset($_POST['test']) ? $_POST['test'] : '';

if ($submit || $test) {
	$error = null;
	if (!$subject || !$msg || !$blasting || !$schedule) {
		$error = 'Data you input is incomplete !!!';
	}
	else {
		if ($submit) {
			if ($id) {
				$blast['bschedule'] = date('Y-m-d H:i:s',strtotime($schedule));
				$blast['bsubject'] = addslashes($subject);
				$blast['bcontent'] = addslashes($msg);
				$blast['bsms'] = addslashes($msgsms);
				$blast['bblasting'] = $blasting;
				$blast['bmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
				
				if ($app == 1) {
					$blast['bstatus'] = 1;
					
					$sql = sql_getTable("select * from customer_tab WHERE cstatus=1");
					foreach($sql as $k => $v) {
						$fields = array();
						$fields2 = array();
						
						if ($blasting == 1 || $blasting == 2) {
							if (filter_var($v['cemail'], FILTER_VALIDATE_EMAIL)) {
								$fields['etype'] = 1;
								$fields['euid'] = $v['cid'];
								$fields['eemail'] = $v['cemail'];
								$fields['esubject'] = addslashes($subject);
								$fields['econtent'] = addslashes($msg);
								$fields['edate'] = date("Y-m-d H:i:s");
								$fields['eschedule'] = date('Y-m-d H:i:s',strtotime($schedule));
								$fields['estatus'] = 1;
								$fields['ecreated'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
								sql_query(sql_insert("email_queue_tab", $fields));
							}
						}
						
						if ($blasting == 1 || $blasting == 3) {
							$phone = explode('*', $v['cphone']);
							$fields2['stype'] = 1;
							$fields2['suid'] = $v['cid'];
							$fields2['sphone'] = $phone[0];
							$fields2['smessage'] = addslashes(strip_tags($msgsms));
							$fields2['sdate'] = date("Y-m-d H:i:s");
							$fields2['sschedule'] = date('Y-m-d H:i:s',strtotime($schedule));
							$fields2['sstatus'] = 1;
							$fields2['screated'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
							sql_query(sql_insert("sms_queue_tab", $fields2));
						}
					}
				}
				
				sql_query(sql_update("blasting_tab", $blast, "bid='$id'"));
			}
			else {
				$error = 'Invalid input data !!!';
			}
		}
		else {
			if (!$testing) {
				$error = 'Data you input is incomplete !!!';
			}
			else {
				$testing = str_replace(' ','',$testing);
				$testing = explode(',', $testing);

				foreach($testing as $k => $v) {
					if (filter_var($v, FILTER_VALIDATE_EMAIL)) {
						__send_email($v,$subject,$msg,false);
					}
					else {
						if (is_numeric($v)) {

						}
					}
				}


			}
		}
	}
	if ($submit) {
		if (!empty($error)) {
			echo "<p><font color=red>Error :</font></p>";
			echo "<p>( ".$error." )</p>";
			gotoURL(-1, 3);
			exit;
		}
		else {
			echo "<p><font color=blue>Blasting Queue successfully updated :</font></p>";
			echo "<p>( 3 秒內會自動反回前面，或按 <a href='blasting.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
			gotoURL(-2, 3);
			exit;
		}
	}
	else {
		if (!empty($error)) {
			echo "<p><font color=red>Error :</font></p>";
			echo "<p>( ".$error." )</p>";
		}
		else {
			echo "<p><font color=blue>Testing successfully sent :</font></p>";
			echo "<p>( 3 秒內會自動反回前面，或按 <a href='blasting.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		}
	}
}
else {
	$detail 	= sql_getVar("select * from blasting_tab where bid='$id'");
	$subject = $detail['bsubject'];
	$msg = $detail['bcontent'];
	$msgsms = $detail['bsms'];
	$msgsms = $detail['bsms'];
	$blasting = $detail['bblasting'];
	$schedule = $detail['bschedule'];
}
?>

<script src="js/tinymce/tinymce.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="js/datetimepicker/build/jquery.datetimepicker.min.css"/>
<script src="js/datetimepicker/build/jquery.datetimepicker.full.min.js" type="text/javascript"></script>
<h3 class="pull-left">Blasting</h3><input class="btn btn-default pull-right" type="button" value="返回 (B)" onclick="history.go(-1);"><br /><br /><br />
<form class="form-horizontal form-approved" name="form" action="" method="post">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
				<div class='form-group'>
			    <label for='manager' class='col-sm-2 control-label'>Template Email</label>
					<div class='col-sm-8'>
						<select class="form-control" name="tpl_email">
							<?php echo select_template(0,2,2); ?>
						</select>
					</div>
				</div>
				<div class="form-group">
			    <label for="subject" class="col-sm-2 control-label">Subject</label>
					<div class="col-sm-8">
						<input type="text" name="subject" class="form-control" value="<?php echo $subject; ?>">
					</div>
				</div>
				<div class="form-group">
			    <label for='msg' class='col-sm-2 control-label'>Messages Email</label>
					<div class='col-sm-8'>
						<textarea id="mce" name="msg" class="form-control" ><?php echo $msg; ?></textarea>
					</div>
				</div>
				<div class='form-group'>
			    <label for='manager' class='col-sm-2 control-label'>Template SMS</label>
					<div class='col-sm-8'>
						<select class="form-control" name="tpl_sms">
							<?php echo select_template(0,2,1); ?>
						</select>
					</div>
				</div>
				<div class='form-group'>
			    <label for='msg' class='col-sm-2 control-label'>Messages SMS</label>
					<div class='col-sm-8'>
						<textarea name="msgsms" class="form-control" ><?php echo $msgsms; ?></textarea>
					</div>
				</div>
				<div class='form-group'>
			    <label for='blasting' class='col-sm-2 control-label'>Blasting</label>
					<div class='col-sm-8'>
						<div class="radio">
						  <label>
						    <input type="radio" name="blasting" id="blasting-1" value="1" <?php echo ($blasting == 1 ? 'checked' : ''); ?>>
						    All
						  </label>
						</div>
						<div class="radio">
						  <label>
						    <input type="radio" name="blasting" id="blasting-2" value="2" <?php echo ($blasting == 2 ? 'checked' : ''); ?>>
						    Email
						  </label>
						</div>
						<div class="radio disabled">
						  <label>
						    <input type="radio" name="blasting" id="blasting-3" value="3" <?php echo ($blasting == 3 ? 'checked' : ''); ?>>
						    SMS
						  </label>
						</div>
					</div>
				</div>
				<div class='form-group'>
			    <label for='testing' class='col-sm-2 control-label'>Testing</label>
					<div class='col-sm-8'>
						<textarea id="testing" name="testing" class="form-control" ><?php echo $rtesting; ?></textarea>
					</div>
				</div>
				<div class='form-group'>
			    <label for='schedule' class='col-sm-2 control-label'>Schedule</label>
					<div class='col-sm-8'>
						<input type="text" name="schedule" class="form-control" value="<?php echo $schedule; ?>" id="datetimepicker">
					</div>
				</div>
				<hr />
				<div class='form-group'>
			    <label class='col-sm-2'>&nbsp;</label>
			    <div class="col-sm-8">
			    	<input name="test" class="btn btn-default" type="submit" value="Testing">
						<input name="submit" class="btn btn-default" type="submit" value="Submit (S)">
				<?php if ($_SESSION['root'] == 1) { ?>
						<span id="input_approve_button"><input name="app" type="button" value="Approved (A)" class="btn btn-default" style="width:110px;"> </span>
			  <?php } ?>
			    </div>
			  </div>
				<hr />
				<div class='form-group'>
			    <label class='col-sm-2'>&nbsp;</label>
			    <div class="col-sm-8">
			    	<p><i><b>Note:</b></i></p>
						<p><i>SMS blast will auto remove style and images</i></p>
						<p><i>Testing separate email or phone by comma</i></p>
			    </div>
			  </div>
</form>
<script>
$('input[name="app"]').click(function(){
	$('form.form-approved').append('<input type="hidden" name="app" value="1">');
	$('form.form-approved').submit();
	$('input[name="submit"]').click();
});
$.datetimepicker.setLocale('en');
$('#datetimepicker').datetimepicker();
	tinymce.init({
  selector: '#mce',
  height: 500,
  menubar: false,
  plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table contextmenu paste code'
  ],
  toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
  content_css: '//www.tinymce.com/css/codepen.min.css'
});
shortcut.add("Ctrl+B", function () {history.go(-1); });


$('select[name="tpl_email"]').change(function(){
	$.post( "/ajax_template.php", { bid: $(this).val() }).done(function( data ) {
		tinymce.EditorManager.execCommand('mceRemoveEditor',true, 'mce');
		$('input[name="subject"]').val(data.bsubject)
		$('textarea[name="msg"]').val(data.bcontent)
		tinymce.EditorManager.execCommand('mceAddEditor',true, 'mce');
	});
});

$('select[name="tpl_sms"]').change(function(){
	$.post( "/ajax_template.php", { bid: $(this).val() }).done(function( data ) {
		$('textarea[name="msgsms"]').val(data.bsubject)
	});
});
</script>
<?php
include_once "footer.php";
?>
