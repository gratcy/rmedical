<?php

$column_orderby_max		= 0;
foreach ($items as $item) {
	if (!is_numeric($item[$orderby])) {
		$column_orderby_max		= 0;
		break;
	}
	$column_orderby_max		= max($column_orderby_max, $item[$orderby]);
}




$bar_width				= 100;
$bar_count_interval		= 100;
$record_date			= array();
$record_count			= array();
$record_max				= 0;


$bar_max_count			= max(ceil($column_orderby_max / $bar_count_interval) * $bar_count_interval, 1);


echo "<table width=100% cellpadding=0 cellspacing=10 border=0 style='border:solid 1px #aaaaaa'>";
echo "	<tr>";


// Print Column Names
foreach ($columns as $field => $column) {
	
	// Parse column setting
	$infos					= explode(",", $column);
	$column					= array();
	foreach ($infos as $info) {
		list($name, $value)	= explode(":", trim($info));
		$column[$name]		= trim($value);
	}
	$columns[$field]		= $column;

	$arrow = "";
	if ($orderby == $field) {
		
		if ($ordertype == 'asc') {
			$order = "desc";
			$arrow = " &uarr;";
		} else {
			$order = "asc";
			$arrow = " &darr;";
		}
	} else {
		$order = "desc";
	}

	if (!startWith($field, '#'))
		$label	= "<a href='" . getURL() . "&orderby=$field%40$order'>{$column['name']}</a>";

		
	$class				= (isset($column['class'])) ? "class=" . $column['class'] : "";
    
	echo "<td $class><b>$label$arrow</b><br></td>\n";
}


if ($column_orderby_max != 0) {
	echo "		<td width=10></td>";
	echo "		<td></td>";
}
echo "	</tr>";




if (empty($items)) {
	
	echo "<tr height=100><td colspan=20 align=center>抱歉，沒有記錄。</td></tr>";

}



$sum			= array();

foreach ($items as $item) {
	
	array2obj($item);
	
	echo "<tr>";

	foreach ($columns as $field => $column) {
		$value			= $item->$field;
		$class			= "";

		if ($column['class'] == "number") {
			$sum[$field]	+= $value;

			$value		= number_format($value, 0);
			$class		= "class=number";

		}
		
		$width			= (isset($column['width'])) ? "width={$column['width']}" : "";
		echo "<td $class $width>$value</td>";
	}

	if ($column_orderby_max != 0) {
		$width				= round($bar_width * $item->$orderby / $bar_max_count);
		echo "<td width=10></td>";
		echo "<td width=$bar_width_max><hr size=3 width=$width% align=left color=#ff7700></td>";
	}
	echo "</tr>\r\n";

}


if (!empty($columns_end)) {
	
	echo "<tr><td colspan=20><hr size=1></td></tr>";
	echo "<tr>";
	
	foreach ($columns_end as $field => $column) {
		
		if ($column == '[sum]')
			echo "<td class=number>" . number_format($sum[$field], 0) . "</td>";
		elseif ($column == '[average]')
			echo "<td class=number>" . number_format($sum[$field]/count($items), 0) . "</td>";
		else
			echo "<td>$column</td>";
		
	}
	
	echo "</tr>\r\n";
}


echo "</table>";



?>