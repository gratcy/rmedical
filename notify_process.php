<?php

include_once "inc_common.php";
	
$SMS_USER				= "rock-medical";
$SMS_PASSWORD			= "healthcare";



echo <<<EOS
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>磐石管理系統 Rock Management System</title>

<link href="style.css" rel="stylesheet" type="text/css">

<style>
textarea {
	overflow-y	: scroll;
	height		: 100px;
}
</style>

</head>


<body bgcolor=#ffffff style='margin:10px;'>
EOS;


if (isset($_POST)) {

	$send_email		= ($_POST['action'] == '傳送短信及電郵' || $_POST['action'] == '傳送電郵');
	$send_sms		= ($_POST['action'] == '傳送短信及電郵' || $_POST['action'] == '傳送短信');
	
	$tos			= array();
	foreach ($_POST as $name => $value) {
		if (substr($name, 0, 3) == "to_")
			$tos[]		= substr($name, 3);
	}
	
	if ($send_email) {
	
		$mail_sender	= "磐石 <contact@rock-medical.com>";
		$mail_sender	= explode("<", $mail_sender);
			
		$header			 = "MIME-Version: 1.0\r\n";
		$header			.= "From: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
		$header			.= "Reply-To: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
		$header			.= "Return-Path: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
		$header			.= "Content-Type: text/html; charset=utf-8\r\n";
		$header			.= "X-Mailer: PHP/" . phpversion();
		
		$emails		= sql_getArray("select email from staff where email != '' and id in (" . implode(',', $tos) . ")");

		foreach ($emails as $email) {
		
   			set_time_limit(10);
			//include_once "mail/mailer_proxy.php";
			$result		= mail($email, $_POST['email_title'], nl2br($_POST['email_content']), $header);
			if ($result != true)
				echo "Error sending email to : $email<br>";
		}
		
		echo '<p><font color=blue>傳送電郵成功。</font></p>';
		

	}
	
	if ($send_sms) {

		set_time_limit(60);
		include "sms_client.php";
		
		$phones		= sql_getArray("select mobile from staff where mobile != '' and id in (" . implode(',', $tos) . ")");
		
		$sms		= new SMS_CLIENT($SMS_USER, $SMS_PASSWORD);
		$result		= $sms->send($phones, $_POST['sms_content']);
		$results	= explode(', ', $result);
		
		$error		= false;
		$error_msg	= "";
		
		foreach ($results as $result) {
			if (substr($result, 0, 5) == 'Error') {
				$error			= true;
				$error_msg		.= $result . "<br>";
			}
		}
		
		if ($error) {
			echo "<p><font color=blue>傳送時發生錯誤：<br>$error_msg</font></p>";
		} else {
			echo '<p><font color=blue>傳送短信成功。</font></p>';
		}
		

	
	}
	
	echo "<input type=button value='關閉' onclick=\"parent.dialog_close('');\">";

}


echo "</body></html>";



?>