<?php



echo "<link href='style_report.css' rel='stylesheet' type='text/css' media='print'>";

echo "</td></tr></table>";
echo "<table class='table table-borderless'><tr><td style='padding : 0px 20px;' class=report_list_outer>";

echo "<div id='paging_header' class=noprint></div>";




///////////////////////////////////////////////////////////////////
//	Statistics Bar
///////////////////////////////////////////////////////////////////


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




///////////////////////////////////////////////////////////////////
//	Table columns
///////////////////////////////////////////////////////////////////

ob_start();


echo "<div class='table-responsive'>";
echo "<table class='table table-borderless report_list'>";

echo "	<tr class='hidden'><td colspan=20 class=print_page_header></td></tr>";


echo "	<tr height=30 class=nodisplay><td colspan=20 align=center valign=top><div class=report_title>$report_title</div></td></tr>";

echo "	<tr height=40 class=nodisplay><td colspan=20 valign=bottom>$report_description</td></tr>";

echo "	<tr height=30 class=report_column_title>";


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
	echo "		<td width=10>&nbsp;</td>";
	echo "		<td>&nbsp;</td>";
}
echo "	</tr>\r\n";

$print_header			= ob_get_contents();
ob_end_clean();




ob_start();
echo "	<tr class='hidden' valign='bottom'>";
echo "		<td colspan=20 class=print_page_footer></td>";
echo "	</tr>";
echo "</table>";
echo "</divZ>";
$print_footer			= ob_get_contents();
ob_end_clean();




///////////////////////////////////////////////////////////////////
//	Output Table
///////////////////////////////////////////////////////////////////

if (empty($items)) {

	echo "<div class='page'>";
	echo $print_header;
	echo "<tr height=100><td colspan=20 align=center>抱歉，沒有記錄。</td></tr>";
	echo $print_footer;
	echo "</div>";
	return;

}

$count_page_item	= 1;
$record_per_page	= 30;
$record_count		= count($items);


$items				= array_split($items, 0, $record_per_page);


$current_page		= 1;
$page_total			= count($items);
$sum				= array();
$new_next_item		= true;


foreach ($items as $page) {


	if ($current_page == 1)				echo "<div id=page$current_page>\r\n";
	else								echo "<div id=page$current_page class='hiddenpage'>\r\n";

	if ($current_page != $page_total)	echo "<div class='page pagebreakafter'>\r\n";
	else								echo "<div class='page'>";


	echo $print_header;

	$row			= 0;
$qty = 0;
	foreach ($page as $item) {

		array2obj($item);
$qty += $item -> quantity;
		$bgcolor			= ($row % 2 == 0) ? '#ffffff' : '#eeeeee';

		echo "<tr height=20 bgcolor=$bgcolor>";

		$roll_sum			= (!$new_next_item && $item->name_subitem == "");

		if ($roll_sum) {
			$style			= "style='border-bottom:solid 1px #000000; color:#ff3300; font-weight:bold;'";
			$new_next_item	= true;
		} else {
			$style			= "";
			$new_next_item	= false;
		}

		foreach ($columns as $field => $column) {
			$value			= $item->$field;
			$class			= "";

			if ($column['class'] == "float") {
				$sum[$field]	+= $value;

				if (empty($value) || is_numeric($value))
					$value		= number_format($value, 2);
				$class		= "class=number";

			}

			if ($column['class'] == "number") {
				$sum[$field]	+= $value;

				if (empty($value) || is_numeric($value))
					$value		= number_format($value, 0);
				$class		= "class=number";

			}

			if ($value == "")	$value	= "&nbsp;";

			$width			= (isset($column['width'])) ? "width={$column['width']}" : "";
			echo "<td $class $width $style><div class=single_row_td>$value</div></td>";
		}

		if ($column_orderby_max != 0) {
			$width				= round($bar_width * $item->$orderby / $bar_max_count);
			echo "<td $style width=10>&nbsp;</td>";
			echo "<td $style width=$bar_width_max ><hr size=3 width=$width% align=left color=#ff7700></td>";
		}
		echo "</tr>\r\n";

		$row++;

	}

	for ($i = count($page); $i < $record_per_page; $i++) {
		echo "<tr height=20 class=nodisplay><td></td></tr>";
	}

	if (!empty($columns_end)) {
		$columns_end -> quantity = $qty / 2;
		echo "<tr height=30 class=report_column_footer>";
		foreach ($columns as $field => $column) {
			$value			= $columns_end->$field;
			$class			= "";

			if ($column['class'] == "float") {
				$sum[$field]	+= $value;

				if (empty($value) || is_numeric($value))
					$value		= number_format($value, 2);
				$class		= "class=number";

			}

			if ($column['class'] == "number") {
				$sum[$field]	+= $value;

				if (empty($value) || is_numeric($value))
					$value		= number_format($value, 0);
				$class		= "class=number";

			}

			if ($value == '')	$value = '&nbsp;';

			$width			= (isset($column['width'])) ? "width={$column['width']}" : "";
			echo "<td $class $width><b>$value</b></td>";
		}

		if ($column_orderby_max != 0) {
			echo "		<td width=10>&nbsp;</td>";
			echo "		<td>&nbsp;</td>";
		}

		echo "</tr>\r\n";
	}

	$print_date			= date("Y-m-d h:m:s");

	echo "<tr height=15 class=nodisplay><td></td></tr>";
	echo "<tr height=18 class=nodisplay>";
	echo "	<td colspan=20 align=right> $current_page / $page_total   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 列印日期 $print_date </td>";
	echo "</tr>";


	echo $print_footer;
	echo "</div>\r\n";
	echo "</div>\r\n";
	echo "</div>\r\n";

	$current_page++;

}


$record_sql		= "select $record_count";
echo "<div id='paging_footer' class=noprint>";
include "report_paging.php";
echo "</div>";

echo "<div class=noprint><br><br>";

if (isset($log))		$log->show(3);

echo "<br>";
echo "</div>";
?>

<script type="text/javascript">
$(function(){
	$('.table_form select, .table_filter select, select.select2').select2({
  placeholder: "Select a state",
  allowClear: true
});
	$('#logo').click(function(){
		window.location.href='/';
	});
	$('#daterange').daterangepicker({
		dateFormat: 'YYYY-MM-DD',
		locale: {
		  format: 'YYYY-MM-DD'
		},
	});
});
</script>
<?php
exit;


?>
