<?php
include_once "inc_common.php";
header('Content-type: application/json');
$bid = isset($_POST['bid']) ? (int) $_POST['bid'] : 0;


$detail 	= sql_getVar("select * from blasting_template_tab where bid=".$bid);
echo json_encode($detail);
