<?php

@session_name('rock');
@session_start();

require_once "bin/inc_setting.php";
require_once "bin/inc_database.php";
require_once "bin/inc_library.php";

$user				= sql_getObj("select * from service_user where id='" . ($_SESSION['user_id'] * 1) . "'");

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->edit))	{	exit; }



$id					= $_GET['id'] * 1;
$invoice_id			= $_GET['invoice_id'] * 1;

if (!empty($id)) {

	$item_info			= sql_getObj("select * from item where id='$id'");
	$rec_id				= sql_getValue("select max(rec_id) from invoice_detail where invoice_id='$invoice_id'") + 1;

	$rand_id			= rand(1000, PHP_INT_MAX);

	echo "
		<table class='table table-borderless' bgcolor='#dddddd' id='item_row_$rand_id'>
			<tr bgcolor=#ffffff>
				<input type=hidden name='cms_item::$rand_id::invoice_id::new'	value='$invoice_id'>
				<input type=hidden name='cms_item::$rand_id::item_id::new'	value='$id'>
				<input type=hidden name='cms_item::$rand_id::item_brand::new'	value='$item_info->brand'>
				<input type=hidden name='cms_item::$rand_id::rec_id::new'		value='$rec_id'>
				<td width=100>$item_info->item_id</td>
				<td width=420>$item_info->name</td>
				<td width=50><input class=number type=text name='cms_item::$rand_id::quantity::new'			value='1'							size=2 nextinput=cms_item::$rand_id::price::new		onblur='calculate_item(\"$rand_id\", \"::new\");'></td>
				<td width=50><input class=number type=text name='cms_item::$rand_id::price_original::new'		value='$item_info->price'			size=2 nextinput=cms_item::$rand_id::price::new></td>
				<td width=50><input class=number type=text name='cms_item::$rand_id::price::new'				value='$item_info->price'			size=2 nextinput=input_pulldownmenu_add_item_id		onblur='calculate_item(\"$rand_id\", \"::new\");'></td>
				<td width=50><input class=number type=text name='cms_item::$rand_id::amount::new'				value='$item_info->price'			size=2 readonly style='color:#777777'></td>
				<td width=40><input type=checkbox name='cms_item::$rand_id::null::new'						value='delete'	onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$rand_id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
			</tr>
		</table>
		<script>
			calculate();
				getFormItem('form', 'cms_item::$rand_id::quantity::new').focus();
				getFormItem('form', 'cms_item::$rand_id::quantity::new').select();
			if (document.getElementById('empty_item')) document.getElementById('empty_item').style.display='none';
		</script>
		";

}

if (isset($_GET['custom'])) {

	$rec_id				= sql_getValue("select max(rec_id) from invoice_detail where invoice_id='$invoice_id'") + 1;

	$rand_id			= rand(1000, PHP_INT_MAX);

	echo "
		<table class='table table-borderless' bgcolor='#dddddd' id='item_row_$rand_id'>
			<tr bgcolor=#ffffff>
				<input type=hidden name=cms_item::$rand_id::invoice_id::new	value='$invoice_id'>
				<input type=hidden name=cms_item::$rand_id::item_id::new	value='N/A'>
				<input type=hidden name=cms_item::$rand_id::item_brand::new	value='0'>
				<input type=hidden name=cms_item::$rand_id::rec_id::new		value='$rec_id'>
				<td width=100>N/A</td>
				<td width=420><input type=text name='cms_item::$rand_id::name::new'							value=''		size=37 nextinput=cms_item::$rand_id::quantity::new></td>
				<td width=50><input class=number type=text name='cms_item::$rand_id::quantity::new'			value='1'		size=2 nextinput=cms_item::$rand_id::price::new			onblur='calculate_item(\"$rand_id\", \"::new\");'></td>
				<td width=50><input class=number type=text name=cms_item::$rand_id::price_original::new		value='0'		size=2 nextinput=input_pulldownmenu_add_item_id></td>
				<td width=50><input class=number type=text name=cms_item::$rand_id::price::new				value='0'		size=2 nextinput=input_pulldownmenu_add_item_id			onblur='calculate_item(\"$rand_id\", \"::new\");'></td>
				<td width=50><input class=number type=text name=cms_item::$rand_id::amount::new	value='0'	size=2 readonly style='color:#777777'></td>
				<td width=40><input type=checkbox name='cms_item::$rand_id::null::new'						value='delete'	onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$rand_id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
			</tr>
		</table>
		<script>
			calculate();
				getFormItem('form', 'cms_item::$rand_id::name::new').focus();
		</script>
		";

}

//echo "<script></script>";


?>
