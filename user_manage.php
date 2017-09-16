<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='user_manage.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

if (isset($_GET['delete'])) {

	$id		= $_GET['delete'] * 1;
	sql_query("delete from service_user where id='$id'");
	sql_query("delete from service_user_privilege where user_id='$id'");
	gotoURL(-1);
	exit;

}
$lang = lang('使用者管理');

echo "<h3 class='pull-left'>$lang</h3>";


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;



echo "
			<a href='user_add.php' class='pull-right'><i class='fa fa-plus'></i> 新增使用者</a><br><br><br>
";


$columns		= array(
			"#1"				=> "",
			"user"				=> "登入名稱",
			"name"				=> "使用者名稱",
			"staff_id"			=> "員工名稱",
			"privilege"			=> "權限",
			"#2"				=> "編輯",
			"#3"				=> "刪除"
						);
$columns_class	= array(
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

$inputs->options['search_field']		= $search_field;
$inputs->value['search_field']			= reset($search_field);
if (!empty($_GET))
	$inputs->value						= $_GET;




echo "
<div class='table-responsive'>
<table class='table table-borderless simple_list'>
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

    	echo "<td $class><b>$column$arrow</b><br></td>\n";
	}

echo "	</tr> ";







$search_word		= sql_secure($_GET['search_word']);
$search_field		= sql_secure($_GET['search_field']);

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";

$filter				.= " and status!='deleted'";

$items				= sql_getTable("select * from service_user where $filter order by $orderby $ordertype limit $offset, $record_per_page");


if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有用戶。</td></tr>\r\n";

}


$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';


	$item->date_modify			= printdate($item->date_modify);
	$item->staff_id				= sql_getValue("select name from staff where id='$item->staff_id'");
	if ($_SESSION['lang'] == 'hk') {
		$user_privilege					= sql_getValue("select group_concat(distinct `group` separator ', ') from service_user_privilege where user_id='$item->id' order by `order` asc");
	}
	else {
		$user_privilege					= sql_getValue("select group_concat(distinct `group_en` separator ', ') from service_user_privilege where user_id='$item->id' order by `order` asc");
	}

	echo "<tr bgcolor=$bgcolor>";
	echo "<td>$item->id</td>";
	echo "<td>$item->user</td>";
	echo "<td>$item->name</td>";
	echo "<td width=300>$item->staff_id</td>";
	echo "<td>$user_privilege</td>";

	if (!empty($privilege->edit))
		echo "<td width=60>
						<a href='user_edit.php?id=$item->id'><i class='fa fa-pencil'></i></a>
						</td>";
	else echo "<td width=60></td>";
	if (!empty($privilege->delete))
		echo "<td width=60>
						<a href='javascript:if (confirm(\"確定要刪除使用者 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>
						</td>";
	else echo "<td width=60></td>";
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

    	echo "<td $class><b>$column$arrow</b><br></td>\n";
	}
echo "</tr>";
echo "</tfoot>";
echo "</table>";
echo "</div>";


//	Paging function
$record_sql				= "select count(*) from service_user where $filter";


$record_count			= sql_getValue($record_sql);
$page_total				= ceil($record_count / $record_per_page);

$page_start				= max($topage - 5, 1);
$page_end				= min($topage + 5, $page_total);

if ($page_total > 1) {
	echo "<table class='table table-borderless'><tr><td>總共 $record_count 記錄</td><td align=right>";

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
