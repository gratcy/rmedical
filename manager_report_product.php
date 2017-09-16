<?php

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='manager_report_product.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

//include "privilege_special.php";


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
	gotoURL("manager_report_product.php?date_select=recent&recent=1&page=staff_group");
	exit;
}




$inputs->value				= $_GET;


$page						= sql_secure(popURL('page'));
$filter_value				= sql_secure(popURL('filter_value'));
$url						= getURL();



$filter						= 1;




$report_title				= "產品銷售報告（管理員版）";


echo <<<EOS
<h3 class='pull-left'>$report_title</h3>
<br>
<br>
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
		<label for='input_month' class='col-sm-2 control-label'><input id='date_select_between' type='radio' name='date_select' id='2' value='between' $date_checked_3/> 開始</label>
		<div class='col-sm-3'>
			<div class="input-group">
				$inputs->date_start
				<div class="input-group-addon" onclick="show_cal(this, 'date_start');"><i class="fa fa-calendar-o"></i></div>
			</div>
		</div>
		<label for='input_month' class='col-sm-1 control-label'>結束</label>
		<div class='col-sm-3'>
			<div class="input-group">
				$inputs->date_end
				<div class="input-group-addon" onclick="show_cal(this, 'date_end');"><i class="fa fa-calendar-o"></i></div>
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


$show_type						= $_GET['show_type'] * 1;
$staff_group					= $_GET['staff_group'] * 1;
$staff_id						= $_GET['staff_id'] * 1;
$customer_id					= $_GET['customer_id'] * 1;
$brand_id						= $_GET['brand_id'] * 1;

if (empty($show_type))		$show_type	= 1;

$option_show_type				= array("簡單報告" => 1, "詳細報告" => 2);
$option_staff_group				= sql_getArray("select description, id from class_staff_group order by description asc");
$option_staff_id				= sql_getArray("select name, id from staff order by `class`, name");
$option_customer_id				= sql_getArray("select name, id from customer order by name asc");
$option_brand_id				= sql_getArray("select description, id from class_brand order by description asc");


$inputs->clear();
$inputs->add('show_type'	, 'radio'		, $show_type	, 'Filter value', $option_show_type		, 60);
$inputs->add('staff_group'	, 'select2', $staff_group	, 'Filter value', $option_staff_group	, 60);
$inputs->add('staff_id'		, 'select2', $staff_id		, 'Filter value', $option_staff_id		, 60);
$inputs->add('customer_id'	, 'select2', $customer_id	, 'Filter value', $option_customer_id	, 60);
$inputs->add('brand_id'		, 'select2', $brand_id		, 'Filter value', $option_brand_id		, 60);


echo <<<EOS
	<div class='form-group'>
    <label for='input_show_type' class='col-sm-2 control-label'>顯示</label>
		<div class='col-sm-8'>
			$inputs->show_type
		</div>
	</div>
	<div class='form-group'>
    <label for='input_staff_group' class='col-sm-2 control-label'>組別</label>
		<div class='col-sm-8'>
			$inputs->staff_group
		</div>
	</div>
	<div class='form-group'>
    <label for='input_staff_id' class='col-sm-2 control-label'>員工</label>
		<div class='col-sm-8'>
			$inputs->staff_id
		</div>
	</div>
	<div class='form-group'>
    <label for='input_customer_id' class='col-sm-2 control-label'>客戶</label>
		<div class='col-sm-8'>
			$inputs->customer_id
		</div>
	</div>
	<div class='form-group'>
    <label class='col-sm-2 control-label'>品牌</label>
		<div class='col-sm-8'>
			$inputs->brand_id
			<br>
			<input class='btn btn-default' type='submit' value='確定'>
		</div>
	</div>
<br>
<br>
EOS;




if (empty($dates))			exit;







$date_end			= end($dates);
$date_start			= reset($dates);

$filter				= "(a.date_order >= '$date_start' and a.date_order <= '$date_end')";
if (!empty($staff_group))		$filter		.= " and a.staff_group='$staff_group'";
if (!empty($staff_id))			$filter		.= " and a.staff_id='$staff_id'";
if (!empty($customer_id))		$filter		.= " and a.customer_id='$customer_id'";
if (!empty($brand_id))			$filter		.= " and a.item_brand='$brand_id'";



if ($show_type == 1) {

	$report_description	= "<div style='float:left'>";
	if (!empty($staff_group))
		$report_description	.= "組別 : " . sql_getValue("select description from class_staff_group where id='$staff_group'") . " &nbsp;  &nbsp;";
	if (!empty($staff_id))
		$report_description	.= "員工 : " . sql_getValue("select name from staff where id='$staff_id'") . " &nbsp;  &nbsp;";
	if (!empty($customer_id))
		$report_description	.= "客戶 : " . sql_getValue("select name from customer where id='$customer_id'") . " &nbsp;  &nbsp;";
	if (!empty($brand_id))
		$report_description	.= "品牌 : " . sql_getValue("select description from class_brand where id='$brand_id'") . " &nbsp;  &nbsp;";
	$report_description	.= "</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";


	$columns			= array(
				"name_series"					=> "name:系列",
				"name"							=> "name:名稱",
				"quantity"						=> "name:數量			, class:number",
				"average_price"					=> "name:平均折前售價	, class:number",
				"average_price_discounted"		=> "name:平均折後售價	, class:number",
				"cost"							=> "name:採購價			, class:number",
				"discount_percentage"			=> "name:平均拆扣		, class:number",
							);

	$bar_width_max		= 300;

	get_orderby();



	$items				= sql_getTable("
			select
				b.name_series as name_series,
				b.name_subitem as name_subitem,
				b.name as name,
				a.item_quantity as quantity,
				sum(a.average_price) as average_price,
				sum(a.average_price_discounted) as average_price_discounted,
				b.cost as cost,
				avg(a.discount_percentage)*100 as discount_percentage
			from
				(
					select
						a.*,
						sum(quantity) as item_quantity,
						sum(price*quantity)/sum(quantity) as average_price,
						sum(amount_discounted)/sum(quantity) as average_price_discounted,
						sum(amount_discounted) / sum(price*quantity) as discount_percentage
					from
						invoice_detail a
					where
						$filter and a.item_id != 0
					group by a.item_id
				) a
			join
				item b on a.item_id=b.id

			group by b.name_series asc, b.name_subitem asc
			with rollup
						");

	$other_item			= array(
									"name_series"			=> "其他雜項",
									"name_subitem"			=> "",
									"name"					=> "其他雜項",
								);

	array_splice($items, count($items)-1, 0, array($other_item, $other_item));


	$count				= sql_getValue("
					select count(*) from
					(
						select
							distinct a.item_id
						from
							invoice_detail a
						where
							$filter
					) b
						");

	$columns_end		= array_pop($items);
	$columns_end['name_series']		= "總產品數：$count  　　　　加總：";
	array2obj($columns_end);


	$columns_end		= sql_getObj("
			select
				concat('總產品數：', count(distinct a.item_id), '') as name_series,
				 '　加總' as name_subitem,
				sum(a.quantity) as quantity,
				'N/A' as average_price,
				sum(a.price*a.quantity) as sum_price,
				'N/A' as average_price_discounted,
				sum(a.amount_discounted) as sum_price_discounted,
				avg(b.cost * a.quantity) as cost,
				avg(a.amount_discounted / (a.price * a.quantity))*100 as discount_percentage
			from
				invoice_detail a join item b on a.item_id=b.id
			where
				$filter
					");


}



if ($show_type == 2) {

	$report_description	= "<div style='float:left'>";
	if (!empty($staff_group))
		$report_description	.= "組別 : " . sql_getValue("select description from class_staff_group where id='$staff_group'") . " &nbsp;  &nbsp;";
	if (!empty($staff_id))
		$report_description	.= "員工 : " . sql_getValue("select name from staff where id='$staff_id'") . " &nbsp;  &nbsp;";
	if (!empty($customer_id))
		$report_description	.= "客戶 : " . sql_getValue("select name from customer where id='$customer_id'") . " &nbsp;  &nbsp;";
	if (!empty($brand_id))
		$report_description	.= "品牌 : " . sql_getValue("select description from class_brand where id='$brand_id'") . " &nbsp;  &nbsp;";
	$report_description	.= "</div><div style='float:right'>日期 : $date_start 至 $date_end</div>";


	$columns			= array(
				"name_series"					=> "name:系列",
				"name"							=> "name:名稱",
				"quantity"						=> "name:數量			, class:number",
				"average_price_discounted"		=> "name:平均折後售價	, class:number",
				"sum_price_discounted"			=> "name:總折後售價		, class:number",
				"cost"							=> "name:採購價			, class:number",
				"cost_amount"					=> "name:總成本			, class:number",
				"profit"						=> "name:利潤			, class:number",
				"discount_percentage"			=> "name:平均拆扣		, class:number",
							);

	$bar_width_max		= 300;

	get_orderby();


	$items				= sql_getTable("
			select
				b.name_series as name_series,
				b.name_subitem as name_subitem,
				b.name as name,
				a.item_quantity as quantity,
				sum(a.average_price) as average_price,
				sum(a.sum_price) as sum_price,
				sum(a.average_price_discounted) as average_price_discounted,
				sum(a.sum_price_discounted) as sum_price_discounted,
				b.cost as cost,
				sum(b.cost * a.item_quantity) as cost_amount,
				sum(a.sum_price_discounted) - sum(b.cost * a.item_quantity) as profit,
				avg(a.discount_percentage)*100 as discount_percentage
			from
				(
					select
						*,
						sum(quantity) as item_quantity,
						sum(price*quantity)/sum(quantity) as average_price,
						sum(price*quantity) as sum_price,
						sum(amount_discounted)/sum(quantity) as average_price_discounted,
						sum(amount_discounted) as sum_price_discounted,
						sum(amount_discounted) / sum(price*quantity) as discount_percentage
					from
						invoice_detail a
					where
						$filter and item_id != 0
					group by item_id
				) a
			join
				item b on a.item_id=b.id

			group by b.name_series asc, b.name_subitem asc
			with rollup
						");

	$other_item			= sql_getObj("select
											sum(quantity) as quantity,
											sum(price*quantity) as sum_price,
											sum(amount_discounted) as sum_price_discounted
										from invoice_detail a where $filter and item_id = 0 ");

	$other_item			= array(
									"name_series"			=> "其他雜項",
									"name_subitem"			=> "",
									"name"					=> "其他雜項",
									"quantity"				=> $other_item->quantity,
									"sum_price"				=> $other_item->sum_price,
									"sum_price_discounted"	=>	$other_item->sum_price_discounted
								);

	array_splice($items, count($items)-1, 0, array($other_item, $other_item));


	$count				= sql_getValue("
					select count(*) from
					(
						select
							distinct a.item_id
						from
							invoice_detail a
						where
							$filter
					) b
						");

	$columns_end		= array_pop($items);
	$columns_end['name_series']		= "總產品數：$count  　　　　加總：";
	array2obj($columns_end);


	$columns_end		= sql_getObj("
			select
				concat('總產品數：', count(distinct a.item_id), '') as name_series,
				 '　加總' as name_subitem,
				sum(a.quantity) as quantity,
				'N/A' as average_price_discounted,
				sum(a.amount_discounted) as sum_price_discounted,
				'N/A' as cost,
				sum(b.cost * a.quantity) as cost_amount,
				sum(a.amount_discounted) - sum(b.cost * a.quantity) as profit,
				avg(a.amount_discounted / (a.price * a.quantity))*100 as discount_percentage
			from
				invoice_detail a join item b on a.item_id=b.id
			where
				$filter
					");


}




/////////////////////////////////////////////////////
// Display Statistics
/////////////////////////////////////////////////////

include "report_list2.php";




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
