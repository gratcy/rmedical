<?php

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory.php'");
if (empty($privilege->edit))	{	gotoURL("inventory.php"); exit; }

?>

<h3 class="pull-left">記錄</h3>

<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>


<?php


$site_id						= sql_secure($_GET['site_id']);
$site_name						= sql_getValue("select name from site where id='$site_id'");
$date_start						= sql_secure($_GET['date_start']);
$duration						= sql_secure($_GET['duration']);



$inputs		= new Inputs();
$inputs->add(
			'date_start'				, 'text'		, '1'					, '100%',
			'duration'					, 'select'		, '2'					, '100%'
			);


$options_duration					= array(5, 7, 10, 14);
$inputs->options['duration']		= $options_duration;

$inputs->value['date_start']		= $date_start;
$inputs->value['duration']			= $duration;

$inputs->tag['date_start']						= "class='form-control'";
$inputs->tag['duration']						= "class='form-control'";

echo <<<EOS



		<div class='pull-right noprint'>
			<!-- <input class='btn btn-default' type=button value='導出表格(E)' onclick='form2.submit()' /> -->
			<input class='btn btn-default' style='width:80px;' type=button value='返回 (B)' onClick="location.href='inventory.php';">
		</div>
		<br><br><br>



<form class="form-horizontal" id=search_box action='' method='GET'>
	<input type=hidden name=site_id value=$site_id>
	<div class='form-group'>
		<label for='site_name' class='col-sm-1 control-label'>名稱: </label>
		<div class='col-sm-2'>
		<span style='line-height: 32px;'>$site_name</span>
	 	</div>
		<label for='date_start' class='col-sm-1 control-label'>開始日期: </label>
		<div class='col-sm-2'>
			<div class='input-group'>
				$inputs->date_start
				<div class='input-group-addon' onclick=\"show_cal(this, 'date_start');\"><i class='fa fa-calendar-o'></i></div>
			</div>
		</div>
		<label for='duration' class='col-sm-2 control-label'>並列日數：: </label>
		<div class='col-sm-2'>
			$inputs->duration
		</div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=submit value='確定'>
    </div>
  </div>
</form>

EOS;


$date			= $date_start;

if (empty($date) or empty($duration)) {

	echo "<table class='table table-borderless table_form'>
				<tr height=50 bgcolor=white><td colspan=20 align=center>請選擇日期和並列日數。</td></tr>\r\n
		  </table>";
	exit;

}

$dates			= array();
for ($i = 0; $i < $duration; $i++) {
	$dates[$i]			= date("n 月 d 日", strtotime("+$i day", strtotime($date)));
	$between_date[$i]	= date("Y-n-d", strtotime("+$i day", strtotime($date)));
}


echo "<table class='table table-borderless table_form'>";
echo "		<tr>";
echo "			<th align=left>產品</th>";

foreach ($dates as $thisdate) {
	echo "			<th align=right colspan=3 style='border-left:solid 3px #aaaaaa;'>$thisdate</th>";
}

echo "		</tr>\r\n";
echo "		<tr>";
echo "			<th align=left></th>";

foreach ($dates as $thisdate) {
	echo "			<td align=right style='border-left:solid 3px #aaaaaa;'>賣</td>";
	echo "			<td align=right>調</td>";
	echo "			<td align=right>補</td>";
}
echo "		</tr>\r\n";



$it_ids				= sql_getArray("select id from inventory_transaction where site_from='$site_id' and `date` in ('" . implode("','", $between_date) . "') ");
if(!empty($it_ids)) {
	$item_ids 		= sql_getArray("select item_id from inventory_transaction_detail where inventory_transaction_id in (" . implode(",", $it_ids) . ") ");
	if (!empty($item_ids))
		$brands		= sql_getArray("select distinct b.id, b.description from item a join class_brand b on a.brand=b.id where a.id in (" . implode(",", $item_ids) . ") order by b.description asc");
}else
	echo "<tr height=50 bgcolor=white><td colspan=100 align=center>沒有記錄。</td></tr>\r\n";

$count			= 0;

if (!empty($brands)) {
	foreach ($brands as $brand_id => $brand_name) {

		$items		= sql_getTable("select id, name_short as name from item where brand='$brand_id' and id  in (" . implode(",", $item_ids) . ") order by name asc");

		foreach ($items as $item) {

			array2obj($item);

			$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

			echo "<tr bgcolor=$bgcolor><td><b>$brand_name</b> - $item->name</td>";

			foreach ($between_date as $thisdate) {

				$inventory_sold		= sql_getValue("select ifnull(sum(b.amount), 0) from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where a.type='sold' and a.site_from='$site_id' and b.item_id='$item->id' and a.date='$thisdate'");
				$inventory_adjust	= sql_getValue("select ifnull(sum(b.amount), 0) from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where a.type='調整' and a.site_from='$site_id' and b.item_id='$item->id' and a.date='$thisdate'");
				$inventory_supply 	= sql_getValue("select ifnull(sum(b.amount), 0) from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where a.type='返貨' and a.site_from='$site_id' and b.item_id='$item->id' and a.date='$thisdate'");

				$inventory_sold		= $inventory_sold * -1;

				if (empty($inventory_sold))		$inventory_sold		= '';
				if (empty($inventory_adjust))	$inventory_adjust		= '';
				if (empty($inventory_supply))	$inventory_supply		= '';

				echo "			<td align=right style='border-left:solid 3px #aaaaaa;'>$inventory_sold</td>";
				echo "			<td align=right>$inventory_adjust</td>";
				echo "			<td align=right>$inventory_supply</td>";
			}


			echo "</tr>";

		}

	}

}

echo "</table>";




include "footer.php";

?>
