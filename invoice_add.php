<?php

include_once "header.php";

$privilege				= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->edit))	{	gotoURL("invoice.php"); exit; }


$prefix_invoice_id		= "INV" . date("Ym");
$suffix_invoice_id		= substr(sql_getValue("select invoice_id from invoice where invoice_id like '$prefix_invoice_id%' order by invoice_id desc limit 1"), 9);
$new_invoice_id			= $prefix_invoice_id . padding($suffix_invoice_id+1, 4);

$fields		= array(
					"invoice_id"		=> $new_invoice_id,
					"date_order"		=> date("Y-m-d H:i:s"),
					"discount"			=> 100
					);




sql_query(sql_insert("invoice", $fields));
$invoice_id		= sql_insert_id();

echo "<meta http-equiv='refresh' content='0;url=invoice_edit.php?id=$invoice_id'>";


include_once "footer.php";

?>