<?php

//////////////////////////////////////////////////////////////////////////////////
//                                Built by Hyman                                //
//////////////////////////////////////////////////////////////////////////////////

// Template Class (Abstract)

class Log {

	var	$file			= '';
	var	$logTime		= '';
	var	$logTitle		= '';
	var	$name		= '';

	var $lines			= array();
	var $times			= array();


	var	$startTime		= '';

	var $content		= array();
	
	var $disable		= false;
	
	function Log($file = '') {
		if ($file == '')
			$this->file	= dirname($_SERVER['SCRIPT_FILENAME']) . '/log - ' . basename($_SERVER['PHP_SELF']) . '.html';
		else
			$this->file	= dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $file;
		
		$this->logTime	= date('Y-m-d H:i:s');
		$this->logTitle	= $_SERVER['REQUEST_URI'];
			
		$this->startTime = $this->getMicroTime();


		register_shutdown_function('__log_shutdown');
		global $_LOG_INSTANCE;
		$_LOG_INSTANCE	= $this;
		
	}

	function getMicroTime() {
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec);
	}

	function getTime() {
		return substr($this->getMicroTime() - $this->startTime, 0, 8);
	}
	
	function add($message) {
		if ($this->disable)		return;
		
		if (empty($message))	$message	= '&nbsp;';
		
		$this->times[]	= $this->getTime();
		$this->lines[]	= $message;
	}
	
	function rec() {
		$this->add("<font class=noprint color=red>Record ...</font>");
	}
	
	function save($mode	= 'overwrite') {
		if ($this->disable)			return;
		if (empty($this->lines))	return;
		
		$style	= "<style> td { font-family: \"Arial\", \"新細明體\", \"細明體\";	font-size : 12px;  color : #555555} </style>\r\n";
		
		if ($mode == 'overwrite') {
			$fp	= fopen($this->file, 'w');
			fwrite($fp, $style);
		} elseif ($mode == 'insert') {
			$fp	= fopen($this->file, 'r+');
		} elseif ($mode == 'append') {
			$fp	= fopen($this->file, 'a+');
		}
		
		if (!is_resource($fp))	return;
		
		$content	= '';
		$content	.= "<table width=800 cellpadding=2 cellspacing=1 border=0 bgcolor=cccccc>";
		
		$content	.= "<tr bgcolor=eeeeee><td width=150><b>$this->logTime</b></td><td><b>$this->logTitle</b></td></tr>\r\n";
		
		foreach ($this->lines as $key => $value) {
			$content	.= "<tr bgcolor=ffffff><td align=right style='padding-right:15px;'>{$this->times[$key]}</td><td>$value</td></tr>\r\n";
		}
		
		$content	.= "<tr bgcolor=ffffff><td align=right style='padding-right:15px;'>" . $this->getTime() . "</td><td>Script ended ...</td></tr>\r\n";
		$content	.= "</table>";
		
		fwrite($fp, $content);
		
		fclose($fp);
	}

	function show($style = 1) {
		$time = $this->getTime();
		if ($style == 1)
			echo "<script>alert('Show {$this->name} in {$time} seconds.'); </script>";
		if ($style == 2)
			echo "<span class=noprint>Show {$this->name} in {$time} seconds.</span>";
		if ($style == 3)
			echo "<span class=\"noprint\"><p align=center>Show {$this->name} in {$time} seconds.</p></span>";
		if ($style == 4)
			echo $time;

	}
	
	function enable() {
		$this->disable	= false;
	}
	
	function disable() {
		$this->disable	= true;
	}

}




function __log_shutdown() {
	global $_LOG_INSTANCE;
	$_LOG_INSTANCE->save();
}

?>
