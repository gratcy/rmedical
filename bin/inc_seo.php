<?php

require_once "class_snoopy.php";

function inject_words($word) {
	
	$snoopy		= new Snoopy();

	$start		= rand(1, 999);
	
	$word_encoded		= rawurlencode(iconv('utf-8', 'big5', $word));
	$url		= "http://www.google.com.hk/search?q=$word_encoded&complete=1&hl=zh-TW&start=$start&sa=N";
	
	$snoopy->fetchtext($url);
	$result		= substr($snoopy->results, 100, -80);
	
	$words		= explode(' ', $word);
	
	$replace	= array();
	foreach ($words as $key	=> $value) {
		$replace[$key]	= iconv('utf-8', 'big5', "<font color=red>$value</font>");
		$words[$key]	= iconv('utf-8', 'big5', "$value");
	}
	$words[]			= iconv('utf-8', 'big5', "頁庫存檔 - 類似網頁");
	$replace[]			= "<br>";
	
	$result		= str_replace($words, $replace, $result);
	
	return $result;
}


function inject_search($wordlist) {
	$word		= $wordlist[array_rand($wordlist)];
	$word_big5	= rawurlencode(iconv('utf-8', 'big5', $word));
	$word_utf8	= rawurlencode($word);

	$url_google	= "http://www.google.com.hk/search?q=$word_big5";
	$url_yahoo	= "http://hk.search.yahoo.com/search?ei=UTF-8&p=$word_utf8&fr=FP-tab-web-t&meta=rst%3Dhk";

	return	
			//" <iframe src='$url_google' style='display:none' security='restricted'></iframe> ". 
			" <iframe src='$url_yahoo' style='display:none' security='restricted'></iframe> ";
}


?>