<?php


if (!function_exists("sql_secure")) {
	die("Function missing. Unable to process data update");
}


$cache									= array();
$cache['id']							= $id;
$cache['prefix']						= $prefix;
$cache['field']							= $field;
$cache['other']							= $other;


$cms_result								= false;
$cms_update_count						= 0;


	
if (empty($cms_table))
	$cms_table							= sql_secure($_POST['cms_table']);
if (empty($cms_key))
	$cms_key							= sql_secure($_POST['cms_key']);
if (empty($cms_prefix))
	$cms_prefix							= "cms";


$columns								= sql_getTable("show columns from `$cms_table`");
foreach ($columns as $index => $info) {
	$columns[$index]					= $info['Field'];
}



$data									= array();
$delete									= array();


foreach ($_POST as $name => $value) {
	
	list($prefix, $id, $field, $other)	= explode("::", $name);
	
	if ($prefix	!= $cms_prefix)			continue;
	
	
	if ($other	== 'new') {
		$id								= "new_$id";
	}
	
	if ($field == 'null' && $value == 'delete') {
		$delete[]						= $id;
		continue;
	}
	
	if (!in_array($field, $columns)) {
		continue;
	}
	
	if (startWith($other, 'session_')) {
		$value							= $_SESSION[substr($other, 8)];
	}
	
	if (startWith($other, 'now')) {
		$value							= date("Y-m-d H:i:s");
	}
	
	
	
	$field								= sql_secure($field);
	$value								= sql_secure($value);

	
	$data[$id][$field]					= $value;
	
	$cms_update_count++;
	
}


$domain_path							= ($domain == "everyone.myftp.org") ? "rock/" : "";


$insert_fields							= "";
$insert_sqls							= "";

foreach ($data as $id => $fields) {
	if (in_array($id, $delete))
		continue;

	if (startWith($id, "new_")) {
		
		$sql							= sql_insert($cms_table, $fields);
		$sql							= explode(" values ", $sql);
		
		if ($sql[0] == $insert_fields) {
			$insert_sqls				.= " , " . $sql[1];
		} else {
			
			if (!empty($insert_sqls)) {
				$result			= sql_query($insert_sqls);
				//$result		= http_post("http://$domain/{$domain_path}cms_process_query.php", array("sql" => $insert_sqls));
//				debug_dump($insert_sqls);
			}
			
			$insert_fields				= $sql[0];
			$insert_sqls				= $sql[0] . " values " . $sql[1];
		}
		
	} else {
		mysql_query(sql_update($cms_table, $fields, "`$cms_key`='$id'"));
//		debug_dump(sql_update($cms_table, $fields, "`$cms_key`='$id'"));
	}
	

}

if (!empty($insert_sqls)) {
	$result			= sql_query($insert_sqls);
	//$result		= http_post("http://$domain/{$domain_path}cms_process_query.php", array("sql" => $insert_sqls));
//	debug_dump($insert_sqls);
}



foreach ($delete as $id) {
	if (!startWith($id, "new_")) {
		mysql_query("delete from `$cms_table` where `$cms_key`='$id'");
//		debug_dump("delete from `$cms_table` where `$cms_key`='$id'");
	}
}

unset($data, $delete);

$cms_result								= true;
	

array2var($cache);
unset($cache);




?>