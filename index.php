<?php

require_once "header.php";

include_once "bin/class_inputs.php";
//~ include_once "bin/class_csi.php";

//backup sql table
//include_once "db_backup_table.php";

//backup inventrory
//~ include_once "backup_inventory.php";

//~ sql_query("delete from invoice_detail where invoice_id not in (select id from invoice) and date_order < now()");


$inputs		= new Inputs();
$inputs->add(
			'month'					, 'select'			, '3'		, '155',
			'recent'				, 'select'			, '3'		, '155'
						);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;

$inputs->options['recent']				= array("一個月" => 1,"兩個月" => 2, "三個月" => 3, "半年" => 6, "一年" => 12);


$inputs->tag['month']					= "onfocus='document.getElementById(\"date_select_month\").checked = true;'";
$inputs->tag['recent']					= "onfocus='document.getElementById(\"date_select_recent\").checked = true;'";


$inputs->value				= $_GET;

$filter						= 1;

$filter_value	= sql_getValue("select id from staff where staff_id='$user->staff_id'");
$lang = lang('首頁');
echo <<<EOS

<h3>$lang</h3><br>

EOS;




$date_select				= $_GET['date_select'];
$date_checked_1				= ($date_select == 'recent') ? 'checked' : '';
$date_checked_2				= ($date_select == 'month') ? 'checked' : '';
$date_checked_3				= ($date_select == 'between') ? 'checked' : '';

$recent				= $_GET['recent'] * 1;
if (empty($recent)) $recent = 3;

$date_start			= date("Y-m-d", strtotime("-$recent month"));
$date_end			= date("Y-m-d");

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

<!--
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
			<input class='btn btn-default' type='submit' name='1' id='1' value='確定' />
		</td>
	</tr>
</table>


<br>
<br>

-->

EOS;


?><?php




$date_end			= end($dates);
$date_start			= reset($dates);


$date_end			= date("Y-m-d");
$date_start			= date("Y-m-d", strtotime("-3 month"));


//echo $date_end."<br />".$date_start;
//echo $user->staff_id;
//exit;



$filter_date		= "(date_order>= '$date_start' and date_order<= '$date_end')";

$staff_id			= sql_getValue("select id from staff where staff_id='$user->staff_id'");
$filter_staff		= " and staff_id='$staff_id'";


/////////////////////////////////////////////////////
// Staff Info
/////////////////////////////////////////////////////

$customer = sql_getTable("SELECT COUNT(*) as total FROM customer_tab WHERE cstatus=1");
$distributor = sql_getTable("SELECT COUNT(*) as total FROM customer WHERE status!='deleted'");
$product = sql_getTable("SELECT COUNT(*) as total FROM item WHERE status!='deleted'");
$invoice = sql_getTable("SELECT COUNT(*) as total FROM invoice");

$info				= $user;
echo <<<EOS
<div class='table-responsive'>
<table class='table table-borderless table_form'>
	<tr>
		<td style="white-space:normal">
        <div class="col-lg-12 col-xs-12">
			<h3>Welcome <b>$info->name</b> to System Information Rock Medical.</h3>
		</div>
		</td>
	</tr>
EOS;

$lang_customer = lang('终端客户');
$lang_distributor = lang('客戶資料');
$lang_product = lang('產品資料');
$lang_invoice = lang('出單');


echo <<<EOS
	<tr>
	<td>
	
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <h3>{$customer[0]['total']}</h3>

              <p>$lang_customer</p>
            </div>
            <div class="icon">
              <i class="ion ion-person"></i>
            </div>
            <a href="/customers.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <h3>{$distributor[0]['total']}</h3>

              <p>$lang_distributor</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="/customer.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3>{$product[0]['total']}</h3>

              <p>$lang_product</p>
            </div>
            <div class="icon">
              <i class="ion ion-ipad"></i>
            </div>
            <a href="/item.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3>{$invoice[0]['total']}</h3>

              <p>$lang_invoice</p>
            </div>
            <div class="icon">
              <i class="ion ion-paper-airplane"></i>
            </div>
            <a href="/invoice.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
	</td>
	</tr>
	</table>

EOS;
include_once "footer.php";
