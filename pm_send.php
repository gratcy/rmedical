<?php

include_once "header.php";


echo "<center>";
echo "<div class=simple_dialog style='width:500px;'>";
echo "<h1>發送電子郵件</h1>";



if ($_POST['action'] == 'pm_send') {

	$fields					= sql_secure($_POST, 'to,title, content');
	
	$email					= $_POST['to'];
	$title					= $_POST['title'];
	$content				= $_POST['content'];
	$content 				= nl2br($content);

	$mail_sender	= "$SITENAME <contact@admin.rock-medical.com>";
	$mail_sender	= explode("<", $mail_sender);

	$header			= "MIME-Version: 1.0\r\n";
	$header			.= "From: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
	$header			.= "Reply-To: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
	$header			.= "Return-Path: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
	$header			.= "Content-Type: text/html; charset=utf-8\r\n";
	$header			.= "X-Mailer: PHP/" . phpversion();

	$success		= @mailer($email, $title, $content, $header);
	
	if ($success) {
		echo "<p><font color=blue>電子郵件已成功傳送！</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='board.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-1, 3);
	}
	else {
		echo "<p><font color=red>電子郵件發送失敗！</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='board.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-1, 3);
	}
}

echo "</div>";
echo "</center>";		


include "footer.php";

?>