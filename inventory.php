<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


$lang = lang('倉存貨品');
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
  <input class='btn btn-default' type=button value='過往記錄' onclick='location.href="inventory_oldrecord.php";'>
  <input class='btn btn-default' type=button value='$excel (E)' onclick='exportform.submit()' />
</div>
<br><br><br>


EOS;

$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;


$date_start_sold	= date("Y-m-d", time() - 3600 * 24 * 3);


$columns		= array(
			"id"					=> "#",
			"name"					=> "名稱",
			"date_start"			=> "開始日期",
			"manager_staff_id"		=> "總負責人",
			"item"					=> "銷售產品",
			"quantity_3day_sold"	=> "三天內售出",
			"need_quantity"			=> "需補貨產品",
			"remark"				=> "備注",
            "#1"					=> "導出",
            "#2"					=> "列印",
			"#3"					=> "存貨",
			"#4"					=> "出貨",
			"#5"					=> "調整",
            "#6"					=> "記錄"
						);


$columns_sql	= array(
			"manager_staff_id"	=> "select name from staff where staff.id=site.manager_staff_id",
			"item"				=> "select count(*) from inventory where inventory.site_id=site.id"
						);
$columns_class	= array(
			"status"			=> "number",
			"#1"				=> "noprint",
			"#2"				=> "noprint",
            "#3"				=> "noprint",
			"#4"				=> "noprint",
            "#5"				=> "noprint",
			"#6"				=> "noprint"
						);


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'search_word'				, 'text'		, '字眼'					, '30',
			'search_field'				, 'select'		, '欄位'					, '80',
			'date_start'				, 'text'		, '1'					, '10',
			'date_end'					, 'text'		, '2'					, '10'
			);


$search_field							= array_flip($columns);
unset($search_field['']);
unset($search_field['查看']);
unset($search_field['出貨']);
unset($search_field['調整']);
unset($search_field['導出']);
unset($search_field['列印']);
unset($search_field['記錄']);

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

$date_start			= $_GET['date_start'];
$date_end			= $_GET['date_end'];

if (empty($search_field))
	$search_word	= '';

if (isset($columns_sql[$search_field]))
	$search_field	= "(" . $columns_sql[$search_field] . ")";

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";

$filter				.= " and status!='deleted'";


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
        $filter				.= " and (date_start >= '$date_start' and date_end <= '$date_end')";

}



$items				= sql_getTable("select * from site where $filter order by $orderby $ordertype limit $offset, $record_per_page");


if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有記錄。</td></tr>\r\n";

}

$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->staff				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), nl2br($item->staff));

	$item->manager_staff_id		= sql_getValue("select name from staff where id='$item->manager_staff_id'");

	$item_count					= sql_getValue("select count(*) from inventory where site_id='$item->id'");

	$from_query					= urlencode($_SERVER['QUERY_STRING']);

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"inventory_edit.php?id=$item->id&from_query=$from_query\";'";
		$view_link				= "<a href='inventory_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-archive'></i></a>";
		$transaction_link		= "<a href='inventory_transaction_add.php?site_from=$item->id'><i class='fa fa-refresh'></i></a>";
		$adj_link				= "<a href='inventory_adjust_add.php?site_id=$item->id'><i class='fa fa-pencil-square-o'></i></a>";
        $rec_link				= "<a href='inventory_record.php?site_id=$item->id'><i class='fa fa-list'></i></a>";
	} else {
		$doubleclick			= "";
		$view_link				= "";
		$transaction_link		= "";
		$adj_link				= "";
        $rec_link				= "";
	}


    if (!empty($privilege->print))	{
		$export_link				= "<a href='inventory_site_export.php?id=$item->id' target=_blank><i class='fa fa-download'></i></a>";
	} else {
		$export_link				= "";
	}

    if (!empty($privilege->print))	{
		$print_link				= "<a href='inventory_print.php?id=$item->id&from_query=$from_query' target=_blank><i class='fa fa-print'></i></a>";
	} else {
		$print_link				= "";
	}

	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->id</td>";
	echo "<td>$item->name</td>";
	echo "<td>$item->date_start</td>";
	echo "<td>$item->manager_staff_id</td>";
	echo "<td>$item_count</td>";
	echo "<td>$item->quantity_3day_sold</td>";
	echo "<td>$item->quantity_need</td>";
	echo "<td>$item->remark</td>";
    echo "<td class=noprint width=40>$export_link</td>";
    echo "<td class=noprint width=40>$print_link</td>";
	echo "<td class=noprint width=40>$view_link</td>";
	echo "<td class=noprint width=40>$transaction_link</td>";
	echo "<td class=noprint width=40>$adj_link</td>";
    echo "<td class=noprint width=40>$rec_link</td>";
	echo "</tr>";

}



echo "</form>";
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
            <input type=hidden name=page value='inventory' />

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

<script>

shortcut.add("Ctrl+N", function () { location.href="item_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });
</script>

EOS;

include "bin/class_csi.php";

include_once "footer.php";

?>
