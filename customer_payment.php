<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customer_payment.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$lang = lang('客戶付款');
echo <<<EOS
<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<h3 class="pull-left">$lang</h3>
EOS;



if (isset($_GET['delete'])) {

	$id		= sql_secure($_GET['delete']);
	sql_query("update customer_payment  set status='deleted' where id='$id'");
	$invoices			= sql_getArray("select invoice_id from customer_payment_detail where customer_payment_id='$id'");
	sql_query("delete from customer_payment_detail where customer_payment_id='$id'");
	foreach ($invoices as $invoice) {
		sql_query("update invoice set unpaid=amount_net - ifnull((select sum(amount) from customer_payment_detail where invoice_id=invoice.id), 0), date_pay='$customer_payment->date' where id='$invoice'");
	}
	gotoURL(-1);
	exit;

}


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;


$print_url			= str_replace(".php?", "_print.php?", getURL());

echo "
		<div class='pull-right'>
			<input class='btn btn-default' type=button value='新增客戶付款 (N)' onclick='location.href=\"customer_payment_add.php\";'>
			<input class='btn btn-default' type=button value='列印 (P)' onclick='window.open(\"$print_url\");'>
			<input class='btn btn-default' type=button value='".lang('導出表格')." (E)' onclick='exportform.submit()' />
			<input class='btn btn-default' type=button value='檢查付款' onclick='location.href=\"customer_payment_check.php\";'>
		</div>
		<br /><br /><br />
";
$columns		= array(
			"payment_id"		=> "編號",
			"date"				=> "付款日期",
			"refno"				=> "參考資料",
			"customer"			=> "客戶名稱",
			"class_customer "	=> "客戶類型",
			"customer_id"		=> "聯絡人",
			"amount"			=> "總額",
			"staff_id"			=> "收款人",
			"#2"				=> "編輯",
			"#3"				=> "刪除"
						);

	$columns_sql	= array(
			"customer"				=> "select name from customer where customer.id=customer_payment.customer_id",
			"class_customer"		=> "select description from class_customer join customer on class_customer.id=customer.class where customer_payment.id = customer.id",
			"customer_id"			=> "select attention from customer where id=customer_payment.customer_id",
			"staff_id"				=> "select name from staff where id=customer_payment.staff_id"
						);

$columns_class	= array(
            "#2"						=> "noprint",
            "#3"						=> "noprint"
						);

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'search_word'				, 'text'		, '字眼'					, '30',
			'search_field'				, 'select'		, '欄位'					, '80',
            'date_start'				, 'text'		, '1'					, '20',
			'date_end'					, 'text'		, '2'					, '20'
			);

$search_field							= array_flip($columns);
unset($search_field['']);
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



$date_start			= $_GET['date_start'];
$date_end			= $_GET['date_end'];

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
        $filter				.= " and (`date`>= '$date_start' and `date`<= '$date_end')";

}


$filter				.= " and status !='deleted'";


$items				= sql_getTable("select * from customer_payment where $filter order by $orderby $ordertype limit $offset, $record_per_page");


if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有記錄。</td></tr>\r\n";

}


$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->cost_currency		= $currency_sign[$item->cost_currency];

	$item->date_modify			= printdate($item->date_modify);

	$item->amount				= number_format($item->amount, 2);

    $from_query					= urlencode($_SERVER['QUERY_STRING']);

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"customer_payment_edit.php?id=$item->id&from_query=$from_query\";'";
		$edit_link				= "<a href='customer_payment_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除產品 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}

	$staff						= sql_getValue("select name from staff where id='$item->staff_id'");
	$customer					= sql_getObj("select * from customer where id='$item->customer_id'");
	$customer_class				= sql_getValue("select description from class_customer where id='$customer->class'");



	echo "<tr bgcolor=$bgcolor $doubleclick>";
	echo "<td>$item->payment_id</td>";
	echo "<td>$item->date</td>";
	echo "<td>$item->refno</td>";
	echo "<td>$customer->name</td>";
	echo "<td>$customer_class</td>";
	echo "<td>$customer->attention</td>";
	echo "<td class=number>$item->amount</td>";
	echo "<td>$staff</td>";
	echo "<td width=60 class=noprint>$edit_link</td>";
	echo "<td width=60 class=noprint>$delete_link</td>";
	echo "</tr>\r\n";
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
            <input type=hidden name=page value='customer_payment' />

		</td>
      </form>
	</tr>
</table>
EOS;

//	Paging function
$record_sql				= "select count(*) from customer_payment where $filter";


echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

echo <<<EOS

<script>

shortcut.add("Ctrl+N", function () { location.href="customer_payment_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });
</script>

EOS;


include_once "footer.php";

?>
