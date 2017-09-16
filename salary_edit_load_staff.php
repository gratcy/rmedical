<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='salary.php'");
if (empty($privilege->edit))	{	gotoURL("salary.php"); exit; }


$customer_id		= $_GET['cid'] * 1;

if (empty($customer_id))	exit;

echo "<script>\r\n";


echo "pulldownmenu_add_invoice_id = new PulldownMenu();";

$count				= 0;

$data				= sql_getArray("select concat(invoice_id, ' ( ', date_order, ' ) - \$', amount_net), id from invoice where customer_id='$customer_id' and unpaid != 0 order by date_order asc");

foreach ($data as $name => $id) {
	
	echo "pulldownmenu_add_invoice_id.item[$count]	= '" . htmlentities($name, ENT_QUOTES, 'UTF-8') . "';";
	echo "pulldownmenu_add_invoice_id.value[$count]	= '$id';";
	
	
	$count++;
	
}

echo "</script>\r\n";

include_once "footer.php";

?>