<?php

require_once "header.php";

//$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice_check.php'");
//if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


include_once "bin/class_inputs.php";

$inputs		= new Inputs();
$inputs->add(
			'date_start'			, 'text'			, '1'		, '20',
			'date_end'				, 'text'			, '2'		, '20',
			'month'					, 'select'			, '3'		, '155',
			'recent'				, 'select'			, '3'		, '155'
						);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;
$inputs->options['recent']				= array("一個月" => 1, "三個月" => 3, "半年" => 6, "一年" => 12, "二年" => 24, "三年" => 36, "五年" => 60);


$inputs->tag['month']					= "onfocus='document.getElementById(\"date_select_month\").checked = true;'";
$inputs->tag['recent']					= "onfocus='document.getElementById(\"date_select_recent\").checked = true;'";
$inputs->tag['date_start']				= "onfocus='document.getElementById(\"date_select_between\").checked = true;'";
$inputs->tag['date_end']				= "onfocus='document.getElementById(\"date_select_between\").checked = true;'";


if (empty($_GET))
	gotoURL("invoice_check.php?date_select=recent&recent=1");

$inputs->value				= $_GET;
$page						= sql_secure(popURL('page'));
$filter_value				= sql_secure(popURL('filter_value'));
$url						= getURL();
$filter						= 1;



$date_select				= $_GET['date_select'];
$date_checked_1				= ($date_select == 'recent') ? 'checked' : '';
$date_checked_2				= ($date_select == 'month') ? 'checked' : '';
$date_checked_3				= ($date_select == 'between') ? 'checked' : '';

if ($date_checked_1 == 'checked') {
		$recent				= $_GET['recent'] * 1;
        $date_start			= date("Y-m-d", strtotime("-$recent month"));
        $date_end			= date("Y-m-d");
} else if ($date_checked_2 == 'checked') {
        $date_months		= str_replace("月", "", $_GET['month']);
        $date_month         = explode("年", $date_months);
        $year 				= trim($date_month[0]);
        $month				= trim($date_month[1]);
        $date_start			= "$year-$month-01";
        $date_end			= "$year-$month-" . date("t", mktime(0, 0, 0, $month, 1, $year));
} else if ($date_checked_3 == 'checked') {
        $date_start			= $_GET['date_start'];
        $date_end			= $_GET['date_end'];
}


$date_range			= "(a.date_order>= '$date_start' and a.date_order<= '$date_end')";





echo <<<EOS
<h3>檢查出單</h3>
<br />
<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<a class=tab>日期</a>
<table class='table table-borderless'>
<form action='' method=get>
<input type=hidden name=page value='$page'>
	<tr>
		<td width=300 valign=top>
			<input id='date_select_recent' type='radio' name='date_select' id='1' value='recent' $date_checked_1/>
			最近 $inputs->recent
			<input type='submit' name='1' id='1' value='確定' />
		</td>
		<td width=300 valign=top>
			<input id='date_select_month' type='radio' name='date_select' id='1' value='month' $date_checked_2/>
			月份 $inputs->month
			<input type='submit' name='2' id='2' value='確定' />
		</td>
		<td valign=top>
			<input id='date_select_between' type='radio' name='date_select' id='2' value='between' $date_checked_3/>
			開始 $inputs->date_start <img src='js/calendar.gif' onclick="show_cal(this, 'date_start');" />
			<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			結束 $inputs->date_end <img src='js/calendar.gif' onclick="show_cal(this, 'date_end');" />
	    	<input type='submit' name='3' id='3' value='確定' />
	    </td>
	</tr>
</table>
<br>
<br>

EOS;






set_time_limit(30);
$invoices_01		= sql_getTable("select a.id, a.invoice_id, a.amount_gross, ifnull(sum(b.amount), 0) as detail_amount, a.amount_gross- ifnull(sum(b.amount),0) as diff, a.date_order, status from invoice a join invoice_detail b on (a.id=b.invoice_id) where $date_range group by a.id having abs(diff) > 0.1 order by diff");

set_time_limit(30);
$invoice_id1		= sql_getArray("select a.id from invoice a where $date_range");
$invoice_id2		= sql_getArray("select a.invoice_id from invoice_detail a where $date_range");

set_time_limit(30);
$invoice_ids		= array_diff($invoice_id1, $invoice_id2);
$invoice_ids		= implode(", ", $invoice_ids);

if (empty($invoice_ids))
	$invoice_ids	= 0;

set_time_limit(30);
$invoices_02		= sql_getTable("select a.id, a.invoice_id, a.amount_gross, 0 as detail_amount, a.amount_gross as diff, a.date_order, status from invoice a where $date_range and id in ($invoice_ids) and a.amount_gross != 0");



$invoices			= array_merge($invoices_01, $invoices_02);





/////////////////////////////////////////////////////
// Display Statistics
/////////////////////////////////////////////////////


echo "<div class='table-responsive'>";
echo "<table class='table table-borderless simple_list'>";

echo "<tr>";
echo "<th>#</th>";
echo "<th>編號</th>";
echo "<th class=number>帳單總額</th>";
echo "<th class=number>詳細帳單加總</th>";
echo "<th class=number>相差</th>";
echo "<th>日期</th>";
echo "</tr>";

if (empty($invoices)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有記錄。</td></tr>\r\n";

}

foreach ($invoices as $invoice) {

	array2obj($invoice);

	echo "<tr bgcolor=white>";
	echo "<td width=100>$invoice->id</td>";
	echo "<td><a href='invoice_edit.php?id=$invoice->id' target=_blank>$invoice->invoice_id</a></td>";
	echo "<td class=number>$invoice->amount_gross</td>";
	echo "<td class=number>$invoice->detail_amount</td>";
	echo "<td class=number>$invoice->diff</td>";
	echo "<td width=100>$invoice->date_order</td>";
	echo "</tr>";

	flush();

}

echo "</table>";
echo "</div>";




require_once "footer.php";


?>