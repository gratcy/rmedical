<?php
ob_start();
include_once "inc_common.php";

Ignore_User_Abort(False);

$site_id		= $_GET['id'] * 1;

$site_name		= sql_getValue("select name from site where id='$site_id'");


 $items			= sql_getTable(
     		"select
				c.description as brand,
				b.name as item_name, 
				a.amount as amount 
			from 
				inventory a
				join item b on a.item_id=b.id 
				join class_brand c on b.brand=c.id
			where
				a.site_id=$site_id order by brand");


$titles		= array("品牌","物品名稱","存貨數量",);

array_unshift($items, $titles);


$xls 		= new Excel();


$xls->addArray ( $items );

$filename	 = $site_name."_".date('Ymd');

$xls->generateXML ($filename);

ob_end_flush();
?>