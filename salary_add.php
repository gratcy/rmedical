<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='salary.php'");
if (empty($privilege->edit))	{	gotoURL("salary.php"); exit; }


$prefix_salary_id		= "SAL" . date("Ym");
$suffix_salary_id		= substr(sql_getValue("select salary_id from salary where salary_id like '$prefix_salary_id%' order by salary_id desc limit 1"), 9);
$new_salary_id			= $prefix_salary_id . padding($suffix_salary_id+1, 4);

$fields		= array(
					"salary_id"			=> $new_salary_id,
					"date_issue"		=> date("Y-m-d")
					);


sql_query(sql_insert("salary", $fields));
$salary_id		= sql_insert_id();
echo "<meta http-equiv='refresh' content='0;url=salary_edit.php?id=$salary_id'>";


include_once "footer.php";

?>