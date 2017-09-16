<?
@session_start();
if (isset($_SESSION['LoginID'])) {
	if (isset($_SESSION['UserInformation']) && $_SESSION['UserInformation']==$_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"]) {
		$User = MySQL_Data('SELECT `LoginID` ID, `Authority`, `Edit` FROM `members` WHERE CONVERT(`LoginID` USING utf8) = \''.addslashes($_SESSION['LoginID']).'\' AND BINARY `Password` = DECODE(0x'.$_SESSION['Password'].', \''.addslashes($_SERVER['REMOTE_ADDR'].' '.$_SERVER["HTTP_X_FORWARDED_FOR"]).'\') LIMIT 1');
	}
} elseif (isset($_COOKIE['UserInformation'])) {
	$_SESSION['LoginID'] = substr($_COOKIE['UserInformation'], 0, strpos($_COOKIE['UserInformation'], ' '));
	$_SESSION['UserInformation'] = $_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"];
	$_SESSION['Password'] = MySQL_Data('SELECT DECODE(0x'.substr($_COOKIE['UserInformation'], strpos($_COOKIE['UserInformation'], ' ')+1).', \''.addslashes(substr($_SERVER['REMOTE_ADDR'], 0, strrpos($_SERVER['REMOTE_ADDR'], '.'))).' '.($_SERVER["HTTP_X_FORWARDED_FOR"]=='' ? '' : addslashes(substr($_SERVER["HTTP_X_FORWARDED_FOR"], 0, strrpos($_SERVER["HTTP_X_FORWARDED_FOR"], '.')))).'\')');
	$User = MySQL_Data('SELECT `LoginID` ID, `Authority` FROM `members` WHERE `LoginID` = \''.addslashes($_SESSION['LoginID']).'\' AND `Password` = \''.addslashes($_SESSION['Password']).'\' LIMIT 1');
	$_SESSION['Password'] = MySQL_Data('SELECT HEX(ENCODE(\''.addslashes($_SESSION['Password']).'\', \''.addslashes($_SERVER['REMOTE_ADDR'].' '.$_SERVER["HTTP_X_FORWARDED_FOR"]).'\'))');
	setcookie('UserInformation', $_COOKIE['UserInformation'], time()+7776000);
	Counter('cLogin:'.$_SESSION['LoginID'], 'Control');
}

if (isset($User['ID'])) {
	$LogPage = '/admin/logging.php?Action=Logout&Link='.rawurlencode($_SERVER['REQUEST_URI']);
} else {
	unset($User);
	if (isset($_SESSION['LoginID'])) {
		setcookie('UserInformation', '');
		session_destroy();
	}
	$LogPage = '/admin/logging.php?Link='.rawurlencode($_SERVER['REQUEST_URI']);
}

function UserLogin() {
	global $User, $NowCount, $TodayCount, $YesterdayCount, $MonthlyCount, $TotalCount;
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$IP = $_SERVER["HTTP_X_FORWARDED_FOR"];
		$UserInformation = $_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"].' %';
	} else {
		$IP = $_SERVER['REMOTE_ADDR'];
		$UserInformation = $_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"].' %';
	}
	$TotalFail = MySQL_Data('SELECT `Control` FROM `browse_record` WHERE `IP` = '.ip2long($IP).' AND `User Information` LIKE \''.addslashes($UserInformation).'\' AND `LastVisit` > \''.date("Y-m-d H:i:s", mktime()-900).'\'');
	if (is_array($TotalFail)) $TotalFail = implode('', $TotalFail);
	if (ereg('Login:[a-zA-Z0-9]+\[fail\];', $TotalFail)) $TotalFail = count(split('Login:[a-zA-Z0-9]+\[fail\];', $TotalFail))-1; else $TotalFail = 0;
	if ($TotalFail < 5) {
		if ($_SESSION['LoginID'] = MySQL_Data('SELECT `LoginID` FROM `members` WHERE CONVERT(`LoginID` USING utf8) = \''.addslashes($_POST['LoginID']).'\' AND `Password` = PASSWORD(\''.addslashes($_POST['Password']).'\')')) {
			$_SESSION['Password'] = MySQL_Data('SELECT HEX(ENCODE(PASSWORD(\''.addslashes($_POST['Password']).'\'), \''.addslashes($_SERVER['REMOTE_ADDR'].' '.$_SERVER["HTTP_X_FORWARDED_FOR"]).'\'))');
			$_SESSION['UserInformation'] = $_SERVER['HTTP_USER_AGENT'].' '.$_SERVER["HTTP_VIA"];
			if (isset($_POST['SavePassword'])) setcookie('UserInformation', $_SESSION['LoginID'].' '.MySQL_Data('SELECT HEX(ENCODE(PASSWORD(\''.addslashes($_POST['Password']).'\'), \''.substr($_SERVER['REMOTE_ADDR'], 0, strrpos($_SERVER['REMOTE_ADDR'], '.')).' '.($_SERVER["HTTP_X_FORWARDED_FOR"]=='' ? '' : addslashes(substr($_SERVER["HTTP_X_FORWARDED_FOR"], 0, strrpos($_SERVER["HTTP_X_FORWARDED_FOR"], '.')))).'\'))'), time()+7776000);
			$User['ID'] = $_SESSION['LoginID'];
			Counter('Login:'.$_POST['LoginID'], 'Control');
			include_once Message('歡迎您回來，'.$_SESSION['LoginID'].'。現在將轉入登錄前頁面。', $_GET['Link']);
		} else {
			Counter('Login:'.$_POST['LoginID'].'[fail]', 'Control');
			include_once Message('用戶名無效或密碼錯誤錯誤，您可以有至多 5 次嘗試，請重新登入。', '?Link='.rawurlencode($_GET['Link']), '重新登入');
		}
	} else {
		Counter('Login:'.$_POST['LoginID'].'[fail]', 'Control');
		include_once Message('累計 5 次錯誤嘗試，15 分鐘內您將不能登錄網頁。', '/password.php?Link='.rawurlencode($_GET['Link']), '重新取得密碼');
	}
}

function UserLogout() {
	setcookie('UserInformation', '');
	session_destroy();
	Counter('Logout', 'Control');
}

function CheckVerifyKey() {
	$LastVisit = MySQL_Data('SELECT UNIX_TIMESTAMP(`FirstVisit`) FROM `browse_record` WHERE `Control` LIKE \'%Login:'.addslashes($_GET['LoginID']).';%\' OR `Control` LIKE \'%ResetPW:'.addslashes($_GET['LoginID']).';%\' ORDER BY `FirstVisit` DESC LIMIT 1');
	ereg('([a-z])', $_GET['VerifyKey'], $VerifyKey);
	$VerifyKey = strpos($_GET['VerifyKey'], $VerifyKey[1]);
	$Difference = substr($_GET['VerifyKey'], 0, $VerifyKey);
	$VerifyKey = substr($_GET['VerifyKey'], $VerifyKey+1);
	if ($VerifyKey==md5($_GET['LoginID'].' '.$LastVisit.' '.$Difference) && mktime()<=$LastVisit+$Difference+259200) {
		mysql_db_query(MySQL_Data('SELECT `Database` FROM `members` WHERE CONVERT(`LoginID` USING utf8) = \''.$_GET['LoginID'].'\''), 'UPDATE `members` SET `Password` = PASSWORD(\''.addslashes($_POST['Password']).'\') WHERE `LoginID` = \''.addslashes($_GET['LoginID']).'\'');
		return true;
	} else {
		return false;
	}
}

function SendVerifyKey($Member) {
	$LastVisit = MySQL_Data('SELECT UNIX_TIMESTAMP(`FirstVisit`) FROM `browse_record` WHERE `Control` LIKE \'%Login:'.addslashes($Member['LoginID']).';%\' OR `Control` LIKE \'%ResetPW:'.addslashes($Member['LoginID']).';%\' ORDER BY `FirstVisit` DESC LIMIT 1');
	if ($LastVisit) $VerifyKey = mktime()-$LastVisit; else $VerifyKey = mktime();
	$VerifyKey = $VerifyKey.chr(mt_rand(97,122)).md5($Member['LoginID'].' '.$LastVisit.' '.$VerifyKey);
	SendMail($Member['E-Mail'], $SiteName.' - 會員帳戶密碼', '<p>如果要重設您的'.$SiteName.'會員帳戶密碼，請造訪以下連結。</p>
<p>會員帳戶：'.$Member['LoginID'].'</p>
<p>重設密碼：<a href="http://'.$_SERVER['SERVER_NAME'].'/password.php?LoginID='.$Member['LoginID'].'&VerifyKey='.$VerifyKey.'">http://'.$_SERVER['SERVER_NAME'].'/password.php?LoginID='.$Member['LoginID'].'&VerifyKey='.$VerifyKey.'</a></p>
<p>如果按下上述的連結並沒有作用，請複製該 URL 並貼到新的瀏覽器視窗中。</p>
<p>感謝您瀏覽<a href="http://'.$_SERVER['SERVER_NAME'].'">'.$SiteName.'</a></p>');
}

function Authority($Authority) {
	$UserAuthority = $GLOBALS['User']['Authority']^str_pad('', strlen($GLOBALS['User']['Authority']), chr(146).chr(248).chr(184).chr(168).chr(247).chr(153).chr(148));
	$AuthorityID = MySQL_Data('SELECT `ID` FROM `authority` WHERE `Name` = \''.$Authority.'\'');
	if (($Index = strpos($UserAuthority, chr(0)))!==false) {
		$Authority = '';
		for ($i=$Index+1;$i<strlen($UserAuthority);$i++) $Authority .= MySQL_Data('SELECT `Authority` FROM `authority_group` WHERE `ID` = '.ord($UserAuthority[$i]));
		$UserAuthority = ($Authority^str_pad('', strlen($Authority), chr(146).chr(248).chr(184).chr(168).chr(247).chr(153).chr(148))).substr($UserAuthority, 0, $Index);
		$GLOBALS['User']['Authority'] = $UserAuthority^str_pad('', strlen($UserAuthority), chr(146).chr(248).chr(184).chr(168).chr(247).chr(153).chr(148));
	}
	if (($index = strpos($UserAuthority, chr(1)))!==false) {
		while (($Chr = substr($UserAuthority, -1)) != chr(1)) $UserAuthority = str_replace($Chr, '', $UserAuthority);
		$UserAuthority = str_replace(chr(1), '', $UserAuthority);
		$GLOBALS['User']['Authority'] = $UserAuthority^str_pad('', strlen($UserAuthority), chr(146).chr(248).chr(184).chr(168).chr(247).chr(153).chr(148));
	}
	return strpos($UserAuthority, chr($AuthorityID)) !== false ? true : false;
}

function MySQL_Encrypt_Data($Query, $MySQL_Database=false) {
	$Query = str_replace(']', ', \'Gavin Fu\')', str_replace('[`', 'AES_DECRYPT(`', str_replace('[\'', 'AES_ENCRYPT(\'', $Query)));
	$Query = str_replace('{`', 'CONV(HEX(`', str_replace('`}', '`), 16, 10)', $Query));
	return MySQL_Data($Query, $MySQL_Database);
}

function MySQL_Insert($MySQL_Table, $Data, $MySQL_Database=false) {
	$Result = mysql_Data("SHOW COLUMNS FROM `$MySQL_Table`", $MySQL_Database);
	$Variables = ''; $Values = '';
	for ($i=0;$i<count($Result);$i++) {
		$KeyName = str_replace(' ', '_', $Result[$i][Field]);
		if ($Result[$i][Field]=='Authority' && (isset($Data['Authority']) || isset($Data['Group']))) {
			if ($Variables!='') {$Variables .= ', '; $Values .= ', ';}
			$Variables .= '`'.$Result[$i][Field].'`';
			$Authority = '';
			if (isset($Data['Authority'])) {
				for ($j=0; $j<count($Data['Authority']); $j++) {
					$Data['Authority'][$j] = chr($Data['Authority'][$j]);
				}
				shuffle($Data['Authority']);
				$Authority = implode('', $Data['Authority']);
			}
			if (isset($Data['disAuthority'])) {
				for ($j=0; $j<count($Data['disAuthority']); $j++) {
					$Data['disAuthority'][$j] = chr($Data['disAuthority'][$j]);
				}
				shuffle($Data['disAuthority']);
				$Authority .= chr(1).implode('', $Data['disAuthority']);
			}
			if (isset($Data['Group'])) {
				for ($j=0; $j<count($Data['Group']); $j++) {
					$Data['Group'][$j] = chr($Data['Group'][$j]);
				}
				shuffle($Data['Group']);
				$Authority .= chr(0).implode('', $Data['Group']);
			}
			$Values .= $Authority=='' ? '\'\'' : '0x'.bin2hex($Authority^str_pad('', strlen($Authority), chr(146).chr(248).chr(184).chr(168).chr(247).chr(153).chr(148)));
		} elseif (isset($Data[$KeyName])) {
			if ($Result[$i][Field]=='Password') {
				if ($Variables!='') {$Variables .= ', '; $Values .= ', ';}
				$Variables .= '`'.$Result[$i][Field].'`';
				$Values .= 'PASSWORD(\''.addslashes($Data[$KeyName]).'\')';
			} elseif (is_array($Data[$KeyName]) && implode('', $Data[$KeyName])!='') {
				if ($Variables!='') {$Variables .= ', '; $Values .= ', ';}
				$Variables .= '`'.$Result[$i][Field].'`';
				if (isset($Data[$KeyName]['Y']) && isset($Data[$KeyName]['M']) && isset($Data[$KeyName]['D'])) {
					if (eregi('blob', $Result[$i]['Type'])) $Values .= '0x'.dechex(mktime(0, 0, 0, $Data[$KeyName]['M'], $Data[$KeyName]['D'], $Data[$KeyName]['Y']));
					else $Values .= '\''.$Data[$KeyName]['Y'].'-'.$Data[$KeyName]['M'].'-'.$Data[$KeyName]['D'].'\'';
				} else {
					if (eregi('blob', $Result[$i]['Type'])) $Values .= 'AES_ENCRYPT(\''.addslashes(implode("\r\n", $Data[$KeyName])).'\', \'Gavin Fu\')';
					else $Values .= '\''.addslashes(implode("\r\n", $Data[$KeyName])).'\'';
				}
			} elseif (!is_array($Data[$KeyName]) && $Data[$KeyName]!='') {
				if ($Variables!='') {$Variables .= ', '; $Values .= ', ';}
				$Variables .= '`'.$Result[$i][Field].'`';
				if (eregi('blob', $Result[$i]['Type'])) $Values .= 'AES_ENCRYPT(\''.addslashes($Data[$KeyName]).'\', \'Gavin Fu\')';
				else $Values .= '\''.addslashes($Data[$KeyName]).'\'';
			}
		}
	}
	if ($Variables!='') mysql_db_query($MySQL_Database ? $MySQL_Database : $GLOBALS['MySQL_Database'], 'INSERT INTO `'.$MySQL_Table.'` ('.$Variables.') VALUES ('.$Values.')', $GLOBALS['MySQL']) or trigger_error(mysql_error().' \'<b>'.$Query.'</b>\'', E_USER_ERROR);
}

function MySQL_Update($MySQL_Table, $Data, $Args=false, $MySQL_Database=false) {
	if (gettype($Args)!='array') {$MySQL_Database=$Args; $Args=false;}
	$Result = mysql_Data("SHOW COLUMNS FROM `$MySQL_Table`", $MySQL_Database);
	$Update = ''; $Where = '';
	for ($i=0;$i<count($Result);$i++) {
		$KeyName = str_replace(' ', '_', $Result[$i][Field]);
		if ($Result[$i][Field]=='Authority' && (isset($Data['Authority']) || isset($Data['Group']))) {
			if ($Update!='') $Update .= ', ';
			$Authority = '';
			if (isset($Data['Authority'])) {
				for ($j=0; $j<count($Data['Authority']); $j++) {
					$Data['Authority'][$j] = chr($Data['Authority'][$j]);
				}
				shuffle($Data['Authority']);
				$Authority = implode('', $Data['Authority']);
			}
			if (isset($Data['disAuthority'])) {
				for ($j=0; $j<count($Data['disAuthority']); $j++) {
					$Data['disAuthority'][$j] = chr($Data['disAuthority'][$j]);
				}
				shuffle($Data['disAuthority']);
				$Authority .= chr(1).implode('', $Data['disAuthority']);
			}
			if (isset($Data['Group'])) {
				for ($j=0; $j<count($Data['Group']); $j++) {
					$Data['Group'][$j] = chr($Data['Group'][$j]);
				}
				shuffle($Data['Group']);
				$Authority .= chr(0).implode('', $Data['Group']);
			}
			$Update .= '`'.$Result[$i][Field].'` = 0x'.bin2hex($Authority^str_pad('', strlen($Authority), chr(146).chr(248).chr(184).chr(168).chr(247).chr(153).chr(148)));
		} elseif (isset($Data[$KeyName])) {
			if ($Result[$i][Field]=='Password') {
				if ($Data[$KeyName]!='')
					if ((Authority('管理帳戶') && $Data['LoginID']!=$GLOBALS['User']['ID']) || ($Data['LoginID']==$GLOBALS['User']['ID'] && MySQL_Data('SELECT \'True\' FROM `members` WHERE `LoginID` = \''.addslashes($Data['LoginID']).'\' AND `Password` = PASSWORD(\''.addslashes($Data['OldPassword']).'\')'))) {
						if ($Update!='') $Update .= ', ';
						$Update .= '`Password` = PASSWORD(\''.addslashes($Data['Password']).'\')';
					} else {
						include_once Message('原密碼不正確，您不能修改 密碼 或 電子郵件地址，請返回。');
						exit;
					}
			} elseif ($Result[$i][Field]=='E-Mail' && isset($Data['OldE-Mail'])) {
				if ($Data['E-Mail'] != $Data['OldE-Mail'])
					if ((Authority('管理帳戶') && $Data['LoginID']!=$GLOBALS['User']['ID']) || ($Data['LoginID']==$GLOBALS['User']['ID'] && MySQL_Data('SELECT \'True\' FROM `members` WHERE `LoginID` = \''.addslashes($Data['LoginID']).'\' AND `Password` = PASSWORD(\''.addslashes($Data['OldPassword']).'\')'))) {
						if ($Update!='') $Update .= ', ';
						$Update .= '`E-Mail` = AES_ENCRYPT(\''.$Data['E-Mail'].'\', \'Gavin Fu\')';
					} else {
						include_once Message('原密碼不正確，您不能修改 密碼 或 電子郵件地址，請返回。');
						exit;
					}
			} elseif (is_array($Data[$KeyName]) && implode('', $Data[$KeyName])!='') {
				if ($Update!='') $Update .= ', ';
				if (isset($Data[$KeyName]['Y']) && isset($Data[$KeyName]['M']) && isset($Data[$KeyName]['D'])) {
					if (eregi('blob', $Result[$i]['Type'])) $Update .= '`'.$Result[$i][Field].'` = 0x'.dechex(mktime(0, 0, 0, $Data[$KeyName]['M'], $Data[$KeyName]['D'], $Data[$KeyName]['Y']));
					else $Update .= '`'.$Result[$i][Field].'` = \''.$Data[$KeyName]['Y'].'-'.$Data[$KeyName]['M'].'-'.$Data[$KeyName]['D'].'\'';
				} else {
					if (eregi('blob', $Result[$i]['Type'])) $Update .= '`'.$Result[$i][Field].'` = AES_ENCRYPT(\''.addslashes(implode("\r\n", $Data[$KeyName])).'\', \'Gavin Fu\')';
					else $Update .= '`'.$Result[$i][Field].'` = \''.addslashes(implode("\r\n", $Data[$KeyName])).'\'';
				}
			} elseif ($Data[$KeyName]=='' || is_array($Data[$KeyName])) {
				if ($Args===false && ($Result[$i]['Key']=='PRI' || $Result[$i][Field]=='LoginID') || (is_array($Args) && in_array($Result[$i][Field], $Args))) {
					if (isset($Data['old'.$KeyName]) && $Data[$KeyName] != $Data['old'.$KeyName]) {
						if ($Update!='') $Update .= ', ';
						if ($Result[$i]['Null']=='NO') $Update .= '`'.$Result[$i][Field].'` = \'\'';
						else $Update .= '`'.$Result[$i][Field].'` = NULL';
						if ($Where!='') $Where .= ' AND ';
						$Where .= 'CONVERT(`'.$Result[$i][Field].'` USING utf8) = \''.addslashes($Data['old'.$KeyName]).'\'';
					} else {
						if ($Where!='') $Where .= ' AND ';
						if ($Result[$i]['Null']=='NO') $Where .= 'CONVERT(`'.$Result[$i][Field].'` USING utf8) = \'\'';
						else $Where .= '`'.$Result[$i][Field].'` IS NULL';
					}
				} else {
					if ($Update!='') $Update .= ', ';
					if ($Result[$i]['Null']=='NO') $Update .= '`'.$Result[$i][Field].'` = \'\'';
					else $Update .= '`'.$Result[$i][Field].'` = NULL';
				}
			} else {
				if (eregi('blob', $Result[$i]['Type'])) {
					if ($Update!='') $Update .= ', ';
					$Update .= '`'.$Result[$i][Field].'` = AES_ENCRYPT(\''.addslashes($Data[$KeyName]).'\', \'Gavin Fu\')';
				} elseif ($Args===false && ($Result[$i]['Key']=='PRI' || $Result[$i][Field]=='LoginID') || (is_array($Args) && in_array($Result[$i][Field], $Args))) {
					if (isset($Data['old'.$KeyName])) {
						if ($Update!='') $Update .= ', ';
						$Update .= '`'.$Result[$i][Field].'` = \''.addslashes($Data[$KeyName]).'\'';
						if ($Where!='') $Where .= ' AND ';
						if ($Data['old'.$KeyName] == '') {
							if ($Where!='') $Where .= ' AND ';
							if ($Result[$i]['Null']=='NO') $Where .= 'CONVERT(`'.$Result[$i][Field].'` USING utf8) = \'\'';
							else $Where .= '`'.$Result[$i][Field].'` IS NULL';
						} else $Where .= 'CONVERT(`'.$Result[$i][Field].'` USING utf8) = \''.addslashes($Data['old'.$KeyName]).'\'';
					} else {
						if ($Where!='') $Where .= ' AND ';
						$Where .= 'CONVERT(`'.$Result[$i][Field].'` USING utf8) = \''.addslashes($Data[$KeyName]).'\'';
					}
				} else {
					if ($Update!='') $Update .= ', ';
					$Update .= '`'.$Result[$i][Field].'` = \''.addslashes($Data[$KeyName]).'\'';
				}
			}
		}
	}
	if ($Update!='' && $Where!='') mysql_db_query($MySQL_Database ? $MySQL_Database : $GLOBALS['MySQL_Database'], 'UPDATE `'.$MySQL_Table.'` SET '.$Update.' WHERE '.$Where, $GLOBALS['MySQL']) or trigger_error(mysql_error().' \'<b> UPDATE `'.$MySQL_Table.'` SET '.$Update.' WHERE '.$Where.$Query.'</b>\'', E_USER_ERROR);
}
?>