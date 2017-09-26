<?php

if (isset($_GET['export']) && $_GET['export'] == 1)	ob_start();


include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_invoice.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$export_link	= setQueryString(getURL(), "export=1");


$inputs		= new Inputs();
$inputs->add(
			'date_start'			, 'text'			, '1'		, '100%',
			'date_end'				, 'text'			, '2'		, '100%',
			'month'					, 'select2'			, '3'		, '100%',
			'recent'				, 'select2'			, '3'		, '100%'
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




$lang = lang('帳單統計');
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
	$dto = '';
	$dfrom = '';
}

$dates				= array();
while ($date_start <= $date_end) {
    $dates[]		= date('Y-m-d', $date_start);
    $date_start		+= 86400;		// 60 x 60 x 24
}





$excel = lang('導出表格');

echo <<<EOS
<input class='btn btn-default pull-right' type='button' value='$excel' onclick="window.location.href='$export_link'">
<br />
<br />
<br />

<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
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

if ($_SESSION['root'] == 1) {
	if ($page == 'staff_group')
		$options				= sql_getArray("select description, id from class_staff_group order by description asc");
	if ($page == 'staff')
		$options				= sql_getArray("select a.name,  a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");
}
else {
	if ($page == 'staff_group')
		$options				= sql_getArray("select description, id from class_staff_group WHERE id=".$_SESSION['group']." order by description asc");
	if ($page == 'staff')
		$options				= sql_getArray("select a.name,  a.id from staff a join class_staff b on a.`class`=b.id WHERE a.`group`=".$_SESSION['group']." order by a.`class`, a.name");
}
if ($page == 'customer')
	$options				= sql_getArray("select name, id from customer order by name asc");


$inputs->clear();
$inputs->add('filter_value', 'select2', $filter_value, 'Filter value', $options, 80);


echo <<<EOS
<a class=tab href='{$url}&page=staff_group'>組別</a>
<a class=tab href='{$url}&page=staff'>簽發人</a>
<a class=tab href='{$url}&page=customer'>客戶</a>
<table class='table table-borderless report_list' style='margin-top: 10px;'>
	<tr>
		<td>
			<div class='form-group'>
		    <label for='input_filter_value' class='col-sm-2 control-label'>選擇</label>
				<div class='col-sm-6'>
					 $inputs->filter_value
				</div>
				<div class='col-sm-2'>
					<input type=submit value='確定' class="btn btn-default">
				</div>
			</div>
		</td>
	</tr>
</form>
</table>
<br>

EOS;

pushURL('page', $page);
pushURL('filter_value', $filter_value);





if (empty($dates))			exit;









$date_end			= end($dates);
$date_start			= reset($dates);
$filter				= "(a.date_order >= '$date_start' and a.date_order <= '$date_end')";

$filter				.= "and a.status != 'deleted'";


if ($_GET['page'] == 'staff_group') {

	$report_description	= "<div style='float:left'>組別 : " . sql_getValue("select description from class_staff_group where id='$filter_value'") . "</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";

	$columns			= array(
				"invoice_id"				=> "name:記錄編號		, width:150",
				"date_order"				=> "name:帳單日期		, width:100",
				"customer"					=> "name:客戶",
				"issueby"					=> "name:簽發人",
				"amount_gross"				=> "name:拆前總額		, class:number",
				"amount_net"				=> "name:拆後總額		, class:number",
				"average_discount"			=> "name:拆扣 (%)		, class:number",
				"unpaid"					=> "name:未付總數		, class:number",
							);

	$bar_width_max		= 300;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and a.staff_group='$filter_value'";


	 $items			= sql_getTable("
     		select
				a.invoice_id as invoice_id,
				a.date_order as date_order,
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


	$columns_end		= sql_getObj("
			select
				concat('記錄數：', count(*)) as invoice_id,
				concat('加總：') as date_order,
				sum(a.amount_gross) as amount_gross,
				sum(a.amount_net) as amount_net,
				(sum(a.amount_net)/sum(a.amount_gross)*100) as average_discount,
				sum(a.unpaid) as unpaid
			from
				invoice a
			where
				$filter");
}

if ($_GET['page'] == 'staff') {

	$report_description	= "<div style='float:left'>銷售員 : " . sql_getValue("select name from staff where id='$filter_value'") . "</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";

	$columns			= array(
				"invoice_id"				=> "name:記錄編號		, width:150",
				"date_order"				=> "name:帳單日期		, width:100",
//				"paymentterms"				=> "name:付款方式		, width:120",
				"customer"					=> "name:客戶",
				"issueby"					=> "name:簽發人",
				"amount_gross"				=> "name:拆前總額		, class:number",
				"amount_net"				=> "name:拆後總額		, class:number",
				"average_discount"			=> "name:拆扣 (%)		, class:number",
				"unpaid"					=> "name:未付總數		, class:number",
							);

	$bar_width_max		= 400;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and a.staff_id='$filter_value'";

	 $items			= sql_getTable("
     		select
				a.invoice_id as invoice_id,
				a.date_order as date_order,
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

	$columns_end		= sql_getObj("
			select
				concat('記錄數：', count(*)) as invoice_id,
				concat('加總：') as date_order,
				sum(a.amount_gross) as amount_gross,
				sum(a.amount_net) as amount_net,
				(sum(a.amount_net)/sum(a.amount_gross)*100) as average_discount,
				sum(a.unpaid) as unpaid
			from
				invoice a
			where
				$filter");

}

if ($_GET['page'] == 'customer') {

	$report_description	= "<div style='float:left'>客戶 : " . sql_getValue("select name from customer where id='$filter_value'") . "</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";

	$columns			= array(
				"invoice_id"				=> "name:記錄編號		, width:150",
				"date_order"				=> "name:帳單日期		, width:100",
//				"paymentterms"				=> "name:付款方式		, width:120",
				"customer"					=> "name:客戶",
				"issueby"					=> "name:簽發人",
				"amount_gross"				=> "name:拆前總額		, class:number",
				"amount_net"				=> "name:拆後總額		, class:number",
				"average_discount"			=> "name:拆扣 (%)		, class:number",
				"unpaid"					=> "name:未付總數		, class:number",
							);



    $bar_width_max		= 400;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and a.customer_id='$filter_value'";

		if ($_SESSION['root'] == 0) $filterLevel .= " AND a.staff_group=".$_SESSION['group'];
	 $items			= sql_getTable(
     		"select
				a.invoice_id as invoice_id,
				a.date_order as date_order,
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
				$filter $filterLevel order by $orderby $ordertype");

	$columns_end		= sql_getObj("
			select
				concat('加總') as invoice_id,
				sum(a.amount_gross) as amount_gross,
				sum(a.amount_net) as amount_net,
				(sum(a.amount_net)/sum(a.amount_gross)*100) as average_discount,
				sum(a.unpaid) as unpaid
			from
				invoice a
			where
				$filter $filterLevel");

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
