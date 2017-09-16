<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customer_payment.php'");
if (empty($privilege->edit))	{	gotoURL("customer_payment.php"); exit; }


$prefix_id		= "PC" . date("Ym");
$suffix_id		= substr(sql_getValue("select payment_id from customer_payment where payment_id like '$prefix_id%' order by payment_id desc limit 1"), 9);
$new_id			= $prefix_id . padding($suffix_id+1, 4);

$fields		= array(
					"payment_id"		=> $new_id,
					"date"				=> date("Y-m-d H:i:s"),
					"modify_user"		=> $user->id,
					"status"			=> "deleted"
					);


sql_query(sql_insert("customer_payment", $fields));
$payment_id		= sql_insert_id();
gotoURL("customer_payment_edit.php?id=$payment_id");


include_once "footer.php";

?>