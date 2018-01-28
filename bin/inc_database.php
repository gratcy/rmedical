<?php
$DB_LINK = mysql_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD);
if ($DB_LINK) {
	$DB_TABLE = mysql_select_db($DB_DATABASE, $DB_LINK);
	if (!$DB_TABLE) {
		echo "<script> window.alert('Fail connecting to database, please try again later.'); </script>";
		exit();
	}
} else {
	echo "<script> window.alert('Fail connecting to database, please try again later.'); </script>";
	exit();
}

mysql_query("set names utf8");
//mysql_query("set character set utf8");



// Create SQL statement from a $source array , with a string $fields of fields seperated by ','
function sql_fieldSelection($source, $args) {
	$fields = explode(',', $args);

	// Predefine some values;
	$postdate	= $regdate	= $date		= date("Y-m-d H:i:s"); 
	$ip_post	= $ip		= $_SERVER['REMOTE_ADDR'];
	$hit		= 0;

	$result = array();
	foreach ($fields as $key) {
		if (is_array($source[$key]))	{		// In case the input field is array (e.g. 'select' in form)
			$source[key] = join(';', $source[$key]);
		}
		$value = sql_secure($source[$key]);		// Use source value
		if (empty($value) and $value != '0')	$value = $$key;		// Use predefined value
		if (empty($value) and $value != '0')	$value = '';		// Use empty string;
		$result[$key] = $value;
	}
	
	return $result;
}


function sql_query($sql, $always_report = false) {
	global $DB_LINK, $SQL_LAST;
	$SQL_LAST		= $sql;
	$run_sql_result = mysql_query($sql);
	if ((mysql_errno($DB_LINK) != 0) or $always_report) {
		echo "SQL : <font color='#335577'>$sql</font><br>\n";
		if ($run_sql_result)	echo "Result : Success !<br>\n";
		else					echo @mysql_errno($DB_LINK) . " : " . mysql_error($DB_LINK) . "<br>\n";
		global $no_error;
		$no_error = false;
	}
	return $run_sql_result;
}

function sql_last() {
	global $SQL_LAST;
	return $SQL_LAST;
}

function sql_check($sql) {
	return (@mysql_num_rows(@mysql_query($sql)) >= 1);
}

function sql_getValue($sql) {
	return @array_shift(@mysql_fetch_row(@sql_query($sql)));
}

function sql_getVar($sql) {
	$data	= @mysql_fetch_assoc(@sql_query($sql));
	array2var($data);
	return $data;
}

function sql_getObj($sql) {
	$data	= @mysql_fetch_assoc(@sql_query($sql));
	if (is_array($data))	array2obj($data);
	return $data;
}

function sql_getFields($source) {
	if (is_resource($source)) {
		$fields	= array();
		for ($i = 0; $i < mysql_num_fields($source); $i++) {
			$fields[]	= mysql_field_name($source, $i);
		}
		return $fields;
	} else if (is_string($source)) {
		
		$fields	= subString($source, 'select ', ' from');
		$fields	= split(',|, ', $fields);
		foreach ($fields as $key => $value) {
			if (contain($value, '.'))
				$fields[$key]	= substr($value, strpos($source, '.') + 1);
		}
		return $fields;
	}
}

function sql_getArray($sql) {
	$result	= array();
	$db		= sql_query($sql);
	while ($row = @mysql_fetch_row($db)) {
		if (count($row)	> 1)
			$result[$row[0]]	= $row[1];
		else
			$result[]			= $row[0];
	}
	return $result;
}

function sql_getTable($sql, $mode = 'assoc') {
	$result	= array();
	$db		= sql_query($sql);
	if ($mode == 'assoc')
		while ($row = @mysql_fetch_assoc($db))
			$result[]	= $row;
	else {
		if ($mode == 'title') {
			$column = array();
			for ($i=0; $i < mysql_num_fields($db); $i++)
			    $column[$i]		= mysql_field_name($db, $i);
		}
		$result[] = $column;
		while ($row = @mysql_fetch_row($db))
			$result[]	= $row;
	}
	return $result;
}

function sql_insert_id() {
	global $DB_LINK;
	return mysql_insert_id($DB_LINK);
	// return sql_getValue("select last_insert_id()");
}

function sql_setParameter($sql, $command, $value) {			// Change property of sql , e.g. limit, order by
	if (!startWith($sql, "select"))		return $sql;

	$prefix = substr($command, 0, max(strpos($command, ' '), strlen($command))) . " ";
	$keyword = array("select " => 0, "from " => 1, "where " => 2, "group " => 3, "having " => 4, "order " => 5, "limit " => 6);
	
	// Find insert pos in keyword list
	$insertPos = false;
	$insertPos = @$keyword[$prefix];
	if ($insertPos === false)		return $sql;

	// Process if using union
	$result = array();
	$sqls = spliti(" union ", $sql);

	foreach ($sqls as $sql) {
	
		// Find phrases' position
		$phrasePos	= array();
		foreach ($keyword as $word => $index) {
			$pos = 0;
			while (true) {
				if (PHP_VERSION >= '5')
					$pos = stripos($sql, $word, $pos);
				else
					$pos = strpos($sql, $word, $pos);

				if ($pos === false)
					break;
				else
					$phrasePos[] = $pos;
				$pos++;
			}
		}
		array_push($phrasePos, strlen($sql));
		sort($phrasePos);

		// Split the sql commands
		$done		= false;
		$phrase		= array();
		for ($i=0; $i < count($phrasePos)-1; $i++) {
			$phrase[$i] = substr($sql, $phrasePos[$i], $phrasePos[$i+1] - $phrasePos[$i]);
			if (startWith($phrase[$i], $prefix)) {		// replace the command phrase
				$phrase[$i]	= $value . " ";
				$done		= true;
			}
			
			if (!$done) {
				$_prefix = substr($phrase[$i], 0, max(strpos($phrase[$i], ' '), strlen($phrase[$i]))) . " ";
				$_prefixPos = @$keyword[$_prefix];
				if ($_prefixPos > $insertPos) {
					$phrase[$i]	= "$value {$phrase[$i]}";
					$done = true;
				}
			}
		}
		
		if (!$done)		$phrase[] = " $value";
		
		$result[] = join($phrase);
	}
	
	return join($result, ' union ');
}

function sql_insert($table, $fields, $escapeQuoteFields = '') {
	$escapeQuoteFields = explode(',', $escapeQuoteFields);
	if (is_array($fields))
		foreach ($fields as $key => $value)
			if (array_search($key, $escapeQuoteFields) === false)		$fields[$key] = "'" . str_replace(array("\\", "'"), array("\\\\", "\'"), $value) ."'";
			elseif ($value == '')										unset($fields[$key]);
	else
		return false;
	$result		= "insert into `$table` (`" . implode("`,`",array_keys($fields)) . "`) values (" . implode(",", array_values($fields)) . ")";
	$result		= str_replace(array("'[null]'", "'[NULL]'"), "null", $result);
	return $result;
}



function sql_update($table, $fields, $condition = '', $escapeQuoteFields = '') {
	if (!empty($condition))	{
		if (!sql_check("select 1 from $table where $condition"))
			return sql_insert($table, $fields, $escapeQuoteFields);
		$condition	= ' where ' . $condition;
	}

	// Delete escape quote fields
	$escapeQuoteFields = explode(',', $escapeQuoteFields);
	foreach ($fields as $key => $value)
		if (array_search($key, $escapeQuoteFields) === false)		$fields[$key] = "'" . str_replace(array("\\", "'"), array("\\\\", "\'"), $value) . "'";
		elseif ($value == '')										unset($fields[$key]);

	$setstr = array();
	foreach ($fields as $key => $value)
		$setstr[] = "`$key`=$value";
	$result		= "update `$table` set " . implode(', ', $setstr) . $condition;
	$result		= str_replace(array("='[null]'", "='[NULL]'"), "=null", $result);
	return $result;
}



// Check for input message for attacking script or database command
function sql_secure($content, $fields = '') {
	if (is_string($content)) {
		$word	 = array("script"	, "cookie"	, "unescape"	, "update"	, "exec"	, "select"	, "delete");
		$message = trim($content);
	
		for ($i=0; $i < count($word); $i++) {
			if (stristr("$message", $word[$i])) {
				$replace = '- ' . chunk_split(strtoupper($word[$i]), 1, ' ') . '-';
				$message = eregi_replace($word[$i], $replace[$i], $message);
			}
		}
		
		if (get_magic_quotes_gpc() == 0)	return $message;
		else								return stripslashes($message);
	
		//return str_ireplace($words, $replace, trim($message));
	} else if (is_array($content)) {
		$result	= array();
		if (empty($fields))		$fields	= array_keys($content);
		else					$fields	= explode(',', $fields);
		foreach ($fields as $key)
			$result[trim($key)]	= sql_secure(trim($content[trim($key)]));
		return $result;
	}
}


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



?>
