<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>磐石管理系統 Rock Management System</title>


</head>


<body bgcolor=#ffffff>
<div id=logo></div>

<div id=login_status align=right>
	 <i class='fa fa-user'></i> Admin  &nbsp;&nbsp; &nbsp;&nbsp;
	 <i class='fa fa-sign-out'></i><a href='login_logout.php'> 登出</a>
	 <script> var is_member	= true;	</script>
</div>
<div id=login_menu><table width=100% cellpadding=0 cellspacing=0 border=0 background='images/menu_bkgnd.jpg'>
		<tr height=1>
			<td bgcolor=#ff7777></td>
		</tr>
		<tr>
			<td style='color:#ffffff; padding:5px; letter-spacing: 2px; font-weight:bold;' class='size13'>

		</td>
		</tr>
		<tr height=1>
			<td bgcolor=#ff7777></td>
		</tr>
	</table>


</div>


<?php


include_once "bin/inc_config.php";
include_once "bin/class_csv.php";


$filename	= "record/inventory-2010-7-20.csv";


$data			= array();


	$csv		= new CSV_Reader($filename);
	$csv->getRow();

	while ($row = $csv->getRow()) {
//		if ($row[1] != $site_name)	continue;
//		$data["<b>" . $row[2] . "</b> - " . $row[3]][$i]	= $row[4];
		$data[]	= $row;
	}
	$csv->close();


dump_table($data);


?>




<script>

shortcut.add("Ctrl+N", function () { location.href="invoice_add.php"; });
shortcut.add("Ctrl+P", function () { window.open(""); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });
</script>

<cetner><p align=center>Show  in 0.161247 seconds.</p></center>
