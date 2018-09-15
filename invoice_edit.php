<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->edit))	{	gotoURL("invoice.php"); exit; }

echo "<h3 class='pull-left'>".lang('編輯出單')."</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a onclick='history.go(-1);''>".lang('返回')." (B)</a></span><br /><br /><br />";

$id					= sql_secure($_GET['id']);

$from_query			= $_GET['from_query'];

$newcust = (int) $_POST['newcust'];

$oldclass = (int) $_POST['oldclass'];
$oldstaff_id = (int) $_POST['oldstaff_id'];
$oldsite_id = (int) $_POST['oldsite_id'];
$oldname = isset($_POST['oldname']) ? $_POST['oldname'] : '';
$oldaddress = isset($_POST['oldaddress']) ? $_POST['oldaddress'] : '';
$olddelivery_address = isset($_POST['olddelivery_address']) ? $_POST['olddelivery_address'] : '';
$oldtel = isset($_POST['oldtel']) ? $_POST['oldtel'] : '';
$oldfax = isset($_POST['oldfax']) ? $_POST['oldfax'] : '';
$oldemail = isset($_POST['oldemail']) ? $_POST['oldemail'] : '';

$newclass = (int) $_POST['newclass'];
$newstaff_id = (int) $_POST['newstaff_id'];
$newsite_id = (int) $_POST['newsite_id'];
$newname = isset($_POST['newname']) ? $_POST['newname'] : '';
$newaddress = isset($_POST['newaddress']) ? $_POST['newaddress'] : '';
$newdelivery_address = isset($_POST['newdelivery_address']) ? $_POST['newdelivery_address'] : '';
$newtel = isset($_POST['newtel']) ? $_POST['newtel'] : '';
$newfax = isset($_POST['newfax']) ? $_POST['newfax'] : '';
$newemail = isset($_POST['newemail']) ? $_POST['newemail'] : '';

$invid = (int) $_GET['id'];
$oldcustid = (int) $_POST['cms::'.$invid.'::customer_id'];
$newsite_id = (int) $_POST['cms::'.$invid.'::site_id'];
$newstaff_id = (int) $_POST['cms::'.$invid.'::staff_id'];
$sendemail = (int) $_POST['sendemail'];
$custname = '';
$custemail = '';

if (isset($_POST['cms_update'])) {
	$newcustid = 0;
	if ($newcust == 1) {
		$insert = sql_query("INSERT INTO customer (class, staff_id, site_id, name, address, delivery_address, tel, fax, email) VALUES ('$newclass', '$newstaff_id', '$newsite_id', '$newname', '$newaddress', '$newdelivery_address', '$newtel', '$newfax', '$newemail')");
		$newcustid = sql_insert_id();
		$_POST['cms::'.$invid.'::customer_id'] = $newcustid;
		$custname = $newname;
		$custemail = $newemail;
	}
	else {
		$update = sql_query("update customer SET class = '$oldclass', staff_id = '$oldstaff_id', site_id = '$oldsite_id', name = '$oldname', address = '$oldaddress', delivery_address = '$olddelivery_address', tel = '$oldtel', fax = '$oldfax', email = '$oldemail' WHERE id=" . $oldcustid);
		$custname = $oldname;
		$custemail = $oldemail;
	}

	$cms_table			= "invoice";
	$cms_key			= "id";
	$cms_prefix			= "cms";
	include "cms_process.php";

	$cms_table			= "invoice_detail";
	$cms_key			= "id";
	$cms_prefix			= "cms_item";
	include "cms_process.php";

	sql_query("update invoice set staff_class=(select `class` from staff where staff.id=invoice.staff_id), staff_group=(select `group` from staff where staff.id=invoice.staff_id), customer_id='".($newcustid ? $newcustid : $oldcustid)."' where id='$id'");

	$invoice			= sql_getObj("select * from invoice where id='$id'");

	sql_query("update invoice_detail set
					discount = price / price_original * 100,
					amount	= quantity * price,
					date_order='$invoice->date_order',
					customer_id='".($newcustid ? $newcustid : $oldcustid)."',
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

	if ($sendemail === 1 && $invoice->staff_id && !empty($custemail)) {
		$res = '';
		$qty = 0;
		$wew = sql_getTable("select name FROM staff WHERE id=" . $invoice->staff_id);
		$items = sql_getTable("select * FROM invoice_detail WHERE invoice_id=" . $invid);
		foreach($items as $k => $v) {
			$warranty = sql_getTable("select warranty FROM item WHERE id=" . $v['item_id']);
			$res .= '<tr>';
			$res .= '<td>'.$v['name'].'</td>';
			$res .= '<td>'.$v['quantity'].'</td>';
			$res .= '<td>'.($warranty[0]['warranty'] == 1 ? date('Y-m-d',strtotime("+1 year")) : 'No Warranty').'</td>';
			$res .= '<td>'.$v['price'].'</td>';
			$res .= '</tr>';
			$qty += (int) $v['quantity'];
		}
		$res .= '<tr><td><b>Total</b></td><td>'.array_sum($qty).'</td><td></td><td>'.array_sum($price).'</td></tr>';

		$Qdata['dateorder'] = date('Y-m-d H:i:s');
		$Qdata['sales'] = $wew[0]['name'];
		$Qdata['sono'] = $_POST['cms::'.$invid.'::invoice_id'];
		$Qdata['table'] = $res;
		$Qdata['cname'] = $custname;

		__send_email($custemail, 'Rock Trading Transaction', $Qdata,dirname(__FILE__) . '/tpl/transaction.html');
	}

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

$inputs->tag['amount_gross']				= "class=form-control number readonly";
$inputs->tag['discount']					= "class=form-control number style='color:#777777'";
$inputs->tag['amount_net']					= "class=form-control number style='color:#777777'";
$inputs->tag['deposit']						= "class=form-control number";
$inputs->tag['sales_record']				= "class=form-control number";
$inputs->tag['balance']						= "class=form-control number readonly style='color:#777777'";
$inputs->tag['unpaid']						= "class=form-control number readonly style='color:#777777'";


$inputs->tag['invoice_id']					= "class='form-control' style='max-width:34.5%;' nextinput=cms::$id::date_order";
$inputs->tag['date_order']					= "class='form-control' style='max-width:34.5%;display:inline-block;' nextinput=input_pulldownmenu_customer_id";
$inputs->tag['customer_id']					= "class='form-control' style='max-width:60%;' nextinput=input_pulldownmenu_staff_id";
$inputs->tag['site_id']						= "class='form-control' style='max-width:60%;' nextinput=input_pulldownmenu_staff_id";
$inputs->tag['staff_id']					= "class='form-control' style='max-width:60%;' nextinput=input_pulldownmenu_add_item_id";
$inputs->tag['amount_cash']					= "class='form-control' style='max-width:60%;' nextinput=cms::$id::overtime";
$inputs->tag['overtime']					= "class='form-control' style='max-width:60%;display:inline-block;' nextinput=input_pulldownmenu_add_item_id";

$inputs->tag['quantity_sum']				= "class='form-control' style='max-width:75%;' nextinput=cms::$id::amount_gross";
$inputs->tag['amount_gross']				= "class='form-control' style='max-width:70%;display:inline-block' nextinput=cms::$id::discount";
$inputs->tag['amount_net']					= "class='form-control' style='max-width:70%;display:inline-block' nextinput=cms::$id::deposit";
$inputs->tag['discount']					= "class='form-control' style='max-width:70%;display:inline-block' nextinput=cms::$id::amount_net";
$inputs->tag['sales_record']					= "class='form-control' style='max-width:70%;display:inline-block'";
$inputs->tag['deposit']					= "class='form-control' style='max-width:70%;display:inline-block'";
$inputs->tag['balance']					= "class='form-control' style='max-width:70%;display:inline-block'";
$inputs->tag['unpaid']					= "class='form-control' style='max-width:70%;display:inline-block'";

$inputs->tag['deliveryterms']				= "class='form-control' style='max-width:75%;display:inline-block'";
$inputs->tag['paymentterms']				= "class='form-control' style='max-width:75%;display:inline-block'";
$inputs->tag['remark']				= "class='form-control' style='max-width:75%;display:inline-block'";

$inputs->tag['submit_button']				= "class=button";

$inputs2				= new Inputs();
$inputs2->add('add_item_id', 'select2', "", "", sql_getArray("select concat(left(barcode, 4), ' - ', name), id from item WHERE status!='deleted' AND (barcode is not null OR barcode!='') order by name ASC"), 80);
$inputs2->tag['add_item_id']					= "class='form-control' style='width:50%; display:inline-block'";

$inputs3				= new Inputs();
$inputs3->add(
			'oldname'							, 'text'			, '名稱'						, '100%',
			'oldclass'							, 'select'	, '類別'						, '100%',
			'oldstaff_id'						, 'select2'	, '組別'						, '100%',
			'oldsite_id'						, 'select2'	, '銷售地點'					, '100%',
			'oldaddress'						, 'textarea'		, '地址'						, '100%',
			'olddelivery_address'				, 'textarea'		, '送貨地址'					, '100%',
			'oldtel'							, 'text'			, '電話'						, '100%',
			'oldfax'							, 'text'			, '傳真'						, '100%',
			'oldemail'							, 'text'			, '電郵'						, '100%',
			'newname'							, 'text'			, '名稱'						, '100%',
			'newclass'							, 'select'	, '類別'						, '100%',
			'newstaff_id'						, 'select2'	, '組別'						, '100%',
			'newsite_id'						, 'select2'	, '銷售地點'					, '100%',
			'newaddress'						, 'textarea'		, '地址'						, '100%',
			'newdelivery_address'				, 'textarea'		, '送貨地址'					, '100%',
			'newtel'							, 'text'			, '電話'						, '100%',
			'newfax'							, 'text'			, '傳真'						, '100%',
			'newemail'							, 'text'			, '電郵'						, '100%'
				);

$inputs3->options['oldclass']					= sql_getArray("select description, id from class_customer order by description asc");
$inputs3->options['oldstaff_id']				= sql_getArray("select name, id from staff order by name asc");
$inputs3->options['oldsite_id']					= sql_getArray("select name, id from site order by name asc");

$inputs3->options['newclass']					= sql_getArray("select description, id from class_customer order by description asc");
$inputs3->options['newstaff_id']				= sql_getArray("select name, id from staff order by name asc");
$inputs3->options['newsite_id']					= sql_getArray("select name, id from site order by name asc");

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

$newCust = lang('新客戶');
$newYes = lang('是');
$newNo = lang('不是');
$addProduct = lang('新增產品');
$totalAmount = lang('總數量');
$AddItem = lang('確定');
$AddCustomItem = lang('新增自訂產品');
$submitAndSendEmail = lang('確定担发送邮件');
$completeOrder = lang('詳細資料');
$simpleOrder = lang('備註');
$saveAndPreview = lang('儲存及預覽');
echo <<<EOS

<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>
<style>
@media screen and (max-width: 767px) {
	.resp-mobile-total {
		display: contents
		float: right;
	}
	.resp-mobile-total > input {
		margin-bottom: 10px;
		width: 68px;
	}
}
</style>
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
		<td colspan=11>$inputs->invoice_id</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>出單日期</td>
		<td colspan=11>$inputs->date_order <i class="fa fa-calendar-o" onclick="show_cal(this, 'cms::$id::date_order');"></i></td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>$newCust</td>
		<td>
		$newYes <input type="radio" value="1" name="newcust">
		$newNo <input type="radio" value="0" name="newcust" checked>
		<td>
		<td colspan=9 align=left valign=top></td>
	</tr>
	
	<tr class="oldcust">
		<td width=120 align=right style='vertical-align: middle'>客戶</td>
		<td>$inputs->customer_id</td>
		<td colspan=10 align=left valign=top>客戶付款單：</td>
	</tr>

	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>名稱</td>
		<td>$inputs3->oldname</td>
		<td colspan=10 align=left valign=top></td>
	</tr>

	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>類別</td>
		<td>$inputs3->oldclass</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>組別</td>
		<td>$inputs3->oldstaff_id</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>銷售地點</td>
		<td>$inputs3->oldsite_id</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>地址</td>
		<td>$inputs3->oldaddress</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>送貨地址</td>
		<td>$inputs3->olddelivery_address</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>電話</td>
		<td>$inputs3->oldtel</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>傳真</td>
		<td>$inputs3->oldfax</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="oldcust2">
		<td width=120 align=right style='vertical-align: middle'>電郵</td>
		<td>$inputs3->oldemail</td>
		<td colspan=10 align=left valign=top></td>
	</tr>


	<tr class="newcust">
		<td width=120 align=right style='vertical-align: middle'>名稱</td>
		<td>$inputs3->newname</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="newcust">
		<td width=120 align=right style='vertical-align: middle'>地址</td>
		<td>$inputs3->newaddress</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="newcust">
		<td width=120 align=right style='vertical-align: middle'>送貨地址</td>
		<td>$inputs3->newdelivery_address</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="newcust">
		<td width=120 align=right style='vertical-align: middle'>電話</td>
		<td>$inputs3->newtel</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="newcust">
		<td width=120 align=right style='vertical-align: middle'>傳真</td>
		<td>$inputs3->newfax</td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr class="newcust">
		<td width=120 align=right style='vertical-align: middle'>電郵</td>
		<td>$inputs3->newemail</td>
		<td colspan=10 align=left valign=top></td>
	</tr>

	<tr class="separatorcust" style="border-top:3px solid #ccc">
		<td width=120 align=right style='vertical-align: middle'></td>
		<td></td>
		<td colspan=10 align=left valign=top></td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>銷售地點</td>
		<td>$inputs->site_id</td>
		<td colspan=10 rowspan=4 align=left valign=top>$customer_payment_reference</td>
	</tr>

	<tr >
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
		<td height=350 colspan=12 valign=top>


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
            <div class='tab_switch' id=tab_detail	align=center	onclick='tab_toggle(this, "tab_content_detail")'	>$completeOrder</div>
            <div class='tab_switch' id=tab_remark	align=center	onclick='tab_toggle(this, "tab_content_remark")'	style='background-image:URL(images/list_tab_button_off.jpg);'>$simpleOrder</div>
        </td>
   	</tr>
</table>


<table class='table table-borderless tab_switch_content' id='tab_content_detail'>
	<tr>
		<td valign=top>

<table class='table table-borderless' $add_product_display>
	<tr>
		<td>$addProduct ：</td>
			<td style="width:75%">$inputs2->add_item_id</td>
			<td><input id='add_item_submit' class='btn btn-default' type='button' value='$AddItem' style='width:70px;' onclick='
				CSI_load(itemlist, "invoice_edit_item_add.php?invoice_id=$id&id=" + document.getElementById("form").elements.namedItem("add_item_id").value, "", "append");
				document.getElementById("form").elements.namedItem("add_item_id").value = "";
				return true;'>
			<input class='btn btn-default' type=button value='$AddCustomItem' onclick='CSI_load(itemlist, "invoice_edit_item_add.php?invoice_id=$id&custom=1", "", "append");'>
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
					<td width=350>$item->name</td>
					<td width=60><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::quantity			value='$item->quantity'				size=2 nextinput=cms_item::$item->id::price_original	onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=80><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::price_original		value='$item->price_original'		size=2 nextinput=cms_item::$item->id::price></td>
					<td width=80><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::price				value='$item->price'				size=2 nextinput=add_item_id			onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=80><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::amount				value='$item->amount'				size=2 readonly style='color:#777777'></td>
					<td width=40><input type=checkbox name='cms_item::$item->id::null'						value='delete'						onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$item->id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
				</tr>
			";
	} else {

		echo "
				<tr class=border_top id='item_row_$item->id'>
					<td width=100>N/A 					<input type=hidden name=cms_item::$item->id::item_id	value='0'></td>
					<td width=350><input style='min-width:60px' type=text			   name=cms_item::$item->id::name				value='$item->name'					size=37 nextinput=cms_item::$item->id::quantity></td>
					<td width=60><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::quantity			value='$item->quantity'				size=2 nextinput=cms_item::$item->id::price_original				onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=80><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::price_original		value='$item->price_original'		size=2 nextinput=cms_item::$item->id::price></td>
					<td width=80><input style='min-width:60px' class=form-control number type=text name=cms_item::$item->id::price				value='$item->price'				size=2 nextinput=add_item_id			onblur='calculate_item(\"$item->id\", \"\");'></td>
					<td width=80><input class=form-control number type=text name=cms_item::$item->id::amount				value='$item->amount'				size=2 readonly style='color:#777777'></td>
					<td width=40><input type=checkbox name='cms_item::$item->id::null'						value='delete'						onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$item->id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
				</tr>
			";
	}


	$count++;

}





echo <<<EOS

</table>
<div id='itemlist' style="margin-top: -20px"></div>

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

	<tr><td colspan=12 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=12 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=12 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td style="text-align:right;">$totalAmount</td>
		<td colspan="7">$inputs->quantity_sum</td>
		<td></td>
		<td style="text-align:right;" class="resp-mobile-total">總額		&nbsp; $inputs->amount_gross</td>
		<td colspan="2" style="text-align:right;" class="resp-mobile-total">銷售額	&nbsp; $inputs->sales_record</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="8"></td>
		<td style="text-align:right;" class="resp-mobile-total">折扣		&nbsp; $inputs->discount</td>
		<td colspan="2" align=right class="resp-mobile-total">訂金		&nbsp; $inputs->deposit</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="8"></td>
		<td style="text-align:right;" class="resp-mobile-total">折後數	&nbsp; $inputs->amount_net</td>
		<td colspan="2" align=right class="resp-mobile-total">結算		&nbsp; $inputs->balance</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="8"></td>
		<td class="resp-mobile-total">&nbsp</td>
		<td colspan="2"  align=right class="resp-mobile-total">未付	&nbsp; $inputs->unpaid</td>
	</tr>

	<tr><td colspan=12 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=12 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=12 height=10 style='padding:0px;'></td></tr>

	<tr $save_button_display>
		<td colspan=12 align=center>
			<input type=button id="SaveAndSendEmail" class='btn btn-default' value='$submitAndSendEmail (S & E)' class=noprint>
			<input type=button class='btn btn-default' value='$AddItem (S)' onclick='document.getElementById("form").submit();' class=noprint>
			<input type=button class='btn btn-default' value='$saveAndPreview (P)' onclick='getFormItem("form", "saveprint").value="true"; document.getElementById("form").submit();'  class=noprint>
		</td>
	</tr>


<div id=csi_return class=remark>



</table>


<script type="text/javascript">
function calculate_item(itemid, newitem) {
	var ck = $('#form [name="cms_item::' + itemid + '::amount' + newitem + '"]').val()
	if (ck) {
	 	getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value	= roundNum(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::price' + newitem).value));

		calculate();
	}
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
<script type="text/javascript">
$(document).ready(function(){
	$('#SaveAndSendEmail').click(function() {
		$('#form').append('<input type="hidden" value="1" name="sendemail">');
		$('#form').submit()
		document.getElementById("form").submit();
	})

	$('input[name="newcust"]').click(function(){
		$('tr.separatorcust').show();
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

	$('select[name="<?php echo $inputs->prefix; ?>customer_id"]').change(function(){
		if ($(this).val()) {
			$.post( "/ajax_customer.php", { cid: $(this).val(), type: 2 }).done(function( data ) {
				$('input[name="oldname"]').val(data.name);
				$('select[name="oldclass"]').val(data.class).change();
				$('select[name="oldstaff_id"]').val(data.staff_id).change();
				$('select[name="oldsite_id"]').val(data.site_id).change();
				$('textarea[name="oldaddress"]').val(data.address);
				$('textarea[name="olddelivery_address"]').val(data.delivery_address);
				$('input[name="oldfax"]').val(data.fax);
				$('input[name="oldtel"]').val(data.tel);
				$('input[name="oldemail"]').val(data.email);
				$('tr.oldcust2').show();
			});
		}
	});

	$('select[name="add_item_id"]').on('change', function() {
		document.addEventListener("keydown", function(event) {
			if (event.which === 13) {
				$('#add_item_submit').click()
			}
		})
	})
	$('select[name="<?php echo $inputs->prefix; ?>customer_id"]').change()
})
</script>
