<?php

include "inc_common.php";

if (empty($user))			exit;


$site_id			= $_GET['site'] * 1;
$item_id			= $_GET['item'] * 1;


$site_names			= sql_getArray("select id, name from site");


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>磐石管理系統 Rock Management System</title>

<link href="style.css" rel="stylesheet" type="text/css">
<link href="style_print.css" rel="stylesheet" type="text/css" media="print">

<script language="JavaScript" type="text/javascript" src="js/shortcut.js"></script>

</head>


<body bgcolor=#ffffff style='margin:15px;'>

<table class='table table-borderless' align='left'>
	<tr>
		<th colspan=10 bgcolor=#eeeeff>相關倉存</td></tr>
	</tr>
	<tr>
		<td><b>地點 1</b></td>
		<td><b>地點 2</b></td>
		<td><b>事件</b></td>
		<td><b>備註</b></td>
		<td><b>日期</b></td>
		<td><b>初始貨存</b></td>
		<td><b>結果貨存</b></td>
		<td><b>更改數量</b></td>
	</tr>

<?php

$sql				= "select a.site_from, a.site_to, a.type, a.remark, date(a.date) as 'date', b.change_from, b.change_to, b.amount from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where (a.site_from='$site_id' or a.site_to='$site_id') and b.site_id='$site_id' and item_id='$item_id'";
$month_previous		= "";


$data				= sql_getTable($sql);
foreach ($data as $info) {

	array2obj($info);

	if ($info->type == 'sold') {
		$invoice_id			= array_pop(explode(":", $info->remark));
		$invoice_link		= "&nbsp; &nbsp; <a href='invoice_edit.php?id=$invoice_id' target=_blank>查看</a>";
	} else {
		$invoice_link		= "";
	}

	$month		= substr($info->date, 0, 7);

	if ($month != $month_previous) {
		echo "<tr><td colspan=10 height=3 bgcolor=blue></td></tr>";
	}

	echo "<tr>";
	echo "<td>" . $site_names[$info->site_from] . "</td>";
	echo "<td>" . $site_names[$info->site_to] . "</td>";
	echo "<td>$info->type</td>";
	echo "<td>$info->remark $invoice_link</td>";
	echo "<td>$info->date</td>";
	echo "<td align=right>$info->change_from</td>";
	echo "<td align=right>$info->change_to</td>";
	echo "<td align=right>$info->amount</td>";
	echo "</tr>\r\n";

	$month_previous			= $month;

}








?>


</table>


<table cellspacing=0 cellpadding=5 border=1 style='border-collapse:collapse; border:solid 1px #aaaaaa; margin:10px;' align=left>
	<tr>
		<th colspan=10 bgcolor=#eeffee>相關帳單</td></tr>
	</tr>
	<tr>
		<td><b>ID</b></td>
		<td><b>帳單編號</b></td>
		<td><b>出單日期</b></td>
		<td><b>帳單總價</b></td>
		<td><b>賣出該物品</b></td>
	</tr>

<?php



$sql				= "select a.id, a.invoice_id, a.amount_gross, b.quantity, a.date_order from `invoice` a join invoice_detail b on a.id=b.invoice_id where a.site_id='$site_id' and item_id='$item_id'";
$month_previous		= "";


$data				= sql_getTable($sql);
foreach ($data as $info) {

	array2obj($info);


	$info->amount_gross	= number_format($info->amount_gross, 2);
	$invoice_link		= "&nbsp; &nbsp; <a href='invoice_edit.php?id=$info->id' target=_blank>查看</a>";

	$month				= substr($info->date_order, 0, 7);

	if ($month != $month_previous) {
		echo "<tr><td colspan=10 height=3 bgcolor=blue></td></tr>";
	}

	echo "<tr>";
	echo "<td>$info->id</td>";
	echo "<td>$info->invoice_id $invoice_link</td>";
	echo "<td>$info->date_order</td>";
	echo "<td align=right>$info->amount_gross</td>";
	echo "<td align=right>$info->quantity</td>";
	echo "</tr>\r\n";

	$month_previous			= $month;

}








?>


</table>



</body>
</html>
