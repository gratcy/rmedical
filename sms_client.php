<?php

class SMS_CLIENT {
	
	private $user						= "";
	private $password					= "";
	private $proxy						= "http://www.everyone.com.hk/tools/sms/proxy.php";
	private $proxy_time					= "http://www.everyone.com.hk/tools/sms/time.php";
	
	private $quota						= 0;
	
	function SMS_CLIENT($user = '', $password = '') {
		
		if ($user == '') {
			global	$SMS_USER, $SMS_PASSWORD;
			$user 		= $SMS_USER;
			$password 	= $SMS_PASSWORD;
		} 
		
		$this->user 	= $user;
		$this->password = $password;
		
	}
	
	
	function send($phone, $message, $phone_prefix = "852", $encoding = 'utf-8') {

		if (!is_array($phone))
			$phone				= str_replace(array("(", ")", "+", "-", " "), "", $phone);
		else {
			foreach ($phone as $key => $value) {
				$phone[$key]		= str_replace(array("(", ")", "+", "-", " "), "", $value);
			}
			$phone 				= implode(',', $phone);
		}
		
		$message 		= rawurlencode($message);
		
		$hash			= md5($this->password . $this->getTime());
		
		$uri			= "$this->proxy?user=$this->user&hash=$hash&send&phone=$phone&message=$message&phone_prefix=$phone_prefix&encoding=$encoding";
		$result			= file_get_contents($uri);
		return $result;
		
	} 
	
	
	function getQuota() {
		$hash			= md5($this->password . $this->getTime());
		
		$uri			= "$this->proxy?user=$this->user&hash=$hash&quota";
		$result			= file_get_contents($uri);
		return $result;
	}
	
	private function getTime() {
		return file_get_contents($this->proxy_time);
	}
	
	
}



?>