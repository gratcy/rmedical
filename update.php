<?php

include_once "header.php";



	$fp				= fopen("product_update.csv", 'r');
	
	$row = fgetcsv($fp);
	
	$data			= array();
	
	while (($row = fgetcsv($fp)) !== FALSE) {
		
		$row++;
		
		$row							= big2utf($row);
		
		$fields							= array();
		$fields['item_id']				= $row[0];
		$fields['name']					= $row[3];
		$fields['name_short']			= $row[5];
		
		$item_id						= $fields['item_id'];
		
		$data[]							= $fields;
		
		$sql							= sql_update("item", $fields, "item_id='$item_id'");
//		sql_query($sql);
		
	}
	
	
	dump_table($data);


?>