<?php


//////////////////////////////////////////////////////////////////////////
//					Debug and Hyperlink Function						//
//////////////////////////////////////////////////////////////////////////

register_shutdown_function("session_write_close");

function __get_template_type($id, $type) {
	if ($type == 1)
		return ($id == 1 ? 'SMS' : 'Email');
	else
		return ($id == 1 ? 'SMS <input type="radio" checked="checked" name="type" value="1" /> Email <input type="radio" name="type" value="2" />' : 'SMS <input type="radio" name="type" value="1" /> Email <input type="radio" checked="checked" name="type" value="2" />');
}

function dump($obj, $exit = false) {
	if ($exit)	while (@ob_end_clean());
	echo "<pre style='font-family: \"Arial\", \"新細明體\", \"細明體\";	font-size : 12px; letter-spacing:1px'>\n";
	ob_start();
	if (is_bool($obj))		echo ($obj ? 'true' : 'false');
	elseif (is_string($obj))	echo htmlentities($obj);
	else					print_r($obj);
	$str	= ob_get_contents();
	ob_end_clean();
	echo $str;
	echo "</pre>\n";
	if ($exit)	session_write_close();
	if ($exit)	exit;
}

function dump_html($obj, $exit = false) {
	if ($exit)	while (@ob_end_clean());
	echo "<pre style='font-family: \"Arial\", \"新細明體\", \"細明體\";	font-size : 12px; letter-spacing:1px'>\n";
	ob_start();
	if (is_bool($obj))		echo ($obj ? 'true' : 'false');
	elseif (is_string($obj))	echo ($obj);
	else					print_r($obj);
	$str	= ob_get_contents();
	ob_end_clean();
	echo htmlentities($str, ENT_QUOTES, "UTF-8");
	echo "</pre>\n";
	if ($exit)	session_write_close();
	if ($exit)	exit;
}

function dump_table($table, $mode = 'title', $exit = false) {
	if (!is_array($table))	echo "<p><font color=red>Input parameter error.</font></p>";
	if ($exit)	while (@ob_end_clean());
	echo "<style> td { font-family: \"Arial\", \"新細明體\", \"細明體\";	font-size : 12px;  } </style>\r\n";
	echo "<table cellpadding=3 cellspacing=0 border=1 style=''>";
	if ($mode == 'title') {
		echo "<tr>";
		$title	= current($table);
		if (is_array($title))
		foreach ($title as $key => $value) {
			echo "<th>$key</th>";
		}
		echo "</tr>\r\n";
	}
	
	foreach ($table as $row) {
		echo "<tr>";
		if (is_array($row) || is_object($row))
		foreach ($row as $col) {
			if (empty($col))
				$col 	= '&nbsp;';
			echo "<td>$col</td>";
		}
		echo "</tr>\r\n";
	}
	echo "</table>";
	if ($exit)	session_write_close();
	if ($exit)	exit;
}

function alert($obj) {
	echo "<script>alert('";
	if (!is_string($obj))		$obj		= var_export($obj, true);
	echo str_replace(array("\r", "\n", "'"), array(' ', ' ', '"'), $obj);
	echo "');<" . "/script>";
}

function error($msg, $return = -1) {
	while (@ob_end_clean());
	echo "<html><head><script>alert('$msg');</script></head></html>";
	gotoURL($return);
	exit;
}

function msg($msg) {
	echo "<script> window.alert('$msg'); </script>";
}

function gotoURL($link, $delay = 0, $close_session = true) {
//	if (strpos($link, 'http') === false)
//		$link = $GLOBALS['ROOTPATH'].$link;
	if ($delay == 0)		while (@ob_end_clean());

	if (is_numeric($link))
		echo "<script> setTimeout('history.go($link);', " . $delay*1000 . "); </script>";
	else if ($delay == 0)
		echo "<meta http-equiv='refresh' content='0;url=$link'>";
	else
		echo "<html><head><script> setTimeout('location.href=\"$link\";', " . $delay*1000 . "); </script></head></html>";
	if ($close_session)		session_write_close();
	if ($delay == 0)		exit;
}

function setQueryString($url, $newquerystring) {
	$args		= func_get_args();
	$url		= array_shift($args);

	$gets		= array();
	$fields		= array(); 

	$parse		= parse_url($url);
	$baseurl	= $parse['path'];
	$query		= $parse['query'];

	$fields 	= explode('&', $query);
	
	foreach ($fields as $field) {
		if ($field == '')	continue;
		list($name, $value)	= explode('=', $field);
		$gets[$name]	= $value;
	}
	
	foreach ($args as $newquerystring) {
		if (strpos($newquerystring , '=') === false) {
			$gets[$newquerystring]	= '';
			continue;
		}
		
		list($name, $value) = explode('=', $newquerystring);
		
		if ($value == '') {
			unset($gets[$name]);
			continue;
		}
		
		$gets[$name]	= $value;
	}
	$query 	= rawurldecode(http_build_query($gets));

	$url	= $baseurl . "?" . $query;
	
	return $url;
}

$ORIGINAL_URL	= "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";
function getURL($mode = 'full') {
	global $ORIGINAL_URL;
	if ($mode != 'full')	return $_SERVER['PHP_SELF'] ;
	return $ORIGINAL_URL;
}

function popURL($var) {
	global $ORIGINAL_URL;
	global $$var; 
	$$var 			= $_GET[$var];
	$ORIGINAL_URL	= setQueryString($ORIGINAL_URL, "$var=");
	return $$var;
}

function pushURL($var, $value) {
	global $ORIGINAL_URL;
	$ORIGINAL_URL	= setQueryString($ORIGINAL_URL, "$var=$value");
}


function array2var($array, $valuearray = '') {			// array2var($key, $value) or array2var( [$key=>$value] );
	if (!is_array($array))	return;
	if ($valuearray == '') {
		foreach ($array as $key =>$value) {
			global $$key;
			$$key = $value;			
		}
	} else {
		foreach ($array as $key => $value) {
			global $$key;
			$$key = $valuearray[$key];
		}
	}
	return true;
}

function array_column_sort() {							// $array = array_column_sort($array, 'col1', 'col2', SORT_DESC, 'name');
	$args = func_get_args();
	$marray = array_shift($args);
	$msortline = "return (array_multisort(";
	foreach ($args as $arg) {
		$i++;
		if (is_string($arg))
			foreach ($marray as $row)
				$sortarr[$i][] = $row[$arg];
		else	$sortarr[$i] = $arg;
		$msortline .= "\$sortarr[".$i."],";
	}
	eval($msortline . "\$marray));");
	return $marray;
}


// Reverse = false	: field name to column name
// Reverse = true	: column name to field name
function array_convert_title(&$array, $mode = 'col', $reverse = false) {
	if (is_bool($mode)) {
		echo "<p><font color=red>Error in using array_convert_title : wrong `mode` arguement.</font></p>";
		return false;
	}
	if (!$reverse) {
		if ($mode == 'col') {
			$title	= array_keys(current($array));
			foreach ($array as $key => $value) {
				$array[$key]	= array_values($array[$key]);
			}
			array_unshift($array, $title);
		} 
		if ($mode == 'row') {
			$title	= array_keys($array); 
			$array	= array_values($array);
			array_unshift($array, $title);
		}
		
		if ($mode == 'table') {
			$array	= array_convert_title(array_convert_title($array, 'row'), 'col');
		} 
		
		return $array;
	} else {
		if ($mode == 'col') {
			$title	= array_shift($array);
			foreach ($array as $key => $value) {
				$array[$key]	= array_combine($title, $value);
			}
		} 
		if ($mode == 'row') {
			$title 	= array();
			foreach ($array as $key => $value) {
				$title[$key]	= array_shift($value);
			}
			$array	= array_combine($title, $value); ;
		}
		
		if ($mode == 'table') {
			$array	= array_convert_title(array_convert_title($array, 'row', true), 'col', true);
		} 
		
		return $array;
	}
}


function array_average($array) {
	$count	= 0; 
	$sum	= 0;
	foreach ($array as $value) {
		if ($value != 0)
			$count++;
		$sum += $value;
	}
	if ($count == 0)
		$count = 1;
	return $sum / $count;
}


function array_rename(&$array, $old_key, $new_key) {
	if (is_array($old_key)) { 
		foreach ($old_key as $key => $value) {
			array_rename($array, $value, $new_key[$key]);
		}
	}  else {
		$value 	= $array[$old_key];
		unset($array[$old_key]);
		$array[$new_key]	= $value;
	}
	return $array;
}


function array_delete_empty(&$array) {
	if (is_array($array))
	foreach ($array as $key => $value) {
		if (trim($value) == '') {
			unset($array[$key]);
		}
	}
	return $array;
}


function array_trim(&$array) {
	if (is_array($array))
	foreach ($array as $key => $value) {
		if (trim($value) == '') {
			unset($array[$key]);
		} else {
			$array[$key]	= trim($value);
		}
	}
}


function array_strip_tags(&$array, $exclude = "") {
	$exclude			= explode(",", $exclude);
	array_trim($exclude);
	if (is_array($array))
	foreach ($array as $key => $value) {
		if (in_array($value, $exclude))
			continus;
		$array[$key]	= strip_tags($value);
	}
}


function fill_blank_cell($table) {
	if (is_array($table)) {
		foreach ($table as $key => $value) {
			if (is_array($value))
				$table[$key]	= fill_blank_cell($value);
			else if ($value == '')
				$table[$key]	= '&nbsp;';
		}
	} else  if ($table == '')
		$table	= '&nbsp;';
	return $table;
}


function array_split(&$array, $col = 0, $row = 0) {
	$result			= array();
	if ($col > 0) {
		$row		= ceil(count($array) / $col);
	} else {
		$col 		= ceil(count($array) / $row);
	}
	for ($i = 0; $i < $col; $i++) {
		$result[]	= array_splice($array, 0, $row);
	}
	$array 			= $result;
	return $result;
}


function array_flip_2d(&$array) {
	$result			= array();
	foreach($array as $a_key => $a_value){
		foreach($a_value as $b_key => $b_value){
			$result[$b_key][$a_key] = $b_value;
		}
	}
	$array			= $result;
	return $result;
}


function file2array($file, &$array) {
	$array 	= array();
	$data	= file($file);
	foreach ($data as $line) {
		$line					= trim($line);
		if (empty($line))		continue;
		$line	= explode('<sep>', $line);
		if (count($line) == 2)
			$array[$line[0]]	= $line[1];
		else 
			$array[]			= $line;
	}
	return $array;
} 

function array2file($array, $file) {
	$fp		= fopen($file, "w");
	foreach ($array as $key => $data) {
		if (empty($data))	continue;
		if (is_array($data))
			fwrite($fp, implode('<sep>', $data) . "\r\n");
		else 
			fwrite($fp, "$key<sep>$data\r\n");
	}
	fclose($fp);
}


//////////////////////////////////////////////////////////////////////////
//					String Functions									//
//////////////////////////////////////////////////////////////////////////


function startWith($string, $subString) {
	if (PHP_VERSION >= '5')
		return (@stripos(ltrim($string), $subString) === 0);
	else
		return (@strpos(ltrim($string), $subString) === 0);
}

function endWith($string, $subString) { 
	return (substr(rtrim($string), 0 - strlen($subString))	==  $subString);
}

function contain($string, $subString, $offset = 0) {
	if (PHP_VERSION >= '5')
		return (stripos(trim($string), $subString, $offset) !== false);
	else
		return (strpos(strtoupper(trim($string)), strtoupper($subString), $offset) !== false);
}

function subString($string, $header, $footer, $offset = 0) {
	if ($header == '')
		return substr($string, 0, stripos($string, $footer, $offset) - strlen($string));
	if ($footer == '')
		return substr($string, stripos($string, $header, $offset));
	$pos_header		= stripos($string, $header, $offset) + strlen($header);
	$pos_footer		= stripos($string, $footer, $pos_header);
	
	if ($pos_header === false) {
		echo "<p><font color=red>Warning ! SubString() Header not found : " . htmlentities($header) . "</font></p>";
		trigger_error("Warning ! SubString() Header not found : " . htmlentities($header));
	}
	if ($pos_footer === false) {
		echo "<p><font color=red>Warning ! SubString() Footer not found : " . htmlentities($footer) . "</font></p>";
		trigger_error("Warning ! SubString() Footer not found : " . htmlentities($footer));
	}
	
	return substr($string, $pos_header, $pos_footer - strlen($string));
}

function str_remove($string, $startStr, $endStr, $offset = 0) {
	$ustring		= strtoupper($string);
	$startStr		= strtoupper($startStr);
	$endStr			= strtoupper($endStr);
	
	$pos_start		= strpos($ustring, $startStr, $offset);
	$pos_end		= strpos($ustring, $endStr, $pos_start + strlen($startStr)) + strlen($endStr);

	if ($pos_start === false) {
		echo "<p><font color=red>Warning ! str_remove() start string not found !</font></p>";
		trigger_error("Warning ! str_remove() start string not found !");
	}
	if ($pos_end === false) {
		echo "<p><font color=red>Warning ! str_remove() end string not found !</font></p>";
		trigger_error("Warning ! str_remove() end string not found !");
	}
	
	return substr($string, 0, $pos_start) . substr($string, $pos_end);
}

function printdate($date) {
	return str_replace('-', '-', substr($date, 0, 10));	
}

function getWeekString($weekday, $format = 'l') {
	return date($format, ($weekday+3)*86400);		// 86400 = 60 x 60 x 24 (1 day)
}

function padding($str, $length) {
	while (strlen($str) < $length)
		$str = '0' . $str;
	return $str;
}

function moneyPadding($str) {
	if (strpos($str, '.') === false)
		$str = $str . '.';
	while (strlen($str) - strpos($str, '.') < 2)
		$str = $str . '0';
	return $str;
}

function removeWhiteSpace(&$str) {
	$str = str_replace(array("\r", "\n", "\t", " "), '', $str);
}



function big2utf($str) { 
	if (is_array($str)) {
		foreach ($str as $k => $s)
			$str[$k] 	= big2utf($s);
		return $str;
	}
	return iconv('BIG-5', 'UTF-8', $str);
}

function utf2big($str) {
	if (is_array($str)) {
		foreach ($str as $k => $s)
			$str[$k] 	= utf2big($s);
		return $str;
	}
	return iconv('UTF-8', 'BIG-5', $str);
}


//////////////////////////////////////////////////////////////////////////
//					Validation Function									//
//////////////////////////////////////////////////////////////////////////

// Validate Email format
function validate_mail($str) {
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+){1,}$", $str);
}


// Validate Login ID format
function validate_id($str) {
	return eregi("^([_0-9A-Za-z]*)$", $str);
}

function validate_password($str) {
	return (eregi("^([_0-9A-Za-z]*)$", $str) and (strlen($str) >= 5));
}

function validate_date($str) {
	return ereg ("^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$", $str);
}

function validate_phone($str) {
	return ereg ("^([0-9 ]{8,16})$", $str);
}



//////////////////////////////////////////////////////////////////////////
//					Encryption Function									//
//////////////////////////////////////////////////////////////////////////

function encrypt($data, $cypher = '') {
	
/*	if ($cypher == '')	$cypher = $GLOBALS['DB_ENCRYPT_KEY'];
	$data	= addslashes(serialize($data));
	list($code) = @mysql_fetch_row(@mysql_query("select hex(aes_encrypt('$data', '$cypher'))"));
	return $code;
*/
	if ($cypher == '')	$cypher = $GLOBALS['DB_ENCRYPT_KEY'];
	$cypher	.=	"dzrkelr";

	$data	= serialize($data);
	$code	= _encryption_convert($data, $cypher);
	$_code	= '';
	for ($i=0; $i < strlen($code); $i++) {
		$_code	.= padding(dechex(ord($code{$i})), 2);
	}
	return $_code;
}

function decrypt($code, $cypher = '') {
	
/*	if ($cypher == '')	$cypher = $GLOBALS['DB_ENCRYPT_KEY'];
	list($unhex, $data) = @mysql_fetch_row(@mysql_query("select @code:=0x$code, aes_decrypt(@code, '$cypher'), @code=''"));
	if ($data == null)	return 'invalid';
	$data = unserialize($data);
	return $data;
*/

	if ($cypher == '')	$cypher = $GLOBALS['DB_ENCRYPT_KEY'];
	$cypher	.=	"dzrkelr";
	$_code	= '';
	for ($i=0; $i < strlen($code); $i+=2) {
		$_code	.= chr(hexdec($code{$i} . $code{$i+1}));
	}
	$data	= _encryption_convert($_code, $cypher);
	$data	= unserialize($data);
	return $data;

}

// Encrypt function
// Source from : http://www.phpbuilder.com/board/showthread.php?t=10326721
function _encryption_convert($str, $key = ''){ 
	if($key=='')		return $str; 
	$key	=	str_replace(chr(32),'',$key); 
	if (strlen($key)<8) exit('key error'); 
	$kl=strlen($key)<32?strlen($key):32; 
	$k=array();for($i=0;$i<$kl;$i++){ 
	$k[$i]=ord($key{$i})&0x1F;} 
	$j=0;for($i=0;$i<strlen($str);$i++){ 
	$e=ord($str{$i}); 
	$str{$i}=$e&0xE0?chr($e^$k[$j]):chr($e); 
	$j++;$j=$j==$kl?0:$j;} 
	return $str; 
}



//////////////////////////////////////////////////////////////////////////
//					Quick Save Functions								//
//////////////////////////////////////////////////////////////////////////

_quick_create_table();

function _quick_create_table() {
	if (!isset($GLOBALS['DB_LINK']))		return;
	sql_query("
		CREATE TABLE IF NOT EXISTS `quick_storage` ( 
		    `name` VARCHAR( 255 ) NOT NULL,
		    `value` TEXT NOT NULL,
		    primary key (`name`)
		) ENGINE=MyISAM; ");
}

function quick_save($name, $value) {
	$value	= serialize($value);
	$exist	= sql_check("select value from quick_storage where name='" . addslashes($name) . "'");
	if ($exist) {
		sql_query("update quick_storage set value='" . addslashes($value) . "' where name='" . addslashes($name) . "'");
	} else {
		sql_query("insert into quick_storage values ('" . addslashes($name) . "', '" . addslashes($value) . "')");
	}
}

function quick_load($name) {
	return unserialize(sql_getValue("select value from quick_storage where name='" . addslashes($name) . "'"));
}

function quick_append($name, $value) {
	$value	= quick_load($name) . $value;
	quick_save($name, $value);
}

function quick_delete($name) {
	sql_query("delete from quick_storage where name='" . addslashes($name) . "'");
}

function quick_list($prefix) {
	return sql_getArray("select name from quick_storage where name like '" . addslashes($prefix) . "%'");
}


//////////////////////////////////////////////////////////////////////////
//					Image Functions										//
//////////////////////////////////////////////////////////////////////////

function getFixedImageSize($file, $maxWidth, $maxHeight) {
	$imagesize	= @getimagesize($file);

	if ($imagesize[1] == 0 or $imagesize[0] == 0)	return array(0, 0);

	$set_width	= $maxWidth;
	$set_height	= $maxHeight;
	if (($imagesize[0] / $imagesize[1]) >= 1)
		$set_height	= ceil($imagesize[1] * $set_width / $imagesize[0]);
	else
		$set_width	= ceil($imagesize[0] * $set_height / $imagesize[1]);
	return array($set_width, $set_height);
}

//" 

// $default_image is the image file to be display if $file not exist
function displayImage($file, $default_image = '', $maxWidth = 0, $maxHeight = 0, $link = '', $tag= '') {
	if (isset($_SESSION)) {
		$info	= $_SESSION['displayImage_imagesize'][$file];
		if (empty($info)) {
			$info	= $_SESSION['displayImage_imagesize'][$file]	= @getimagesize($file);
			if (count($_SESSION['displayImage_imagesize']) > 100)
				array_splice($_SESSION['displayImage_imagesize'], 0, 10);
		}
	} else {
		$info	= @getimagesize($file);
	}

	if ($info[1] == 0 or $info[0] == 0) {		// Error in image file or zero size image
		if ($default_image != '') {
			return displayImage($default_image, '', $maxWidth, $maxHeight, $link, $tag);
		} else
			return "";
	}

	if ($link != '') {
		$link_prefix	= "<a href='$link'>";
		$link_suffix	= "</a>";
		$link_from_png	= "style='cursor:hand' onclick='location.href=\"$link\"'";
	} else {
		$link_prefix	= '';
		$link_suffix	= '';
		$link_from_png	= '';
	}
	

	$setWidth	= $info[0];
	$setHeight	= $info[1];

	if ($maxWidth	== 0)	$maxWidth	= $setWidth;
	if ($maxHeight	== 0)	$maxHeight	= $setHeight;

	if ($setWidth > $maxWidth) {
		$setWidth	= $maxWidth;
		$setHeight	= ceil($info[1] * $setWidth / $info[0]);
	}  
	if ($setHeight > $maxHeight) {
		$setHeight	= $maxHeight;
		$setWidth	= ceil($info[0] * $setHeight / $info[1]); 
	}


	if ($info[2] == 1)			// GIF
		return "$link_prefix<img src='$file' width=$setWidth height=$setHeight border=0 $tag>$link_suffix";
	if ($info[2] == 2)			// JPG
		return "$link_prefix<img src='$file' width=$setWidth height=$setHeight border=0 $tag>$link_suffix";
	if ($info[2] == 3)			// PNG
		return "<table width=$setWidth height=$setHeight cellspacing=0 cellpadding=0 border=0><tr><td $link_from_png style='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\"$file\")'><img src='$file' width=$setWidth height=$setHeight border=0 $tag></td></tr></table>";
	if ($info[2] == 4 || $info[2] == 13) {			// SWF

		$swf_file		= substr($file, 0, -4);
		$basename		= subString(basename($file), '', '.');

		return <<<EOS
$link_prefix<script language="javascript">
	if (AC_FL_RunContent == 0) {
		alert("This page requires AC_RunActiveContent.js.");
	} else {
		AC_FL_RunContent(
			'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0',
			'width', '$setWidth',
			'height', '$setHeight',
			'src', '$swf_file',
			'quality', 'high',
			'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
			'align', 'middle',
			'play', 'true',
			'loop', 'false',
			'scale', 'showall',
			'wmode', 'window',
			'devicefont', 'false',
			'id', '$basename',
			'bgcolor', '#ffffff',
			'name', '$basename',
			'menu', 'false',
			'allowFullScreen', 'false',
			'allowScriptAccess','sameDomain',
			'movie', '$swf_file',
			'salign', ''
			); //end AC code
	}
</script>$link_suffix
EOS;

	}

	return "Image display error !<br>";
}

function displayImage_clear_buffer() {
	$_SESSION['displayImage_imagesize']		= array();
}

function get_status_blasting($id) {
	if ($id == 0) return 'Pending';
	else if ($id == 1) return 'Approved';
	else return 'Cancel';
}



function displayVideo($file, $maxWidth = 0, $maxHeight = 0, $autostart = true, $contorls = true, $ui_mode = 'none') {

	$autostart 	= ($autostart) ? 'true' : 'false';
	$contorls 	= ($contorls) ? 'true' : 'false';
	$loop 		= 'true';
	
	if ($ui_mode != 'none')	$maxHeight  += 50;
	
	$result		= <<<EOS
<OBJECT ID="MediaPlayer" WIDTH=$maxWidth HEIGHT=$maxHeight style='display:block; margin:0px;'
	CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"
	STANDBY="Loading Windows Media Player ..." 
	TYPE="application/x-oleobject"
	CODEBASE="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112">

	<PARAM name="autoStart" value="$autostart"></param>
	<PARAM name="filename" value="$file"></param>
	<PARAM name="URL" value="$file"></param>
	<param name="showControls" value="$contorls"></param>
	<param name="uiMode" value="$ui_mode"></param>
	<param name="loop" value="$loop"></param>
	<EMBED TYPE="application/x-mplayer2" 
		SRC="$file"
		NAME="MediaPlayer"
		WIDTH=$maxWidth 
		loop="$loop"
		HEIGHT=$maxHeight>
	</EMBED>
</OBJECT>
EOS;
	
	return $result;

}



function rgb2hsl($rgb) {
	$rgb 	= str_replace('#', '', $rgb);
	$red 	= hexdec(substr($rgb, 0, 2)) / 255;
	$green	= hexdec(substr($rgb, 2, 2)) / 255;
	$blue 	= hexdec(substr($rgb, 4, 2)) / 255;
	
	$maxcolor	= max($red, $green, $blue);
	$mincolor	= min($red, $green, $blue);
	$delta		= $maxcolor - $mincolor;

	$lightness	= $maxcolor * 255;
	
	if ($maxcolor != 0)
		$situation	= 255 * $delta / $maxcolor ;

	if ($maxcolor == $mincolor) {
		$hue		= 0;
		$situation	= 0;
		return array("H" => $hue, "S" => $situation, "L" => $lightness);
	}
	
	if ($maxcolor == $red)
		$hue	= ($green - $blue) / $delta;
	elseif ($maxcolor == $green )
		$hue 	= 2.0 + ($blue - $red) / $delta; 
	elseif ($maxcolor == $blue)
		$hue	= 4.0 + ($red - $green) / $delta;

	$hue		= $hue * 60;
	if ($hue < 0)	$hue += 360;

	return array("H" => $hue, "S" => $situation, "L" => $lightness);
}


function hsl2rgb($hue, $situation, $lightness) {
	$situation 	= $situation / 255;
	$lightness	= $lightness / 255;
	$hue 		= $hue % 360;

	if ($situation == 0) {
		$red 	= $green 	= $blue 	= $lightness;
		$color 	= dechex(round($red * 255)) . dechex(round($green * 255)) . dechex(round($blue * 255));
		return $color;
	}
	
	$hue 		= $hue / 60;
	$range		= floor($hue);
	$temp		= $hue - $range;
	$p 			= $lightness * (1 - $situation);
	$q			= $lightness * (1 - ($situation * $temp));
	$t			= $lightness * (1 - ($situation * (1 - $temp)));
	
	if ($range == 0) {
		$red 	= $lightness;
		$green	= $t;
		$blue 	= $p;
	} elseif ($range == 1) {
		$red 	= $q;
		$green	= $lightness;
		$blue 	= $p;
	} elseif ($range == 2) {
		$red 	= $p;
		$green	= $lightness;
		$blue 	= $t;
	} elseif ($range == 3) {
		$red 	= $p;
		$green	= $q;
		$blue 	= $lightness;
	} elseif ($range == 4) {
		$red 	= $t;
		$green	= $p;
		$blue 	= $lightness;
	} elseif ($range == 5) {
		$red 	= $lightness;
		$green	= $p;
		$blue 	= $q;
	}		
	
	$hex_red	= padding(dechex(round($red * 255)), 2);
	$hex_green	= padding(dechex(round($green * 255)), 2);
	$hex_blue	= padding(dechex(round($blue * 255)), 2);
	
	$color		= $hex_red . $hex_green . $hex_blue;
	
	return $color;
	
}


function hue_shift($color, $hue_shift) {
	$hsl		= rgb2hsl($color);
	$hsl['H']	= looping($hsl['H'] + $hue_shift, 360, 0);
	return hsl2rgb($hsl['H'], $hsl['S'], $hsl['L']);
	
}

//////////////////////////////////////////////////////////////////////////
//					Other Functions										//
//////////////////////////////////////////////////////////////////////////


function extension($filename) {
	$path	= pathinfo($filename);
	return  $path['extension'];
}

function create_folder($folder) {
	$name	= basename($folder);
	$folder	= dirname($folder);

	if (!is_dir($folder))
		create_folder($folder);

	if (!is_dir("$folder/$name")) {
		$orig_folder	= getcwd();
		chdir($folder);
		mkdir($name);
		chdir($orig_folder);
	}
}

function handleFileUpload($upload_path, $prefix = '') {
	$successFiles = array();
	foreach ($_FILES as $file) {
		// Case no file upload
		if ($file['error'] == UPLOAD_ERR_NO_FILE)	continue;
		
		// Case file upload error
		if ($file['error'] != UPLOAD_ERR_OK) {
			unlink($file['tmp_name']);
			return false;
		}

		if ($file['error'] == UPLOAD_ERR_OK)
			if (move_uploaded_file($file['tmp_name'], $upload_path . $prefix . basename($file['name'])))
				array_push($successFiles, $prefix . basename($file['name']));
			else
				return false;
	}
	return $successFiles;
}


function looping($n, $max, $min = 0) {
	$interval = $max - $min + 1;
	$n = ($n - $min) % $interval + $min;
	if ($n == $min - 1)		$n = $max;
	return $n;
}

function duplicate($obj) {
	if (!is_object($obj))	return $obj;
	eval('$result = new ' . get_class($obj) . '()');
	foreach ($obj as $key => $value) {
		$result->$key = duplicate($value);
	}
	return $result;
}


function price_format($price) {
	if (empty($price))	return '';
	return (contain($price, '.')) ? round($price, 1) : "$price.0";
}

function __send_email($to,$subject,$data,$tpl) {
	global $smtp;
	include('Mail.php');
	$wew = new Mail(array('smtp_hostname' => $smtp['host'], 'smtp_username' => $smtp['username'], 'smtp_password' => $smtp['password'], 'smtp_port' => $smtp['port'], 'protocol' => 'smtp'));
	$wew -> setTo($to);
	$wew -> setFrom('noreply@rockhkmedical.com');
	$wew -> setSender('noreply@rockhkmedical.com');
	$wew -> setReplyTo('noreply@rockhkmedical.com');
	$wew -> setSubject($subject);
	
	if ($tpl) {
		foreach($data as $k => $v)
			$$k = $v;
		$tpl = file_get_contents($tpl);
		$tpl = str_replace('{rck:','$',$tpl);
		$tpl = str_replace(':}','',$tpl);
		$tpl = addslashes($tpl);
		@eval("\$tpl = \"$tpl\";");
	}
	else {
		$tpl = $data;
	}
	
	$wew -> setHtml($tpl);
	$wew -> send();
	return true;
}

function notify_admin($title, $message) {
	
	global $NOTIFY_EMAIL, $MAILTO_WEBMASTER;
	
	$content	= '';

	$mail_sender			= explode("<", $MAILTO_WEBMASTER);

	$header					= "MIME-Version: 1.0\r\n";
	$header					.= "From: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
	$header					.= "Reply-To: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
	$header					.= "Return-Path: =?UTF-8?B?" . base64_encode($mail_sender[0]) . "?=<" . $mail_sender[1] . "\r\n";
	$header					.= "Content-Type: text/html; charset=utf-8\r\n";
	$header					.= "X-Mailer: PHP/" . phpversion();


	if (is_array($message)) {
		foreach ($message as $name => $value)
			$content	.= "<p><b>$name : </b><br>" . nl2br($value) . "</p><br>\r\n";
	} else {
		$content	= nl2br($message);
	}
	
	if (is_array($NOTIFY_EMAIL)) {
		foreach ($NOTIFY_EMAIL as $email)
			@mail($email, $title, $content, $header);
	} else {
		@mail($NOTIFY_EMAIL, $title, $content, $header);
	}
	
}


function http_post($url, $data) {

	$port			= 80;
	$timeout		= 30;
	
	$pathinfo		= array2obj(parse_url($url));

	$fp				= fsockopen($pathinfo->host, $port, $errno, $errstr, $timeout);
	
	if(!$fp){
		echo "Socket error: [" . $errno . "] " . $errstr;
	}


	foreach ($data as $key => $value) {
		$key		= urlencode(stripslashes($key));
		$value		= urlencode(stripslashes($value));
		$data[$key]	= "$key=$value";
	}
	$data			= implode("&", $data);

	$header			= "";
	$header			.= "POST $pathinfo->path HTTP/1.0\r\n";
	$header			.= "Host: $pathinfo->host\r\n";
	$header			.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header			.= "Content-Length: " . strlen($data) . "\r\n\r\n";
	fputs ($fp, $header . $data);
	
	$result			= "";
	while (!feof($fp)) {
		$result		.= fgets($fp, 1024);
	}
	fclose ($fp);
	
	$header_pos		= strpos($result, "\r\n\r\n");
	
	$result			= substr($result, $header_pos+4);

	return $result;
	
}

//////////////////////////////////////////////////////////////////////////
//					PHP 4 Compatibility									//
//////////////////////////////////////////////////////////////////////////

if (!function_exists('array2obj')) {
	function array2obj(&$array) { 
		if (!is_array($array))	return $array;
		$temp		= $array;
		$array 		= new AdvArrayObject();
		foreach ($temp as $key =>$value) {
			$array->$key = $value;			
		}
		return $array;
	}
	
	class AdvArrayObject {
		function get($name) {
			return $this->$name;
		}
		function toArray() {
			$result	= array();
			foreach ($this as $name => $value)
				$result[$name]	= $value;
			return $result;
		}
	}

}


if (!function_exists('array_combine')) {
	function array_combine($keys, $values) {
		$result		= array(); 
		reset($values);
		foreach ($keys as $key) {
			$result[$key]		= current($values);
			next($values);
		}
		return $result;
	}
}


if (!function_exists('http_build_query')) {
	function http_build_query($data) {
		$result 	= array();
		foreach ($data as $key => $value) {
			if (is_bool($value))
				$result[]	= rawurlencode($key) . '=' . (($value) ? '1' : '0');
			else if ($value == '' || $value == null)
				$result[]	= rawurlencode($key);
			else 
				$result[]	= rawurlencode($key)  . '=' . rawurlencode($value);
		} 
		return implode('&', $result);
	}
}


if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$fp 		= fopen($filename, 'w');
		fwrite($fp, $data);
		fclose($fp);
	}
}

function select_choose($data, $id) {
	$res = '';
	foreach($data as $k => $v) {
		if ($id == $k)
			$res .= '<option value="'.$k.'" selected>'.$v.'</option>';
		else
			$res .= '<option value="'.$k.'">'.$v.'</option>';
	}
	return $res;
}

function select_product($id) {
	$sql = sql_getTable("select name, id,price from item order by name asc");
	$res = '<option value="0">--Choose Product--</option>';
	foreach($sql as $k => $v) {
		if ($id == $v['id'])
			$res .= '<option price="'.$v['price'].'" value="'.$v['id'].'" selected>'.$v['name'].'</option>';
		else
			$res .= '<option price="'.$v['price'].'" value="'.$v['id'].'">'.$v['name'].'</option>';
	}
	return $res;
}

function select_template($id, $type, $mtype) {
	$sql = sql_getTable("select bname, bid FROM blasting_template_tab WHERE btype=".$type." AND bmtype=".$mtype." ORDER BY bid DESC");
	$res = '<option value="0">--Choose Template--</option>';
	foreach($sql as $k => $v) {
		if ($id == $v['bid'])
			$res .= '<option value="'.$v['bid'].'" selected>'.$v['bname'].'</option>';
		else
			$res .= '<option value="'.$v['bid'].'">'.$v['bname'].'</option>';
	}
	return $res;
}

function select_manager($id) {
	$sql = sql_getTable("select name, id FROM staff ORDER BY name ASC");
	$res = '<option value="0">--Choose Manager--</option>';
	foreach($sql as $k => $v) {
		if ($id == $v['id'])
			$res .= '<option value="'.$v['id'].'" selected>'.$v['name'].'</option>';
		else
			$res .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
	}
	return $res;
}

function get_total_SO($sid) {
	$sql = sql_getTable("select COUNT(*) as total FROM transaction_tab WHERE YEAR(tdate)=".date('Y')." AND MONTH(tdate)=".date('m')." AND tstore=" . $sid);
	return $sql[0]['total'];
}

function get_payment_type($id) {
	$data = array('Cash', 'Debit', 'Credit Card');
	return $data[$id];
}

function get_status_queue($id) {
	$arr = array('Cancel', 'Pending', 'Sent');
	return $arr[$id];
}

function __get_month($id) {
	$id = (int) $id;
	$month = array('Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
	return $month[($id - 1)];
}

function get_date_dropdown($date,$prefix='') {
	$dd = 0;
	$mm = 0;
	$yyyy = 0;

	if ($date) {
		$date = explode('-', date('d-m-Y', $date));
		$dd = $date[0];
		$mm = $date[1];
		$yyyy = $date[2];
	}

	$res = '<div class="col-sm-3"><select id="'.$prefix.'dd" name="'.$prefix.'dd" class="form-control">';
	$res .= '<option value="0">Day</option>';
	for($i=1;$i<=31;++$i) {
		if ($dd == $i) {
			$res .= '<option value="'.$i.'" selected>'.$i.'</option>';
		}
		else {
			$res .= '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	$res .= '</select></div>';
	$res .= ' <div class="col-sm-3"><select id="'.$prefix.'mm" name="'.$prefix.'mm" class="form-control">';
	$res .= '<option value="0">Month</option>';
	for($i=1;$i<=12;++$i) {
		if ($mm == $i) {
			$res .= '<option value="'.$i.'" selected>'.__get_month($i).'</option>';
		}
		else {
			$res .= '<option value="'.$i.'">'.__get_month($i).'</option>';
		}
	}
	$res .= '</select></div>';
	$res .= ' &nbsp; <div class="col-sm-3"><select id="'.$prefix.'yyyy" name="'.$prefix.'yyyy" class="form-control">';
	$res .= '<option value="0">Year</option>';
	for($i=1980;$i<=2010;++$i) {
		if ($yyyy == $i) {
			$res .= '<option value="'.$i.'" selected>'.$i.'</option>';
		}
		else {
			$res .= '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	$res .= '</select></div>';
	return $res;
}
