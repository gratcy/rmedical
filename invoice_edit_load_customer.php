<?php

include_once "inc_common.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->edit))	{	gotoURL("invoice.php"); exit; }


$customer_id		= $_GET['cid'] * 1;
$invoice_id			= $_GET['iid'] * 1;

if (empty($customer_id))	exit;

$customer			= sql_getObj("select * from customer where id='$customer_id'");
$staff				= sql_getObj("select * from staff where id='$customer->staff_id'");
$site				= sql_getObj("select * from site where id='$customer->site_id'");

echo <<<EOS
<script>

document.getElementById("form").elements.namedItem("cms::$invoice_id::discount").value			= "$customer->discount";

document.getElementById("form").elements.namedItem("input_pulldownmenu_site_id").value			= "$site->name";
document.getElementById("form").elements.namedItem("cms::$invoice_id::site_id").value			= "$customer->site_id";

//document.getElementById("form").elements.namedItem("input_pulldownmenu_staff_id_value").value	= "$customer->staff_id";
//document.getElementById("form").elements.namedItem("input_pulldownmenu_staff_id").value			= "$staff->name";
calculate();

</script>

EOS;

?>