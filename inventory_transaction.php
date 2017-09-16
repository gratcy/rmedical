<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory_transaction.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }



if (isset($_GET['delete'])) {

	include_once "inventory_library.php";

	$id		= sql_secure($_GET['delete']);
	inventory_transaction_delete($id);
	gotoURL(-1);
	exit;

}



$lang = lang('管理記錄');
$excel = lang('導出表格');

echo <<<EOS
<h3 class='pull-left'>$lang</h3>

<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<div class='pull-right'>
            <input class='btn btn-default' type=button value='過往記錄' onclick='location.href="inventory_oldrecord.php";' />
            <input class='btn btn-default' type=button value='$excel (E)' onclick='exportform.submit()' />
</div>
<br><br><br>


EOS;

$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;


$columns		= array(
			"id"					=> "編號",
			"date"					=> "日期",
			"site"					=> "地點",
			"type"					=> "類型",
			"remark"				=> "備注",
			"date_create"			=> "建立日期",
			"#2"					=> "列印",
			"#3"					=> "編輯",
			"#4"					=> "刪除"
						);


$columns_sql	= array(
			"site"					=> "select group_concat(name separator ' ') from site where id=inventory_transaction.site_from or id=inventory_transaction.site_to",
						);
$columns_class	= array(
			"#2"				=> "noprint",
			"#3"				=> "noprint",
			"#4"				=> "noprint"
						);


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'search_word'				, 'text'		, '字眼'					, '30',
			'search_field'				, 'select'		, '欄位'					, '80',
			'date_start'				, 'text'		, '1'						, '10',
			'date_end'					, 'text'		, '2'						, '10'
			);


$search_field							= array_flip($columns);
unset($search_field['']);
unset($search_field['列印']);
unset($search_field['編輯']);
unset($search_field['刪除']);

$inputs->tag['search_word']						= "class='form-control'";
$inputs->tag['search_field']						= "class='form-control'";
$inputs->options['search_field']		= $search_field;
$inputs->value['search_field']			= reset($search_field);
$inputs->tag['date_start']						= "class='form-control'";
$inputs->tag['date_end']						= "class='form-control'";
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
		<label for='date_start' class='col-sm-1 control-label'>開始 :</label>
		<div class='col-sm-2'>
			<div class='input-group'>
				$inputs->date_start
				<div class='input-group-addon' onclick=\"show_cal(this, 'date_start');\"><i class='fa fa-calendar-o'></i></div>
			</div>
		</div>
		<label for='date_end' class='col-sm-1 control-label'>結束 :</label>
		<div class='col-sm-2'>
			<div class='input-group'>
				$inputs->date_end
				<div class='input-group-addon' onclick=\"show_cal(this, 'date_end');\"><i class='fa fa-calendar-o'></i></div>
			</div>
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

$default_order				= "id@desc";


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

$date_start			= $_GET['date_start'];
$date_end			= $_GET['date_end'];

if (empty($search_field))
	$search_word	= '';

if (isset($columns_sql[$search_field]))
	$search_field	= "(" . $columns_sql[$search_field] . ")";

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";

$filter				.= " and type!='sold'";


if ($date_start && $date_end) {

        $date_start			= strtotime($date_start);
        $date_end			= strtotime($date_end);

        if ($date_end < $date_start)
            $date_end		= $date_start;

        $dates				= array();
        while ($date_start <= $date_end) {
            $dates[]		= date('Y-m-d', $date_start);
            $date_start		+= 86400;		// 60 x 60 x 24
        }

        $date_end			= end($dates);
        $date_start			= reset($dates);
        $filter				.= " and (date >= '$date_start' and date <= '$date_end')";

}



$items				= sql_getTable("select * from inventory_transaction where $filter order by $orderby $ordertype limit $offset, $record_per_page");
$site_names			= sql_getArray("select id, name from site");

if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有記錄<b></b></td></tr>\r\n";

}

$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$site_from					= $site_names[$item->site_from];
	$site_to					= $site_names[$item->site_to];

	$site						= $site_from . (empty($site_to) ? "" : " &nbsp;&nbsp; 至 &nbsp;&nbsp; $site_to");

	$item->date					= substr($item->date, 0, 10);
//	$item->date_create			= substr($item->date_create, 0, 10);

	$from_query					= urlencode($_SERVER['QUERY_STRING']);

	if (!empty($privilege->print))	{
		$print_link				= "<a href='inventory_transaction_print.php?id=$item->id&from_query=$from_query' target=_blank><i class='fa fa-print'></i></a>";
	} else {
		$print_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除記錄 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}

	if (!empty($privilege->edit))	{
		if ($item->type == '調整') {
			$doubleclick			= "ondblclick='location.href=\"inventory_adjust_edit.php?id=$item->id&from_query=$from_query\";'";
			$edit_link				= "<a href='inventory_adjust_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
			$delete_link			= "";
		} else if ($item->type == '返貨') {
			$doubleclick			= "ondblclick='location.href=\"inventory_supply_edit.php?id=$item->id&from_query=$from_query\";'";
			$edit_link				= "<a href='inventory_supply_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
		} else {
			$doubleclick			= "ondblclick='location.href=\"inventory_transaction_edit.php?id=$item->id&from_query=$from_query\";'";
			$edit_link				= "<a href='inventory_transaction_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
		}
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->id</td>";
	echo "<td>$item->date</td>";
	echo "<td>$site</td>";
	echo "<td>$item->type</td>";
	echo "<td>$item->remark</td>";
	echo "<td>$item->date_create</td>";
	echo "<td class=noprint width=60>$print_link</td>";
	echo "<td class=noprint width=60>$edit_link</td>";
	echo "<td class=noprint width=60>$delete_link</td>";
	echo "</tr>";

}



echo "</form>";
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
            <input type=hidden name=page value='inventory' />

		</td>
      </form>
	</tr>
</table>
EOS;






//	Paging function
$record_sql				= "select count(*) from inventory_transaction where $filter";
echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

echo <<<EOS

<script>

shortcut.add("Ctrl+N", function () { location.href="item_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });
</script>

EOS;

include "bin/class_csi.php";


include_once "footer.php";

?>
