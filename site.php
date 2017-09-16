<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='site.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


$lang = lang('銷售地點');
echo <<<EOS
<h3 class="pull-left">$lang</h3>

EOS;




if (isset($_GET['delete'])) {

	$id		= sql_secure($_GET['delete']);
	sql_query("update site set status='deleted' where id='$id'");
	gotoURL(-1);
	exit;

}


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;

$print_url			= str_replace(".php?", "_print.php?", getURL());

echo "
		<div class='pull-right'>
			<input class='btn btn-default' type=button value='新增銷售地點 (N)' onclick='location.href=\"site_add.php\";'>
			<input class='btn btn-default' type=button value='列印 (P)' onclick='print();'>
			<input class='btn btn-default' type=button value='".lang('導出表格')."(E)' onclick='exportform.submit()' />
		</div>
		<br /><br /><br />
";


$columns		= array(
			"#1"					=> "",
			"name"					=> "名稱",
			"date_start"			=> "開始日期",
			"date_end"				=> "結束日期",
			"manager_staff_id"		=> "總負責人",
			"staff"					=> "推廣員",
			"item_type_count"		=> "產品種類",
			"#item_count"			=> "產品總數",
			"remark"				=> "備注",
			"#2"					=> "編輯",
			"#3"					=> "刪除"
						);

$columns_sql	= array(
			"brand"						=> "select description from class_brand where id=item.brand",
			"manager_staff_id"			=> "select name from staff where id=invoice.staff_id",
			"item_count"				=> "select count(distinct b.name_series) from (select item_id from inventory where site_id=site.id) a join item b on a.item_id=b.id"
						);
$columns_class	= array(
			"status"			=> "number",
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
unset($search_field['產品總數']);

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





$items				= sql_getTable("select * from site where $filter order by $orderby $ordertype limit $offset, $record_per_page");


if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有產品。</td></tr>\r\n";

}



$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->staff				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), nl2br($item->staff));

	$item->manager_staff_id		= sql_getValue("select name from staff where id='$item->staff_id'");
	$item_type_count			= sql_getValue("select count(distinct b.name_series) from (select item_id from inventory where site_id='$item->id') a join item b on a.item_id=b.id");
	$item_count					= sql_getValue("select count(*) from inventory where site_id='$item->id'");


	$from_query					= urlencode($_SERVER['QUERY_STRING']);

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"site_edit.php?id=$item->id&from_query=$from_query\";'";
		$edit_link				= "<a href='site_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除記錄 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}

	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->id</td>";
	echo "<td>$item->name</td>";
	echo "<td>$item->date_start</td>";
	echo "<td>$item->date_end</td>";
	echo "<td>$item->manager_staff_id</td>";
	echo "<td>$item->staff</td>";
	echo "<td>$item_type_count</td>";
	echo "<td>$item_count</td>";
	echo "<td>$item->remark</td>";
	echo "<td class=noprint width=60>$edit_link</td>";
	echo "<td class=noprint width=60>$delete_link</td>";
	echo "</tr>";

}
echo "<tfoot>";
echo "<tr>";
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
            <input type=hidden name=page value='site' />

		</td>
      </form>
	</tr>
</table>
EOS;

//	Paging function
$record_sql				= "select count(*) from site where $filter";
echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";




echo <<<EOS

<div class=print_page_footer></div>

<script>

shortcut.add("Ctrl+N", function () { location.href="site_add.php"; });
shortcut.add("Ctrl+P", function () { print("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });
</script>

EOS;

include_once "footer.php";

?>
