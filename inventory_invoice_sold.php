<?php

/////////////////////////////////////////////
//	Inputs :
//		$invoice		- Object of invoice
/////////////////////////////////////////////


$it_id					= sql_getValue("select id from inventory_transaction where type='sold' and remark='invoice_id:$invoice->id'");


if (empty($invoice->site_id))		return;

if (empty($it_id)) {
	
	//	New transaction
//	echo "<p>new transaction</p>";
	
	if ($invoice->quantity_sum == 0)	return;
	
	
	$fields					= array();
	$fields['site_from']	= $invoice->site_id;
	$fields['site_to']		= 0;
	$fields['type']			= "sold";
	$fields['remark']		= "invoice_id:$invoice->id";
	$fields['date']			= $invoice->date_order;
	$fields['modify_user']	= $user->id;
	$fields['date_create']	= date("Y-m-d H:i:s");
	
	sql_query(sql_insert("inventory_transaction", $fields));
	
	$it_id					= sql_insert_id();

} else {

	//	Edit old transaction
//	echo "<p>old transaction</p>";
	
	
}



$inventory_transaction	= sql_getObj("select * from inventory_transaction where id='$it_id'");
$items					= sql_getArray("select item_id, sum(quantity) from invoice_detail where invoice_id='$invoice->id' group by item_id");



include_once "inventory_library.php";
inventory_transaction_detail_save($inventory_transaction, $items);	
inventory_stock_statistic_update($invoice->site_id);


?>