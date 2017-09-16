<?php
include_once "inc_common.php";
header('Content-type: application/json');
$cid = isset($_POST['cid']) ? (int) $_POST['cid'] : 0;


$detail 	= sql_getVar("select * from customer_tab where cid=".$cid);
if ($detail) {
	$detail['cbirthday'] = date('Y-m-d', $detail['cbirthday']);
	echo json_encode($detail);
}
else {
	echo json_encode(array());
}
