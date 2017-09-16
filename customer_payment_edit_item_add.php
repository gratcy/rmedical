<?php

include_once "inc_common.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customer_payment.php'");
if (empty($privilege->edit))	{	gotoURL("customer_payment.php"); exit; }



$id					= $_GET['id'] * 1;
$cp_id				= $_GET['cp_id'] * 1;
$c_id				= $_GET['c_id'] * 1;

if (isset($_GET['date_start'])) {

	$date_start		= sql_secure($_GET['date_start']);
	$date_end		= sql_secure($_GET['date_end']);

	$data			= sql_getTable("select * from invoice where date_order >= '$date_start' and date_order <= '$date_end' and customer_id = '$c_id' and unpaid != 0 order by date_order asc");

} else {

	$data			= sql_getTable("select * from invoice where id='$id'");

}


foreach ($data as $invoice_info) {

	array2obj($invoice_info);

	$rec_id				= sql_getValue("select max(rec_id) from customer_payment_detail where customer_payment_id='$cp_id'") + 1;

	$rand_id			= rand(1000, PHP_INT_MAX);

	echo "
		<table class='table table-borderless' bgcolor='#dddddd' id='item_row_$rand_id' clearrecord='cms_item::$rand_id::null::new'>
			<tr bgcolor=#ffffff>
				<input type=hidden name=cms_item::$rand_id::invoice_id::new				value='$invoice_info->id'>
				<input type=hidden name=cms_item::$rand_id::customer_payment_id::new	value='$cp_id'>
				<input type=hidden name=cms_item::$rand_id::rec_id::new					value='$rec_id'>

				<td width=100>$invoice_info->invoice_id</td>
				<td width=100>$invoice_info->date_order</td>
				<td width=80><input class=number type=text value='$invoice_info->amount_net'	size=5 readonly style='color:#777777'></td>
				<td width=80><input class=number type=text value='$invoice_info->deposit'		size=5 readonly style='color:#777777'></td>
				<td width=80><input class=number type=text value='$invoice_info->balance'		size=5 readonly style='color:#777777'></td>
				<td width=80><input class=number type=text value='$invoice_info->unpaid'		size=5 readonly style='color:#777777'></td>
				<td width=80><input class=number type=text name=cms_item::$rand_id::amount::new	value='$invoice_info->unpaid'	size=5></td>
				<td width=40><input type=checkbox name='cms_item::$rand_id::null::new'			value='delete'					onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$rand_id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
			</tr>
		</table>
		<script>calculate();</script>
		";

}

echo "<script>if (document.getElementById('empty_item')) document.getElementById('empty_item').style.display='none';</script>";


?>