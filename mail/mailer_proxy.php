<?php


$MAILER_URL			= "http://www.ecmug.com/mail/mailer.php";

function mailer($to, $subject, $message, $header = '', $parameters = '') {
	
	global	$MAILER_URL;

	$data			= array(
		'to'			=> $to,
		'subject'		=> $subject,
		'message'		=> $message,
		'header'		=> $header,
		'parameters'	=> $parameters
					);
	
	$data				= rawurlencode(serialize($data));

	$result				= file_get_contents("$MAILER_URL?data=$data");
	
	$result				= ($result == 'true') ? true : $result;
	
	return $result;

	
}

/*

function mailer($name, $email, $subject, $content, $sender, $reply) {
	
	global	$MAILER_URL;
	
	$data	= array(
		'name'		=> $name,
		'email'		=> $email,
		'subject'	=> $subject,
		'content'	=> $content,
		'sender'	=> $sender,
		'reply'		=> $reply
					);
	
	$data	= rawurlencode(serialize($data));
//	$data	= serialize($data);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$MAILER_URL?data=$data");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result	= curl_exec($ch);
	curl_close($ch);
	
	$result	= ($result == 'true') ? true : $result;
	
	return $result;

	
}
*/


?>