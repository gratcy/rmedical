<?php

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='report_staff_summery.php'");
if (empty($privilege->view))	{	gotoURL("index.php"); exit; }



$inputs		= new Inputs();
$inputs->add(
			'date_start'			, 'text'			, '1'		, '100%',
			'date_end'				, 'text'			, '2'		, '100%',
			'month'					, 'select2'			, '3'		, '100%',
			'recent'				, 'select2'			, '3'		, '100%'
						);

$inputs->options['month']				= sql_getArray("select distinct concat(year(date_order), ' 年 ', month(date_order), ' 月    ') from invoice where date_order > 0 order by date_order desc") ;

$inputs->options['recent']				= array("一個月" => 1,"兩個月" => 2, "三個月" => 3, "半年" => 6, "一年" => 12);


$inputs->tag['month']					= "onfocus='document.getElementById(\"date_select_month\").checked = true;'";
$inputs->tag['recent']					= "onfocus='document.getElementById(\"date_select_recent\").checked = true;'";
$inputs->tag['date_start']				= "onfocus='document.getElementById(\"date_select_between\").checked = true;'";
$inputs->tag['date_end']				= "onfocus='document.getElementById(\"date_select_between\").checked = true;'";


if (empty($_GET)) {
	gotoURL("report_staff_summery.php?date_select=recent&recent=3&page=staff_group");
	exit;
}




$inputs->value				= $_GET;


$page						= sql_secure(popURL('page'));
$filter_value				= sql_secure(popURL('filter_value'));
$url						= getURL();



$filter						= 1;



$lang = lang('員工概覽');

$report_title				= $lang;


echo <<<EOS
<h3 class='pull-left'>$report_title</h3>
EOS;





$date_select				= $_GET['date_select'];
$date_checked_1				= ($date_select == 'recent') ? 'checked' : '';
$date_checked_2				= ($date_select == 'month') ? 'checked' : '';
$date_checked_3				= ($date_select == 'between') ? 'checked' : '';

//if ($date_checked_1 == 'checked') {
		$recent				= $_GET['recent'] * 1;
        $date_start			= date("Y-m-d", strtotime("-$recent month"));
        $date_end			= date("Y-m-d");
/*} else if ($date_checked_2 == 'checked') {
        $date_months		= str_replace("月", "", $_GET['month']);
        $date_month         = explode("年", $date_months);
        $year 				= trim($date_month[0]);
        $month				= trim($date_month[1]);
        $date_start			= "$year-$month-01";
        $date_end			= "$year-$month-" . date("t", mktime(0, 0, 0, $month, 1, $year));
} else if ($date_checked_3 == 'checked') {
        $date_start			= $_GET['date_start'];
        $date_end			= $_GET['date_end'];
}*/


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


<br><br><br>
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

$inputs->clear();
$inputs->add('filter_value', 'select2', $filter_value, 'Filter value', $options, '100%');


echo <<<EOS
<a class=tab href='{$url}&page=staff_group'>組別</a>
<a class=tab href='{$url}&page=staff'>員工</a>
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

?><?php

pushURL('page', $page);
pushURL('filter_value', $filter_value);


if (empty($dates))			exit;
if (empty($filter_value))	exit;


$date_end			= end($dates);
$date_start			= reset($dates);


$date_end			= date("Y-m-d");
$date_start			= date("Y-m-d", strtotime("-3 month"));


//echo $date_end."<br />".$date_start;
//exit;



$filter_date		= "(date_order>= '$date_start' and date_order<= '$date_end')";


$filter_staff		= ($page == 'staff_group') ? "and staff_group='$filter_value'" : "and staff_id='$filter_value'";


/////////////////////////////////////////////////////
// Display Statistics
/////////////////////////////////////////////////////


echo "<table class='table table-borderless table_form'>";
if ($page == 'staff') {
    echo "<tr>
              <td colspan=2>";

        $info				= sql_getObj("select * from staff where id='$filter_value'");

		include "report_staff_info.php";

    	 echo "</td>
          </tr>";
}

    echo "	<tr>";


echo "		<td width=50% valign=top>";



{

    $row	= array();

	$count	= $recent;
	if ($recent==6)		$count	=4;
	if ($recent==12)		$count	=5;


	$period				= 1;
    $date_start_period0 = date("Y-m-d");
    $date_start_period1	= date("Y-m-d", strtotime("-$period month"));
    $date_start_period2	= date("Y-m-d", strtotime("-" . ($period*2) . " month"));
    $date_start_period3	= date("Y-m-d", strtotime("-" . ($period*3) . " month"));
    $date_start_period4	= date("Y-m-d", strtotime("-" . ($period*6) . " month"));
    $date_start_period5	= date("Y-m-d", strtotime("-" . ($period*12) . " month"));



    for($i=1; $i<= $count; $i++){

  $row[0]			= sql_getVar("select '$date_start_period0 ~ $date_start_period1' as '時間',  sum(quantity_sum) as '總銷售數', sum(amount_net) as '淨總額' from invoice where (date_order >'$date_start_period1' and date_order < '$date_start_period0') $filter_staff");

  $row[1]			= sql_getVar("select '$date_start_period1 ~ $date_start_period2' as '時間',  sum(quantity_sum) as '總銷售數', sum(amount_net) as '淨總額' from invoice where (date_order >'$date_start_period2' and date_order < '$date_start_period1') $filter_staff");

  $row[2]			= sql_getVar("select '$date_start_period2 ~ $date_start_period3' as '時間',  sum(quantity_sum) as '總銷售數', sum(amount_net) as '淨總額' from invoice where (date_order >'$date_start_period3' and date_order < '$date_start_period2') $filter_staff");

  $row[3]			= sql_getVar("select '$date_start_period3 ~ $date_start_period4' as '時間',  sum(quantity_sum) as '總銷售數', sum(amount_net) as '淨總額' from invoice where (date_order >'$date_start_period4' and date_order < '$date_start_period3') $filter_staff");

 $row[4]			= sql_getVar("select '$date_start_period4 ~ $date_start_period5' as '時間',  sum(quantity_sum) as '總銷售數', sum(amount_net) as '淨總額' from invoice where (date_order >'$date_start_period5' and date_order < '$date_start_period4') $filter_staff");


    }

	echo "<span class=report_summery_title>$recent 個月銷售額</span>";
	show_table($row);

}


echo "		</td>";
echo "		<td width=50% valign=top>";



{

	$data			= sql_getTable("select b.name as '產品', sum(a.quantity) as '數量', sum(a.amount_discounted) as '總數' from invoice_detail a join item b on a.item_id=b.id where $filter_date $filter_staff group by a.item_id order by sum(a.amount_discounted) desc limit 3");
	echo "<span class=report_summery_title>$recent 個月銷量最高產品</span>";
	show_table($data);



}




echo "	</tr>";
echo "	<tr>";
echo "		<td width=50% valign=top>";

{



    $row     = array();

	$count	= $recent;
		if ($recent==6)		$count	=4;
		if ($recent==12)		$count	=5;


	$period				= 1;
    $date_start_period0 = date("Y-m-d");
    $date_start_period1	= date("Y-m-d", strtotime("-$period month"));
    $date_start_period2	= date("Y-m-d", strtotime("-" . ($period*2) . " month"));
    $date_start_period3	= date("Y-m-d", strtotime("-" . ($period*3) . " month"));
    $date_start_period4	= date("Y-m-d", strtotime("-" . ($period*6) . " month"));
    $date_start_period5	= date("Y-m-d", strtotime("-" . ($period*12) . " month"));



    for($i=1; $i<= $count; $i++){

	$row[0]			= sql_getVar("select '$date_start_period0 ~ $date_start_period1' as '時間',  sum(unpaid) as '總數' from invoice where (date_order >'$date_start_period1' and date_order < '$date_start_period0') $filter_staff");

	$row[1]			= sql_getVar("select '$date_start_period1 ~ $date_start_period2' as '時間',  sum(unpaid) as '總數' from invoice where (date_order >'$date_start_period2' and date_order < '$date_start_period1') $filter_staff");

	$row[2]			= sql_getVar("select '$date_start_period2 ~ $date_start_period3' as '時間',   sum(unpaid) as '總數' from invoice where (date_order >'$date_start_period3' and date_order < '$date_start_period2') $filter_staff");

	$row[3]			= sql_getVar("select '$date_start_period3 ~ $date_start_period4' as '時間',   sum(unpaid) as '總數' from invoice where (date_order >'$date_start_period4' and date_order < '$date_start_period3') $filter_staff");

	$row[4]			= sql_getVar("select '$date_start_period4 ~ $date_start_period5' as '時間',   sum(unpaid) as '總數' from invoice where (date_order >'$date_start_period5' and date_order < '$date_start_period4') $filter_staff");


    }



	echo "<span class=report_summery_title>未找數額</span>";
	show_table($row);



}



echo "		</td>";
echo "		<td width=50% valign=top>";



{

	$data			= sql_getTable("select b.name as '客戶', sum(a.quantity) as '數量', sum(a.amount_discounted) as '總數' from invoice_detail a join customer b on a.customer_id=b.id where $filter_date and a.staff_id='$filter_value' group by a.customer_id order by sum(a.amount_discounted) desc limit 3");
	echo "<span class=report_summery_title>$recent 個月銷量最高客戶</span>";
	show_table($data);


}




echo "	</tr>";
echo "</table>";

exit;





function show_table($data) {

	echo "<table class='table table-borderless' bgcolor='#aaaaaa'>";

	echo "<tr bgcolor=#ffffff>";
	$title	= current($data);
	if (is_array($title))
	foreach ($title as $key => $value) {
		echo "<th>$key</th>";
	}
	echo "</tr>\r\n";

	$count	= 0;
	foreach ($data as $row) {

		$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

		echo "<tr bgcolor=$bgcolor>";
		if (is_array($row) || is_object($row))
		foreach ($row as $col) {
			$class		= '';
			if (empty($col))
				$col 	= '&nbsp;';
			if (is_numeric($col)) {
				$col	= number_format($col, 0);
				$class	= 'class=number';
			}
			echo "<td $class>$col</td>";
		}
		echo "</tr>\r\n";
		$count++;
	}
	echo "</table>";

}



include_once "footer.php";

?>
