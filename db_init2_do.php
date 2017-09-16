<?php


require_once "bin/inc_config.php";


$index		= $_GET['index'] * 1;

$ids		= unserialize(file_get_contents("content/db_init2_data.txt"));


for ($i = 0; $i<10; $i++) {

	$id			= $ids[$index];
	
	if (empty($id)) {
		echo "Done !!";
		exit;
	}
	
	//echo "Processing invoice $id ... ";
	
	$invoice			= sql_getObj("select * from invoice where id='$id'");
/*	
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
					staff_class='$invoice->staff_class',
					staff_group='$invoice->staff_group'
					where invoice_id='$id'");
					*/

	sql_query("update invoice_detail set 
					name = (select name from item where invoice_detail.item_id=item.id)
					where invoice_id='$id' and item_id != '0'");
	
	sql_query("update invoice set balance=(amount_net-deposit) where id='$id'");
	sql_query("update invoice set unpaid=amount_net - ifnull((select sum(amount) from customer_payment_detail where invoice_id=invoice.id), 0) where id='$id'");
/*	*/
	
	//echo "Done<br>";
	
	$index++;

}

echo <<<EOS
<script>

document.getElementById("cur_id").innerHTML = "$index";
document.getElementById("msg_id").innerHTML = "Processing invoice $id ... Done.";

//location.href	= "db_init2_do.php?index=$index";


CSI_load("loadblock", "db_init2_do.php?index=$index");


</script>
EOS;

?>