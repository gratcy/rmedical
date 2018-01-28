<?php
error_reporting(0);

// Set the document url path for "include" in different folder
$DOCUMENT_ROOT	= $_SERVER['DOCUMENT_ROOT'];
define('DOCROOT', $DOCUMENT_ROOT);
$DOCUMENT_URL	= $DOCUMENT_ROOT;
if (dirname($_SERVER["REQUEST_URI"]) != "\\")
	$DOCUMENT_URL	.= dirname($_SERVER["REQUEST_URI"]);
if (@is_dir(basename($_SERVER["REQUEST_URI"])))
	$DOCUMENT_URL	.= '/' . basename($_SERVER["REQUEST_URI"]);


ini_set('date.timezone', 'Asia/Hong_Kong');


$CURRENT_YEAR	= date('Y');



require_once "class_log.php";
require_once "class_template.php";
//require_once "class_user.php";
require_once "inc_setting.php";
require_once "inc_database.php";
require_once "inc_library.php";
//require_once "inc_session.php";
//require_once "inc_seo.php";


//require_once "templates.php";

/*
if ($DISABLE_VISITOR != true) {
	Counter(basename($_SERVER['SCRIPT_NAME']) . "?" . $_SERVER['QUERY_STRING']);
	require_once "inc_visitor.php";
}
*/

?>
