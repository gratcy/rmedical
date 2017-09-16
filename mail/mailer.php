<?php

$data			= rawurldecode($_GET['data']);
$data			= stripslashes($data);
$data			= unserialize($data);

$result			= @mail($data['to'], $data['subject'], $data['message'], $data['header'], $data['parameters']);

if ($result)	echo "true";
else			echo "false";

exit;


/*
function mailer($name, $email, $subject, $content, $sender, $reply) {
	

	$mail_server			= 'localhost';
	$mail_server_username	= '';
	$mail_server_password	= '';
	
	$SMTPAuth				= ($mail_server_username != '');
	
	
	/////////////////////////////////////////////
	// Process mail	
	/////////////////////////////////////////////
	
	require_once "class_mailer.php";
	
	$mail = new PHPMailer();
	
	$mail->Priority		= 3;
	$mail->Encoding		= "8bit";
	$mail->CharSet		= "utf-8";
	$mail->Mailer		= "smtp";
	$mail->WordWrap		= 0;
	$mail->Host			= $mail_server;
	$mail->Port			= 25;
	$mail->Helo			= "localhost.localdomain";
	$mail->SMTPAuth		= $SMTPAuth;
	$mail->Username		= $mail_server_username;
	$mail->Password		= $mail_server_password;
	$mail->ContentType	= "text/html";
	$mail->IsHTML(true);
	
	
	
	$mail->From			= $reply;
	$mail->FromName		= $sender;
	$mail->Sender		= $sender;
	$mail->Subject		= $subject;
	$mail->Body			= $content;
	$mail->AltBody		= "";
	$mail->AddReplyTo($reply, $sender);
	
	//$mail->ClearAddresses();
	$mail->AddAddress($email);
	$mail->Send();
	
	
	$error	= $mail->ErrorInfo;
	
	$mail->SmtpClose();
	
	return ($error == '');


}




function mailer($name, $email, $subject, $content, $sender, $reply) {
	

	$mail_server			= 'localhost';
	$mail_server_username	= '';
	$mail_server_password	= '';
	
	$SMTPAuth				= ($mail_server_username != '');
	
	
	/////////////////////////////////////////////
	// Process mail	
	/////////////////////////////////////////////
	
	require_once "class_mailer.php";
	
	$mail = new PHPMailer();
	
	$mail->Priority		= 3;
	$mail->Encoding		= "8bit";
	$mail->CharSet		= "utf-8";
	$mail->Mailer		= "smtp";
	$mail->WordWrap		= 0;
	$mail->Host			= $mail_server;
	$mail->Port			= 25;
	$mail->Helo			= "localhost.localdomain";
	$mail->SMTPAuth		= $SMTPAuth;
	$mail->Username		= $mail_server_username;
	$mail->Password		= $mail_server_password;
	$mail->ContentType	= "text/html";
	$mail->IsHTML(true);
	
	
	
	$mail->From			= $reply;
	$mail->FromName		= $sender;
	$mail->Sender		= $sender;
	$mail->Subject		= $subject;
	$mail->Body			= $content;
	$mail->AltBody		= "";
	$mail->AddReplyTo($reply, $sender);
	
	//$mail->ClearAddresses();
	$mail->AddAddress($email);
	$mail->Send();
	
	
	$error	= $mail->ErrorInfo;
	
	$mail->SmtpClose();
	
	return ($error == '');


}
*/

?>
