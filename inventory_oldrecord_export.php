<?php

ob_start();
include_once "inc_common.php";

Ignore_User_Abort(False);

$filename			= $_POST['filename'];
$site_name			= $_POST['site_name'];


if (empty($filename) or empty($site_name))	exit;


include "bin/class_csv.php";
$csv		= new CSV_Reader($filename);

$datas		= array();
while ($row = $csv->getRow()) {
	
	if ($row[1] != $site_name)   continue; 
	
	$items[]	= $row;
}




ini_set('memory_limit', '128M');

	
$titles				= array("ID","Site Name","Brand Nmae","Item Name","Stock");

array_unshift($items, $titles);



$xls 		= new Excel();

$xls->addArray ( $items );

$filename	 = $site_name."_".date('Ymd');

$xls->generateXML ($filename);



ob_end_flush();
?>