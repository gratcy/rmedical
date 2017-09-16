<?

$default_language		= 'CT';		// 'CS' or 'CT'

//require_once "templates.php";

function ChineseS2T($String) {
	$Traditional = sql_getArray('SELECT `Traditional` FROM `chinese` WHERE `TransformTo` IS NULL OR `TransformTo` = \'Traditional\' ORDER BY LENGTH(`Simplified`) DESC, `Simplified` ASC');
	$Simplified = sql_getArray('SELECT CONCAT(\'/\', `Simplified`, \'/\') FROM `chinese` WHERE `TransformTo` IS NULL OR `TransformTo` = \'Traditional\' ORDER BY LENGTH(`Simplified`) DESC, `Simplified` ASC');
	$Traditional[] = '-ct.jpg';
	$Simplified[] = '/-cs\.jpg/i';
	$Traditional[] = '-ct.gif';
	$Simplified[] = '/-cs\.gif/i';
	$Traditional[] = '-ct.png';
	$Simplified[] = '/-cs\.png/i';
	$Traditional[] = '-ct.swf';
	$Simplified[] = '/-cs\.swf/i';
	$Traditional[] = '-ct.css';
	$Simplified[] = '/-cs\.css/i';
	$Traditional[] = '-ct.js';
	$Simplified[] = '/-cs\.js/i';
	$Traditional[] = '-ctfile.';
	$Simplified[] = '/-csfile\./i';
	$Traditional[] = 'ctfolder/';
	$Simplified[] = '/csfolder\//i';
	return preg_replace($Simplified, $Traditional, $String);
}

function ChineseT2S($String) {
	$Traditional = sql_getArray('SELECT CONCAT(\'/\', `Traditional`, \'/\') FROM `chinese` WHERE `TransformTo` IS NULL OR `TransformTo` = \'Simplified\' ORDER BY LENGTH(`Traditional`) DESC, `Traditional` ASC');
	$Simplified = sql_getArray('SELECT `Simplified` FROM `chinese` WHERE `TransformTo` IS NULL OR `TransformTo` = \'Simplified\' ORDER BY LENGTH(`Traditional`) DESC, `Traditional` ASC');
	$Traditional[] = '/-ct\.jpg/i';
	$Simplified[] = '-cs.jpg';
	$Traditional[] = '/-ct\.gif/i';
	$Simplified[] = '-cs.gif';
	$Traditional[] = '/-ct\.png/i';
	$Simplified[] = '-cs.png';
	$Traditional[] = '/-ct\.swf/i';
	$Simplified[] = '-cs.swf';
	$Traditional[] = '/-ct\.css/i';
	$Simplified[] = '-cs.css';
	$Traditional[] = '/-ct\.js/i';
	$Simplified[] = '-cs.js';
	$Traditional[] = '/-ctfile\./i';
	$Simplified[] = '-csfile.';
	$Traditional[] = '/ctfolder\//i';
	$Simplified[] = 'csfolder/';
	return preg_replace($Traditional, $Simplified, $String);
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

function mySerialize($Variable) {
	if (count($Variable)==0) return;
	$String='array(';
	foreach ($Variable as $Key => $Value) {
		if (is_array($Value)) $String.= '\''.$Key.'\' => '.mySerialize($Value).', ';
		else $String.= '\''.$Key.'\' => \''.$Value.'\', ';
	}
	return substr($String, 0, -2).')';
}

function myUnserialize($String) {
	if ($String!='') eval('$Variable = '.$String.';');
	return $Variable;
}

myUnserialize(ChineseS2T(mySerialize($_POST)));
?>