<?php

include_once "inc_common.php";


//$user_name	= $_COOKIE['user_name'];

//$user		= sql_getObj("select * from service_user where user='$user_name'");

if (empty($user)) 		exit;


if (isset($_GET['read'])) {

	$read		= $_GET['read'] * 1;
	$mid		= $_GET['mid'] * 1;

	if (sql_check("select 1 from board where id='$read' and (`from`='$user->user' or concat(',', `to`, ',') like '%,$user->user,%' or `to` = 'all')"))
		sql_query("update board set `highlight`=replace(`highlight`, '$user->user,', '') where id='$read'");

	$item		= sql_getObj("select * from board where id='$read'");

	if (!contain(",$item->highlight,", ",$user->user,") and !empty($item->highlight))
		echo "<script> document.getElementById('flag$mid').className = 'fa fa-flag-checkered'; </script>";
	else
		echo "<script> document.getElementById('flag$mid').style.display = 'none'; </script>";

	exit;

}

if (isset ($_GET['movefolder'])) {
	$id					= $_GET['id'] * 1;
	$folder_id			= $_GET['movefolder'] *1;
	$folder				= sql_getValue("select folder from folder where id = $folder_id");

	sql_query("update board set folder='$folder' where id='$id'");

	exit;
}

if (isset($_GET['delete'])) {

	$id		= $_GET['delete'] * 1;

	if (!sql_check("select 1 from board where (concat(',', `to`, ',') like '%,$user->user,%' or `to` = 'all' or `to` = 'all,') and id='$id'"))	die("Error");

	sql_query("update board set status='deleted' where id='$id'");
	exit;

}

?>