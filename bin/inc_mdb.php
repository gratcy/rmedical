<?php 

//////////////////////////////////////////////////////////////////////////////////
//	Connect and query function for Microsoft Access .mdb file					//
//////////////////////////////////////////////////////////////////////////////////

function mdb_connect($file, $user = '', $password = '') {
	global $__mdb_connection;
	if (!isset($__mdb_connection))
		$__mdb_connection	= array();

	$file	= str_replace('/', '\\', $file);
	if (!is_file($file))
		echo "Database file not found : $file<br>";

	$conn	= new COM("ADODB.Connection") or die("Cannot start ADO");
	$conn->Open("DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$file");
	$__mdb_connection[]	= $conn;
	return $conn;
}

function mdb_query($sql, $conn = '') {
	if ($conn == '')
		$conn	= $GLOBALS['__mdb_connection'][count($GLOBALS['__mdb_connection'])-1];
	return @$conn->Execute($sql);
}

function mdb_fetch_row($resource) {
	if ($resource->EOF)		return false;
	$result	= array();
	for ($i=0; $i<$resource->Fields->Count; $i++) {
		$temp		= $resource->Fields($i);
		$result[]	= $temp->Value;
	}
	$resource->MoveNext();
	return $result;
}

function mdb_fetch_assoc($resource) {
	if ($resource->EOF)		return false;
	$result	= array();
	for ($i=0; $i<$resource->Fields->Count; $i++) {
		$temp	= $resource->Fields($i);
		$result[$temp->Name]	= $temp->Value;
	}
	$resource->MoveNext();
	return $result;
}

function mdb_num_rows($resource) {
	return "unknown";
	return $resource->RecordCount;
}

function mdb_getFields($resource) {
	$fields	= array();
	for ($i=0; $i<$resource->Fields->Count; $i++) {
		$temp		= $resource->Fields($i);
		$fields[]	= $temp->Name;
	}
	return $fields;
}







?>