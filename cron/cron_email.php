<?php
include_once "../inc_common.php";
error_reporting(E_ALL);
function __update_sent($id) {
	$fields['edatesent'] = date('Y-m-d H:i:s');
	$fields['estatus'] = 2;
	return sql_query(sql_update("email_queue_tab", $fields, "eid='$id'"));
}

$arr = sql_getTable("select * FROM email_queue_tab WHERE eschedule <= now() AND estatus=1");
foreach($arr as $k => $v) {
	__send_email($v['eemail'],$v['esubject'],$v['econtent'],false);
	var_dump($v);
	__update_sent($v['eid']);
}
