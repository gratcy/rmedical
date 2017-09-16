<?php

include "../bin/inc_config.php";

$view	= $_GET['view'];

$view	= decrypt($view);

if (isset($view['email'])) {
	
	if (sql_check("select 1 from me_mail_trace where queue_id='{$view['queue_id']}' and email='{$view['email']}'"))
		sql_query("update me_mail_trace set count = count + 1 where queue_id='{$view['queue_id']}' and email='{$view['email']}'");
	else 
		sql_query("insert into me_mail_trace (queue_id, email) values ('{$view['queue_id']}', '{$view['email']}')");

}

$filename	= "r" . rand(1216, 9973);
header("Content-Type: image/jpeg");
header("Content-Disposition: attachment; filename=$filename.gif");

$file	= "./{$view['file']}";
$fp	= fopen($file, 'r');
fpassthru($fp);
fclose($fp);




?>