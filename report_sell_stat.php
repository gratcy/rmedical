<?php
if (isset($_GET['export']) && $_GET['export'] == 1)	ob_start();
include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_sell_stat.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$export_link	= setQueryString(getURL(), "export=1");

$inputs		= new Inputs();
$inputs->add(
			'date_start'			, 'text'			, '1'		, '100%',
			'date_end'				, 'text'			, '2'		, '100%',
			'month'					, 'select2'			, '3'		, '100%',
			'recent'				, 'select2'			, '3'		, '100%',
			'row'					, 'select2'			, '4'		, '100%'
						);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;
$inputs->options['recent']				= array("一個月" => 1, "三個月" => 3, "半年" => 6, "一年" => 12, "二年" => 24, "三年" => 36, "五年" => 60);
$inputs->options['row']					= array("組別" => "staff_group", "員工" => "staff", "產品" => "item", "產品品牌" => "item_brand", "客戶" => "customer");

$inputs->tag['month']					= "onfocus='var date_select=document.getElementById(\"date_select_month\"); date_select.checked = true;'";
$inputs->tag['date_start']				= "onfocus='var date_select=document.getElementById(\"date_select_between\"); date_select.checked = true;'";
$inputs->tag['date_end']				= "onfocus='var date_select=document.getElementById(\"date_select_between\"); date_select.checked = true;'";


if (empty($_GET)) {
	gotoURL("report_sell_stat.php?date_select=recent&recent=3&page=staff_group");
	exit;
}




$inputs->value				= $_GET;


$page						= sql_secure(popURL('page'));
$filter_value				= sql_secure(popURL('filter_value'));
$url						= getURL();



$filter						= 1;





$lang = lang('銷售總額');
$report_title				= $lang;


echo <<<EOS
<h3 class='pull-left'>$report_title</h3>

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
		$drange = $_GET['daterange'];
		$drange = explode(' - ', $drange);
        $date_start			= $drange[0];
        $date_end			= $drange[1];
}


$date_start			= strtotime($date_start);
$date_end			= strtotime($date_end);

if ($date_end < $date_start)
    $date_end		= $date_start;

if (isset($_GET['daterange'])) {
	$dto = date('Y-m-d', $date_end);
	$dfrom = date('Y-m-d', $date_start);
}
else {
	$dto = date('Y-m-d');
	$dfrom = date('Y-m-d', strtotime('-1 month'));
}

$dates				= array();
while ($date_start <= $date_end) {
    $dates[]		= date('Y-m-d', $date_start);
    $date_start		+= 86400;		// 60 x 60 x 24
}





$excel = lang('導出表格');
echo <<<EOS
<input class="btn btn-default pull-right" type='button' value='$excel' onclick="window.location.href='$export_link'">
<br />
<br />
<br />

<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<h3 class='pull-left'>日期</h3>
<br><br><br>
<form action='' method=get class='form-horizontal'>
	<input type=hidden name=page value='$page'>
	<div class='form-group'>
		<label for='input_recent' class='col-sm-2 control-label'><input id='date_select_recent' type='radio' name='date_select' id='1' value='recent' $date_checked_1/> 最近</label>
		<div class='col-sm-6'>
			$inputs->recent
		</div>
		<div class='col-sm-2'>
			<input type='submit' class='btn btn-default' name='1' id='1' value='確定' />
		</div>
	</div>
	<div class='form-group'>
		<label for='input_month' class='col-sm-2 control-label'><input id='date_select_month' type='radio' name='date_select' id='1' value='month' $date_checked_2/> 月份</label>
		<div class='col-sm-6'>
			$inputs->month
		</div>
		<div class='col-sm-2'>
			<input type='submit' class='btn btn-default' name='2' id='2' value='確定' />
		</div>
	</div>
	<div class='form-group'>
		<label for='input_month' class='col-sm-2 control-label'><input id='date_select_between' type='radio' name='date_select' id='2' value='between' $date_checked_3/> 開始 - 結束</label>
		<div class='col-sm-6'>
			<div class="input-group">
				<input onfocus="$('#date_select_between').prop('checked', true);" type='text' id='daterange' name='daterange' class='form-control' autocomplete='off' value='$dfrom - $dto' style='width: 100%' />
					<div class="input-group-addon"><i class="fa fa-calendar-o"></i></div>
			</div>
		</div>
		<div class='col-sm-1'>
			<input type='submit' class='btn btn-default' name='3' id='3' value='確定' />
		</div>
	</div>
	<br>
	<br>
EOS;


if (empty($page))			$page	= 'staff_group';

if ($page == 'staff_group')
	$options				= sql_getArray("select description, id from class_staff_group order by description asc");
if ($page == 'staff')
	$options				= sql_getArray("select a.name,  a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");
if ($page == 'item')
	$options				= sql_getArray("select `class`, id from item order by `class` asc");
if ($page == 'brand')
	$options				= sql_getArray("select description, id from class_brand order by description asc");
if ($page == 'customer')
	$options				= sql_getArray("select name, id from customer order by name asc");


$inputs->clear();
$inputs->add('filter_value', 'select', $filter_value, 'Filter value', $options, 80);


echo <<<EOS
<a class=tab href='{$url}&page=staff_group'>組別</a>
<a class=tab href='{$url}&page=staff'>銷售員</a>
<a class=tab href='{$url}&page=item'>產品</a>
<a class=tab href='{$url}&page=brand'>產品品牌</a>
<a class=tab href='{$url}&page=customer'>客戶</a>
<table class='table table-borderless hidden'>
	<tr>
<!--		<td>選擇 &nbsp; $inputs->filter_value <input type=submit value='確定'></td>-->
	</tr>
</form>
</table>

EOS;

pushURL('page', $page);
pushURL('filter_value', $filter_value);





if (empty($dates))			exit;
//if (empty($filter_value))	exit;





$date_end			= end($dates);
$date_start			= reset($dates);
$filter				= "(date_order>= '$date_start' and date_order<= '$date_end')";


if ($_GET['page'] == 'staff_group') {

	$columns			= array(
				"name"						=> "name:組別		, width:350 , link:report_sell_detail.php",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	$bar_width_max		= 500;

	get_orderby();
	if ($_SESSION['root'] == 1)
		$filterLevel = "";
	else
		$filterLevel = " AND a.staff_group=" . $_SESSION['group'];
	
	$items				= sql_getTable("select b.description as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join class_staff_group b on a.staff_group=b.id where $filter $filterLevel group by staff_group order by $orderby $ordertype");

	$columns_end		= sql_getObj("select concat('記錄數：', count(*), ' 　　　　加總：') as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a where $filter");

}

if ($_GET['page'] == 'staff') {

	$columns			= array(
				"name"						=> "name:銷售員		, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;

	if ($_SESSION['root'] == 1)
		$filterLevel = "";
	else
		$filterLevel = " AND b.`group`=" . $_SESSION['group'];
		
	$items				= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join staff b on a.staff_id=b.id where $filter $filterLevel group by a.staff_id order by $orderby $ordertype");

	$columns_end		= sql_getObj("select concat('記錄數：', count(*), ' 　　　　加總：') as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a where $filter");

}

if ($_GET['page'] == 'item') {

	$columns			= array(
				"name"						=> "name:產品		, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;
	if ($_SESSION['root'] == 1)
		$filterLevel = "";
	else
		$filterLevel = " AND c.`group`=" . $_SESSION['group'];

	$items				= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join item b on a.item_id=b.id join staff c on a.staff_id=c.id where $filter $filterLevel group by b.`class` , a.item_id order by $orderby $ordertype");
	$itemsHighQ			= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join item b on a.item_id=b.id join staff c on a.staff_id=c.id where $filter $filterLevel group by b.`class` , a.item_id order by SUM(a.quantity) DESC LIMIT 10");
	$itemsHighA			= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join item b on a.item_id=b.id join staff c on a.staff_id=c.id where $filter $filterLevel group by b.`class` , a.item_id order by SUM(a.amount) DESC LIMIT 10");

	$columns_end		= sql_getObj("select concat('記錄數：', count(*), ' 　　　　加總：') as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a where $filter");

}

if ($_GET['page'] == 'brand') {

	$columns			= array(
				"name"						=> "name:產品品牌	, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;
	if ($_SESSION['root'] == 1)
		$filterLevel = "";
	else
		$filterLevel = " AND c.`group`=" . $_SESSION['group'];

	$items				= sql_getTable("select b.description as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join class_brand b on a.item_brand=b.id join staff c on a.staff_id=c.id where $filter $filterLeve group by a.item_brand order by $orderby $ordertype");

	$columns_end		= sql_getObj("select concat('記錄數：', count(*), ' 　　　　加總：') as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a where $filter");

}

if ($_GET['page'] == 'customer') {

	$columns			= array(
				"name"						=> "name:客戶		, width:350",
				"quantity"					=> "name:銷售數量	, class:number",
				"amount"					=> "name:銷售總額	, class:number"
							);

	get_orderby();

	$bar_width_max		= 500;
	if ($_SESSION['root'] == 1)
		$filterLevel = "";
	else
		$filterLevel = " AND c.`group`=" . $_SESSION['group'];

	$items				= sql_getTable("select b.name as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a join customer b on a.customer_id=b.id join staff c on a.staff_id=c.id where $filter $filterLeve group by a.customer_id order by $orderby $ordertype");

	$columns_end		= sql_getObj("select concat('記錄數：', count(*), ' 　　　　加總：') as name, sum(a.quantity) as quantity, sum(a.amount) as amount from invoice_detail a where $filter");

}
















/////////////////////////////////////////////////////
// Display Statistics
/////////////////////////////////////////////////////

if ($_GET['export'] == 1) {

	ob_end_clean();
	include "report_export.php";
} else {
	include "report_list1.php";
}



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
