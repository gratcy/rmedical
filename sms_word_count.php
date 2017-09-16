<?php

function isChinese($getStr){
	
	return (preg_match("/[\x80-\xff]./", $getStr));

}

function strlen_utf8($str) {
	$i = 0;
	$count = 0;
	$len = strlen ($str);
	while ($i < $len) {
		$chr = ord ($str[$i]);
		$count++;
		$i++;
		
		if($i >= $len) break;
		
		if($chr & 0x80) {
			$chr <<= 1;
			while ($chr & 0x80) {
				$i++;
				$chr <<= 1;
			}
		}
	}
	return $count;
}





$original_string		= $_GET['word'] . $_POST['word'];
	

if (isChinese($original_string))
	echo strlen_utf8($original_string)."/70";
else
	echo strlen($original_string)."/160";
		

$big5					= iconv('utf-8', 'big5', $original_string);
$utf					= iconv('big5', 'utf-8', $big5);

if ($original_string != $utf)
echo " (<font color=red>Some word may not able to send due to character encoding.</font>)";


exit;

?>