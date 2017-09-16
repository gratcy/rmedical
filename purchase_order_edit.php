<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='purchase_order.php'");
if (empty($privilege->edit))	{	gotoURL("purchase_order.php"); exit; }

echo "<h3 class='pull-left'>編輯採購訂單</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a onclick='history.go($history_back);'>返回 (B)</a></span><br /><br /><br />";

$id					= sql_secure($_GET['id']);

$from_query			= $_GET['from_query'];

if (isset($_POST['cms_update'])) {

	$cms_table			= "purchase_order";
	$cms_key			= "id";
	$cms_prefix			= "cms";
	include "cms_process.php";

	$cms_table			= "purchase_order_detail";
	$cms_key			= "id";
	$cms_prefix			= "cms_item";
	include "cms_process.php";



	$purchase_order		= sql_getObj("select * from purchase_order where id='$id'");



	sql_query("update purchase_order_detail set
					amount = quantity * price * discount / 100,
					amount_discounted = amount * $purchase_order->discount / 100,
					date_order='$purchase_order->date_order',
					supplier_id='$purchase_order->supplier_id',
					staff_id='$purchase_order->staff_id',
					staff_class='$purchase_order->staff_class',
					staff_group='$purchase_order->staff_group'
					where purchase_order_id='$id' and item_id != 0");

	sql_query("update purchase_order_detail set
					name = (select name from item where purchase_order_detail.item_id=item.id)
					where purchase_order_id='$id' and item_id != 0");


	sql_query("update purchase_order_detail set
					amount = quantity * price,
					amount_discounted = quantity * price,
					date_order='$purchase_order->date_order',
					supplier_id='$purchase_order->supplier_id',
					staff_id='$purchase_order->staff_id',
					staff_class='$purchase_order->staff_class',
					staff_group='$purchase_order->staff_group'
					where purchase_order_id='$id' and item_id = 0");

//	sql_query("update purchase_order set balance=(amount_net-deposit) where id='$id'");

	if ($cms_update_count > 0) {

		alert("編輯記錄成功。");

		gotoURL("purchase_order.php?$from_query", 0);
		exit;
	}

}



include_once "bin/class_inputs.php";
$inputs				= new Inputs();
$inputs->prefix		= "cms::$id::";
$inputs->add(
			'purchase_order_id'				, 'text'			, '採購單號'					, '50',
			'date_order'					, 'text'			, '出單日期'					, '10',
			'supplier_id'					, 'select2'	, '供應商'						, '50',
			'staff_id'						, 'select2'	, '簽發人'						, '50',
			'deliveryterms'					, 'text'			, '送貨條款'					, '80',
			'paymentterms'					, 'text'			, '付款條款'					, '80',

			'quantity_sum'					, 'text'			, '總數量'						, '10',
			'amount_gross'					, 'text'			, '總額'						, '10',
			'discount'						, 'text'			, '拆扣 (%)'					, '10',
			'amount_net'					, 'text'			, '淨總額'						, '10',
			'deposit'						, 'text'			, '訂金'						, '10',
			'balance'						, 'text'			, '結算'						, '10',
			'remark'						, 'textarea'		, '備註'						, '515',
			'submit_button'					, 'submit'			, '確定'						, '100'
				);



if ($_POST['action'] == 'edit')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from purchase_order where id='$id'");


$inputs->options['supplier_id']				= sql_getArray("select a.name,  a.id from supplier a join class_supplier b on a.`class`=b.id order by a.`class`, a.name");
$inputs->options['staff_id']				= sql_getArray("select a.name,  a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");

$inputs->tag['purchase_order_id']		= "class='form-control' style='max-width:40%;'";
$inputs->tag['supplier_id']					= "class='form-control' style='max-width:40%;'";
$inputs->tag['staff_id']						= "class='form-control' style='max-width:40%;'";
$inputs->tag['date_order']					= "class='form-control' style='max-width:20%;display:inline-block;'";
$inputs->tag['amount_gross']				= "class=number";
$inputs->tag['discount']					= "class=number";
$inputs->tag['amount_net']					= "class=number";
$inputs->tag['deposit']						= "class=number";
$inputs->tag['balance']						= "class=number readonly style='color:#777777'";
$inputs->tag['unpaid']						= "class=number readonly style='color:#777777'";
$inputs->tag['quantity_sum']				= "class='form-control' style='max-width:20%;'";
$inputs->tag['amount_gross']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['amount_net']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['discount']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['deposit']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['balance']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['deliveryterms']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['paymentterms']				= "class='form-control' style='max-width:50%;display:inline-block'";
$inputs->tag['remark']				= "class='form-control' style='max-width:50%;display:inline-block'";

$inputs->tag['submit_button']				= "class=button";

$supplier_id								= $inputs->value['supplier_id'];


$inputs2				= new Inputs();
$inputs2->add('add_item_id', 'select2', "", "", sql_getArray("select concat(item_id, ' - ', name), id from item where supplier_id='$supplier_id' order by name"), 80);
$inputs2->tag['add_item_id']					= "class='form-control' style='width:50%; display:inline-block'";




$history_back			= (!isset($_GET['new'])) ? '-1' : '-2';

echo <<<EOS

<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<table class='table table-borderless table_form'>

<form id=form name=form action='' method=post onsubmit='nextTab(); return false;'>
<input type=hidden name=cms_update value=edit>

	<colgroup>
		<col width=120 style="background:''" />
		<col width=360 style="background:''" />
		<col width=160 style="background:''" />
		<col width=160 style="background:''" />
		<col width=100 style="background:''" />
	</colgroup>

	<tr>
		<td width=120 align=right style='vertical-align: middle'>採購單號</td>
		<td colspan=9>$inputs->purchase_order_id</td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>供應商</td>
		<td>$inputs->supplier_id</td>
		<td colspan=7 align=right style='vertical-align: middle'>出單日期	&nbsp; $inputs->date_order</td>
		<td style='vertical-align: middle;'><i class="fa fa-calendar-o" onclick="show_cal(this, 'cms::$id::date_order');"></i></td>
	</tr>
	<tr>
		<td width=120 align=right style='vertical-align: middle'>簽發人</td>
		<td colspan=9>$inputs->staff_id</td>
	</tr>

	<tr><td colspan=10 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td height=350 colspan=10 valign=top>


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


<div class='tab_switch' id=tab_detail	align=center	onclick='tab_toggle(this, "tab_content_detail")'	>詳細資料</div>
<div class='tab_switch' id=tab_remark	align=center	onclick='tab_toggle(this, "tab_content_remark")'	style='background-image:URL(images/list_tab_button_off.jpg);'>備註</div>


<table class='table table-borderless tab_switch_content' id='tab_content_detail'>
	<tr>
		<td valign=top>

<table class='table table-borderless'>
	<tr>
		<td>
			新增產品 ：
			$inputs2->add_item_id
			<input type=button class='btn btn-default' value='確定' style='width:70px;' onclick='
				CSI_load(itemlist, "purchase_order_item_add.php?purchase_order_id=$id&id=" + document.getElementById("form").elements.namedItem("add_item_id").value, "", "append");
				document.getElementById("form").elements.namedItem("add_item_id").value = "";
				document.getElementById("form").elements.namedItem("input_pulldownmenu_add_item_id").value = "";
				document.getElementById("form").elements.namedItem("input_pulldownmenu_add_item_id").focus();'>
			<input type=button class='btn btn-default' value='新增自訂產品' onclick='
				CSI_load(itemlist, "purchase_order_item_add.php?purchase_order_id=$id&custom=1", "", "append");
				'>
		</td>
	</tr>

</table>


<table class='table table-borderless' bgcolor='#dddddd'>
	<tr height=30 style='font-weight:bold'>
		<td width=100>編號</td>
		<td width=270>物品</td>
		<td width=70>原價</td>
		<td width=70>數量</td>
		<td width=70>單價</td>
		<td width=70>折扣 (%)</td>
		<td width=70>實價</td>
		<td width=70>總數</td>
		<td width=40>刪除</td>
	</tr>

EOS;

$items				= sql_getTable("select * from purchase_order_detail where purchase_order_id='$id'");
$item_count			= count($items);

$count			= 0;
//echo "select * from purchase_order_detail where purchase_order_id='$id'";
if (empty($items)) {
	echo "<tr id=empty_item bgcolor=#ffffff height=100><td colspan=10 align=center>暫時沒有物品。</td></tr>";
}

foreach ($items as $item) {

	array2obj($item);

	$item_info						= sql_getObj("select * from item where id='$item->item_id'");

	$item->amount					= round($item->amount);
	$item->amount_discounted		= round($item->amount_discounted);

	if ($item->item_id != 0) {

		echo "
				<tr height=1 bgcolor=#cccccc><td style='padding:0px' colspan=20></td></tr>
				<tr bgcolor=#ffffff>
					<td width=100>$item_info->item_id</td>
					<td width=270>$item->name</td>
					<td width=70><input class=number type=text name=r$item->id::price1 value='$item->cost_original' size=2 readonly style='color:#777777'></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::quantity value='$item->quantity' size=2></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::price value='$item->price' size=2></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::discount value='$item->discount' size=2></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::amount value='$item->amount' size=2 readonly style='color:#777777'></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::amount_discounted value='$item->amount_discounted' size=2 readonly style='color:#777777'></td>
					<td width=40><input type=checkbox name='cms_item::$item->id::null::delete' value='delete'></td>
				</tr>
			";
	} else {

		echo "
				<tr height=1 bgcolor=#cccccc><td style='padding:0px' colspan=20></td></tr>
				<tr bgcolor=#ffffff>
					<td width=100>N/A</td>
					<td width=270><input type=text name=cms_item::$item->id::name			value='$item->name' size=37></td>
					<td width=70><input class=number type=text name=r$item->id::price1 value='N/A' size=2 readonly style='color:#777777'></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::quantity value='$item->quantity' size=2></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::price value='$item->price' size=2></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::discount value='N/A' size=2 readonly style='color:#777777'></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::amount value='$item->amount' size=2 readonly style='color:#777777'></td>
					<td width=70><input class=number type=text name=cms_item::$item->id::amount_discounted value='$item->amount_discounted' size=2 readonly style='color:#777777'></td>
					<td width=40><input type=checkbox name='cms_item::$item->id::null::delete' value='delete'></td>
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
		<td width=120 align=right>送貨條款</td>
		<td colspan=4>$inputs->deliveryterms</td>
	</tr>
	<tr>
		<td width=120 align=right>付款條款</td>
		<td colspan=4>$inputs->paymentterms</td>
	</tr>
	<tr>
		<td width=120 align=right>備註</td>
		<td colspan=4>$inputs->remark</td>
	</tr>

</table>

		</td>
	</tr>

	<tr><td colspan=10 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=10 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=10 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td align=right style='vertical-align: middle'>總數量</td>
		<td>$inputs->quantity_sum</td>
		<td colspan="5"></td>
		<td width=160 align=right style='vertical-align: middle'>總額		&nbsp; $inputs->amount_gross</td>
		<td width=160 align=right style='vertical-align: middle'>淨總額	&nbsp; $inputs->amount_net</td>
		<td><input class='btn btn-default' type=button value='重新計算' onclick='calculate();'></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="6"></td>
		<td width=160 align=right>折扣	&nbsp; $inputs->discount</td>
		<td width=160 align=right>訂金	&nbsp; $inputs->deposit</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td colspan="6"></td>
		<td></td>
		<td align=right>結算	&nbsp; $inputs->balance</td>
		<td></td>
	</tr>

	<tr><td colspan=10 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=10 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=10 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td colspan=10 align=center><input class='btn btn-default' type=button value='確定 (S)' onclick='document.getElementById("form").submit();'></td>
	</tr>


<div id=csi_return class=remark>



</form>
</table>


<script>



var input_pulldownmenu_supplier_id_value_previous	= document.getElementById('input_pulldownmenu_supplier_id_value').value;
document.getElementById('input_pulldownmenu_supplier_id_value').onchange = function () {
	if (input_pulldownmenu_supplier_id_value_previous == document.getElementById('input_pulldownmenu_supplier_id_value').value)
		return;

	CSI_submit("purchase_order_load_supplier.php?sid=" + document.getElementById("form").elements.namedItem("cms::$id::supplier_id").value + "&poid=$id");

	input_pulldownmenu_supplier_id_value_previous = document.getElementById('input_pulldownmenu_supplier_id_value').value;
}






function stopRKey(evt) {
  var evt = (evt) ? evt : ((event) ? event : null);
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
  if ((evt.keyCode == 13) && (node.type=="text"))  {
  	nextTab();
  	return false;
  }
}


var stopRKey_onkeypress_previous	= document.onkeypress;

function stopRKey_onkeypress(evt) {
	var evt = (evt) ? evt : ((event) ? event : null);
	if (stopRKey_onkeypress_previous)
		stopRKey_onkeypress_previous(evt);
	stopRKey(evt);
}

document.onkeypress = stopRKey_onkeypress;



function calculate() {

	var ids		= new Array();
	var news	= new Array();

	var form	= document.getElementById('form');
	for (var i = 0; i < form.elements.length; i++ ) {
		var item	= form.elements[i];
		var names	= item.name.split('::');
		if (names[0] == 'cms_item' && names[2] == 'quantity') {
			ids.push(names[1]);
			if (names[3] == 'new')
				news[names[1]] = 'new';
		}
	}

	var quantity_sum	= 0;
	var amount_gross	= 0;
	var amount_net		= 0;
	for (var i = 0; i < ids.length; i++ ) {
		var itemid	= ids[i];
		var newitem	= "";
		if (news[itemid] == 'new')
			newitem	= "::new";

		if (getFormItem('form', 'cms_item::' + itemid + '::discount' + newitem).value != 'N/A') {

			getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value				= Math.round(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::price' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::discount' + newitem).value) / 100, 1);
			getFormItem('form', 'cms_item::' + itemid + '::amount_discounted' + newitem).value	= Math.round(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value) * parseFloat(getFormItem('form', 'cms::$id::discount').value) / 100, 1);

			if (getFormItem('form', 'cms_item::' + itemid + '::null::delete').checked)			continue;

			quantity_sum	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value);
			amount_gross	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::price' + newitem).value);
			amount_net		+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount_discounted' + newitem).value);

		} else {

			getFormItem('form', 'cms_item::' + itemid + '::amount_discounted' + newitem).value	= Math.round(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value) * parseFloat(getFormItem('form', 'cms_item::' + itemid + '::price' + newitem).value), 1);
			getFormItem('form', 'cms_item::' + itemid + '::amount' + newitem).value				= getFormItem('form', 'cms_item::' + itemid + '::amount_discounted' + newitem).value;

			if (getFormItem('form', 'cms_item::' + itemid + '::null::delete').checked)			continue;

			quantity_sum	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::quantity' + newitem).value);
			amount_net		+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount_discounted' + newitem).value);

		}

	}

	getFormItem('form', 'cms::$id::quantity_sum').value		= quantity_sum;
	getFormItem('form', 'cms::$id::amount_gross').value		= Math.round(amount_gross, 2);
	getFormItem('form', 'cms::$id::amount_net').value		= Math.round(amount_net, 2);

	getFormItem('form', 'cms::$id::balance').value			= Math.round(getFormItem('form', 'cms::$id::amount_net').value - getFormItem('form', 'cms::$id::deposit').value, 2);

}

function getFormItem(formid, itemid) {
	return document.getElementById(formid).elements.namedItem(itemid);
}




shortcut.add("Ctrl+S", function () {document.getElementById("form").submit(); });
shortcut.add("Ctrl+B", function () {history.go($history_back); });
shortcut.add("Ctrl+C", function () {calculate(); });




</script>



EOS;

include_once "bin/class_csi.php";
$csi			= new CSI();


include "footer.php";

?>
