<?
//error_reporting(E_CORE_ERROR);

require_once $_SERVER['DOCUMENT_ROOT'].'/bin/templates.php';

$Javascript = '<script type="text/JavaScript" src="'.$MainFolder.'/script/main.js"></script>
';
$MetaTag = '<link rel="shortcut icon" href="http://'.$_SERVER['HTTP_HOST'].'/favicon.ico" type="image/vnd.microsoft.icon" /> 
<link rel="icon" href="http://'.$_SERVER['HTTP_HOST'].'/favicon.ico" type="image/vnd.microsoft.icon" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
';
$CSS = '<link href="'.$MainFolder.'/style/main.css" rel="stylesheet" type="text/css">
';
$NowCount = MySQL_Data('SELECT COUNT(*) FROM `browse_record` WHERE DATE_ADD(`LastVisit`, INTERVAL 3 MINUTE) > now()');
$TodayCount = MySQL_Data('SELECT COUNT(*) FROM `browse_record` WHERE `FirstVisit` LIKE \''.date("Y-m-d", mktime()).'%\'');
$YesterdayCount = MySQL_Data('SELECT COUNT(*) FROM `browse_record` WHERE `FirstVisit` LIKE \''.date("Y-m-d", mktime()-86400).'%\'');
$MonthlyCount = MySQL_Data('SELECT COUNT(*) FROM `browse_record` WHERE `FirstVisit` LIKE \''.date("Y-m").'%\'');
$TotalCount = MySQL_Data('SELECT Sum(`Count`) FROM `Counter`');
$MaxDate = MySQL_Data('SELECT `Year`, `Month` FROM `Counter` ORDER BY `Year` DESC , `Month` DESC LIMIT 1');
$TotalCount += MySQL_Data("SELECT Count(*) Count FROM `browse_record` WHERE `FirstVisit` > '".($MaxDate['Year']+2000)."-".($MaxDate['Month']<10 ? '0'.$MaxDate['Month'] : $MaxDate['Month'])."-31'");
?>