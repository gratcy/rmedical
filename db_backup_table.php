<?php


$date		= date("Y-m-d");

$files		= glob("database_backup/*.zip");

if(!empty($files)) {
	foreach ($files as $file) {
		
		$create_date	=date("Y-m-d", fileatime($file));
		
		if ($create_date == $date) 
			$backup		= true;
	
		$out_date		= strtotime($date) - strtotime($create_date);
		
		if ($out_date > 86400*30)
			@unlink($file);
			
	}
}


if (!$backup) {
	
	$files		= glob("database/*.sql");//delete old files!
	
	if(!empty($files)) {
		foreach ($files as $file) {
			@unlink($file);
		}
	}
	
	$tables			= sql_getArray("show tables");
	$path			= str_replace("\\", "/", getcwd()) . "/database";
		
	foreach ($tables as $table) {
		
		sql_query("select * into outfile '$path/$table.sql' from $table");
		
	}
	
	$archive		= "database.zip";
	
	require_once "bin/class_zip.php";
	$zip		= new ZIP($archive);
	$zip->init();
	$files		= glob("database/*.sql");
	
	if(!empty($files)) {
		foreach ($files as $file) {
			$zip->addFile($file);
			@unlink($file);
		}
	}
	
	$zip->save();

	@unlink("database_backup/$date.zip");
	rename("database.zip", "database_backup/$date.zip");
	

}else{
	
}



?>