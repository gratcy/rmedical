<?php

include_once "inc_common.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='salary.php'");
if (empty($privilege->edit))	{	gotoURL("salary.php"); exit; }



$salary_id			= $_GET['sid'] * 1;
$invoice_id			= $_GET['iid'] * 1;
$staff_id			= $_GET['staff_id'] * 1;

if (isset($_GET['date_begin'])) {

	$date_begin		= sql_secure($_GET['date_begin']);
	$date_end		= sql_secure($_GET['date_end']);

	$data			= sql_getTable("select * from invoice where date_order >= '$date_begin' and date_order <= '$date_end' and staff_id='$staff_id' and status!='deleted' order by date_order asc");

} else {

	$data			= sql_getTable("select * from invoice where id='$invoice_id'");

}





foreach ($data as $invoice_info) {

	array2obj($invoice_info);

	$rec_id				= sql_getValue("select max(rec_id) from salary_detail where salary_id='$salary_id'") + 1;

	$rand_id			= get_unique_id();

	$customer_name		= sql_getValue("select name from customer where id='$invoice_info->customer_id'");


	$sales				= $invoice_info->sales_record + $invoice_info->amount_cash;
	$commission_id		= sql_getValue("select commission_id from staff where id='$staff_id'");
	$commission			= sql_getValue("select money_percent * ($sales-range_begin)/100 + money_fixed from commission_detail where commission_id='$commission_id' and $sales >= range_begin and $sales <= range_end");

	echo "
		<table class='table table-borderless' bgcolor='#dddddd' id='item_row_$rand_id' clearrecord='cms_item::$rand_id::null::new'>
			<tr bgcolor=#ffffff>
				<input type=hidden name=cms_item::$rand_id::salary_id::new		value='$salary_id'>
				<input type=hidden name=cms_item::$rand_id::refno::new			value='$invoice_info->invoice_id'>
				<input type=hidden name=cms_item::$rand_id::rec_id::new			value='$rec_id'>
				<input type=hidden name=cms_item::$rand_id::customer_id::new	value='$invoice_info->customer_id'>
				<input type=hidden name=cms_item::$rand_id::date_sales::new		value='$invoice_info->date_order'>

				<td width=100>$invoice_info->invoice_id</td>
				<td width=80>$invoice_info->date_order</td>
				<td width=180>$customer_name</td>
				<td width=60><input class=number type=text name=cms_item::$rand_id::amount_sales::new	value='$invoice_info->sales_record'	style='width:60px;'></td>
				<td width=60><input class=number type=text name=cms_item::$rand_id::cashsale::new		value='$invoice_info->amount_cash'	style='width:60px;'></td>
				<td width=60><input class=number type=text name=cms_item::$rand_id::ot_time::new		value='$invoice_info->overtime'		style='width:60px;'></td>
				<td width=60><input class=number type=text name=cms_item::$rand_id::commission::new		value='$commission'					style='width:60px;'></td>
				<td width=60><input class=number type=text name=cms_item::$rand_id::salary::new			value='0'							style='width:60px;'></td>
				<td width=30><input type=checkbox name='cms_item::$rand_id::null::new'					value='delete'						onclick='if (confirm(\"確定要刪除這個項目？\")) { document.getElementById(\"item_row_$rand_id\").style.display=\"none\"; calculate(); } else { this.checked=false; }'></td>
			</tr>
		</table>
		";

}

echo "<script>if (document.getElementById('empty_item')) document.getElementById('empty_item').style.display='none';</script>";

echo "<script>calculate();</script>";

?>