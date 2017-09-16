<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='manager_item.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

//include "privilege_special.php";

echo <<<EOS
<h3>產品價格</h3>
<br>
EOS;


if ($_POST['action'] == 'edit') {

	$cms_table			= "item";
	$cms_key			= "id";
	include "cms_process.php";

	echo "<p><font color=blue>已成功更新資料</font></p>";

}


if (isset($_GET['delete'])) {

	$id		= sql_secure($_GET['delete']);
	sql_query("update item set status='deleted' where id='$id'");
	gotoURL(-1);
	exit;

}


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;






$columns		= array(
			"#1"				=> "",
			"id"				=> "編號",
			"brand"				=> "品牌",
			"class"				=> "種類",
			"name"				=> "產品名稱",
			"cost"				=> "採購價",
			"price"				=> "售價"
						);

$columns_class	= array(
			"cost"				=> "number",
			"price"				=> "number"
						);

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'month'					, 'select'			, '3'		, '200'
			);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;
$inputs->tag['month']						= "class='form-control'";

if (!empty($_GET))
	$inputs->value						= $_GET;


echo "
<form class='form-horizontal' id=search_box action='' method='GET'>
	<div class='form-group'>
		<label for='month' class='col-sm-2 control-label'><i class='fa fa-search'></i> &nbsp; 搜尋銷售月份 :</label>
		<div class='col-sm-2'>
	 		$inputs->month
	 	</div>
		<div class='col-sm-2'>
    	<input class='btn btn-default' type=submit value='確定'>
    </div>
  </div>
</form>
";





echo "
<div class='table-responsive'>
<table class='table table-borderless simple_list'>
<form action='' method=post>
<input type=hidden name=action value=edit>
	<tr>";

$default_order				= "id@asc";


if (empty($_SESSION['sort_order'][getURL('file')]))
	$_SESSION['sort_order'][getURL('file')]		= $default_order;

if (isset($_GET['orderby']))
	$_SESSION['sort_order'][getURL('file')]		= sql_secure(popURL('orderby'));


list($orderby, $ordertype)						= explode("@", $_SESSION['sort_order'][getURL('file')]);


	// Print Column Names
	foreach ($columns as $field => $column) {
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
			$order		= "asc";
		}

		$class			= (isset($columns_class[$field])) ? "class=" . $columns_class[$field] : "";

		if (!startWith($field, '#'))
			$column	= "<a href='" . getURL() . "&orderby=$field%40$order'>$column</a>";

    	echo "<td $class><b>$column$arrow</b><br></td>\n";
	}

echo "	</tr> ";







$search_word		= sql_secure($_GET['search_word']);
$search_field		= sql_secure($_GET['search_field']);

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";

$filter				.= " and status!='deleted'";



if (!empty($_GET['month'])) {
    $date_months		= str_replace("月", "", $_GET['month']);
    $date_month         = explode("年", $date_months);
    $year 				= trim($date_month[0]);
    $month				= trim($date_month[1]);
    $date_start			= "$year-$month-01";
    $date_end			= "$year-$month-" . date("t", mktime(0, 0, 0, (integer) $month, 1, (integer) $year));
    $item_ids			= sql_getArray("select distinct item_id from invoice_detail where date_order >= '$date_start' and date_order <= '$date_end'");
    $item_ids			= implode(",", $item_ids);
    if (!empty($item_ids))
    	$filter				.= " and id in ($item_ids) ";
}


$filter				.= " and status!='deleted'";

$items				= sql_getTable("select * from item where $filter order by $orderby $ordertype limit $offset, $record_per_page");


if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有產品。</td></tr>\r\n";

}



$count = 0;
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
	echo "<td>$item->name</td>";
	echo "<td class=number><input type=text name='cms::$item->id::cost' value='$item->cost' size=5 class=number></td>";
	echo "<td class=number><input type=text name='cms::$item->id::price' value='$item->price' size=5 class=number></td>";
	echo "</tr>";
}

echo "<tr bgcolor=#aaaaaa><td colspan=15></td></tr>\r\n";
echo "<tr bgcolor=#ffffff><td colspan=15 align=center><input class='btn btn-default' type=submit value='確定' ></td></tr>";
echo "</form>";
echo "</table>";
echo "</div>";


//	Paging function
$record_sql				= "select count(*) from item where $filter";


$record_count			= sql_getValue($record_sql);
$page_total				= ceil($record_count / $record_per_page);

$page_start				= max($topage - 5, 1);
$page_end				= min($topage + 5, $page_total);

if ($page_total > 1) {
	echo "<table width=100% cellpadding=0 cellspacing=0 border=0><tr><td>總共 $record_count 記錄</td><td align=right>";

	$temp				= $topage;
	popURL('topage');
	$topage				= $temp;

	$url							= urldecode(getURL());

	if ($page_start != 1)			echo "<a class='page_number' href='$url&topage=1'>&lt;&lt;</a>";
	if ($topage != 1)				echo "<a class='page_number' href='$url&topage=" . ($topage-1) . "'>&lt;</a>";
	if ($page_start != 1)			echo "<a class='page_number' href='$url&topage=1'>...</a>";

	for ($i=$page_start; $i<=$page_end; $i++) {
		if ($i == $topage)
			echo "<a class='page_number' href='#'><b><font color=black>$i</font></b></a>";
		else
			echo "<a class='page_number' href='$url&topage=$i'>$i</a>";
	}

	if ($page_end != $page_total)	echo "<a class='page_number' href='$url&topage=$page_end'>...</a>";
	if ($topage != $page_total)		echo "<a class='page_number' href='$url&topage=" . ($topage+1) . "'>&gt;</a>";
	if ($page_end != $page_total)	echo "<a class='page_number' href='$url&topage=$page_end'>&gt;&gt;</a>";

	echo "</td></tr></table>";
}

include_once "footer.php";

?>
