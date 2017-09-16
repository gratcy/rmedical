<?php

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_invoice_record.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }



$inputs		= new Inputs();
$inputs->add(
			'date_start'			, 'text'			, '1'		, '20',
			'date_end'				, 'text'			, '2'		, '20',
			'month'					, 'select2'			, '3'		, '155',
			'recent'				, 'select2'			, '3'		, '155'
						);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;
$inputs->options['recent']				= array("一個月" => 1, "三個月" => 3, "半年" => 6, "一年" => 12, "二年" => 24, "三年" => 36, "五年" => 60);


$inputs->tag['month']					= "onfocus='document.getElementById(\"date_select_month\").checked = true;'";
$inputs->tag['recent']					= "onfocus='document.getElementById(\"date_select_recent\").checked = true;'";
$inputs->tag['date_start']				= "onfocus='document.getElementById(\"date_select_between\").checked = true;'";
$inputs->tag['date_end']				= "onfocus='document.getElementById(\"date_select_between\").checked = true;'";


if (empty($_GET)) {
	gotoURL("report_invoice.php?date_select=recent&recent=1&page=staff_group");
	exit;
}




$inputs->value				= $_GET;


$page						= sql_secure(popURL('page'));
$filter_value				= sql_secure(popURL('filter_value'));
$url						= getURL();



$filter						= 1;




$lang = lang('帳單記錄');
$report_title				= $lang;


echo <<<EOS
<h2>$report_title</h2>
<br>

EOS;


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

$date_start			= strtotime($date_start);
$date_end			= strtotime($date_end);

if ($date_end < $date_start)
    $date_end		= $date_start;

$dates				= array();
while ($date_start <= $date_end) {
    $dates[]		= date('Y-m-d', $date_start);
    $date_start		+= 86400;		// 60 x 60 x 24
}






echo <<<EOS


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


if (empty($page))			$page	= 'staff_group';

if ($page == 'staff_group')
	$options				= sql_getArray("select description, id from class_staff_group order by description asc");
if ($page == 'staff')
	$options				= sql_getArray("select a.name,  a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");
if ($page == 'customer')
	$options				= sql_getArray("select name, id from customer order by name asc");


$inputs->clear();
$inputs->add('filter_value', 'select2', $filter_value, 'Filter value', $options, 80);


echo <<<EOS
<a class=tab href='{$url}&page=staff_group'>組別</a>
<a class=tab href='{$url}&page=staff'>員工</a>
<a class=tab href='{$url}&page=customer'>客戶</a>
<table class='table table-borderless'>
	<tr>
		<td>選擇 &nbsp; $inputs->filter_value <input type=submit value='確定'></td>
	</tr>
</form>
</table>
<br>
<br>
EOS;

pushURL('page', $page);
pushURL('filter_value', $filter_value);





if (empty($dates))			exit;









$date_end			= end($dates);
$date_start			= reset($dates);
$filter				= "(a.date_order >= '$date_start' and a.date_order <= '$date_end')";



if ($_GET['page'] == 'staff_group') {

	$columns			= array(
				"invoice_id"				=> "name:記錄編號		, width:150",
				"date_order"				=> "name:帳單日期		, width:100",
				"paymentterms"				=> "name:付款方式		, width:120",
				"customer"					=> "name:客戶",
				"issueby"					=> "name:簽發人",
				"amount_gross"				=> "name:拆前總售價		, class:number",
				"amount_net"				=> "name:拆後總售價		, class:number",
				"average_discount"			=> "name:平均拆扣		, class:number",
				"unpaid"					=> "name:未付總數		, class:number",
							);

	$bar_width_max		= 300;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and a.staff_group='$filter_value'";

	$items				= sql_getTable("
			select
				a.invoice_id as invoice_id,
				a.date_order as date_order,
				a.paymentterms,
				b.name as customer,
				c.name as issueby,
				a.amount_gross,
				a.amount_net,
				(a.amount_net/a.amount_gross*100) as average_discount,
				a.unpaid
			from
				invoice a
				join customer b on a.customer_id=b.id
				join staff c on a.staff_id=c.id
			where
				$filter order by $orderby $ordertype");

}

if ($_GET['page'] == 'staff') {

	$columns			= array(
				"invoice_id"				=> "name:記錄編號		, width:150",
				"date_order"				=> "name:帳單日期		, width:100",
				"paymentterms"				=> "name:付款方式		, width:120",
				"customer"					=> "name:客戶",
				"issueby"					=> "name:簽發人",
				"amount_gross"				=> "name:拆前總售價		, class:number",
				"amount_net"				=> "name:拆後總售價		, class:number",
				"average_discount"			=> "name:平均拆扣		, class:number",
				"unpaid"					=> "name:未付總數		, class:number",
							);

	$bar_width_max		= 400;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and a.staff_id='$filter_value'";

	$items				= sql_getTable("
			select
				a.invoice_id as invoice_id,
				a.date_order as date_order,
				a.paymentterms,
				b.name as customer,
				c.name as issueby,
				a.amount_gross,
				a.amount_net,
				(a.amount_net/a.amount_gross*100) as average_discount,
				a.unpaid
			from
				invoice a
				join customer b on a.customer_id=b.id
				join staff c on a.staff_id=c.id
			where
				$filter order by $orderby $ordertype");

}

if ($_GET['page'] == 'customer') {

	$columns			= array(
				"invoice_id"				=> "name:記錄編號		, width:150",
				"date_order"				=> "name:帳單日期		, width:100",
				"paymentterms"				=> "name:付款方式		, width:120",
				"customer"					=> "name:客戶",
				"issueby"					=> "name:簽發人",
				"amount_gross"				=> "name:拆前總售價		, class:number",
				"amount_net"				=> "name:拆後總售價		, class:number",
				"average_discount"			=> "name:平均拆扣 (%)	, class:number",
				"unpaid"					=> "name:未付總數		, class:number",
							);

	$bar_width_max		= 400;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and a.customer_id='$filter_value'";

	$items				= sql_getTable("
			select
				a.invoice_id as invoice_id,
				a.date_order as date_order,
				a.paymentterms,
				b.name as customer,
				c.name as issueby,
				a.amount_gross,
				a.amount_net,
				(a.amount_net/a.amount_gross*100) as average_discount,
				a.unpaid
			from
				invoice a
				join customer b on a.customer_id=b.id
				join staff c on a.staff_id=c.id
			where
				$filter order by $orderby $ordertype");

}











/////////////////////////////////////////////////////
// Display Statistics
/////////////////////////////////////////////////////

include "report_list1.php";




function get_orderby() {
	global $columns, $default_order, $orderby, $ordertype;

	$default_order		= reset(array_keys($columns)) . "@asc";

	if (empty($_SESSION['sort_order'][getURL('file')]))
		$_SESSION['sort_order'][getURL('file')]		= $default_order;

	if (isset($_GET['orderby']))
		$_SESSION['sort_order'][getURL('file')]		= sql_secure(popURL('orderby'));


	list($orderby, $ordertype)						= explode("@", $_SESSION['sort_order'][getURL('file')]);
}



include_once "footer.php";

?>
