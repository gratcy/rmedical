<?php


require_once "bin/inc_config.php";

$index					= $_GET['index'] * 1;


$ids					= sql_getArray("select id from invoice order by id desc");
file_put_contents("content/db_init2_data.txt", serialize($ids));


$total_record			= count($ids);

echo <<<EOS
<p>Total invoices : $total_record</p>

<p>Processing record : <font color=blue><span id=cur_id></span></font></p>

<p>Start at index : $index</p>

<p><span id=msg_id></span></p>

<div id=loadblock></div>




EOS;

include "bin/class_csi.php";

$csi		= new CSI();

$csi->load("loadblock", "db_init2_do.php?index=$index");


/*
$count					= 0;

foreach ($ids as $id) {
	
	set_time_limit(30);
	
	echo file_get_contents("http://admin.rock-medical.com/");

	echo str_repeat(" ", 1000) . "\r\n";

	flush();
	
}

*/

exit;

set_time_limit(30);

echo "<p>Processing salary update ... </p>";

sql_query("update salary set amount_sales=(select sum(amount_sales) from salary_detail where salary_detail.salary_id = salary.id)");

set_time_limit(30);

echo "<p>Processing customer payment update ... </p>";

sql_query("update customer_payment set amount = (select sum(amount) from customer_payment_detail where customer_payment_id=customer_payment.id)");


?>