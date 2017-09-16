<?

$default_language		= 'CS';		// 'CS' or 'CT'

$Traditional	= sql_getArray('SELECT `Traditional` FROM `chinese` WHERE `TransformTo` IS NULL OR `TransformTo` = \'Traditional\' ORDER BY LENGTH(`Simplified`) DESC, `Simplified` ASC');
$Simplified		= sql_getArray('SELECT `Simplified` FROM `chinese` WHERE `TransformTo` IS NULL OR `TransformTo` = \'Traditional\' ORDER BY LENGTH(`Simplified`) DESC, `Simplified` ASC');

/*
$Traditional[]	= '-ct.jpg';
$Simplified[]	= '/-cs\.jpg/i';
$Traditional[]	= '-ct.gif';
$Simplified[]	= '/-cs\.gif/i';
$Traditional[]	= '-ct.png';
$Simplified[]	= '/-cs\.png/i';
$Traditional[]	= '-ct.swf';
$Simplified[]	= '/-cs\.swf/i';
$Traditional[]	= '-ct.css';
$Simplified[]	= '/-cs\.css/i';
$Traditional[]	= '-ct.js';
$Simplified[]	= '/-cs\.js/i';
$Traditional[]	= '-ctfile.';
$Simplified[]	= '/-csfile\./i';
$Traditional[]	= 'ctfolder/';
$Simplified[]	= '/csfolder\//i';
*/

function ChineseS2T($String) {
	if (empty($String))		return $String;
	if (is_array($String)) {
		foreach ($String as $k => $s)
			$String[$k] 	= ChineseS2T($s);
		return $String;
	}
	global $Simplified, $Traditional;
	return str_replace($Simplified, $Traditional, $String);
}


function ChineseT2S($String) {
	if (empty($String))		return $String;
	if (is_array($String)) {
		foreach ($String as $k => $s)
			$String[$k] 	= ChineseT2S($s);
		return $String;
	}
	global $Simplified, $Traditional;
	return str_replace($Traditional, $Simplified, $String);
}



if (isset($_GET['Language'])) {
	setcookie('Language', $_GET['Language']);
	if ($_GET['Language']=='CS') {
		ob_start('ChineseT2S');
  	 	register_shutdown_function('ob_end_flush');
	}
} elseif (isset($_COOKIE['Language'])) {
	if ($_COOKIE['Language']=='CS') {
		ob_start('ChineseT2S');
    	register_shutdown_function('ob_end_flush');
	}
} else {
	ob_start(($default_language == 'CS') ? 'ChineseT2S' : 'ChineseS2T');
    register_shutdown_function('ob_end_flush');
}


if ($default_language == 'CS')
	ChineseT2S(&$_POST);
else
	ChineseS2T(&$_POST);

?>