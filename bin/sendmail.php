<?php
function SendMail($eMail_Address, $Subject, $Content) {
	set_time_limit(60);
	if (isset($GLOBALS['SMTP_Server']) && $GLOBALS['SMTP_Server']!='') {
		$SMTP_Code[0] = array('EHLO '.$GLOBALS['SMTP_Server']."\r\n",'220,250','HELO error: ');
		if (isset($GLOBALS['SMTP_LoginID']) && $GLOBALS['SMTP_LoginID']!='') {
			$SMTP_Code[1] = array("AUTH LOGIN\r\n",'334','AUTH error:');
			$SMTP_Code[2] = array(base64_encode($GLOBALS['SMTP_LoginID'])."\r\n",'334','AUTHENTIFICATION error : ');
			$SMTP_Code[3] = array(base64_encode($GLOBALS['SMTP_Password'])."\r\n",'235','AUTHENTIFICATION error : ');
		}
		$SMTP_Code[] = array('MAIL FROM: <'.$GLOBALS['eMail_Address'].">\r\n",'250','MAIL FROM &lt;'.$GLOBALS['eMail_Address'].'&gt; error: ');
		$SMTP_Code[] = array('RCPT TO: <'.$eMail_Address.">\r\n",'250','RCPT TO &lt;'.$eMail_Address.'&gt; error: ');
		$SMTP_Code[] = array("DATA\r\n",'354','DATA error: ');
		$SMTP_Code[] = array('From: '.$GLOBALS['eMail_Address']."\r\n",'','');
		$SMTP_Code[] = array('To: '.$eMail_Address."\r\n",'','');
		$SMTP_Code[] = array('Subject: =?big5?B?'.base64_encode($Subject)."?=\r\n",'','');
		$SMTP_Code[] = array("MIME-Version: 1.0\r\n",'','');
		$SMTP_Code[] = array('Content-type: text/html; charset='.$GLOBALS['CharacterSet']."\r\n",'','');
		$SMTP_Code[] = array("Content-Transfer-Encoding: base64\r\n",'','');
		$SMTP_Code[] = array("\r\n",'','');
		$SMTP_Code[] = array(base64_encode($Content)."\r\n",'','');
		$SMTP_Code[] = array(".\r\n",'250','DATA(end)error: ');
		$SMTP_Code[] = array("QUIT\r\n",'221','QUIT error: ');
		if (!$Connect = fsockopen($GLOBALS['SMTP_Server'], 25)) {
			trigger_error('無法建立與外寄郵件伺服器['.$GLOBALS['SMTP_Server'].']的連接。', E_USER_ERROR);
			include_once Message('郵件寄出時發生錯誤，請稍後再試或與網站管理員聯絡。');
			exit;
		}
		while ($Result = fgets($Connect, 256)) if(substr($Result, 3, 1) == " ") break;
		foreach($SMTP_Code as $Req) {
			fputs($Connect, $Req[0]);
			if ($Req[1]) {
				while ($Result = fgets($Connect, 256)) if(substr($Result, 3, 1) == ' ') break;
				if (!strstr($Req[1], substr($Result, 0, 3))) {
					trigger_error($Req[2].$Result, E_USER_ERROR);
					include_once Message('郵件寄出時發生錯誤，請稍後再試或與網站管理員聯絡。');
					exit;
				}
			}
		}
		fclose($Connect);
	} else {
		if (!mail($eMail_Address, $Subject, $Content, 'MIME-Version: 1.0\r\nContent-type: text/html; charset='.$GLOBALS['CharacterSet'].'\r\nFrom: '.$GLOBALS['eMail_Address']."\r\n")) {
			include_once Message('郵件寄出時發生錯誤，請稍後再試或與網站管理員聯絡。');
			exit;
		}
	}
}
?>