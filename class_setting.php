<?php

class Setting {
	
	var $_CACHE;
	
	function Setting() {
		$this->_CACHE		= $_SESSION['SETTING_CACHE'];
		if (empty($this->_CACHE))
			$this->reload();
	}
	

	
	function __get($name) {
		return $this->_CACHE[$name];
	}


	
	function last($name) {
		return end($this->_CACHE[$name]);
	}
	
	

	function reload() {
		
		$utf_bom		= array(
			'UTF-8'				=> chr(hexdec("EF")) . chr(hexdec("BB")) . chr(hexdec("BF")),
			'UTF-16 (BE)'		=> chr(hexdec("FE")) . chr(hexdec("FF")),
			'UTF-16 (LE)'		=> chr(hexdec("FF")) . chr(hexdec("FE")),
			'UTF-32 (BE)'		=> chr(hexdec("00")) . chr(hexdec("00")) . chr(hexdec("FE")) . chr(hexdec("FF")),
			'UTF-32 (LE)'		=> chr(hexdec("FF")) . chr(hexdec("FE")) . chr(hexdec("00")) . chr(hexdec("00"))
							);


		$this->_CACHE	= array();
		
		$files				= glob("setting/*.txt");
		
		if (is_array($files))
		foreach ($files as $file) {
			
			$name			= basename($file, ".txt");
			$data			= @file($file);
			$data[0]		= str_replace($utf_bom, "", $data[0]);

			foreach ($data as $index => $value) {
				if (empty($value))
					unset($data[$index]);
				else
					$data[$index]	= trim($value);
			}
			
			$this->_CACHE[$name]		= $data;
			
		}
		
		$_SESSION['SETTING_CACHE']		= $this->_CACHE;
		
	}
	
	
}

?>