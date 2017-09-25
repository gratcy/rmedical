<?php

include_once "header.php";
//error_reporting(E_ALL);
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customer_payment.php'");
if (empty($privilege->edit))	{	gotoURL("customer_payment.php"); exit; }


echo "<h3 class='pull-left'>編輯客戶付款</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a onclick='history.go(-1);''>返回 (B)</a></span><br /><br /><br />";

$id		= sql_secure($_GET['id']);

$from_query			= $_GET['from_query'];

if ($_POST['action'] == 'edit') {

	$cms_table			= "customer_payment";
	$cms_key			= "id";
	include "cms_process.php";

	$invoices1			= sql_getArray("select invoice_id from customer_payment_detail where customer_payment_id='$id'");

	$cms_table			= "customer_payment_detail";
	$cms_key			= "id";
	$cms_prefix			= "cms_item";
	include "cms_process.php";


	$customer_payment	= sql_getObj("select * from customer_payment where id='$id'");
	$invoices2			= sql_getArray("select invoice_id from customer_payment_detail where customer_payment_id='$id'");
	$invoices			= array_merge($invoices1, $invoices2);

	foreach ($invoices as $invoice) {
		sql_query("update invoice set unpaid=amount_net - ifnull((select sum(amount) from customer_payment_detail where invoice_id=invoice.id), 0), date_pay='$customer_payment->date' where id='$invoice'");
		sql_query("update invoice set status='freeze' where id='$invoice' and unpaid=0");
	}
	sql_query("update customer_payment set amount = (select sum(amount) from customer_payment_detail where customer_payment_id=customer_payment.id) where id='$id'");


//		echo "<p><font color=blue>編輯付款成功 : $item_no</font></p>";
//		echo "<p>( 3 秒內會自動反回前面，或按 <a href='customer_payment.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
//		gotoURL(-2, 3);
//		exit;

	alert("編輯記錄成功。");
	gotoURL("customer_payment.php?$from_query", 0);
	exit;

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->prefix		= "cms::$id::";
$inputs->add(
			'payment_id'					, 'text'			, '編號'						, '50',
			'refno'							, 'text'			, '參考資料'					, '50',
			'date'							, 'text'			, '付款日期'					, '10',
			'date_begin'					, 'text'			, '開始日期'					, '10',
			'date_end'						, 'text'			, '結束日期'					, '10',
			'customer_id'					, 'select2'	, '客戶'						, '50',
			'staff_id'						, 'select2'	, '簽發人'						, '50',

			'amount'						, 'text'			, '總額'						, '10',
			'remark'						, 'textarea'		, '備註'						, '515',
			'status'						, 'hidden'			, ' '							, '0',
			'submit'						, 'submit'			, '確定'						, '100'
				);






if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from customer_payment where id='$id'");

$inputs->value['status']					= "";

$inputs->options['customer_id']				= sql_getArray("select a.name, a.id from customer a join class_customer b on a.`class`=b.id order by a.`class`, a.name");
$inputs->options['staff_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");


$inputs->tag['amount']						= "class=number readonly style='color:#777777'";
$inputs->tag['payment_id']					= "class='form-control' style='width:50%;'";
$inputs->tag['date']					= "class='form-control' style='width:60%;display:inline-block;'";
$inputs->tag['refno']					= "class='form-control' style='width:50%;'";
$inputs->tag['customer_id']					= "class='form-control' style='width:29.5%;'";
$inputs->tag['staff_id']					= "class='form-control' style='width:29.5%;'";
$inputs->tag['remark']					= "class='form-control' style='width:29.5%;'";
$inputs->tag['date_begin']					= "class='form-control' style='width:20%;display:inline-block;margin-right:5px;'";
$inputs->tag['date_end']					= "class='form-control' style='width:20%;display:inline-block;margin-right:5px;'";

$inputs->tag['amount']					= "class='form-control' style='width:50%;display:inline-block'";

$customer_id								= $inputs->value['customer_id'];




$inputs2				= new Inputs();
$inputs2->add('add_invoice_id', 'select2', "", "", sql_getArray("select concat(invoice_id, ' ( ', date_order, ' ) - 未付 \$', unpaid), id from invoice where customer_id='$customer_id' and unpaid != 0 order by date_order asc"), 50);
$inputs2->tag['add_invoice_id']					= "class='form-control' style='width:50%; display:inline-block'";




echo <<<EOS

<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<div class='table-responsive'>
<table class='table table-borderless table_form'>

<form name=form id=form action='' method=post>
<input type=hidden name=action value=edit>
$inputs->status

	<tr>
		<td align=right width=100 style='vertical-align:middle'>付款編號</td>
		<td>$inputs->payment_id</td>
		<td></td>
		<td align=right style='vertical-align:middle'>付款日期	&nbsp; $inputs->date</td>
		<td style='vertical-align: middle;'><i class="fa fa-calendar-o" onclick="show_cal(this, 'date_order');"></i></td>
	</tr>
	<tr>
		<td align=right width=100 style='vertical-align:middle'>參考資料</td>
		<td>$inputs->refno</td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td align=right width=100 style='vertical-align:middle'>客戶</td>
		<td colspan=4>$inputs->customer_id</td>
	</tr>
	<tr>
		<td align=right width=100 style='vertical-align:middle'>簽發人</td>
		<td colspan=4>$inputs->staff_id</td>
	</tr>
	<tr>
		<td align=right style='vertical-align:middle'>開始日期</td>
		<td>$inputs->date_begin <i class="fa fa-calendar-o" onclick="show_cal(this, 'cms::$id::date_begin');"></i>
			&nbsp; &nbsp; &nbsp;
			結束日期 $inputs->date_end <i class="fa fa-calendar-o" onclick="show_cal(this, 'cms::$id::date_end');"></i>
			&nbsp; &nbsp;
			<input class='btn btn-default' type=button value='計算帳單' onclick='CSI_load(itemlist, "customer_payment_edit_item_add.php?cp_id=$id&c_id=" + document.getElementById("form").elements.namedItem("input_pulldownmenu_customer_id_value").value + "&date_start=" + document.getElementById("form").elements.namedItem("cms::$id::date_begin").value + "&date_end=" + document.getElementById("form").elements.namedItem("cms::$id::date_end").value, "", "append");'>
		</td>
		<td colspan="3"></td>
	</tr>
	<tr>
		<td align=right width=100 style='vertical-align:middle'>備註</td>
		<td colspan=4>$inputs->remark</td>
	</tr>

	<tr><td colspan=5 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=5 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=5 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td colspan=5 height=300 valign=top>




<table class='table table-borderless'>
	<tr>
		<td>新增帳單 ：</td>
			<td style="width:75%">$inputs2->add_invoice_id</td>
			<td>
			<input class='btn btn-default' type=button value='確定' onclick='CSI_load(itemlist, "customer_payment_edit_item_add.php?cp_id=$id&id=" + document.getElementById("form").elements.namedItem("add_invoice_id").value, "", "append"); document.getElementById("form").elements.namedItem("add_invoice_id").value = ""; document.getElementById("form").elements.namedItem("input_pulldownmenu_add_invoice_id").value = "";'>
			<input class='btn btn-default' type=button value='清除記錄'	onclick='clear_record();'>
		</td>
	</tr>
</table>


<style>
.border_top td {
	background-color	:	white;
	border-top			: solid 1px #cccccc;
}
</style>


<table class='table table-borderless' bgcolor='#dddddd'>
	<tr height=30 style='font-weight:bold'>
		<td width=100>編號</td>
		<td width=100>日期</td>
		<td width=80>淨總額</td>
		<td width=80>訂金</td>
		<td width=80>結算</td>
		<td width=80>未付</td>
		<td width=80>付款數額</td>
		<td width=40>刪除</td>
	</tr>

EOS;


$items				= sql_getTable("select * from customer_payment_detail where customer_payment_id='$id'");
$item_count			= count($items);

$count				= 0;

$empty_item_display	= (empty($items)) ? "" : "style='display:none'";


echo "<tr id=empty_item bgcolor=#ffffff height=100 $empty_item_display><td colspan=20 align=center>暫時沒有記錄。</td></tr>";

foreach ($items as $item) {

	array2obj($item);

	$invoice_info					= sql_getObj("select * from invoice where id='$item->invoice_id'");

	echo "
			<tr class=border_top id='item_row_$item->id' clearrecord='cms_item::$item->id::null'>
				<td>$invoice_info->invoice_id</td>
				<td>$invoice_info->date_order</td>
				<td><input class=number type=text name=r$item->id::amount_net	value='$invoice_info->amount_net' size=5 readonly style='color:#777777'></td>
				<td><input class=number type=text name=r$item->id::deposit		value='$invoice_info->deposit' size=5 readonly style='color:#777777'></td>
				<td><input class=number type=text name=r$item->id::balance		value='$invoice_info->balance' size=5 readonly style='color:#777777'></td>
				<td><input class=number type=text name=r$item->id::unpaid		value='$invoice_info->unpaid' size=5 readonly style='color:#777777'></td>
				<td><input class=number type=text name=cms_item::$item->id::amount value='$item->amount' size=5></td>
				<td><input type=checkbox name='cms_item::$item->id::null'		value='delete'		onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$item->id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'>
			</tr>
		";


	$count++;

}





echo <<<EOS

</table>
<div id=itemlist></div>


		</td>
	</tr>

	<tr><td colspan=5 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=5 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=5 height=10 style='padding:0px;'></td></tr>


	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td align=right>總額	&nbsp; $inputs->amount</td>
		<td  class=noprint><input class='btn btn-default' type=button value='重新計算' onclick='calculate();'></td>
	</tr>

	<tr><td colspan=5 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=5 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=5 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td colspan=5 align=center  class=noprint><input class='btn btn-default' type=submit value='確定 (S)'></td>
	</tr>


<div id=csi_return class=remark>



</form>
</table>
</div>


<script>

var input_pulldownmenu_customer_id_value_previous	= document.getElementById('input_pulldownmenu_customer_id_value').value;
document.getElementById('input_pulldownmenu_customer_id_value').onchange = function () {
	if (input_pulldownmenu_customer_id_value_previous == document.getElementById('input_pulldownmenu_customer_id_value').value)
		return;

	CSI_submit("customer_payment_load_customer.php?cid=" + document.getElementById("form").elements.namedItem("cms::$id::customer_id").value);

	document.getElementById("form").elements.namedItem("add_invoice_id").value = "";
	document.getElementById("form").elements.namedItem("input_pulldownmenu_add_invoice_id").value = "";

	input_pulldownmenu_customer_id_value_previous = document.getElementById('input_pulldownmenu_customer_id_value').value;
}


function clear_record() {

    var allitem = document.getElementsByTagName("*");

    for (i=0; i < allitem.length; i++) {
        obj   = allitem[i];
        if (obj.getAttribute('clearrecord') != null) {
        	getFormItem("form", obj.getAttribute('clearrecord')).checked = true;
			obj.style.display		= "none";
    	}
    }

    document.getElementById("empty_item").style.display		= "block";

	calculate();

}


function calculate() {

	var ids		= new Array();
	var news	= new Array();

	var form	= document.getElementById('form');
	for (var i = 0; i < form.elements.length; i++ ) {
		var item	= form.elements[i];
		var names	= item.name.split('::');
		if (names[0] == 'cms_item' && names[2] == 'amount') {
			ids.push(names[1]);
			if (names[3] == 'new')
				news[names[1]] = 'new';
		}
	}

	var amount			= 0;
	for (var i = 0; i < ids.length; i++ ) {
		var itemid	= ids[i];
		var newitem	= "";
		if (news[itemid] == 'new')
			newitem	= "::new";

		if (getFormItem('form', 'cms_item::' + itemid + '::null' + newitem).checked)			continue;

		amount			+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value);

	}

	getFormItem('form', 'cms::$id::amount').value			= amount;

}

function getFormItem(formid, itemid) {
	return document.getElementById(formid).elements.namedItem(itemid);
}




shortcut.add("Ctrl+S", function () { document.getElementById("form").submit(); });
shortcut.add("Ctrl+B", function () { history.go(-1); });



</script>



EOS;

include_once "bin/class_csi.php";
$csi			= new CSI();


include "footer.php";

?>
