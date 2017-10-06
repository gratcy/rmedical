<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


$lang = lang('出單');
echo <<<EOS
<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<h3 class='pull-left'>$lang</h3>
EOS;




if (isset($_GET['delete'])) {

	$id		= sql_secure($_GET['delete']);
	delete_record("select * from invoice_detail where invoice_id='$id'");
	delete_record("select * from invoice where id='$id'");

	include_once "inventory_library.php";
	$it_id					= sql_getValue("select id from inventory_transaction where type='sold' and remark='invoice_id:$id'");
	inventory_transaction_delete($it_id);

	gotoURL(-1);
	exit;

}


//	Delete new added but not used record




$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;



echo "
		<div class='pull-right'>
			<input class='btn btn-default' type=button value='新增出單 (N)' onclick='location.href=\"invoice_add.php\";'>
			<input class='btn btn-default' type=button value='".lang('導出表格')." (E)' onclick='exportform.submit()' />
			<input class='btn btn-default' type=button value='檢查帳單' onclick='location.href=\"invoice_check.php\";'>
		</div>
		<br /><br /><br />
";


$columns		= array(
			"invoice_id"				=> "出單編號",
			"date_order"				=> "出單日期",
			"customer_id"				=> "顧客",
			"amount_gross"				=> "總數",
			"sales_record"				=> "銷售額",
			"staff_id"					=> "出單人",
			"#print"					=> "列印",
			"#edit"						=> "編輯",
			"#delete"					=> "刪除"
						);


$columns_sql	= array(
			"customer_id"				=> "select name from customer where id=invoice.customer_id",
			"staff_id"					=> "select name from staff where id=invoice.staff_id"
						);

$columns_class	= array(
			"amount_gross"				=> "number",
			"unpaid"					=> "number",
            "#print"						=> "noprint",
            "#edit"						=> "noprint",
            "#delete"						=> "noprint"
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
<table class='table table-borderless simple_list'>
	<tr>";

$default_order				= "date_order@desc";


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
			$order = "desc";
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

$date_start			= $_GET['date_start'];
$date_end			= $_GET['date_end'];


if (empty($search_field))
	$search_word	= '';

if (isset($columns_sql[$search_field]))
	$search_field	= "(" . $columns_sql[$search_field] . ")";




if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";





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
        $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";

}

$filter				.= " and status !='deleted'";
if ($_SESSION['root'] != 1)
$filter				.= " and staff_id=" . $_SESSION['staff_id'];

$items				= sql_getTable("select * from invoice where $filter order by $orderby $ordertype, id desc limit $offset, $record_per_page");
$customer_names		= sql_getArray("select id, name from customer");
$staff_names		= sql_getArray("select id, name from staff");


if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有記錄。</td></tr>\r\n";

}



$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->cost_currency		= $currency_sign[$item->cost_currency];

	$item->date_modify			= printdate($item->date_modify);
	$item->amount_gross			= "\$" . number_format($item->amount_gross, 2);
	$item->sales_record			= "\$" . number_format($item->sales_record, 2);
	$item->unpaid				= number_format($item->unpaid, 2);


	$customer					= $customer_names[$item->customer_id];
	$staff						= $staff_names[$item->staff_id];


	$from_query					= urlencode($_SERVER['QUERY_STRING']);

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"invoice_edit.php?id=$item->id&from_query=$from_query\";'";
		$edit_link				= "<a href='invoice_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除記錄 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}

	if ($item->status == 'freeze')
		$delete_link			= "";


	if ($item->amount_gross != $item->sales_record)
		$item->sales_record		= "<font color=red>$item->sales_record</font>";

	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->invoice_id</td>";
	echo "<td>$item->date_order</td>";
	echo "<td>$customer</td>";
	echo "<td class=number>$item->amount_gross</td>";
	echo "<td class=number>$item->sales_record</td>";
	echo "<td>$staff</td>";
	if (!empty($privilege->print))
		echo "<td width=60 class=noprint><a href='invoice_edit_print.php?id=$item->id&noreturn=1' target=_blank><i class='fa fa-print'></i></a></td>";
	else
		echo "<td width=60 class=noprint></td>";
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
			$order = "desc";
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
            <input type=hidden name=page value='invoice' />
		</td>
      </form>
	</tr>
</table>

EOS;


//	Paging function

$record_sql				= "select count(*) from invoice where $filter";

echo "<div id='paging_footer'>";

include "paging.php";

echo "</div>";


echo <<<EOS

<script>
shortcut.add("Ctrl+N", function () { location.href="invoice_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });
</script>


EOS;


include_once "footer.php";

?>
