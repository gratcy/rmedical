<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='item.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


$lang = lang('產品資料');
echo <<<EOS
<h3 class="pull-left">$lang</h3>

EOS;




if (isset($_GET['delete'])) {

	$id		= sql_secure($_GET['delete']);
	sql_query("update item set status='deleted' where id='$id'");
	gotoURL(-1);
	exit;

}


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;

$print_url			= str_replace(".php?", "_print.php?", getURL());

echo "
			<div class='pull-right'>
				<input class='btn btn-default' type=button value='新增產品 (N)' onclick='location.href=\"item_add.php\";'>
				<input class='btn btn-default' type=button value='列印 (P)' onclick='window.open(\"$print_url\");'>
				<input class='btn btn-default' type=button value='".lang('導出表格')." (E)' onclick='exportform.submit()' />
			</div>
			<br /><br /><br />
";

$columns		= array(
			"#1"				=> "",
			"item_id"			=> "編號",
			"photo"				=> "圖片",
			"brand"				=> "品牌",
			"class"				=> "種類",
			"name"				=> "產品名稱",
			"name_short"		=> "產品簡稱",
			"name_series"		=> "產品系列",
			"price"				=> "價格",
			"warranty"			=> "備註",
//			"status"			=> "狀態",
			"#2"				=> "編輯",
			"#3"				=> "刪除"
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

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'search_word'				, 'text'		, '字眼'					, '30',
			'search_field'				, 'select'		, '欄位'					, '80'
			);


$search_field							= array_flip($columns);
unset($search_field['']);
unset($search_field['編輯']);
unset($search_field['刪除']);

$inputs->tag['search_word']						= "class='form-control'";
$inputs->tag['search_field']						= "class='form-control'";

$inputs->options['search_field']		= $search_field;
$inputs->value['search_field']			= reset($search_field);
if (!empty($_GET))
	$inputs->value						= $_GET;


echo "
<form class='form-horizontal' id=search_box action='' method='GET'>
	<div class='form-group'>
		<label for='search_word' class='col-sm-1 control-label'><i class='fa fa-search'></i> &nbsp; 搜尋 :</label>
		<div class='col-sm-2'>
	 		$inputs->search_word
	 	</div>
		<div class='col-sm-2'>
			$inputs->search_field
		</div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=submit value='確定'>
    </div>
  </div>
</form>
";


echo "<div id='paging_header'></div>";

echo "
<div class='table-responsive'>
<table class='table table-borderless simple_list' id='main_list'>
<thead>
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
			$order = "asc";
		}

		$class			= (isset($columns_class[$field])) ? "class=" . $columns_class[$field] : "";

		if (!startWith($field, '#'))
			$column	= "<a href='" . getURL() . "&orderby=$field%40$order'>$column</a>";

    	echo "<th $class>$column$arrow</th>\n";
	}

echo "	</tr> </thead>";




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



$items				= sql_getTable("select * from item where $filter order by $orderby $ordertype limit $offset, $record_per_page");
$brand_classes		= sql_getArray("select id, description from class_brand");
$item_classes		= sql_getArray("select id, description from class_item");

if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有產品。</td></tr>\r\n";

}



$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->cost_currency		= $currency_sign[$item->cost_currency];

	$item->date_modify			= printdate($item->date_modify);

	$item->brand				= $brand_classes[$item->brand];
	$item->class				= $item_classes[$item->class];

	$from_query					= urlencode($_SERVER['QUERY_STRING']);

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"item_edit.php?id=$item->id&from_query=$from_query\";'";
		$edit_link				= "<a href='item_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除產品 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}

	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->id</td>";
	echo "<td>$item->item_id</td>";
	if($item->phtot!='')
		echo "<td><img src=$item->phtot></td>";
	else
		echo "<td>&nbsp;</td>";
	echo "<td>$item->brand</td>";
	echo "<td>$item->class</td>";
	echo "<td>$item->name</td>";
	echo "<td>$item->name_short</td>";
	echo "<td>$item->name_series</td>";
	echo "<td class=number>\$$item->price</td>";
	echo "<td class=number>".__get_warranty($item->warranty)."</td>";
	echo "<td class=noprint width=60>$edit_link</td>";
	echo "<td class=noprint width=60>$delete_link</td>";
	echo "</tr>";

}
echo "<tfoot>";
echo "<tr>";
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
			$order = "asc";
		}

		$class			= (isset($columns_class[$field])) ? "class=" . $columns_class[$field] : "";

		if (!startWith($field, '#'))
			$column	= "<a href='" . getURL() . "&orderby=$field%40$order'>$column</a>";

    	echo "<th $class>$column$arrow</th>\n";
	}
echo "</tr>";
echo "</tfoot>";
echo "</table>";
echo "</div>";

echo <<<EOS
<table class='table table-borderless'>
	<tr>
    <form id=exportform method=post action='export_xls.php' style='margin:0px;'>
		<td align=right>
            <input type=hidden name=filter_field value='$search_field' />
            <input type=hidden name=filter_word value='$search_word' />
            <input type=hidden name=date_start value='$date_start' />
            <input type=hidden name=date_end value='$date_end' />
            <input type=hidden name=page value='item' />

		</td>
      </form>
	</tr>
</table>
EOS;

//	Paging function
$record_sql				= "select count(*) from item where $filter";
echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";




echo <<<EOS

<div class=print_page_footer></div>

<script>

shortcut.add("Ctrl+N", function () { location.href="item_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });


</script>

EOS;


include_once "footer.php";

?>
