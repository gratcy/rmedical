<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->edit))	{	gotoURL("invoice.php"); exit; }

echo "<h3 class='pull-left'>編輯出單</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a onclick='history.go(-1);''>返回 (B)</a></span><br /><br /><br />";

$id					= sql_secure($_GET['id']);

$from_query			= $_GET['from_query'];


if (isset($_POST['cms_update'])) {

	$cms_table			= "invoice";
	$cms_key			= "id";
	$cms_prefix			= "cms";
	include "cms_process.php";

	$cms_table			= "invoice_detail";
	$cms_key			= "id";
	$cms_prefix			= "cms_item";
	include "cms_process.php";

	sql_query("update invoice set staff_class=(select `class` from staff where staff.id=invoice.staff_id), staff_group=(select `group` from staff where staff.id=invoice.staff_id) where id='$id'");

	$invoice			= sql_getObj("select * from invoice where id='$id'");

	sql_query("update invoice_detail set
					discount = price / price_original * 100,
					amount	= quantity * price,
					date_order='$invoice->date_order',
					customer_id='$invoice->customer_id',
					staff_id='$invoice->staff_id',
					staff_class='$invoice->staff_class',
					staff_group='$invoice->staff_group',
					amount_discounted=amount*$invoice->discount/100
					where invoice_id='$id'");

	sql_query("update invoice_detail set
					name = (select name from item where invoice_detail.item_id=item.id)
					where invoice_id='$id' and item_id != '0'");

	sql_query("update invoice set amount_gross=(select sum(amount) from invoice_detail where invoice_id='$id'), balance=(amount_net-deposit), modify_user='$user->id' where id='$id'");
	sql_query("update invoice set amount_net=amount_gross*discount/100, unpaid=amount_gross*discount/100 - ifnull((select sum(amount) from customer_payment_detail where invoice_id=invoice.id), 0) where id='$id'");


	include_once "inventory_invoice_sold.php";
	include_once "site_invoice_sold.php";


	if ($_POST['saveprint'] == 'true') {
		gotoURL("invoice_edit_print.php?id=$id");
		exit;
	}

	alert("編輯記錄成功。");

	gotoURL("invoice.php?$from_query", 0);
	exit;


}



include_once "bin/class_inputs.php";
$inputs				= new Inputs();
$inputs->prefix		= "cms::$id::";
$inputs->add(
			'invoice_id'					, 'text'			, '出單編號'					, '50',
			'date_order'					, 'text'			, '出單日期'					, '10',
			'date_pay'						, 'text_readonly'	, '付款日期'					, '10',
			'customer_id'					, 'select2'	, '客戶'						, '50',
			'staff_id'						, 'select2'	, '簽發人'						, '50',
			'site_id'						, 'select2'	, '銷售地點'					, '50',
			'amount_cash'					, 'text'			, '銷售現金'					, '30',
			'overtime'						, 'text'			, '加班時間'					, '30',
			'deliveryterms'					, 'text'			, '送貨條款'					, '80',
			'paymentterms'					, 'text'			, '付款條款'					, '80',

			'quantity_sum'					, 'text'			, '總數量'						, '10',
			'amount_gross'					, 'text'			, '總額'						, '10',
			'sales_record'					, 'text'			, '銷售額'						, '10',
			'discount'						, 'text'			, '拆扣 (%)'					, '10',
			'amount_net'					, 'text'			, '淨總額'						, '10',
			'deposit'						, 'text'			, '訂金'						, '10',
			'balance'						, 'text'			, '結算'						, '10',
			'unpaid'						, 'text_readonly'	, '未付'						, '10',
			'remark'						, 'textarea'		, '備註'						, '515',
			'submit_button'					, 'submit'			, '確定 (S)'					, '100'
				);



if ($_POST['action'] == 'edit')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from invoice where id='$id'");

$inputs->desc2['overtime']					= " ( 小時 )";

$inputs->options['customer_id']				= sql_getArray("select name, id from customer order by name");
$inputs->options['site_id']					= sql_getArray("select name, id from site order by name");
$inputs->options['staff_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");

$inputs->tag['amount_gross']				= "class=number readonly";
$inputs->tag['discount']					= "class=number style='color:#777777'";
$inputs->tag['amount_net']					= "class=number style='color:#777777'";
$inputs->tag['deposit']						= "class=number";
$inputs->tag['sales_record']				= "class=number";
$inputs->tag['balance']						= "class=number readonly style='color:#777777'";
$inputs->tag['unpaid']						= "class=number readonly style='color:#777777'";


$inputs->tag['invoice_id']					= "class='form-control' style='max-width:60%;' nextinput=cms::$id::date_order";
$inputs->tag['date_order']					= "class='form-control' style='max-width:30%;display:inline-block;' nextinput=input_pulldownmenu_customer_id";
$inputs->tag['customer_id']					= "class='form-control' style='max-width:60%;' nextinput=input_pulldownmenu_staff_id";
$inputs->tag['site_id']						= "class='form-control' style='max-width:60%;' nextinput=input_pulldownmenu_staff_id";
$inputs->tag['staff_id']					= "class='form-control' style='max-width:60%;' nextinput=input_pulldownmenu_add_item_id";
$inputs->tag['amount_cash']					= "class='form-control' style='max-width:60%;' nextinput=cms::$id::overtime";
$inputs->tag['overtime']					= "class='form-control' style='max-width:60%;display:inline-block;' nextinput=input_pulldownmenu_add_item_id";

$inputs->tag['quantity_sum']				= "class='form-control' style='max-width:20%;' nextinput=cms::$id::amount_gross";
$inputs->tag['amount_gross']				= "class='form-control' style='max-width:50%;display:inline-block' nextinput=cms::$id::discount";
$inputs->tag['amount_net']					= "class='form-control' style='max-width:50%;display:inline-block' nextinput=cms::$id::deposit";
$inputs->tag['discount']					= "class='form-control' style='max-width:50%;display:inline-block' nextinput=cms::$id::amount_net";
$inputs->tag['sales_record']					= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['deposit']					= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['balance']					= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['unpaid']					= "class='form-control' style='max-width:50%;display:inline-block'";

$inputs->tag['deliveryterms']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['paymentterms']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['remark']				= "class='form-control' style='max-width:50%;display:inline-block'";

$inputs->tag['submit_button']				= "class=button";

$inputs2				= new Inputs();
$inputs2->add('add_item_id', 'select2', "", "", sql_getArray("select concat(left(barcode, 4), ' - ', name), id from item order by name"), 80);
$inputs2->tag['add_item_id']					= "class='form-control' style='width:50%; display:inline-block'";


$customer_payment_reference					= "";
$customer_payment_info						= sql_getTable("select a.id, a.payment_id, a.date, b.amount as amount from customer_payment a join customer_payment_detail b on a.id=b.customer_payment_id where b.invoice_id='$id'");
foreach ($customer_payment_info as $payment_info) {
	array2obj($payment_info);
	$customer_payment_reference				.= "<a href='customer_payment_edit.php?id=$payment_info->id'>$payment_info->payment_id ($payment_info->date) - \$$payment_info->amount</a><br>";
}
if (empty($customer_payment_reference)) {
	$customer_payment_reference				.= "客戶還未付款";
} else {
	$tag_display					= "style='display:none'";
	$add_product_display					= "style='display:none'";
	$save_button_display					= "style='display:none'";
	$disble_save							= "return true;";
	$disble_print							= "return true;";
}


echo <<<EOS

<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<form id=form name=form action='' method=post onsubmit='nextTab(); return false;'>

<table class='table table-borderless table_form'>

<input type=hidden name=cms_update value=edit>
<input type=hidden name=from_query value='$from_query'>
<input type=hidden name=saveprint value=false>

	<colgroup>
		<col width=120 style="background:''" />
		<col width=360 style="background:''" />
		<col width=160 style="background:''" />
		<col width=160 style="background:''" />
		<col width=100 style="background:''" />
	</colgroup>

	<tr>
		<td width=120 align=right style='vertical-align: middle'>出單編號</td>
		<td>$inputs->invoice_id</td>
		<td colspan=8 align=right style='vertical-align: middle'>出單日期	&nbsp; $inputs->date_order</td>
		<td style='vertical-align: middle;'><i class="fa fa-calendar-o" onclick="show_cal(this, 'cms::$id::date_order');"></i></td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>客戶</td>
		<td>$inputs->customer_id</td>
		<td colspan=9 align=left valign=top style='padding-left:147px;'>客戶付款單：</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>銷售地點</td>
		<td>$inputs->site_id</td>
		<td colspan=9 rowspan=4 align=left valign=top style='padding-left:147px;'>$customer_payment_reference</td>
	</tr>

	<tr>
		<td width=120 align=right style='vertical-align: middle'>推廣員</td>
		<td>$inputs->staff_id</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>現金銷售</td>
		<td>$inputs->amount_cash</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>加班時間</td>
		<td>$inputs->overtime</td>
	</tr>


	<tr>
		<td height=350 colspan=11 valign=top>


<script>

tab_current				= null;
tab_current_content		= null;

function tab_toggle(tab, content) {

	if (!tab_current)				tab_current			= document.getElementById('tab_detail');
	if (!tab_current_content)		tab_current_content	= document.getElementById('tab_content_detail');


	if (tab_current)				tab_current.style.fontWeight		= '';
	if (tab_current)				tab_current.style.backgroundImage	= 'URL(images/list_tab_button_off.jpg)';
	if (tab_current_content)		tab_current_content.style.display	= 'none';

	tab.style.fontWeight			= 'bold';
	tab.style.backgroundImage		= 'URL(images/list_tab_button_on.jpg)';

	content							= document.getElementById(content);
	if (content)
		content.style.display			= '';

	tab_current						= tab;
	tab_current_content				= content;
}

</script>

<table class='table table-borderless' style='margin-bottom: -8px' $tag_display>
	<tr>
    	<td>
            <div class='tab_switch' id=tab_detail	align=center	onclick='tab_toggle(this, "tab_content_detail")'	>詳細資料</div>
            <div class='tab_switch' id=tab_remark	align=center	onclick='tab_toggle(this, "tab_content_remark")'	style='background-image:URL(images/list_tab_button_off.jpg);'>備註</div>
        </td>
   	</tr>
</table>


<table class='table table-borderless tab_switch_content' id='tab_content_detail'>
	<tr>
		<td valign=top>

<table class='table table-borderless' $add_product_display>
	<tr>
		<td>
			新增產品 ：
			$inputs2->add_item_id
			<input id='add_item_submit' class='btn btn-default' type='button' value='確定' style='width:70px;' onclick='
				CSI_load(itemlist, "invoice_edit_item_add.php?invoice_id=$id&id=" + document.getElementById("form").elements.namedItem("add_item_id").value, "", "append");
				document.getElementById("form").elements.namedItem("add_item_id").value = "";
				document.getElementById("form").elements.namedItem("input_pulldownmenu_add_item_id").value = "";
				return true;
				document.getElementById("form").elements.namedItem("input_pulldownmenu_add_item_id").focus();'>
			<input class='btn btn-default' type=button value='新增自訂產品' onclick='
				CSI_load(itemlist, "invoice_edit_item_add.php?invoice_id=$id&custom=1", "", "append");
				'>
		</td>
	</tr>

</table>


<style>
.border_top td {
	border-top	: solid 1px #cccccc;
}
</style>


<table class='table table-borderless'>
	<tr height=30 style='font-weight:bold' bgcolor=#dddddd>
		<td width=100>編號</td>
		<td width=420>物品</td>
		<td width=50>數量</td>
		<td width=50>原價</td>
		<td width=50>單價</td>
		<td width=50>總數</td>
		<td width=40>刪除</td>
	</tr>


EOS;

$items				= sql_getTable("select * from invoice_detail where invoice_id='$id'");
$item_count			= count($items);

$count			= 0;

if (empty($items)) {
	echo "<tr id=empty_item bgcolor=#ffffff height=100><td colspan=10 align=center>暫時沒有物品。</td></tr>";
}

foreach ($items as $item) {

	array2obj($item);

	$item_info						= sql_getObj("select * from item where id='$item->item_id'");

	$item->amount					= round($item->amount, 2);
//	$item->amount_discounted		= round($item->amount_discounted, 2);
	$item->discount					= round($item->discount, 2);

	if ($item->item_id != '0') {

		echo "
				<tr class=border_top id='item_row_$item->id'>
					<td width=100>$item_info->item_id  <input type=hidden name=cms_item::$item->id::item_id	value='$item->item_id'></td>
					<td width=420>$item->name</td>
					<td width=50><input class=number type=text name=cms_item::$item->id::quantity			value='$item->quantity'				size=2 nextinput=cms_item::$item->id::price_original	onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::price_original		value='$item->price_original'		size=2 nextinput=cms_item::$item->id::price></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::price				value='$item->price'				size=2 nextinput=input_pulldownmenu_add_item_id			onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::amount				value='$item->amount'				size=2 readonly style='color:#777777'></td>
					<td width=40><input type=checkbox name='cms_item::$item->id::null'						value='delete'						onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$item->id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
				</tr>
			";
	} else {

		echo "
				<tr class=border_top id='item_row_$item->id'>
					<td width=100>N/A 					<input type=hidden name=cms_item::$item->id::item_id	value='0'></td>
					<td width=420><input type=text			   name=cms_item::$item->id::name				value='$item->name'					size=37 nextinput=cms_item::$item->id::quantity></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::quantity			value='$item->quantity'				size=2 nextinput=cms_item::$item->id::price				onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::price_original		value='$item->price_original'		size=2 nextinput=cms_item::$item->id::price></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::price				value='$item->price'				size=2 nextinput=input_pulldownmenu_add_item_id			onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=50><input class=number type=text name=cms_item::$item->id::amount				value='$item->amount'				size=2 readonly style='color:#777777'></td>
					<td width=40><input type=checkbox name='cms_item::$item->id::null'						value='delete'						onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$item->id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
				</tr>
			";
	}


	$count++;

}





echo <<<EOS

</table>
<div id=itemlist></div>

		</td>
	</tr>
</table>



<table class='table table-borderless tab_switch_content' id='tab_content_remark' style='display:none;'>

	<tr>
		<td width=120 align=right style='vertical-align:middle'>送貨條款</td>
		<td colspan=4>$inputs->deliveryterms</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align:middle'>付款條款</td>
		<td colspan=4>$inputs->paymentterms</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align:middle'>備註</td>
		<td colspan=4>$inputs->remark</td>
	</tr>

</table>

		</td>
	</tr>

	<tr><td colspan=11 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=11 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=11 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td align=right>總數量</td>
		<td colspan="7">$inputs->quantity_sum</td>
		<td width=160 align=right>總額		&nbsp; $inputs->amount_gross</td>
		<td width=160 align=right>銷售額	&nbsp; $inputs->sales_record</td>
		<td><!--<input type=button value='計算 (C)' onclick='calculate();'>--></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="7"></td>
		<td width=160 align=right>折扣		&nbsp; $inputs->discount</td>
		<td width=160 align=right>訂金		&nbsp; $inputs->deposit</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="7"></td>
		<td width=160 align=right>折後數	&nbsp; $inputs->amount_net</td>
		<td width=160 align=right>結算		&nbsp; $inputs->balance</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="7"></td>
		<td></td>
		<td align=right>未付	&nbsp; $inputs->unpaid</td>
		<td></td>
	</tr>

	<tr><td colspan=11 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=11 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=11 height=10 style='padding:0px;'></td></tr>

	<tr $save_button_display>
		<td colspan=11 align=center>
			<input type=button class='btn btn-default' value='確定 (S)' onclick='document.getElementById("form").submit();' class=noprint>
			<input type=button class='btn btn-default' value='儲存及預覽 (P)' onclick='getFormItem("form", "saveprint").value="true"; document.getElementById("form").submit();'  class=noprint>
		</td>
	</tr>


<div id=csi_return class=remark>



</table>


<script>



var input_pulldownmenu_customer_id_value_previous	= document.getElementById('input_pulldownmenu_customer_id_value').value;
document.getElementById('input_pulldownmenu_customer_id_value').onchange = function () {

	if (input_pulldownmenu_customer_id_value_previous == document.getElementById('input_pulldownmenu_customer_id_value').value)
		return;

	CSI_submit("invoice_edit_load_customer.php?cid=" + document.getElementById("form").elements.namedItem("cms::$id::customer_id").value + "&iid=$id");

	input_pulldownmenu_customer_id_value_previous = document.getElementById('input_pulldownmenu_customer_id_value').value;

//	document.getElementById('input_pulldownmenu_staff_id').focus();
//	document.getElementById('input_pulldownmenu_staff_id').select();

}


document.getElementById('input_pulldownmenu_add_item_id_value').onchange = function () {

	document.getElementById('add_item_submit').focus();

}


function calculate_item(itemid, newitem) {

	getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value	= roundNum(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::price' + newitem).value));

	calculate();

}


function calculate() {

	var ids		= new Array();
	var news	= new Array();

	var form	= document.getElementById('form');
	for (var i = 0; i < form.elements.length; i++ ) {
		var item	= form.elements[i];
		var names	= item.name.split('::');
		if (names[0] == 'cms_item' && names[2] == 'quantity') {
			if (names[3] == 'new')
				names[1]	+= ",::new";
			ids.push(names[1]);
		}
	}

	var quantity_sum	= 0;
	var amount_gross	= 0;
	var amount_net		= 0;
	var newitem			= "";

	for (var i = 0; i < ids.length; i++ ) {

		var itemid	= ids[i].split(",");

		newitem		= itemid[1];
		itemid		= itemid[0];

		if (newitem == undefined)
			newitem	= '';

		getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value	= roundNum(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::price' + newitem).value));

		if (getFormItem('form', 'cms_item::' + itemid + '::null' + newitem).checked)			continue;

		quantity_sum	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value);
	console.log('cms_item::' + itemid + '::quantity' + newitem);
		amount_gross	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value);

	}

	getFormItem('form', 'cms::$id::quantity_sum').value		= quantity_sum;
	getFormItem('form', 'cms::$id::amount_gross').value		= roundNum(amount_gross);
	getFormItem('form', 'cms::$id::amount_net').value		= roundNum(amount_gross * parseFloat(getFormItem('form', 'cms::$id::discount').value) / 100);

	getFormItem('form', 'cms::$id::balance').value			= roundNum(getFormItem('form', 'cms::$id::amount_net').value - getFormItem('form', 'cms::$id::deposit').value);

	if (isNaN(parseFloat(getFormItem('form', 'cms::$id::quantity_sum').value)))	getFormItem('form', 'cms::$id::quantity_sum').value		= 0;
	if (isNaN(parseFloat(getFormItem('form', 'cms::$id::amount_gross').value)))	getFormItem('form', 'cms::$id::amount_gross').value		= 0;
	if (isNaN(parseFloat(getFormItem('form', 'cms::$id::amount_net').value)))	getFormItem('form', 'cms::$id::amount_net').value		= 0;
	if (isNaN(parseFloat(getFormItem('form', 'cms::$id::deposit').value)))		getFormItem('form', 'cms::$id::deposit').value			= 0;
	if (isNaN(parseFloat(getFormItem('form', 'cms::$id::balance').value)))		getFormItem('form', 'cms::$id::balance').value			= 0;


}



function getFormItem(formid, itemid) {
	return document.getElementById(formid).elements.namedItem(itemid);
}

calculate();


shortcut.add("Ctrl+S", function () { $disble_save document.getElementById("form").submit(); });
shortcut.add("Ctrl+B", function () { history.go(-1); });
shortcut.add("Ctrl+C", function () { calculate(); });
shortcut.add("Ctrl+P", function () { $disble_print getFormItem("form", "saveprint").value="true"; document.getElementById("form").submit(); });



if (getFormItem('form', 'cms::$id::date_order')) {
	getFormItem('form', 'cms::$id::date_order').focus();
	getFormItem('form', 'cms::$id::date_order').select();
}



</script>



EOS;

include_once "bin/class_csi.php";
$csi			= new CSI();

echo "</form>";

include_once "footer.php";

?>
