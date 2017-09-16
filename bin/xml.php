<?php
/* xml.php - Revision: AJAX Release 1.0 */

//	Copyright (c) 2006. Gavin Fu(傅政樺). All rights reserved.
//
//	語法：
//	說明：
//		
//		
//		
//		
//		
//
require_once $_SERVER['DOCUMENT_ROOT'].'/bin/templates.php';
header("Content-Type: text/xml");
if (strtolower($GLOBALS['Cache'])=='no cache') echo '<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
';
elseif (is_int($GLOBALS['Cache']) || ereg('^[0-9]+$', $GLOBALS['Cache'])) echo '<meta http-equiv="Cache-Control" content="max-age='.$GLOBALS['Cache'].'" />
';
echo '<?xml version="1.0" encoding="UTF-8"?>
';
function XML($Variables, $Query, $MySQL_Database=false) {
	if (is_array($Variables)) {
		echo '<'.$Variables['Name'].' Type="'.$Variables['Type'].'">';
		$Variables = $Variables['Name'];
	} else echo '<'.$Variables.'>';
	$PrimaryKey = -1;
	$Result = mysql_db_query($MySQL_Database ? $MySQL_Database : $GLOBALS['MySQL_Database'], $Query, $GLOBALS['MySQL']) or trigger_error(mysql_error(), E_USER_ERROR);
	for ($i=0; $i<mysql_num_fields($Result); $i++) if (mysql_field_name($Result, $i)=='PrimaryKey') {$PrimaryKey = $i;break;}
	if (mysql_num_rows($Result)==0) {
		echo '<Row>NULL</Row>';
	} elseif ($PrimaryKey>=0) {
		if (mysql_num_fields($Result)==2) {
			for ($i=0; $i<mysql_num_rows($Result); $i++) echo '<'.mysql_result($Result, $i, $PrimaryKey).'>'.mysql_result($Result, $i, $PrimaryKey^1).'</'.mysql_result($Result, $i, $PrimaryKey).'>';
		} else {
			while($Temp_Row = mysql_fetch_assoc($Result)) {
				$KeyValue = array_splice($Temp_Row, $PrimaryKey, 1);
				echo '<Row Value="'.$KeyValue['PrimaryKey'].'">';
				while (list($Key, $Value) = each($Temp_Row)) echo '<'.$Key.'>'.$Value.'</'.$Key.'>';
				echo '</Row>';
			}
		}
	} elseif (eregi('LIMIT +1 *$', $Query)) {
		$Temp_Row = mysql_fetch_assoc($Result);
		while (list($Key, $Value) = each($Temp_Row)) echo '<'.$Key.'>'.$Value.'</'.$Key.'>';
	} elseif (mysql_num_rows($Result)==1 && mysql_num_fields($Result)==1) {
		echo mysql_result($Result, 0);
	} elseif (mysql_num_fields($Result)==1) {
		for ($i=0; $i<mysql_num_rows($Result); $i++) echo '<Row>'.mysql_result($Result, $i).'</Row>';
	} else {
		for ($i=0; $i<mysql_num_rows($Result); $i++) {
			echo '<Row>';
			$Temp_Row = mysql_fetch_assoc($Result);
			while (list($Key, $Value) = each($Temp_Row)) echo '<'.$Key.'>'.$Value.'</'.$Key.'>';
			echo '</Row>';
		}
	}
	echo '</'.$Variables.'>';
	mysql_free_result($Result);
}
?>