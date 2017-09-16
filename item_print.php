<?php

include "header_print.php";



$columns		= array(
			"#1"				=> "",
			"id"				=> "編號",
			"brand"				=> "品牌",
			"class"				=> "種類",
			"name"				=> "產品名稱",
			"price"				=> "價格",
			"date_modify"		=> "更新日期",
//			"status"			=> "狀態",
			"#2"				=> "編輯",
			"#3"				=> "刪除"
						);

$columns_width	= array(
			"#1"				=> 50,
			"id"				=> 110,
			"brand"				=> 100,
			"class"				=> 150,
			"name"				=> "free",
			"price"				=> 80,
			"date_modify"		=> 0,
//			"status"			=> 0,
			"#2"				=> 0,
			"#3"				=> 0
						);

$columns_sql	= array(
			"brand"				=> "select description from class_brand where id=item.brand",
			"class"				=> "select description from class_item where id=item.class"
						);
$columns_class	= array(
			"status"			=> "number",
			"date_modify"		=> "noprint",
			"#2"				=> "noprint",
			"#3"				=> "noprint"
			
						);




$default_order				= "id@asc";


if (empty($_SESSION['sort_order'][getURL('file')]))
	$_SESSION['sort_order'][getURL('file')]		= $default_order;

if (isset($_GET['orderby']))
	$_SESSION['sort_order'][getURL('file')]		= sql_secure(popURL('orderby'));


list($orderby, $ordertype)						= explode("@", $_SESSION['sort_order'][getURL('file')]);





if (isset($columns_sql[$orderby]))
	$orderby		= "(" . $columns_sql[$orderby] . ")";


$search_word		= sql_secure($_GET['search_word']);
$search_field		= sql_secure($_GET['search_field']);

if (empty($search_field))
	$search_word	= '';

if (isset($columns_sql[$search_field]))
	$search_field	= "(" . $columns_sql[$search_field] . ")";

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";

$filter				.= " and status!='deleted'";








//	Define Column Title
$str_column_title			= "<thead><tr><td colspan=20 class=print_header></td></tr><tr class=simple_list_title>";
$str_column_title			= "";

$str_column_title			.= "<colgroup>";
foreach ($columns as $field => $column) {
	if ($columns_width[$field] === 0)	continue;
	$str_column_title			.= "<col width='{$columns_width[$field]}' valign=top align=left />";
}
$str_column_title			.= "</colgroup>\r\n";



$str_column_title			.= "<tr class=simple_list_title>";

foreach ($columns as $field => $column) {

	if ($columns_width[$field] === 0)	continue;
	
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
		$order = "asc";
	}

	if (!startWith($field, '#'))
		$column	= "<a href='" . getURL() . "&orderby=$field%40$order' style='color:#ffffff'>$column</a>";
    
	$str_column_title			.= "<th $class><b>$column$arrow</b><br></th>\n";
}
$str_column_title			.= "</tr>\r\n";

$str_column_title			.= "</thead>\r\n";





//	Paging 
$row_per_page		= 34;
$count_record		= sql_getValue("select count(*) from item where $filter");
$total_page			= ceil($count_record / $row_per_page);

$total_page			= min($total_page, 20);



for ($page = 1; $page <= $total_page; $page++) {
	
	$offset				= ($page - 1) * $row_per_page;
	$items				= sql_getTable("select * from item where $filter order by $orderby $ordertype limit $offset, $row_per_page");
	$count				= 0;

	
	$page_break			= ($page != $total_page) ? "style='page-break-after:always'" : "";

	echo "<table width=100% height=99% cellpadding=0 cellspacing=0 border=0 $page_break>";
	echo "<tr><td valign=top>";
	
	echo "	<div class=print_page_header></div>";
	echo "	<table width=100% cellpadding=3 cellspacing=0 border=0 class='simple_list'>";

	echo $str_column_title;
	
	foreach ($items as $item) {
		
		
		array2obj($item);
		
		$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';
		
		$item->cost_currency		= $currency_sign[$item->cost_currency];
		
		$item->date_modify			= printdate($item->date_modify);
		
		$photo						= array_shift(explode("<br>", $item->photo));
		$photo						= "<a href='photo/640_$photo' target=_blank>" . displayImage("photo/80_$photo", "", "", "", "", "style='border:solid 1px #aaaaaa'") . "</a>";
		
		
		$item->brand				= sql_getValue("select description from class_brand where id='$item->brand'");
		$item->class				= sql_getValue("select description from class_item where id='$item->class'");
		
		
		$item_link					= "#";
		
	
		echo "<tr bgcolor=$bgcolor>";
		echo "<td>$item->id</td>";
		echo "<td>$item->item_id</td>";
		echo "<td>$item->brand</td>";
		echo "<td>$item->class</td>";
		echo "<td><input type=text value='$item->name' size=35></td>";
		echo "<td class=number>\$$item->price</td>";
		
		echo "</tr>\r\n";


	}
	
	echo "	</table>";
	
	echo "</td></tr>";
	echo "<tr><td valign=bottom class=print_page_footer>";
	echo "	頁數 : $page / $total_page";
	echo "</td></tr>";
	echo "</table>";

}


echo "</html>";



?>