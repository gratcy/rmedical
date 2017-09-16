<?php 


$visitor_ip	= $_SERVER['REMOTE_ADDR'];


// Skip own visit
if (startWith($visitor_ip, "192."))		return;

$page		= $_SERVER["REQUEST_URI"];
$time		= date('Y-m-d H:i:s');
$date		= date('Y-m-d');


if (contain($page, 'admin'))			return;


mysql_query("CREATE TABLE IF NOT EXISTS `visitor` (
			`id` int( 10 ) NOT NULL AUTO_INCREMENT ,
			`ip` varchar( 25 ) NOT NULL ,
			`time` datetime default NULL ,
			`page` varchar( 80 ) NOT NULL ,
			`view` int( 10 ) NOT NULL ,
			PRIMARY KEY ( `id` ) 
			) ENGINE = MYISAM;	");


if (!sql_check("select 1 from visitor where ip='$visitor_ip' and page='$page' and date(time)='$date' limit 1"))
	mysql_query("insert into visitor (ip, page, time, view) values ('$visitor_ip', '$page', '$time', 1)");
else
	mysql_query("update visitor set view=view+1 where ip='$visitor_ip' and page='$page' and date(time)='$date'");

?>