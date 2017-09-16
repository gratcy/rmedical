<?php
include_once "inc_common.php";

if ($_GET['date']) {
	$filename		= $_GET['date'].".zip";
	$filepath		= getcwd() ."/database_backup/$filename";
	 
	$zip 			= new ZipArchive(); 
	$rs 			= $zip->open($filepath); 
	if($rs){ 
		$fd			= explode(".",basename($filepath));
		$zip->extractTo(getcwd());
		
		$path		= str_replace("\\", "/", getcwd()) . "/database";
		$files		= glob("database/*.sql");
		
		foreach ($files as $file) {
			$tb			=  explode(".",basename($file));
			$table		= $tb[0];
			
			sql_query("delete from $table");
			sql_query("LOAD DATA INFILE '$path/$table.sql' INTO TABLE $table");
			@unlink($file);
		}
		
		$zip->close();
	} 
	
	echo "數據庫恢復成功!";
	gotoURL(-1,3);
	exit;
}






echo "請選擇數據庫需要恢復到的日期：<br><br>";

$files		= glob("database_backup/*.zip");
	
	foreach ($files as $file) {
		$item		= explode(".",basename($file));
		$filename	= $item[0];
		echo $filename."-------------------------------<a href='db_restore_table.php?date=$filename'><font color=red>[ 恢復 ]</font></a> <br>";
	}

echo "<br><br>=====================================<br>";

echo "<font color=red>請慎重選擇操作！</font>";


?>