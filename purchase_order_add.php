<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='purchase_order.php'");
if (empty($privilege->edit))	{	gotoURL("purchase_order.php"); exit; }


$prefix_purchase_order_id		= "PO" . date("Ym");
$suffix_purchase_order_id		= substr(sql_getValue("select purchase_order_id from purchase_order where purchase_order_id like '$prefix_purchase_order_id%' order by purchase_order_id desc limit 1"), 8);
$new_purchase_order_id			= $prefix_purchase_order_id . padding($suffix_purchase_order_id+1, 4);

$fields		= array(
					"purchase_order_id"	=> $new_purchase_order_id,
					"date_order"		=> date("Y-m-d H:i:s"),
					"discount"			=> "100"
					);


sql_query(sql_insert("purchase_order", $fields));
$purchase_order_id		= sql_insert_id();
gotoURL("purchase_order_edit.php?id=$purchase_order_id&new");

include_once "footer.php";

?>