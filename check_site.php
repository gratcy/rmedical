<?php

require_once "inc_common.php";

Ignore_User_Abort(false);
set_time_limit(0);


$customers		= sql_getArray("select a.id from customer a join site b on a.site_id=b.id where a.name != b.name order by a.id asc");

if (!empty($customers))
	sql_query("update customer set name=(select name from site where customer.site_id=site.id) where id in (" . implode(",", $customers) . ")");


$customers		= sql_getTable("select a.id, a.name as name1, b.name as name2 from customer a join site b on a.site_id=b.id where a.name != b.name order by a.id asc");

dump_table($customers);

$customers		= sql_getTable("select * from customer where site_id = 0 order by id asc");

foreach ($customers as $index => $customer) {

	array2obj($customer);
	
	if (contain($customer->name, "屯門") || contain($customer->name, "元朗"))		continue;
	
	
	$site					= array();
	$site['name']			= $customer->name;
	$site['date_create']	= "2010-04-22";
	
	sql_query(sql_insert("site", $site));
	$site_id				= sql_insert_id();
	
	sql_query("update customer set site_id='$site_id' where id='$customer->id'");
	
	$customers[$index]['status']		= "fixed";

}

dump_table($customers);


$sites			= sql_getTable("select * from site where id not in (select site_id from customer) order by id asc");
foreach ($sites as $index => $site) {
	array2obj($site);
	
	$usage		= sql_check("select 1 from inventory where site_id=$site->id");
	if ($usage)
		$sites[$index]['usage']		= "yes";
	else {
		$sites[$index]['usage']		= "no";
		
		sql_query("delete from site where id='$site->id'");
		
	}
	
}

dump_table($sites);

exit;



echo "<table style='table-layout:fixed; font-family:Arial; font-size:13px;' cellpadding=8 cellspacing=0 border=1>";
echo "<colgroup>";
echo "	<col width=100 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "	<col width=120 align=right>";
echo "</colgroup>";

echo "<tr>";
echo "	<td>#</td>";
echo "	<td>ID</td>";
echo "	<td>Date</td>";
echo "	<td>Net Amount</td>";
echo "	<td>Deposit</td>";
echo "	<td>Balance</td>";
echo "	<td>Invoice Unpaid</td>";
echo "	<td>Paid</td>";
echo "	<td>CP Unpaid</td>";
echo "	<td>CP ID</td>";
echo "	<td>Status</td>";
echo "</tr>";	
flush();


$statistic		= array();

$cp				= null;
$cp_id			= 0;
$cp_count		= 0;
$cp_item_count	= 0;


$fixed_record	= array();


foreach ($invoices as $invoice) {
	
	array2obj($invoice);

	if ($invoice->amount_net == 0 && $invoice->unpaid == 0)					continue;
	
	$payment			= sql_getObj("select sum(amount) as amount, customer_payment_id as cp_id from customer_payment_detail where invoice_id='$invoice->id'");
	
	$payment_count		= sql_getTable("select customer_payment_id as cp_id from customer_payment_detail where invoice_id='$invoice->id'");
	$payment_count		= count($payment_count);
	
	$payment_remark		= sql_getValue("select remark from customer_payment where id='$payment->cp_id'");

	$paid				= $payment->amount;
	$unpaid				= $invoice->amount_net - $paid;
	
	if (empty($paid))		$paid		= 0;
	if (empty($unpaid))		$unpaid		= 0;
	
	if ($invoice->amount_net == $paid && $invoice->unpaid == $unpaid)		continue;




	$status				= "Other case";



	if (empty($payment)) {
		$status			= "No payment record";
	}
	
	if ($invoice->unpaid == $invoice->amount_net && $payment_remark != "System Generated Customer Payment") {
		$status			= "0 paid";
		
		if ($cp_item_count > 15)							$cp 	= null;
		if ($invoice->customer_id != $cp['customer_id'])	$cp		= null;
		
		
		if ($cp == null) {
			
			$cp						= array();
			$cp['payment_id']		= "SYSTEM_GEN_" . padding(++$cp_count, 4);
			$cp['date']				= "2009-09-30";
			$cp['date_begin']		= "2009-09-30";
			$cp['date_end']			= "2009-09-30";
			$cp['refno']			= "";
			$cp['method']			= "";
			$cp['remark']			= "System Generated Customer Payment";
			$cp['amount']			= "0";
			$cp['customer_id']		= $invoice->customer_id;
			sql_query(sql_insert("customer_payment", $cp));
			
			$cp_id					= sql_insert_id();
			
			$cp_item_count			= 0;
			
		}
		
		
		$cp_detail							= array();
		$cp_detail['customer_payment_id']	= $cp_id;
		$cp_detail['rec_id']				= $cp_item_count++;
		$cp_detail['invoice_id']			= $invoice->id;
		$cp_detail['amount']				= $invoice->amount_net;
		sql_query(sql_insert("customer_payment_detail", $cp_detail));
		
		$status			.= " - Fixed";
		
		$fixed_record[]	= $invoice->id;
	}
	
	if ($invoice->unpaid == $invoice->amount_net * -1) {
		$status			= "Double paid";
	}
	
	
	if ($payment->remark == "System Generated Customer Payment") {
		$status			= "Fixed";
	}
	
	if ($payment_count > 1) {
		
		$status			.= " - Multiple Payment";
		
		if ($status == "Double paid - Multiple Payment") {
			
			$payments	= sql_getArray("select amount from customer_payment_detail where invoice_id = '$invoice->id'");
			
			if ($payments[0] == $payments[1]) {
				sql_query("delete from customer_payment_detail where invoice_id = '$invoice->id' order by customer_payment_id limit 1");
				$status			.= " - Fixed";
				$fixed_record[]	= $invoice->id;
				
				$fixed_cp_id	= sql_getValue("select customer_payment_id from customer_payment_detail where invoice_id = '$invoice->id'");
				sql_query("update customer_payment set amount = (select sum(amount) from customer_payment_detail where customer_payment_id=customer_payment.id) where id='$fixed_cp_id'");

			}
			
		}


		
	}
	
	$all_cp_id			= sql_getArray("select customer_payment_id as cp_id from customer_payment_detail where invoice_id='$invoice->id'");
	$all_cp_id			= implode(" , ", $all_cp_id);
	
	
	$statistic[$status]++;
	
	echo "<tr>";
	echo "	<td>$invoice->id</td>";
	echo "	<td>$invoice->invoice_id</td>";
	echo "	<td>$invoice->date_order</td>";
	echo "	<td>$invoice->amount_net</td>";
	echo "	<td>$invoice->deposit</td>";
	echo "	<td>$invoice->balance</td>";
	echo "	<td>$invoice->unpaid</td>";
	echo "	<td>$paid</td>";
	echo "	<td>$unpaid</td>";
	echo "	<td>$all_cp_id</td>";
	echo "	<td>$status</td>";
	echo "</tr>";
	flush();
	
}

echo "</table>";



foreach ($fixed_record as $invoice) {
	sql_query("update invoice set unpaid=amount_net - ifnull((select sum(amount) from customer_payment_detail where invoice_id=invoice.id), 0), date_pay='$customer_payment->date' where id='$invoice'");
	sql_query("update invoice set status='freeze' where id='$invoice' and unpaid=0");
}

sql_query("update customer_payment set amount = (select sum(amount) from customer_payment_detail where customer_payment_id=customer_payment.id) where remark='System Generated Customer Payment'");



dump($statistic);


?>