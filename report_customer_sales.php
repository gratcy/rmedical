<?php
if (isset($_GET['export']) && $_GET['export'] == 1)	ob_start();

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_customer_sales.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$export_link	= setQueryString(getURL(), "export=1");

$inputs		= new Inputs();
$inputs->add(
			'period'				, 'select2'			, '3'		, '100%',
			'month'					, 'select2'			, '3'		, '100%',
			'row'					, 'select2'			, '4'		, '100%'
						);

$inputs->options['period']				= array("一個月" => 1, "三個月" => 3, "半年" => 6, "一年" => 12, "二年" => 24, "三年" => 36, "五年" => 60);
$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;

$defaul_date							= date("Y")."年".date("n")."月";

$inputs->value		= $_GET;

if (empty($_GET)) {
	gotoURL("report_customer_sales.php?period=1&month=$defaul_date");
	exit;
}


if (empty($_GET['month']))
	$_GET['month']	= $defaul_date;


$date_months		= str_replace("月", "", $_GET['month']);
$date_month         = explode("年", $date_months);
$year 				= trim($date_month[0]);
$month				= trim($date_month[1]);
$date_start			= "$year-$month-" . date("t", mktime(0, 0, 0, $month, 1, $year));
$date_satar_period0 = "$year-$month";

$url						= getURL();



$filter						= 1;



$lang = lang('客戶銷售');
$report_title				= $lang;


echo <<<EOS
<h3 class='pull-left'>$report_title</h3>
EOS;



if (true) {
		$period				= $_GET['period'] * 1;
        $date_start_period1	= date("Y-m", strtotime($date_start."-$period month"));
        $date_start_period2	= date("Y-m", strtotime($date_start."-" . ($period*2) . " month"));
        $date_start_period3	= date("Y-m", strtotime($date_start."-" . ($period*3) . " month"));
}





$excel = lang('導出表格');
echo <<<EOS
<input class="btn btn-default pull-right" type='button' value='$excel' onclick="window.location.href='$export_link'">
<br />
<br />
<br />

<h3 class='pull-left'>日期</h3>
<br><br><br>
<form action='' method=get class='form-horizontal'>
	<input type=hidden name=page value='$page'>
	<div class='form-group'>
		<label for='input_month' class='col-sm-2 control-label'>計算時間</label>
		<div class='col-sm-3'>
			$inputs->month
		</div>
		<label for='input_month' class='col-sm-2 control-label'>計算時間</label>
		<div class='col-sm-3'>
			$inputs->period
		</div>
		<div class='col-sm-1'>
			<input class="btn btn-default" type='submit' name='1' id='1' value='確定' />
		</div>
	</div>
</form>
<br>
<br>

EOS;



if (empty($period) or empty($month))			exit;


$period1_name		= "$date_start_period1~$date_satar_period0";	$period2_name	= "$date_start_period2~$date_start_period1";		$period3_name	= "$date_start_period3~$date_start_period2";


$filter				= "(c.date_order >= '$date_start_period3')";


if (true) {

	$columns			= array(
				"customer_id"				=> "name:客戶編號		, width:120 , link:report_sell_detail.php",
				"name"						=> "name:客戶名稱",
				"period1"					=> "name:$period1_name	, class:number",
				"period2"					=> "name:$period2_name	, class:number",
				"period3"					=> "name:$period3_name	, class:number",
				"total"						=> "name:總銷售額		, class:number"
							);

	$bar_width_max		= 500;

	get_orderby();

	$items				= sql_getTable("
			select
				a.customer_id as customer_id,
				a.name as `name`,
				sum(if(c.date_order >= '$date_start_period1', c.amount_net + c.amount_cash, 0)) as period1,
				sum(if(c.date_order >= '$date_start_period2' and c.date_order < '$date_start_period1', c.amount_net + c.amount_cash, 0)) as period2,
				sum(if(c.date_order < '$date_start_period2', c.amount_net + c.amount_cash, 0)) as period3,
				sum(c.amount_net + c.amount_cash) as total
			from
				customer a
				join invoice c on a.id=c.customer_id
			where
				$filter group by a.id order by $orderby $ordertype");

		$columns_end		= sql_getObj("
		select
			concat('記錄數：', count(*), ' 　　　') as customer_id,
			 '　加總' as name,
			sum(if(c.date_order >= '$date_start_period1', c.amount_net + c.amount_cash, 0)) as period1,
			sum(if(c.date_order >= '$date_start_period2' and c.date_order < '$date_start_period1', c.amount_net + c.amount_cash, 0)) as period2,
			sum(if(c.date_order < '$date_start_period2', c.amount_net + c.amount_cash, 0)) as period3,
			sum(c.amount_net + c.amount_cash) as total
		from customer a join invoice c on a.id=c.customer_id where $filter");
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
