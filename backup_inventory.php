<?php

include_once "inc_common.php";

$date		= date("Y-m-d");

$files		= glob("record/*.csv");

if (!empty($files)) {
	foreach ($files as $file) {
		
		$create_date	= date("Y-m-d", fileatime($file));
		
		if ($create_date == $date) 
			$backup		= true;
	
		$out_date		= strtotime($date) - strtotime($create_date);
		
//		if ($out_date > 86400*30 * 6)
//			@unlink($file);
		
	}
}

if (!$backup) {
	
	$sql			= "
					select
						a.id as id, 
						d.name as site_name, 
						c.description as brand_name, 
						b.name as item_name, 
						a.amount as amount 
					from 
						inventory a
						join item b on a.item_id=b.id 
						join class_brand c on b.brand=c.id 
						join site d on a.site_id=d.id 
					order by brand_name";
	
	
	
	$Result 		= mysql_query($sql);

	$data 			= '"id","site_name","brand_name","item_name","amount"' . "\n"; //file title
	
	while ($row=mysql_fetch_row($Result)) {
		
		foreach ($row as $name => $value) {
			$row[$name]		= str_replace('"', '""', $value);
		}
		
		$line 		 = "\"" . @join("\",\"",$row)."\"\n";
		$line 		 = preg_replace("!rn|rn!","<br>",$line);
//		$line		.= "";
		$data		.= $line;
		
	}
	
	$path			= str_replace("\\", "/", getcwd())."/";
	$filename		= "inventory-".date('Y-m-d').".csv";
	
	//~ file_put_contents("record/".$filename, $data);

	
	//~ sql_query("insert into backup_inventory (id,backup_date) value ('','$date')");

}

?>
