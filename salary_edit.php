<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='salary.php'");
if (empty($privilege->edit))	{	gotoURL("salary.php"); exit; }


echo "<h3>編輯佣金記錄</h3><br>";

$id					= sql_secure($_GET['id']);

$from_query			= isset($_GET['from_query']) ? $_GET['from_query'] : '';

if ($_POST['action'] == 'edit') {

	$cms_table			= "salary";
	$cms_key			= "id";
	include "cms_process.php";

	$cms_table			= "salary_detail";
	$cms_key			= "id";
	$cms_prefix			= "cms_item";
	include "cms_process.php";

	sql_query("update salary set amount_sales=(select sum(amount_sales) from salary_detail where salary_detail.salary_id = salary.id) where id='$id'");

	if (contain($_POST['save'], "(P)")) {
		gotoURL("salary_edit_print.php?id=$id&from_query=$from_query");
		exit;
	}

	echo "<p><font color=blue>編輯付款成功 : $item_no</font></p>";
	echo "<p>( 3 秒內會自動反回前面，或按 <a href='salary.php?$from_query'> &lt; 這裡 &gt; </a> 返回。 )</p>";
	gotoURL("salary.php?$from_query", 3);
	exit;

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->prefix		= "cms::$id::";
$inputs->add(
			'salary_id'						, 'text'			, '編號'						, '50',
			'date_issue'					, 'text'			, '簽發日期'					, '10',
			'date_begin'					, 'text'			, '開始日期'					, '10',
			'date_end'						, 'text'			, '完結日期'					, '10',
			'issueby_id'					, 'select2'	, '簽發人'						, '50',
			'staff_id'						, 'select2'	, '推廣員'						, '50',
			'amount_sales'					, 'text'			, '總銷售額'					, '10',
			'salary'						, 'text'			, '基本薪金'					, '10',
			'commission'					, 'text'			, '佣金'						, '10',
			'amount'						, 'text'			, '佣金總額'					, '10',
			'remark'						, 'textarea'		, '備註'						, '400',
			'submit_button'					, 'submit'			, '確定 (S)'					, '100'
				);





if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from salary where id='$id'");

$inputs->options['issueby_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");
$inputs->options['staff_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");



$inputs->tag['salary']						= "class=number";
$inputs->tag['commission']					= "class=number";
$inputs->tag['amount_sales']				= "class=number readonly style='color:#777777'";
$inputs->tag['amount']						= "class=number readonly style='color:#777777'";
$inputs->tag['submit_button']				= "class=button";


$staff_id									= $inputs->value['staff_id'];




$inputs2									= new Inputs();
$inputs2->add('add_invoice_id', 'select2', "", "", sql_getArray("select concat(invoice_id, ' ( ', date_order, ' ) - \$', amount_net), id from invoice where staff_id='$staff_id' order by date_order asc"), '100%');
$inputs2->add('select_date_start', 'text', "", "", "", 10);
$inputs2->add('select_date_end', 'text', "", "", "", 10);




echo <<<EOS

<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>


<table class='table table-borderless noprint'>
	<tr>
		<td align=right>
			<input class="size12 btn btn-default" type=button value='返回 (B)' onclick='history.go(-1);' class=button>
		</td>
	</tr>
</table>

<table class='table table-borderless table_form'>

<form name=form id=form action='' method=post>
<input type=hidden name=action value=edit>
<input type=hidden name=from_query value='$from_query'>
<input type=hidden name=saveprint value=false>

	<colgroup style='background:none'>
		<col width=120 align=right />
		<col width=450 />
		<col width=230 align=right />
		<col width=100 />
	</colgroup>

	<tr>
		<td>記錄編號</td>
		<td>$inputs->salary_id</td>
		<td>簽發日期	&nbsp; $inputs->date_issue</td>
		<td><img src='js/calendar.gif' onclick="show_cal(this, 'cms::$id::date_issue');" /></td>
	</tr>
	<tr>
		<td>推廣員</td>
		<td>$inputs->staff_id</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>簽發人</td>
		<td>$inputs->issueby_id</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>開始日期</td>
		<td>
		<div class="input-group">
			$inputs->date_begin
			<div class='input-group-addon' onclick="show_cal(this, 'cms::$id::date_begin');"><i class='fa fa-calendar-o'></i></div>
			</div>
			</td>
	</tr>
	<tr>
		<td>結束日期</td>
	<td>
		<div class="input-group">
			 $inputs->date_end
			<div class='input-group-addon' onclick="show_cal(this, 'cms::$id::date_end');"><i class='fa fa-calendar-o'></i></div>
			</div>
			<input type="button" value="計算佣金" class="btn btn-default" onclick="loadCSI()">
		</td>
	</tr>
	<tr>
		<td>備註</td>
		<td colspan=3>$inputs->remark</td>
	</tr>

	<tr><td colspan=4 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=4 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=4 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td colspan=4 height=300 valign=top>




<table class='table table-borderless'>
	<tr>
		<td>新增帳單 ：</td>
			<td style="width:75%;">$inputs2->add_invoice_id</td>
			<td><input class="btn btn-default" type='button' value='確定'		onclick='CSI_load(itemlist, "salary_edit_item_add.php?sid=$id&iid=" + document.getElementById("form").elements.namedItem("add_invoice_id").value + "&staff_id=" + document.getElementById("form").elements.namedItem("input_pulldownmenu_staff_id_value").value, "", "append"); document.getElementById("form").elements.namedItem("add_invoice_id").value = ""; document.getElementById("form").elements.namedItem("input_pulldownmenu_add_invoice_id").value = "";'></td>
			<td><input class="btn btn-default" type='button' value='清除記錄'	onclick='clear_record();'>
		</td>
	</tr>
</table>


<style>
.border_top td {
	background-color	:	white;
	border-top			: solid 1px #cccccc;
}
</style>


<table class='table table-borderless'>
	<tr height=30 style='font-weight:bold'>
		<td width=100>參考編號</td>
		<td width=80>銷售日期</td>
		<td width=180>客戶名稱</td>
		<td width=60>銷售總額</td>
		<td width=60>現金銷售</td>
		<td width=60>加班時間</td>
		<td width=60>銷售佣金</td>
		<td width=60>其他佣金</td>
		<td width=30>刪除</td>
	</tr>


EOS;


$items				= sql_getTable("select * from salary_detail where salary_id='$id' order by date_sales asc");
$item_count			= count($items);

$count				= 0;

$empty_item_display	= (empty($items)) ? "" : "style='display:none'";


echo "<tr id=empty_item bgcolor=#ffffff height=100 $empty_item_display><td colspan=20 align=center>暫時沒有記錄。</td></tr>";


foreach ($items as $item) {

	array2obj($item);

	$customer_name					= sql_getValue("select name from customer where id='$item->customer_id'");

	echo "
			<tr class=border_top id='item_row_$item->id' clearrecord='cms_item::$item->id::null'>
				<td>$item->refno</td>
				<td>$item->date_sales</td>
				<td>$customer_name</td>
				<td><input class=number type=text name=cms_item::$item->id::amount_sales	value='$item->amount_sales'	style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::cashsale		value='$item->cashsale'		style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::ot_time			value='$item->ot_time'		style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::commission		value='$item->commission'	style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::salary			value='$item->salary'		style='width:60px;' ></td>
				<td><input type=checkbox name='cms_item::$item->id::null' 					value='delete'				onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$item->id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
			</tr>
		";


	$count++;

}





echo <<<EOS

</table>
<div id=itemlist></div>


		</td>
	</tr>

	<tr><td colspan=4 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=4 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=4 height=10 style='padding:0px;'></td></tr>


	<tr>
		<td>記錄數</td>
		<td><input type=text name=item_count value='$item_count' size=10 class="form-control number" readonly></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>銷售佣金</td>
		<td>$inputs->commission</td>
		<td>總銷售額	&nbsp; $inputs->amount_sales</td>
		<td><input class="btn btn-default" type=button value='重新計算 (C)' onclick='calculate();'></td>
	</tr>
	<tr>
		<td>其他佣金</td>
		<td>$inputs->salary</td>
		<td>佣金總額	&nbsp; $inputs->amount</td>
		<td></td>
	</tr>

	<tr><td colspan=4 height=10 style='padding:0px;'></td></tr>
	<tr><td colspan=4 height=1  style='padding:0px;' bgcolor=#cccccc></td></tr>
	<tr><td colspan=4 height=10 style='padding:0px;'></td></tr>

	<tr>
		<td colspan=4 align=center class=noprint>
			<input class="btn btn-default noprint" type=submit name=save value='確定 (S)'>
			<input class="btn btn-default noprint" type=submit name=save value='儲存及預覽 (P)'>
		</td>
	</tr>


<div id=csi_return class=remark>



</form>
</table>


<script>


var input_pulldownmenu_staff_id_value_previous	= $('select[name="cms::$id::staff_id"]:selected').val();
$('select[name="cms::$id::staff_id"]').onchange = function () {
	if (input_pulldownmenu_staff_id_value_previous == $('select[name="cms::$id::staff_id"]:selected').val())
		return;

	CSI_submit("salary_edit_load_staff.php?cid=" + document.getElementById("form").elements.namedItem("cms::$id::staff_id").value);

	document.getElementById("form").elements.namedItem("add_invoice_id").value = "";
	document.getElementById("form").elements.namedItem("input_pulldownmenu_add_invoice_id").value = "";

	input_pulldownmenu_staff_id_value_previous = $('select[name="cms::$id::staff_id"]:selected').val();
}


function getElementsById(id) {
	var tables = document.getElementsByTagName("table");
	var trs = document.getElementsByTagName("tr");
	var arr = new Array();
	for (var i=0; i < tables.length; i++) {
		if (tables[i].id == id) {
			arr[arr.length] = tables[i];
		}
	}
	return arr;
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
		if (names[0] == 'cms_item' && names[2] == 'commission') {
			ids.push(names[1]);
			if (names[3] == 'new')
				news[names[1]] = 'new';
		}

	}

	var salary			= 0;
	var commission		= 0;
	var amount_sales	= 0;
	var amount			= 0;
	var item_count		= 0;

	for (var i = 0; i < ids.length; i++ ) {
		var itemid	= ids[i];
		var newitem	= "";
		if (news[itemid] == 'new')
			newitem	= "::new";

		if (getFormItem('form', 'cms_item::' + itemid + '::null' + newitem).checked)				continue;

		if (isNaN(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::salary' + newitem).value)))			getFormItem('form', 'cms_item::' + itemid + '::salary' + newitem).value				= '0';
		if (isNaN(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::commission' + newitem).value)))		getFormItem('form', 'cms_item::' + itemid + '::commission' + newitem).value			= '0';
		if (isNaN(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::ot_time' + newitem).value)))		getFormItem('form', 'cms_item::' + itemid + '::ot_time' + newitem).value			= '0';
		if (isNaN(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount_sales' + newitem).value)))	getFormItem('form', 'cms_item::' + itemid + '::amount_sales' + newitem).value		= '0';
		if (isNaN(parseFloat(getFormItem('form', 'cms_item::' + itemid + '::cashsale' + newitem).value)))		getFormItem('form', 'cms_item::' + itemid + '::cashsale' + newitem).value			= '0';

		salary			+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::salary' + newitem).value);
		commission		+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::commission' + newitem).value);
		amount_sales	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::amount_sales' + newitem).value);
		amount_sales	+= parseFloat(getFormItem('form', 'cms_item::' + itemid + '::cashsale' + newitem).value);

		item_count++;

	}

	getFormItem('form', 'cms::$id::salary').value			= salary;
	getFormItem('form', 'cms::$id::commission').value		= commission;
	getFormItem('form', 'cms::$id::amount_sales').value		= amount_sales;
	getFormItem('form', 'cms::$id::amount').value			= salary + commission;
	getFormItem('form', 'item_count').value					= item_count;

}

function getFormItem(formid, itemid) {
	return document.getElementById(formid).elements.namedItem(itemid);
}



shortcut.add("Ctrl+S", function () {document.getElementById("form").submit(); });
shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+C", function () {calculate(); });
shortcut.add("Ctrl+P", function () {getFormItem("form", "saveprint").value="true"; document.getElementById("form").submit(); });

function loadCSI() {
	return CSI_load(itemlist, "salary_edit_item_add.php?sid=$id&staff_id=" + $('select[name="cms::$id::staff_id"]:selected').val() + "&date_begin=" + $('input[name="cms::$id::date_begin"]').val() + "&date_end=" + $('input[name="cms::$id::date_end"]').val(), "", "append");
}

</script>



EOS;

include_once "bin/class_csi.php";
$csi			= new CSI();


include "footer.php";

?>
