<?php

echo <<<EOS
<link href="style_print.css" rel="stylesheet" type="text/css">
EOS;

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='purchase_order.php'");
if (empty($privilege->edit))	{	gotoURL("purchase_order.php"); exit; }


$supplier_id			= $_GET['sid'] * 1;
$purchase_order_id		= $_GET['poid'] * 1;

if (empty($supplier_id))	exit;

echo "<script>\r\n";


echo "pulldownmenu_add_item_id = new PulldownMenu();";

$count				= 0;

$data				= sql_getArray("select name, id from item where supplier_id='$supplier_id' order by name asc");

foreach ($data as $name => $id) {
	
	echo "pulldownmenu_add_item_id.item[$count]		= '" . htmlentities($name, ENT_QUOTES, 'UTF-8') . "';";
	echo "pulldownmenu_add_item_id.value[$count]	= '$id';";
	
	
	$count++;
	
}

echo "</script>\r\n";

?>