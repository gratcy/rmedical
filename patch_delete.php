<?php

include_once "header.php";




$items			= sql_getArray("select id from invoice where status='deleted'");
foreach ($items as $id) {
	
	delete_record("select * from invoice_detail where invoice_id='$id'");
	delete_record("select * from invoice where id='$id'");
	
	echo "<p>Deleting ID : $id</p>";
	
}

include_once "footer.php";


?>