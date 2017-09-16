<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='manager_database.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

include "privilege_special.php";

echo <<<EOS
<h3>數據庫管理</h3>
<br>
EOS;


include "db_restore_table.php";

include_once "footer.php";

?>