<?php
session_start();
require_once "inc_config.php";


$pagetime = new Log('page');

// SQL View shortcut
$sqlViewShortcut = array();
$sqlViewShortcut['Purchase Order']	= "select * from purchase_superlutein";
$sqlViewShortcut['Visitor'] 		= "select distinct ip from visitor";


// Admin login account
$admin_dbm_login_disable	= false;

$admin_dbm_login_id			= "stone";
$admin_dbm_login_password	= "12346789";

// Paging function
$topage = $_GET['topage'];
$_SERVER['REQUEST_URI'] = setQueryString($_SERVER['REQUEST_URI'], "topage=");
if (isset($_GET['record_limit']))	{
	$_SESSION['record_limit']	= $_GET['record_limit'];
	$_SERVER['REQUEST_URI'] = setQueryString($_SERVER['REQUEST_URI'], "record_limit=");
	if ($_SESSION['record_limit'] < 1)	$_SESSION['record_limit'] = 100;
	$topage = 1;
}
if (empty($_GET['topage']))		$topage = 1;
$record_limit = $_SESSION['record_limit'];
if (empty($record_limit))			$record_limit				= 100;

// Setting order by parameter
$orderby = $_GET['orderby'];
$_SERVER['REQUEST_URI'] = setQueryString($_SERVER['REQUEST_URI'], "orderby=");




function print_sql_error($sql, $onErrorOnly = false) {
	global $DB_LINK;
	$run_sql_result = @mysql_query($sql);
	if ((mysql_errno($DB_LINK) != 0) or !$onErrorOnly) {
		echo "SQL : <font color='#335577'>$sql</font><br>\n";
		if ($run_sql_result)	echo "Result : Success !\n";
		else					echo mysql_errno($DB_LINK) . " : " . mysql_error($DB_LINK) . "<br>\n";
		global $no_error;
		$no_error = false;
	}

}

function is_binary($str) {
	$result = false;

	if (strlen($str) <= 10) {
		$chars = str_split($str);
	} else {
		$chars = array();
		$chats[0] = substr($str, 0, 1);
		for ($i = 1; $i < 10; $i++) {
			array_push($chars, substr($str, strlen($str) / $i, 1));
		}
	}
	
//	for ($i = 0; $i < strlen($str); $i++) {
//		if (ord($chars[$i])
		
}


if (!isset($_GET['export']))
	echo <<<EOS
<html>
<head>
<title>Database Manager</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="zh-tw">
</head>
<style type="text/css">
<!-- 
body {
	font-family	: arial,"新細明體", "細明體", helvetica,sans-serif;
	font-size	: 12px;
    lletter-spacing: 1px;
    word-spacing: 2px;
	color: #333333;
}

table {
	font-family	: arial,"新細明體", "細明體", helvetica,sans-serif;
	font-size	: 12px;
    lletter-spacing: 1px;
    word-spacing: 2px;
	color: #333333;
}

input {
	font-family	: arial,"新細明體", "細明體", helvetica,sans-serif;
	font-size	: 12px;
	line-height	: 14px;
}

select {
	font-family	: arial,"新細明體", "細明體", helvetica,sans-serif;
	font-size	: 12px;
	line-height	: 14px;
}

a:link,a:active,a:visited {
	text-decoration: none;
	font-size: 12;
	color: #222277;
}
a:hover {
	text-decoration: none;
	font-size: 12;
	color: #C04040;
}
-->
</style>
<body>
EOS;

//////////////////////////////////////////////////////////////////////
//				Login Function										//
//////////////////////////////////////////////////////////////////////

// Validate user
if (isset($_POST['loginid']) and isset($_POST['loginpassword'])) {
	if (isset($admin_dbm_login_sqlcheck)) {
		if (sql_check($admin_dbm_login_sqlcheck))
			$_SESSION['login'] = $_POST['loginid'];
		else
			$login_error = "Login incorrect !";	
	} else {
		if ($_POST['loginid'] == $admin_dbm_login_id and $_POST['loginpassword'] == $admin_dbm_login_password)
			$_SESSION['login'] = $_POST['loginid'];
		else
			$login_error = "Login incorrect !";	
	}
}

// Logout
if (isset($_GET['logout'])) {
	$_SESSION['login'] = '';
}

// Display login form
if (!$admin_dbm_login_disable && empty($_SESSION['login'])) {
	echo <<<EOS
<script language="JavaScript">
function validate() {
	if(!loginInfo.loginid.value){alert('Please enter user name !');loginInfo.loginid.focus();return false;}
	else if(!loginInfo.loginpassword.value){alert('Please enter password !');loginInfo.loginpassword.focus();return false;}
	return true;
}
</script>
<table width='400' border='0' align='center'>
<form name='loginInfo' action='{$_SERVER['PHP_SELF']}' method=post>
<tr><th colspan=2 align=left>Welcome to database manager !</th></tr>
<tr><td colspan=2><font color=red>$login_error</font></td></tr>
<tr><td>User name :</td>
	<td><input type='text' 		name='loginid'></td></tr>
<tr><td>Password :</td>
	<td><input type='password'	name='loginpassword'></td></tr>
<tr><td></td>
	<td><input type='submit' value='Login'></td></tr>
</form>
</table>
<script>loginInfo.loginid.focus();</script>
</body>
</html>
EOS;

	exit();
}

if (!isset($_GET['export']))
	echo "<table border=0 width=95%><tr><td align=right>[ <a href='{$_SERVER['PHP_SELF']}?logout'>Logout</a> ]</td></tr></table>";


//////////////////////////////////////////////////////////////////////
//				Process Function									//
//////////////////////////////////////////////////////////////////////

// Get Table Information
if (isset($_GET['edittable'])) {
	$table	= $_GET['edittable'];
	$result	= mysql_query("select * from $table limit 1");
	$column	= array();
	$columntype = array();
	$columnsize = array();
	for ($i=0; $i < mysql_num_fields($result); $i++) {
		$meta			= mysql_fetch_field($result);
	    $column[$i]		= $meta->name;
		$columntype[$i]	= $meta->type;
		$columnblob[$i]	= $meta->blob;
		$columnsize[$i]	= mysql_field_len($result, $i); // $meta->max_length is incorrect !
	    if (strpos(mysql_field_flags($result, $i), 'binary') !== false)	    	$columnbinary[$i] = true;
	    else																	$columnbinary[$i] = false;

		if ($meta->primary_key == 1)	$primarykey = $column[$i];
	}
	mysql_free_result($result);
}

if (isset($_POST['setprimarykey'])) {
	echo "<h4>Set Primary Key</h4>\n";
	$primarykey = $_POST['setprimarykey'];
	print_sql_error("alter table $table add primary key ($primarykey)");
}


if (isset($_GET['dropprimarykey'])) {
	echo "<h4>Set Primary Key</h4>\n";
	print_sql_error("alter table $table drop primary key");
	unset($primarykey);
}


// Export data
if (isset($_GET['export'])) {

	while (@ob_end_clean());
	
	if (isset($_GET['structure']))				$ext = 'sql';
	elseif (isset($_GET['structureanddata']))	$ext = 'sql';
	elseif (isset($_GET['datasql']))			$ext = 'sql';
	elseif (isset($_GET['datacsv']))			$ext = 'csv';
	elseif (isset($_GET['data']))				$ext = 'sql';
	elseif (isset($_GET['excelxml']))			$ext = 'xml';
	elseif (isset($_GET['excelhtml']))			$ext = 'xls';
	elseif (isset($_GET['excel']))				$ext = 'xls';

	if (isset($_GET['alltable']))			$filename = $DB_DATABASE;
	elseif (isset($_GET['runsql']))			$filename = 'query';
	else									$filename = $_GET['edittable'];
	$filename = "export_$filename.$ext";


	if (isset($_GET['excel'])) {
		require_once "bin/class_excel.php";
		$sql = stripslashes($_POST['runsql'] . $_GET['runsql']);
		if (isset($_GET['runsql']))
			$data	= sql_getTable($sql);
		else
			$data	= sql_getTable("select * from $table");
		$data	= array_convert_title($data);
		$excel	= new Excel();
		$excel->error_report = true;
		$excel->openNew($filename);
		$excel->writeSheet($data);
//		dump($excel->readSheet(), true);
		$excel->download();
		exit();
	}


	if (isset($_GET['excelxml'])) {
		require_once "inc_parser_excel.php";
		$data	= sql_getTable("select * from $table");
		$excel	= new Excel_XML($data);
		$excel->xml_export($filename);
		exit();
	}

	if (isset($_GET['excelhtml'])) {
		require_once "class_excel_html.php";
		$data	= sql_getTable("select * from $table");
		$data	= array_convert_title($data);
		$excel	= new Excel_HTML();
		$excel->openNew($filename);
		$excel->writeSheet($data);
		$excel->download();
		exit();
	}


	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=$filename");
	
	if (isset($_GET['alltable'])) {
		$db_tables = mysql_list_tables($DB_DATABASE);
		while (list($table) = mysql_fetch_row($db_tables)) {

			if (isset($_GET['structure'])) {
				db_export_table_structure($table);
			}
			
			if (isset($_GET['data'])) {
				db_export_table_data($table);
			}

		}
	} else {
		$table = $_GET['edittable'];
		if (isset($_GET['datasql'])) {
			db_export_table_data($table);
		} elseif (isset($_GET['datacsv'])) {
			$sql = mysql_query("select * from $table");
			// Retrieve column information
			$column = array();
			for ($i=0; $i < mysql_num_fields($sql); $i++) {
			    $column[$i]		= mysql_field_name($sql, $i);
			}
			echo '"' . implode('","', $column) . '"' . "\n";

			// Export data in extended sql format
			while ($row = mysql_fetch_row($sql)) {
				echo '"' . implode('","', $row) . '"' . "\n";
			}
		}
		
		
	}
	
	
	exit();
}


// Import data
if (isset($_FILES['importfile']) and ($_FILES['importfile']['error'] == UPLOAD_ERR_OK) and ($_POST['importformat'] == 'ExcelXML')) {
	if (isset($_POST['erasedatabeforeimport']))
		print_sql_error("truncate table {$_GET['edittable']}", true);
	require_once "inc_parser_excel.php";
	$excel	= new Excel_XML($_FILES['importfile']['tmp_name']);
	$data	= $excel->nextTable();
	foreach ($data as $row)
		print_sql_error(sql_insert($table, $row), false);
		
} else if (isset($_FILES['importfile']) and ($_FILES['importfile']['error'] == UPLOAD_ERR_OK) and ($_POST['importformat'] == 'ExcelHTML')) {
	if (isset($_POST['erasedatabeforeimport']))
		print_sql_error("truncate table {$_GET['edittable']}", true);
	require_once "class_excel_html.php";
	$excel	= new Excel_HTML($_FILES['importfile']['tmp_name']);
	$data	= $excel->readSheet();
	$data	= fill_blank_cell($data);
	array_convert_title($data, 'col', true);
	foreach ($data as $row)
		print_sql_error(sql_insert($table, $row), false);
		
} else if (isset($_FILES['importfile']) and ($_FILES['importfile']['error'] == UPLOAD_ERR_OK)) {
	echo "<h4>Import File</h4>\n";

	if (isset($_POST['edittablepost']) and isset($_POST['erasedatabeforeimport']))
		mysql_query("truncate table {$_GET['edittable']}");

/*	// Add quote if no quote in the table
	$file = fopen($_FILES['importfile']['tmp_name'], 'r');
	$newread = fread($file, 512);
	if ($n
	fclose($file);

	if ($newread
	$file = fopen($_FILES['importfile']['tmp_name'] . "-temp", 'w');
*/

	if ($_POST['importformat'] == 'CSV') {
		$str_begin		= '';
		$str_seperator	= ',';
		$str_end		= "\n";
	} elseif ($_POST['importformat'] == 'CSV+Quote') {
		$str_begin		= '"';
		$str_seperator	= '","';
		$str_end		= "\"\n";
	} elseif ($_POST['importformat'] == 'Tab') {
		$str_begin		= '';
		$str_seperator	= "\t";
		$str_end		= "\n";
	} elseif ($_POST['importformat'] == 'Tab') {
		$str_begin		= '';
		$str_seperator	= "\t";
		$str_end		= "\n";
	}
	
//	$skipfirstline = isset($_POST['import_skipfirstline']);


	$file = fopen($_FILES['importfile']['tmp_name'], 'r');

	$stream = "";
	
	while (true) {							// Shifting buffer
		$newread = fread($file, 2048);
		if (strlen($newread) == 0)	break; 
		$stream .= $newread;

		$offset = 0;
		while (true) {						// Shifting lines


			// SQL format import
			if (isset($_POST['runsql'])) {
				//echo "start : " . substr($stream, 0, 20) . "<br>\n";
				$start	= strpos($stream, "insert", $offset);
				$end	= strpos($stream, ");\n", $start);
				if ($end === false)		$end	= strpos($stream, ");\r", $start);
				if ($start === false)	break;
				if ($end === false)		break;
				$line = substr($stream, $start, $end - $start + 3);
				print_sql_error($line, true);
				$stream = substr($stream, $end + 3);
			}
			
			// CSV format import
			if (isset($_POST['edittablepost'])) {
				if ($str_begin != '')				$start	= strpos($stream, $str_begin, $offset);
				else								$start	= $offset;

				// Count number of seperator
				$start_of_end	= $start;
				for ($i=1; $i<count($import_column_name); $i++) {
					$start_of_end	= @strpos($stream, $str_seperator, $start_of_end+1);
				}
//				$start_of_end;
				
				$end	= strpos($stream, $str_end, $start_of_end);
				if ($end === false)		$end	= strpos($stream, "\"\r", $start_of_end);

				if ($start === false)	break;
				if ($end === false)		break;
				$line = trim(substr($stream, $start + strlen($str_begin), $end - $start - (strlen($str_end) - 1)));
				$value = explode($str_seperator, $line);
//dump($line);
				if (!isset($import_column_name)) {
					$import_column_name = $value;
				} else {
					$fields = array();
					for ($i=0; $i<count($import_column_name); $i++) {
						$fields[$import_column_name[$i]] = addslashes($value[$i]);
					}
					print_sql_error(sql_insert($_GET['edittable'], $fields), false);
				}
				
				$stream = substr($stream, $end + strlen($str_end));
				
				/*$lines = split("(\r|\r\n)", $stream);
				for ($i=0; $i<count($lines)-1; $i++) {
					$value = explode('","', $lines[$i]);
					
				}
				$stream = $lines[$i];*/
				
			}

		}
		
	}  

	echo "Done !<br>\n";
	fclose($file);
	unlink($_FILES['importfile']['tmp_name']);
	
}



// Erase all table data
if (isset($_GET['erase']) and isset($_GET['alltable']) and isset($_GET['data'])) {
	$db_alltables	= mysql_list_tables($DB_DATABASE);
	while (list($db_table) = mysql_fetch_row($db_alltables)) {
		mysql_query("delete from $db_table");
	}
}


// Updata table data
if ($_POST['edittablepost'] == '1') {

	$no_error = true;

	$field = array();
	echo "<h4>Update result</h4>\n";

	// Update Edit Rows
	for ($i=0; $i < $_POST['editrowsum']; $i++) {
		array_splice($field, 0);
		$condition = array();
		for ($col=0; $col < count($column); $col++) {
			if (isset($_POST["row{$i}_$col"]))			// Column value
				$field[$column[$col]] = $_POST["row{$i}_$col"];

			if (!isset($primarykey)) {					// Set condition of original column
				$orig = $_POST["orig_row{$i}_$col"];
				if ($columnblob[$col])
					$orig = rawurldecode($orig);
				array_push($condition, $column[$col] . "='" . addslashes($orig) . "'");
			}
		}

		if (isset($primarykey))
			$condition = "$primarykey='" . $_POST["row{$i}_key"] . "'";
		else
			$condition = '(' . join($condition, ' and ') . ')';
		$sql = sql_update($table, $field, $condition);
		print_sql_error($sql, true);
	}
	
	// Insert Rows
	for ($i=0; $i < 5; $i++) {
		array_splice($field, 0);
		for ($col=0; $col < count($column); $col++) {
			if (isset($_POST["irow{$i}_$col"]))
				$field[$column[$col]] = $_POST["irow{$i}_$col"];
		}

		if (join('', $field) == '')	continue;
		$sql = sql_insert($table, $field);

		print_sql_error($sql, true);
	}
	
	if ($no_error)	echo "Success !";
}
	

if (isset($_GET['deleterecord'])) {
	echo "<h4>Delete Record</h4>\n";

	if (isset($primarykey))
		$condition = "$primarykey='" . $_GET["delete_key"] . "'";
	else {
		$condition = array();
		for ($col=0; $col < count($column); $col++) {
			if (!isset($primarykey)) {					// Set condition of original column
				$orig = $_GET["delete_row_$col"];
				array_push($condition, $column[$col] . "='" . addslashes($orig) . "'");
			}
		}
		$condition = '(' . join($condition, ' and ') . ')';
	}
	print_sql_error("delete from $table where $condition");
}


if ($_GET['resortkey'] == '1') {
	echo "<h4>Re-sort Primary Key</h4>\n";

	$no_error = true;

	if (!isset($primarykey)) {
		echo "<b><font color='red'>Warning : No Primary key found !</font></b><br>\n";
	} else {
		$sql_id	= mysql_query("select $primarykey from $table order by $primarykey");
		$total_row = mysql_num_rows($sql_id);
		for ($id=1; $id<=$total_row; $id++) {
			list($oldid) = mysql_fetch_row($sql_id);
			print_sql_error("update $table set $primarykey='$id' where $primarykey='$oldid'", true);
		}
	}
	print_sql_error("alter table $table order by $primarykey", true);
	if ($no_error)	echo "Success !";
}



//////////////////////////////////////////////////////////////////////
//				Display												//
//////////////////////////////////////////////////////////////////////




// List Tables for editing
echo "<h4>Select table</h4>\n";
$table_group = array();
$db_alltables	= mysql_list_tables($DB_DATABASE);

$table_group['nogroup'] = array();
while (list($db_table) = mysql_fetch_row($db_alltables)) {
	$link = "<a href='{$_SERVER['PHP_SELF']}?edittable=$db_table'>$db_table</a>";
	$tablename = explode('_', $db_table);
	if (count($tablename) == 1) {			// No prefix
		array_push($table_group['nogroup'], $link);
	} else {
		if (!isset($table_group[$tablename[0]]))		$table_group[$tablename[0]] = array();
		array_push($table_group[$tablename[0]], $link);
	}
}
echo "<table border=0 cellpadding=2 cellspacing=0><tr>\n";
foreach ($table_group as $groupname => $links) {
	if ($groupname == 'nogroup')	continue;
	if (count($links) == 1) {
		array_push($table_group['nogroup'], $links[0]);
		continue;
	}
	echo "<td style='padding-left:20px' valign=top>" . join(" <br>\n", $links) . "\n</td>\n";
}
echo "<td style='padding-left:20px' valign=top>" . join(" <br>\n", $table_group['nogroup']) . "<br>\n
		<a href='{$_SERVER['PHP_SELF']}'>None</a></td>\n";
echo "</tr></table>";

echo "<p style=''>Functions :
	[ <a href='{$_SERVER['PHP_SELF']}'>Deselect table</a> ]
	[ <a href='{$_SERVER['PHP_SELF']}?export&alltable&data'>Export all data</a> ]
	[ <a href='javascript:if (confirm(\"Are you sure to erase all table data ?\")) location.href=\"{$_SERVER['PHP_SELF']}?erase&alltable&data\"'>Erase all data</a> ]
	</p>\n";




// Run SQL
if (isset($_GET['edittable']))	$str_edittable = "?edittable={$_GET['edittable']}";
else							$str_edittable = '?';
$runsql = stripslashes($_POST['runsql']);
echo <<<EOD

<script>
function ctrl_enter(form) {
	if((event.ctrlKey && window.event.keyCode == 13) || (event.altKey && window.event.keyCode == 83)) {
		//if(validate(this.document.input)) 
		form.submit();
	}
}
</script>

	<h4>Run SQL</h4>
	<table border=0 cellspacing=0>
	<form id=runsqlform action='{$_SERVER['PHP_SELF']}{$str_edittable}' method='post' enctype='multipart/form-data'>
	<tr><td valign=top>SQL : </td>
		<td><textarea name='runsql' cols='90' rows=4 onKeyDown="javascript: ctrl_enter(runsqlform);">$runsql</textarea></td>
	</tr>
	<tr><td colspan=2>Run SQL from file : <input type=file name=importfile size=70> <input type='submit' value='Run'></td></tr>
	</form>
	</table>
	SQL Shortcut : 
EOD;
foreach ($sqlViewShortcut as $name => $sql) {
	$sql = rawurlencode($sql);
	echo " [ <a href='{$_SERVER['PHP_SELF']}{$str_edittable}&runsql=$sql'>$name</a> ] ";
}


// Run SQL
if (!empty($_POST['runsql']) or !empty($_GET['runsql'])) {
	echo "<h4>Run SQL result</h4>\n";
	$sql = stripslashes($_POST['runsql'] . $_GET['runsql']);
	
	$_SESSION['prev_sql'] = $sql;

	if (isset($orderby))
		$sql = sql_setParameter($sql, "order", "order by $orderby");
	
	if (eregi('select ', $sql)) {
		$query = mysql_query($sql);
		$total_records = @mysql_num_rows($query);
		if ($total_records != 0) {
			echo "Total records : <b>$total_records</b><br>";
			echo "<table border='1' cellpadding=5 cellspacing=0><tr>\n";

			// Column name
			for ($i=0; $i < mysql_num_fields($query); $i++) {
				$name	= $label	= mysql_field_name($query, $i);
				$arrow = "";
				if (startWith($orderby, $name)) {
					if (endWith($orderby, 'asc')) {
						$order = "desc";
						$arrow = " &uarr;";
					} else {
						$order = "asc";
						$arrow = " &darr;";
					}
				} else {
					$order = "asc";
				}
		
			    if ($primarykey == $name)	$label = "<font color='#556677'>$label</font>";
				$name	= "<a href='" . $_SERVER['REQUEST_URI'] . "&orderby=$name%20$order'>$label</a>";
				echo "<td align=center valign=top><b>$name</b>$arrow<br>".mysql_field_type($query, $i)."</td>";
			}
			echo "<tr>\n";
			while ($row = mysql_fetch_row($query)) {
				echo "<tr>";
				for ($i=0; $i < count($row); $i++) {
					if (trim($row[$i]) == '')	$row[$i] = '&nbsp;';
//					if (strlen($row[$i]) > 50)	$row[$i] = substr($row[$i], 0, 50) . '...';
					$row[$i] = nl2br($row[$i]);

					
					if (strpos(mysql_field_flags($query, $i), 'binary') !== false and mysql_field_type($query, $i) == 'blob')
						$row[$i] = ' ( Binary ) ';
					
					//if (!ctype_print($row[$i]))	$row[$i] = ' ( Binary ) ';
					echo "<td valign=top>" . $row[$i] . "</td>";
				}
				echo "</tr>\n";
			}
			echo "</table>";
		} else {
			echo "SQL : <font color='#335577'>$sql</font><br>\n";
			echo "No result return !<br>\n";
			echo mysql_errno($DB_LINK) . " : " . mysql_error($DB_LINK) . "<br>\n";
		}
	} else {
		print_sql_error($sql);
	}


} else if (isset($_GET['edittable'])) {				//// Edit Table	////
	
	list($total_records) = mysql_fetch_row(mysql_query("select count(1) from $table"));
	$total_page = max(ceil($total_records / $record_limit), 1);
	
	$pagingString = "To page : ";
	for ($i=1; $i <= $total_page; $i++) {
		if ($topage == $i)	$pageNum = "<b>$i</b>";
		else				$pageNum = $i;
		$pagingString .= " <a href='{$_SERVER['REQUEST_URI']}&topage=$i'>$pageNum</a> ";
	}

	echo <<<EOS
	<h4>Edit table : $table</h4>
	<table border=0 width=90%>
	  <tr>
		<td>Total records : <b>$total_records</b></td>
		<td align=right>$pagingString</td>
		<td align=right width=200>
			Show
			<input type=text name=record size=4 value="$record_limit" onkeydown="if (event.keyCode==13) location.href='{$_SERVER['REQUEST_URI']}&record_limit=' + record.value;">
			records
		</td>
	  </tr>
	</table>
EOS;
	
	if (!isset($primarykey)) {
		echo "<form action='{$_SERVER['PHP_SELF']}?edittable=$table' method='post' ENCTYPE='multipart/form-data'>";
		echo "<b><font color='red'>Warning : No primary key found.</font></b><br>\n";
		echo "Set Primary Key : <select name='setprimarykey'>\n";
		for ($i = 0; $i < count($column); $i++)
			echo "<option value='{$column[$i]}'>{$column[$i]} \n";
		echo "<input type='submit' value='Submit'></select></form>\n";
	} else {
		echo "[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&resortkey=1'>Re-sort Primary Key</a> ]\n";
		echo "[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&dropprimarykey=1'> Drop Prinary Key</a> ] <br>\n";
	}

	echo "<form action='{$_SERVER['PHP_SELF']}?edittable=$table&topage=$topage' method='post' ENCTYPE='multipart/form-data'>";
	echo "Import :	
			<input type=radio name=importformat value='ExcelHTML' checked> Excel HTML
			<input type=radio name=importformat value='ExcelXML'> Excel XML
			<input type=radio name=importformat value='CSV'> CSV
			<input type=radio name=importformat value='CSV+Quote'> CSV with quote
			<input type=radio name=importformat value='Tab'> Tab Seperate
			<input type=file name=importfile size=50>
			<input type=checkbox name=erasedatabeforeimport>Erase data before import<br>\n";
	echo "Export :
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&structure'>Structure</a> ]
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&structureanddata'>Structure and data</a> ]
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&datasql'>Data (SQL)</a> ]
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&excelxml'>Data (Excel XML)</a> ]
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&excelhtml'>Data (Excel HTML)</a> ]
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&excel'>Data (Excel)</a> ]
		[ <a href='{$_SERVER['PHP_SELF']}?edittable=$table&export&datacsv'>Data (CSV)</a> ]<br><br>\n";
	



	echo "<table border='1' cellpadding=1 cellspacing=0><tr>\n";
	echo "<td align=center>#</td>\n";

	$column_count = count($column) + 2;

	// Print Column Names
	for ($i = 0; $i < count($column); $i++) {
		$name	= $label	= $column[$i];
		$arrow = "";
		if (startWith($orderby, $name)) {
			if (endWith($orderby, 'asc')) {
				$order = "desc";
				$arrow = " &uarr;";
			} else {
				$order = "asc";
				$arrow = " &darr;";
			}
		} else {
			$order = "asc";
		}

	    if ($primarykey == $name)	$label = "<font color='#556677'>$label</font>";
		$name	= "<a href='" . $_SERVER['REQUEST_URI'] . "&orderby=$name%20$order'>$label</a>";
	    
    	echo "<td valign='top' align='center'><b>$name</b>$arrow<br>{$columntype[$i]} [{$columnsize[$i]}]</td>\n";
	}
	echo "<td>&nbsp;</td></tr>\n";

	// Calculate page
	if (!isset($orderby))			$orderby = $primarykey;

	$start_record	= ($topage-1) * $record_limit;
	$sql			= "select * from $table";
	
	if (!empty($orderby))
		$sql			= sql_setParameter($sql, "order", "order by $orderby");
	$sql			= sql_setParameter($sql, "limit", "limit $start_record, $record_limit");
	

	$_SESSION['prev_sql'] = $sql;
	$sql			= mysql_query($sql);


	// Print Columns Edits
	$count = 0;
	while ($row = @mysql_fetch_array($sql)) {
				
		echo "<tr onclick=''>";
		echo "<td align=center style='padding:0px 5px 0px 5px'>" . ($count + $start_record + 1) . "</td>";
		
		$i	= 0;
		for ($i = 0; $i < count($column); $i++) {
			$size = min($columnsize[$i] + 1, 30);
			if ($columntype[$i] == 'int')			$size = "size='$size'";
			else if ($columntype[$i] == 'string')	$size = "size='$size'";
			else									$size = '';

			if ($columntype[$i] == 'blob') {
				if ($columnbinary[$i]) {
					echo "<td align='center' valign='top'> ( Binary ) </td>";
					if (!isset($primarykey))
						echo "<input type=hidden name='orig_row{$count}_$i' value='" . rawurlencode($row[$i]) . "'>\n";
				} else {
					echo "<td align='center' valign='top'><textarea name='row{$count}_$i' cols='25' rows='3'>{$row[$i]}</textarea></td>";
					if (!isset($primarykey))
						echo "<input type=hidden name='orig_row{$count}_$i' value='" . addslashes($row[$i]) . "'>\n";
				}
			} else {
				echo "<td align='center' valign='top'><input name='row{$count}_$i' $size value='".$row[$i]."''></td>";
				if (!isset($primarykey))
					echo "<input type=hidden name='orig_row{$count}_$i' value='" . addslashes($row[$i]) . "'>\n";
			}
		}
		
		// Delete row
		$condition = "";
		if (isset($primarykey)) {
			$condition = "&delete_key={$row[$primarykey]}";
		} else {
			for ($i = 0; $i < count($column); $i++) {
				$condition .= "&delete_row_$i=" . addslashes($row[$i]);
			}
		}
		echo "<td align=center valign=top style='padding-top:4px' onmouseover='status=\"\"; return true;'> [ <a href='javascript:if (confirm(\"Are you sure to delete this record ?\")) location.href=\"{$_SERVER['PHP_SELF']}?edittable=$table&deleterecord{$condition}\"'>Delete</a> ]</td>";
		
		
		if (isset($primarykey))
			echo "<input type='hidden' name='row{$count}_key' value='{$row[$primarykey]}'>";
		echo "</tr>\n";
		$count++;
	}

	echo "<input type='hidden' name='editrowsum' value='$count'>\n";	
	
	echo "<tr height=5><td colspan=$column_count><hr width=100% size=3</td></tr>";
	
	// Print 'Insert' Columns
	for ($j = 0; $j < 5; $j++) {
		echo "<tr><td>&nbsp;</td>";
		$i	= 0;
		for ($i = 0; $i < count($column); $i++) {
			$size = min($columnsize[$i] + 1, 30);
			if ($columntype[$i] == 'int')			$size = "size='$size'";
			else if ($columntype[$i] == 'string')	$size = "size='$size'";
			else									$size = '';
			
			if ($columntype[$i] == 'blob') {
//				if ($columnbinary[$i])
					echo "<td align='center' valign='top'><textarea name='irow{$j}_$i' cols='25' rows='3'></textarea></td>";
			} else {
				echo "<td align='center' valign='top'><input name='irow{$j}_$i' $size></td>";
			}

		}
		echo "<td>&nbsp;</td></tr>\n";
	}
	
	echo "<input type='hidden' name='edittablepost' value='1'>";
	echo "<tr><td colspan='$column_count' align='center'><input type='submit' value='Submit'></td></tr>";
	echo "</table>";
	echo "</form>";
}
	


$pagetime->show(3);

echo "</body></html>";


?>
