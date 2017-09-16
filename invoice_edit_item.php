<?php

include_once "inc_common.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->edit))	{	gotoURL("invoice.php"); exit; }



$id					= $_GET['id'] * 1;

$bgcolor			= substr($_GET['bgcolor'], 0, 6);



$items				= sql_getTable("select * from invoice_detail where invoice_id='$id'");
$item_count			= count($items);


if ($_POST['action'] == 'add') {
	
	$data			= sql_secure($_POST['data']);
	
	$data			= explode(",", $data);
	
	array_delete_empty($data);
	
	$data[]			= sql_secure($_POST['item_no']) . "-1";
	$data			= implode(",", $data);

}



if ($_POST['action'] == 'update') {
	$data			= sql_secure($_POST['data']);
	


	
	$data			= explode(",", $data);

	foreach ($data as $index => $item) {
		list($item_no, $quantity)		= explode('-', $item);
		if ($item_no == $_POST[item_no])
			$data[$index]		= $item_no . "-" . ($_POST[quantity]*1); 

		if (isset($_POST['delete']) && $item_no == $_POST[item_no]) {
			unset($data[$index]);
		}

	}
	
	$data			= implode(",", $data);

}



echo <<<EOS
<html>
<head>
<title>System</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>


<body bgcolor=#$bgcolor style='margin:0px;'>
<table width=100% cellpadding=0 cellspacing=0 border=0>
	<form name=form1 action='' method=post enctype="multipart/form-data">
	<input type=hidden name=action value="add">
	<input type=hidden name=data value='$data'>
	<tr>
		<td>
			新增物品（物品編號） ： 
			<input type=text name=id size=20>
			<input type=submit value='確定'>
			<hr size=1>
		</td>
	</tr>
	</form>

	<tr>
		<td>
			物品 ： $item_count 種物品</td>
	</tr>
</table>

<table width=100% cellspacing=0 cellpadding=10 border=0>
EOS;





foreach ($items as $item) {
	
	array2obj($item);

	$name							= sql_getValue("select name from item where id='$item->item_id'");
	
	$delete							= ($quantity == 0) ? "<input type=submit name=delete value='刪除'>" : "";
	
	echo "
		<form action='' method=post>
		<input type=hidden name=item_no value='$item->item_no'>
		<input type=hidden name=action value='update'>
		<input type=hidden name=data value='$data'>
	<tr>
		<td>
			$name
		</td>
		<td>
			編號： <font color=blue>$item->item_no</font>
			數量： <input type=text name=quantity value='$item->quantity' size=2> &nbsp;
			 <input type=submit name=update value='更新'>$delete
		</td>
	</tr>
		</form>
		";
	

	
}





echo "
</table>
	";


if (isset($_GET['return_value'])) {

	list($form, $var)		= explode('.', $_GET['return_value']);
	
	echo <<<EOS
<script>
//alert("$data");
	parent.document.getElementById('$form').elements.namedItem('$var').value="$data";
	
</script>
EOS;

}



?>