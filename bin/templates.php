<?
/* templates.php - Revision: templates Release 1.0 */

//	Copyright (c) 2006. Gavin Fu(傅政樺). All rights reserved.
//
//	+------+---------+------------+---------+
//	| sales                                 |
//	+------+---------+------------+---------+
//	| year | country | product    | profit  |
//	+------+---------+------------+---------+
//	| 2000 | Finland | Computer   | 1500    |
//	| 2001 | Finland | Phone      | 100     |
//	| 2002 | USA     | Phone      | 500     |
//	| 2003 | USA     | Calculator | 760     |
//	| 2004 | USA     | TV         | 2418    |
//	+------+---------+------------+---------+
//
//	語法：MySQL_Data(str Query, [str Database]);
//	說明：
//		Query：MySQL 查詢指令
//		Database：MySQL 資料庫名稱，預設值為 'config.php' 檔內的數據庫名稱
//
//		查詢 MySQL並因應Query和結果的不同，把結果放在字串變數、一維陣列或二維陣列
//
//	例子：
//		$s = MySQL_Data('SELECT * FROM sales WHERE year = 2001');
//		$s[0]['year'] => 2001, $s[0]['country'] => 'Finland', $s[0]['product'] = 'Phone', $s[0]['profit'] = 100
//
//		$s = MySQL_Data('SELECT `year` FROM sales');
//		$s[0] => 2000, $s[1] => 2001, $s[2] => 2002, $s[3] => 2003, $s[4] => 2004
//
//		$s = MySQL_Data('SELECT * FROM sales WHERE year = 2001 LIMIT 1');
//		$s['year'] => 2001, $s['country'] => 'Finland', $s['product'] = 'Phone', $s['profit'] = 100
//		因為Query內包含'LIMIT 1'字串}
//
//		$s = MySQL_Data('SELECT `country` FROM sales WHERE year = 2001');
//		$s => 'Finland'
//
//		$s = MySQL_Data('SELECT `year` `PrimaryKey`, `profit` FROM sales WHERE year < 2003');
//		$s['2000'] => 1500, $s['2001'] => 100, $s['2002'] => 500
//		因為其中一個欄位名稱為'PrimaryKey'
//
//		$s = MySQL_Data('SELECT `year` `PrimaryKey`, `country`, `profit` FROM sales WHERE year = 2001');
//		$s['2000']['country'] => 'Finland', $s['2000']['profit'] => 1500, 
//		$s['2001']['country'] => 'Finland', $s['2001']['profit'] => 100, 
//		$s['2002']['country'] => 'USA', $s['2002']['profit'] => 500
//		因為其中一個欄位名稱為'PrimaryKey'
//



require_once $_SERVER['DOCUMENT_ROOT'].'/bin/config.php';
$MySQL = mysql_pconnect($MySQL_Server, $MySQL_LoginID, $MySQL_Password) or trigger_error(mysql_error(), E_USER_ERROR);
mysql_query('SET CHARACTER SET utf8', $MySQL);
mysql_query('SET collation_connection = \'utf8_general_ci\'', $MySQL);
$MainFolder = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/', 1));

function MySQL_Data($Query, $MySQL_Database=false) {
	$Query = str_replace('<`', 'UNIX_TIMESTAMP(`', str_replace('`>', '`)', $Query));
	$Result = mysql_db_query($MySQL_Database ? $MySQL_Database : $GLOBALS['MySQL_Database'], $Query, $GLOBALS['MySQL']) or trigger_error('MySQL syntax error at <b>'.$Query.'</b>', E_USER_ERROR);
	$PrimaryKey = -1;
	for ($i=0; $i<mysql_num_fields($Result); $i++) if (mysql_field_name($Result, $i)=='PrimaryKey') {$PrimaryKey = $i;break;}
	if (mysql_num_rows($Result)==0) return false;
	elseif ($PrimaryKey>=0) {
		if (mysql_num_fields($Result)==2) {
			for ($i=0; $i<mysql_num_rows($Result); $i++) $Row[mysql_result($Result, $i, $PrimaryKey)]=mysql_result($Result, $i, $PrimaryKey^1);
		} else {
			while($Temp_Row = mysql_fetch_assoc($Result)){
				$KeyValue = array_splice($Temp_Row, $PrimaryKey, 1);
				$Row[$KeyValue['PrimaryKey']] = $Temp_Row;
			}
		}
		return $Row;
	} elseif (mysql_num_rows($Result)==1 && mysql_num_fields($Result)==1) {
		return mysql_result($Result, 0);
	} elseif (eregi('LIMIT +1 *$', $Query)) {
		return mysql_fetch_assoc($Result);
	} else {
		if (mysql_num_fields($Result)==1) {
			for ($i=0; $i<mysql_num_rows($Result); $i++) $Row[$i]=mysql_result($Result, $i);
		} else {
			for ($i=0; $i<mysql_num_rows($Result); $i++) $Row[$i]=mysql_fetch_assoc($Result);
		}
		return $Row;
	}
	mysql_free_result($Result);
}

function Template($File=false, $TemplateFile='/templates/main.php') {
	if (isset($GLOBALS['Content'])) {
		return $GLOBALS['Content'];
	} else {
		$GLOBALS['Content'] = ($File[0] == '/' ? $_SERVER['DOCUMENT_ROOT'].$GLOBALS['MainFolder'].'/templates' : '').$File;
		if (strtolower($GLOBALS['Cache'])=='no cache') $GLOBALS['MetaTag'] .= '<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
';
		elseif (is_int($GLOBALS['Cache']) || ereg('^[0-9]+$', $GLOBALS['Cache'])) $GLOBALS['MetaTag'] .= '<meta http-equiv="Cache-Control" content="max-age='.$GLOBALS['Cache'].'" />
';
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
';
		if (!isset($GLOBALS['Count'])) Counter();
		unset($GLOBALS['MySQL_Server']);
		unset($GLOBALS['MySQL_LoginID']);
		unset($GLOBALS['MySQL_Password']);
		unset($GLOBALS['SMTP_Server']);
		unset($GLOBALS['SMTP_LoginID']);
		unset($GLOBALS['SMTP_Password']);
		return ($TemplateFile[0] == '/' ? $_SERVER['DOCUMENT_ROOT'].$GLOBALS['MainFolder'] : '').$TemplateFile;
	}
}

function Message($Message, $Link='Back', $Control=3, $TemplateFile='/templates/main.php') {
	$GLOBALS['Message'] = '<p>'.$Message.'</p>';
	$GLOBALS['Content'] = $_SERVER['DOCUMENT_ROOT'].$GLOBALS['MainFolder'].'/templates/message.php';
	if ($Link=='') $Link = '/index.php';
	if ($Link=='Back') {
		$Link = 'javascript:history.back()';
		$GLOBALS['Message'] .= '<p><a href="'.$Link.'">[ 點擊這裏返回上一頁 ]</a></p>';
	} elseif (is_int($Control)) {
		$GLOBALS['MetaTag'] .= '<meta http-equiv="Refresh" content="'.$Control.';url='.$Link.'">
';
		$GLOBALS['Message'] .= '<p><a href="'.$Link.'">如果您的瀏覽器沒有自動跳轉，請點擊這裏</a></p>';
	} else {
		$GLOBALS['Message'] .= '<p><a href="'.$Link.'">[ 點擊這裏'.$Control.' ]</a></p>';
	}
	if (strtolower($GLOBALS['Cache'])=='no cache') $GLOBALS['MetaTag'] .= '<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
';
	elseif (is_int($GLOBALS['Cache']) || ereg('^[0-9]+$', $GLOBALS['Cache'])) $GLOBALS['MetaTag'] .= '<meta http-equiv="Cache-Control" content="max-age='.$GLOBALS['Cache'].'" />
';
	if (!isset($GLOBALS['Count'])) Counter();
	unset($GLOBALS['MySQL_Server']);
	unset($GLOBALS['MySQL_LoginID']);
	unset($GLOBALS['MySQL_Password']);
	unset($GLOBALS['SMTP_Server']);
	unset($GLOBALS['SMTP_LoginID']);
	unset($GLOBALS['SMTP_Password']);
	return ($TemplateFile[0] == '/' ? $_SERVER['DOCUMENT_ROOT'].$GLOBALS['MainFolder'] : '').$TemplateFile;
}

function Counter($ID='', $Control='PageID') {
	$GLOBALS['Count'] = true;
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
		$UserInformation = $_SERVER['REMOTE_ADDR'].'-'.$_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"].'-'.crypt(session_id(),$IP);
	} else {
		$IP = $_SERVER['REMOTE_ADDR'];
		$UserInformation = $_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"].'-'.crypt(session_id(),$IP);
	}
	$IP = sprintf("%u", ip2long($IP));
	if (MySQL_Data('SELECT \'true\' FROM `browse_record` WHERE `IP` = '.$IP.' AND `User Information` = \''.addslashes($UserInformation).'\'')) {
		if ($Control=='PageID') {
			if ($ID=='' || MySQL_Data('SELECT \'true\' FROM `browse_record` WHERE (`Page` LIKE \'%;'.addslashes($ID).';%\' OR `Page` LIKE \''.addslashes($ID).';%\') AND `IP` = '.$IP.' AND `User Information` = \''.addslashes($UserInformation).'\'')) {
				mysql_db_query($GLOBALS['MySQL_Database'], 'UPDATE `browse_record` SET `LastVisit` = \''.date("Y-m-d H:i:s").'\' WHERE `IP` = '.$IP.' AND `User Information` = \''.addslashes($UserInformation).'\'');
			} else {
				mysql_db_query($GLOBALS['MySQL_Database'], 'UPDATE `browse_record` SET `Page` = CONCAT(`Page`, \''.addslashes($ID).';\'), `LastVisit` = \''.date("Y-m-d H:i:s").'\' WHERE `IP` = '.$IP.' AND `User Information` = \''.addslashes($UserInformation).'\'');
			}
		} else {
			mysql_db_query($GLOBALS['MySQL_Database'], 'UPDATE `browse_record` SET `Control` = CONCAT(`Control`, \''.addslashes($ID).';\'), `LastVisit` = \''.date("Y-m-d H:i:s").'\' WHERE `IP` = '.$IP.' AND `User Information` = \''.addslashes($UserInformation).'\'');
		}
	} else {
		$NowCount++;
		$TodayCount++;
		$MonthlyCount++;
		$TotalCount++;
		if ($Control=='PageID') {
			mysql_db_query($GLOBALS['MySQL_Database'], 'INSERT INTO `browse_record` (`IP`, `User Information`, `FirstVisit`, `LastVisit`, `Referer`, `Page`, `Control`) VALUES ('.$IP.', \''.addslashes($UserInformation).'\', \''.date("Y-m-d H:i:s").'\', \''.date("Y-m-d H:i:s").'\', '.(isset($_SERVER['HTTP_REFERER']) && !eregi('^[a-z]+://'.$_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']) ? '\''.$_SERVER['HTTP_REFERER'].'\'' : 'NULL').', \''.($ID=='' ? '' : addslashes($ID).';').'\', \'\')');
		} else {
			mysql_db_query($GLOBALS['MySQL_Database'], 'INSERT INTO `browse_record` (`IP`, `User Information`, `FirstVisit`, `LastVisit`, `Referer`, `Page`, `Control`) VALUES ('.$IP.', \''.addslashes($UserInformation).'\', \''.date("Y-m-d H:i:s").'\', \''.date("Y-m-d H:i:s").'\', '.(isset($_SERVER['HTTP_REFERER']) && !eregi('^[a-z]+://'.$_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']) ? '\''.$_SERVER['HTTP_REFERER'].'\'' : 'NULL').', \'\', \''.addslashes($ID).';\')');
		}
	}
	if (MySQL_Data('SELECT \'True\' FROM `Popup` WHERE `Allow` = \'True\'') && !MySQL_Data('SELECT \'true\' FROM `browse_record` WHERE (`Page` LIKE \'%;Popup;%\' OR `Page` LIKE \'Popup;%\') AND `IP` = '.$IP.' AND `User Information` = \''.addslashes($UserInformation).'\' AND `LastVisit` > \''.date("Y-m-d H:i:s", mktime()-3600).'\'') && $_SERVER['PHP_SELF'] != '/admin/logging.php') $GLOBALS['Javascript'] .= '<script type="text/javascript">
<!-- 
window.open(\'/popup.php\',\'Message\',\'scrollbars=yes,resizable=yes,width=550,height=500\');
//-->
</script>
';
}

require_once $_SERVER['DOCUMENT_ROOT'].'/bin/chinese.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/bin/security.php';
?>