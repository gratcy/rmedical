<?php

include "bin/inc_config.php";

//file_put_contents("cms_process_query.txt", stripslashes($_POST['sql']));

sql_query(stripslashes($_POST['sql']));

//echo "done";

?>