<?php

 
	include_once "bin/inc_config.php";
	
	$gid		= $_GET['group'] * 1;
	
	if (!empty($gid)) {
		
		$users		= sql_getArray("select id, name from staff where `group`='$gid' and status != 'deleted' order by name");
		
		$count		= count($users);
		$rows		= ($count < 10) ? 1 : 2;
		
		$cols		= ($count / $rows) + 1;

		$current_cols	= 0;
		
		echo "<table cellpadding=0 cellspacing=3 border=0>";
		echo "<tr>\r\n";
		foreach ($users as $id => $name) {
			
			$info	= sql_getObj("select * from staff where id=$id");
			
			if ($current_cols == 0)	echo "<td valign=top>";
			echo "<input type=checkbox id=to_$id name=to_$id value=1 checked> $name   $info->email  $info->mobile <br>\r\n";
			
			if (++$current_cols >= $cols) {
				$current_cols = 0;
				echo "</td>";
			}
		}
		echo "</tr>";
		echo "</table>";
		
		
		$count		= 0;
		echo "<script>";
		echo "person_checkbox		= new Array();\r\n";
		
		foreach ($users as $id => $name) {
			echo "person_checkbox[$count]		= 'to_$id'\r\n";
			$count++;
		}
		echo "</script>";


	}
	
?>