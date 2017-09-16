<?php

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_item_stat.php'");
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
	gotoURL("report_sell_detail.php?date_select=recent&recent=3&page=staff_group");
	exit;
}




$inputs->value				= $_GET;


$page						= sql_secure(popURL('page'));
$filter_value				= sql_secure(popURL('filter_value'));
$url						= getURL();



$filter						= 1;





$lang = lang('銷售狀況');

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
        $date_start			= strtotime($date_start);
        $date_end			= strtotime($date_end);
}


if ($date_end < $date_start)
    $date_end		= $date_start;

$date_start			= strtotime($date_start);
$date_end			= strtotime($date_end);

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
if ($page == 'item')
	$options				= sql_getArray("select name_series, id from item order by name_series asc");
if ($page == 'brand')
	$options				= sql_getArray("select description, id from class_brand order by description asc");
if ($page == 'customer')
	$options				= sql_getArray("select name, id from customer order by name asc");
if ($page == 'supplier')
	$options				= sql_getArray("select name, id from supplier order by name asc");


$inputs->clear();
$inputs->add('filter_value', 'select2', $filter_value, 'Filter value', $options, 80);


echo <<<EOS
<a class=tab href='{$url}&page=staff_group'>組別</a>
<a class=tab href='{$url}&page=staff'>員工</a>
<a class=tab href='{$url}&page=item'>產品</a>
<a class=tab href='{$url}&page=brand'>產品品牌</a>
<a class=tab href='{$url}&page=customer'>客戶</a>
<a class=tab href='{$url}&page=supplier'>供應商</a>
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
if (empty($filter_value))	exit;








$date_end			= end($dates);
$date_start			= reset($dates);
$filter				= "(date_order>= '$date_start' and date_order<= '$date_end')";


if ($_GET['page'] == 'staff_group') {

	$columns			= array(
				"name"						=> "name:產品			, width:500",
				"price_original"			=> "name:原價			, class:number",
				"quantity"					=> "name:售出數量		, class:number",
				"average_price"				=> "name:平均拆前售價	, class:number",
				"amount"					=> "name:拆後前售價		, class:number",
				"average_price_discount"	=> "name:平均拆後售價	, class:number",
				"amount_discounted"			=> "name:拆後總售價		, class:number",
				"discount_percentage"		=> "name:平均拆扣		, class:number",
							);

	$bar_width_max		= 500;

	get_orderby();

	$items				= sql_getTable("
			select
				b.name as name,
				a.price_original as price_original,
				sum(a.quantity) as quantity,
				avg(a.price) as average_price,
				sum(a.amount) as amount,
				avg(a.amount_discounted/a.quantity) as average_price_discount,
				sum(a.amount_discounted) as amount_discounted,
				(sum(a.amount_discounted)/sum(a.amount)*100) as discount_percentage
			from invoice_detail a join item b on a.item_id=b.id where $filter and staff_group='$filter_value' group by a.item_id order by $orderby $ordertype");

}

if ($_GET['page'] == 'staff') {

	$columns			= array(
				"name"						=> "name:員工		, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;

	$items				= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join staff b on a.staff_id=b.id where $filter group by a.staff_id order by $orderby $ordertype");

}

if ($_GET['page'] == 'item') {

	$columns			= array(
				"name"						=> "name:產品		, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;

	$items				= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join item b on a.item_id=b.id where $filter group by a.item_id order by $orderby $ordertype");

}

if ($_GET['page'] == 'item_brand') {

	$columns			= array(
				"name"						=> "name:產品品牌	, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;

	$items				= sql_getTable("select b.description as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join class_brand b on a.item_brand=b.id where $filter group by a.item_brand order by $orderby $ordertype");

}

if ($_GET['page'] == 'customer') {

	$columns			= array(
				"name"						=> "name:客戶		, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;

	$items				= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join customer b on a.customer_id=b.id where $filter group by a.customer_id order by $orderby $ordertype");

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
