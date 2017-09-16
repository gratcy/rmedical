<?php

include_once "inc_common.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='purchase_order.php'");
if (empty($privilege->edit))	{	exit; }



$id					= $_GET['id'] * 1;
$purchase_order_id	= $_GET['purchase_order_id'] * 1;

if (!empty($id)) {

	$item_info			= sql_getObj("select * from item where id='$id'");
	$rec_id				= sql_getValue("select max(rec_id) from purchase_order_detail where purchase_order_id='$purchase_order_id'") + 1;

	$rand_id			= rand(1000, PHP_INT_MAX);

	echo "
		<table class='table table-borderless' bgcolor='#dddddd'>
			<tr bgcolor=#ffffff>
				<input type=hidden name=cms_item::$rand_id::purchase_order_id::new	value='$purchase_order_id'>
				<input type=hidden name=cms_item::$rand_id::item_id::new	value='$id'>
				<input type=hidden name=cms_item::$rand_id::item_brand::new	value='$item_info->brand'>
				<input type=hidden name=cms_item::$rand_id::rec_id::new		value='$rec_id'>
				<td width=100>$item_info->item_id</td>
				<td width=270>$item_info->name</td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::cost_original::new		value='$item_info->cost' size=2 readonly style='color:#777777'></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::quantity::new			value='1' size=2></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::price::new				value='$item_info->cost' size=2></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::discount::new			value='100' size=2></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::amount::new				value='$item_info->cost' size=2 readonly style='color:#777777'></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::amount_discounted::new	value='$item_info->cost' size=2 readonly style='color:#777777'></td>
				<td width=40><input type=checkbox name='cms_item::$rand_id::null::delete' value='delete'></td>
			</tr>
		</table>
		";

	echo "<script>if (document.getElementById('empty_item')) document.getElementById('empty_item').style.display='none';</script>";

}

if (isset($_GET['custom'])) {

	$rec_id				= sql_getValue("select max(rec_id) from purchase_order_detail where purchase_order_id='$purchase_order_id'") + 1;

	$rand_id			= rand(1000, PHP_INT_MAX);

	echo "
		<table class='table table-borderless' bgcolor='#dddddd'>
			<tr bgcolor=#ffffff>
				<input type=hidden name=cms_item::$rand_id::purchase_order_id::new	value='$purchase_order_id'>
				<input type=hidden name=cms_item::$rand_id::item_id::new	value='0'>
				<input type=hidden name=cms_item::$rand_id::item_brand::new	value='0'>
				<input type=hidden name=cms_item::$rand_id::rec_id::new		value='$rec_id'>
				<td width=100>N/A</td>
				<td width=270><input type=text name=cms_item::$rand_id::name::new				value='' size=37></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::cost_original::new		value='N/A' size=2 readonly style='color:#777777'></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::quantity::new			value='1' size=2></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::price::new				value='0' size=2></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::discount::new			value='N/A' size=2 readonly style='color:#777777'></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::amount::new				value='0' size=2 readonly style='color:#777777'></td>
				<td width=70><input class=number type=text name=cms_item::$rand_id::amount_discounted::new	value='0' size=2 readonly style='color:#777777'></td>
				<td width=40><input type=checkbox name='cms_item::$rand_id::null::delete' value='delete'></td>
			</tr>
		</table>
		";

	echo "<script>if (document.getElementById('empty_item')) document.getElementById('empty_item').style.display='none';</script>";

}



?>