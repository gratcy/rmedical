<?php

Ignore_User_Abort(False);

ini_set('memory_limit', '128M');



foreach ($columns as $field => $column) {
	$infos					= explode(",", $column);

	list($name, $value)		= explode(":", $infos[0]);
	
	$titles[]				= $value;

}


array_unshift($items, $titles);


$xls 		= new Excel();


$xls->addArray ( $items );

$filename	 = date('YmdHis');

$xls->generateXML ($filename);


exit;


?>