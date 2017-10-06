<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customer.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


$lang = lang('客戶資料');
echo <<<EOS
<h3 class="pull-left">$lang</h3>


EOS;




if (isset($_GET['delete']) and isset($privilege->delete)) {

	$id		= sql_secure($_GET['delete']);
	sql_query("update customer set status='deleted' where id='$id'");
	gotoURL(-1);
	exit;

}


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;


$print_url			= str_replace(".php?", "_print.php?", getURL());

echo "
		<div class='pull-right'>
			<input class='btn btn-default' type=button value='新增客戶 (N)' onclick='location.href=\"customer_add.php\";'>
			<input class='btn btn-default' type=button value='".lang('導出表格')." (E)' onclick='exportform.submit()' />
		</div>
		<br /><br /><br />
";

$columns		= array(
			"id"						=> "客戶編號",
			"name"						=> "客戶名稱",
			"address"					=> "地址",
			"tel"						=> "電話",
			"fax"						=> "傳真",
			"sales"						=> "組別",
			"site"						=> "銷售地點",
			"#2"						=> "編輯",
			"#3"						=> "刪除"
						);


$columns_sql	= array(
			"class"				=> "select description from class_customer where id=customer.class",
			"site"				=> "select name from site where id=customer.site_id",
			"sales"				=> "select name from staff where id=customer.staff_id"
						);

$columns_class	= array(
            "#2"						=> "noprint",
            "#3"						=> "noprint"
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

    	echo "<th $class><b>$column$arrow</b><br></th>\n";
	}

echo "	</tr> ";

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

$filter				.= " and status !='deleted'";
if ($_SESSION['root'] != 1) $filter				.= " and staff_id=" . $_SESSION['staff_id'];

$items				= sql_getTable("select * from customer where $filter order by $orderby $ordertype limit $offset, $record_per_page");
$customer_classes	= sql_getArray("select id, description from class_customer");
$staff_names		= sql_getArray("select id, name from staff");
$site_names			= sql_getArray("select id, name from site");

if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=18 align=center>暫時沒有員工。</td></tr>\r\n";

}

$from_query					= urlencode($_SERVER['QUERY_STRING']);
$count						= 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->date_create			= printdate($item->date_create);
	$site_name					= $site_names[$item->site_id];
	$sales						= $staff_names[$item->staff_id];
	$customer_class				= $customer_classes[$item->class];

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"customer_edit.php?id=$item->id&from_query=$from_query\";'";
		$edit_link				= "<a href='customer_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除記錄 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}


	$address					= explode("\n", $item->address);
	$address					= trim($address[0]);
	if ($address != $item->address)
		$address					.= " ...";


	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->id</td>";
	echo "<td><input type=text readonly value='$item->name' style='width:200px; border:0px; background:transparent;'></td>";
//	echo "<td>$customer_class</td>";
	echo "<td><input type=text readonly value='$address' style='width:200px; border:0px; background:transparent;'></td>";
	echo "<td>$item->tel</td>";
	echo "<td>$item->fax</td>";
	echo "<td>$sales</td>";
	echo "<td>$site_name</td>";

	echo "<td width=60 class=noprint>$edit_link</td>";
	echo "<td width=60 class=noprint>$delete_link</td>";
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

    	echo "<th $class><b>$column$arrow</b><br></th>\n";
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
            <input type=hidden name=page value='customer' />

		</td>
      </form>
	</tr>
</table>
EOS;

//	Paging function
$record_sql				= "select count(*) from customer where $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

echo <<<EOS

<script>

shortcut.add("Ctrl+N", function () { location.href="customer_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });

</script>

EOS;



include_once "footer.php";



?>
