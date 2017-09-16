<?php
if (isset($_GET['export']) && $_GET['export'] == 1)	ob_start();

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_customer_aging.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$export_link	= setQueryString(getURL(), "export=1");

$inputs		= new Inputs();
$inputs->add(
			'date_start'			, 'text'			, '1'		, '100%',
			'date_end'				, 'text'			, '2'		, '100%',
			'month'					, 'select2'			, '3'		, '100%',
			'period'				, 'select2'			, '3'		, '100%',
			'row'					, 'select2'			, '4'		, '100%'
						);

$inputs->options['period']				= array("一個月" => 1, "三個月" => 3, "半年" => 6, "一年" => 12, "二年" => 24, "三年" => 36, "五年" => 60);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;
$inputs->options['row']					= array("組別" => "staff_group", "員工" => "staff", "產品" => "item", "產品品牌" => "item_brand", "客戶" => "customer");

$inputs->tag['month']					= "onfocus='var date_select=document.getElementById(\"date_select_month\"); date_select.checked = true;'";
$inputs->tag['date_start']				= "onfocus='var date_select=document.getElementById(\"date_select_between\"); date_select.checked = true;'";
$inputs->tag['date_end']				= "onfocus='var date_select=document.getElementById(\"date_select_between\"); date_select.checked = true;'";
$inputs->tag['period']					= "onfocus='document.getElementById(\"date_select_period\").checked = true;'";


$inputs->value							= $_GET;

$defaul_date							= date("Y")."年".date("n")."月";

if (empty($_GET)) {
	gotoURL("report_customer_aging.php?date_select=period&period=3&page=staff_group");
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



$page						= sql_secure(popURL('page'));
$customer_id				= sql_secure(popURL('customer_id'));
$staff_id					= sql_secure(popURL('staff_id'));
$staff_group				= sql_secure(popURL('staff_group'));
$url						= getURL();

$filter_value				= sql_secure(popURL('filter_value'));




$filter						= 1;



$lang = lang('未付帳目');
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
		<label for='input_month' class='col-sm-2 control-label'>月份</label>
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
	<br>
	<br>

EOS;


?><?php
if (empty($page))			$page	= 'staff_group';

if ($page == 'staff_group')
	$options				= sql_getArray("select description, id from class_staff_group order by description asc");
if ($page == 'staff')
	$options				= sql_getArray("select a.name,  a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");


$inputs->clear();
$inputs->add('filter_value', 'select2', $filter_value, 'Filter value', $options, 80);


echo <<<EOS
<a class=tab href='{$url}&page=staff_group'>組別</a>
<a class=tab href='{$url}&page=staff'>銷售員</a>
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
<br>
EOS;

pushURL('page', $page);
pushURL('filter_value', $filter_value);




if (empty($period) or empty($month))			exit;

$period1_name		= "$date_start_period1~$date_satar_period0";
$period2_name		= "$date_start_period2~$date_start_period1";
$period3_name		= "$date_start_period3~$date_start_period2";

$filter				= "(c.date_order >= '$date_start_period3')";


if ($_GET['page'] == 'staff_group') {

	$group_name			= (!empty($filter_value)) ? sql_getValue("select description from class_staff_group where id='$filter_value'") : "全部";
	$report_description	= "<div style='float:left'>組別 : $group_name</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";

	$columns			= array(
				"customer_id"				=> "name:客戶編號		, width:120 , link:report_sell_detail.php",
				"description"				=> "name:類別",
				"name"						=> "name:客戶名稱",
				"staff_name"				=> "name:簽發人",
				"period1"					=> "name:$period1_name	, class:number",
				"period2"					=> "name:$period2_name	, class:number",
				"period3"					=> "name:$period3_name	, class:number",
				"total"						=> "name:總未付銷售額		, class:number"
							);

	$bar_width_max		= 500;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and staff_group='$filter_value'";

	$items				= sql_getTable("
			select
				a.customer_id as customer_id,
				b.description as `description`,
				a.name as `name`,
				(select name from staff where id=c.staff_id) as staff_name,
				sum(if(c.date_order >= '$date_start_period1', c.unpaid, 0)) as period1,
				sum(if(c.date_order >= '$date_start_period2' and c.date_order < '$date_start_period1', c.unpaid, 0)) as period2,
				sum(if(c.date_order < '$date_start_period2', c.unpaid, 0)) as period3,
				sum(c.unpaid) as total
			from
				customer a
				join class_customer b on a.`class`=b.id
				join invoice c on a.id=c.customer_id

			where
				$filter group by a.id HAVING sum(c.unpaid) !='0' order by $orderby $ordertype");

	$columns_end		= sql_getObj("
		select
			concat('記錄數：', count(*), ' 　　　') as customer_id,
			 '　加總' as name,
			 sum(if(c.date_order >= '$date_start_period1', c.unpaid, 0)) as period1,
			sum(if(c.date_order >= '$date_start_period2' and c.date_order < '$date_start_period1', c.unpaid, 0)) as period2,
			sum(if(c.date_order < '$date_start_period2', c.unpaid, 0)) as period3,
			sum(c.unpaid) as total
		from customer a join class_customer b on a.`class`=b.id join invoice c on a.id=c.customer_id where $filter");
}


if ($_GET['page'] == 'staff') {

	$report_description	= "<div style='float:left'>銷售員 : " . sql_getValue("select name from staff where id='$filter_value'") . "</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";

	$columns			= array(
				"customer_id"				=> "name:客戶編號		, width:120 , link:report_sell_detail.php",
				"description"				=> "name:類別",
				"name"						=> "name:客戶名稱",
				"staff_name"				=> "name:簽發人",
				"period1"					=> "name:$period1_name	, class:number",
				"period2"					=> "name:$period2_name	, class:number",
				"period3"					=> "name:$period3_name	, class:number",
				"total"						=> "name:總未付銷售額		, class:number"
							);

	$bar_width_max		= 500;

	get_orderby();

	if (!empty($filter_value))
		$filter		.= " and c.staff_id='$filter_value'";

	$items				= sql_getTable("
			select
				a.customer_id as customer_id,
				b.description as `description`,
				a.name as `name`,
				(select name from staff where id=c.staff_id) as staff_name,
				sum(if(c.date_order >= '$date_start_period1', c.unpaid, 0)) as period1,
				sum(if(c.date_order >= '$date_start_period2' and c.date_order < '$date_start_period1', c.unpaid, 0)) as period2,
				sum(if(c.date_order < '$date_start_period2', c.unpaid, 0)) as period3,
				sum(c.unpaid) as total
			from
				customer a
				join class_customer b on a.`class`=b.id
				join invoice c on a.id=c.customer_id
			where
				$filter group by a.id HAVING sum(c.unpaid) !='0' order by $orderby $ordertype");

	$columns_end		= sql_getObj("
		select
			concat('記錄數：', count(*), ' 　　　') as customer_id,
			 '　加總' as name,
			 sum(if(c.date_order >= '$date_start_period1', c.unpaid, 0)) as period1,
			sum(if(c.date_order >= '$date_start_period2' and c.date_order < '$date_start_period1', c.unpaid, 0)) as period2,
			sum(if(c.date_order < '$date_start_period2', c.unpaid, 0)) as period3,
			sum(c.unpaid) as total
		from customer a join class_customer b on a.`class`=b.id join invoice c on a.id=c.customer_id where $filter");
}

//~ var_dump($columns);die;
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
