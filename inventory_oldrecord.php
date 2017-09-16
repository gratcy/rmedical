<?php

include_once "header.php";
include_once "bin/class_inputs.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory.php'");
if (empty($privilege->edit))	{	gotoURL("site.php"); exit; }

function get_number_array($min, $max, $padding = 0) {

	$result				= array();

	for ($i = $min; $i <= $max; $i++) {

		$key				= padding($i, $padding);

		$result[$key]		= $key;

	}

	return $result;

}


echo "<h3 class='pull-left'>過往記錄</h3>";

$filter_value				= sql_secure(popURL('filter_value'));
$site_name					= sql_secure($_GET['input_pulldownmenu_filter_value']);
$date						= sql_secure($_GET['date']);
$day						= sql_secure($_GET['day']);
$duration					= sql_secure($_GET['duration']);

$options					= sql_getArray("select name, id from site order by name asc");
$options_date				= sql_getArray("select distinct concat(year(backup_date), ' 年 ', month(backup_date), ' 月    ') from backup_inventory order by backup_date desc");

$options_duration			= array(5, 7, 10, 14);


$data1		= explode("年",$date);
$year		= trim($data1[0]);
$data2		= explode("月",$data1[1]);
$month		= trim($data2[0]);


if (!empty($year) and !empty($month)) {
	$backup_date			= str_replace(" ", "", $year."-".$month);

	$options_day			= sql_getArray("select distinct day(backup_date) from backup_inventory where backup_date like '%$backup_date%' order by backup_date desc") ;
}

$inputs		= new Inputs();

$inputs->add('date', 'select', $date, 'date value', $options_date, '100%');
$inputs->add('day', 'select', $day, 'month value', $options_day, '100%');
$inputs->add('duration', 'select', $duration, 'duration value', $options_duration, '100%');
$inputs->add('filter_value', 'pulldownmenu', $filter_value, 'Filter value', $options, '100%');


$inputs->tag['date']					= "class='form-control'";
$inputs->tag['day']					= "class='form-control'";
$inputs->tag['duration']					= "class='form-control'";
$inputs->tag['filter_value']					= "class='form-control'";
$excel = lang('導出表格');
echo <<<EOS


		<div class='pull-right'>
			<input class='btn btn-default' type=button value='$excel (E)' onclick='form2.submit()' />
			<input class='btn btn-default' type=button value='返回 (B)' onclick='history.go(-1);'>
		</div>
		<br><br><br>

<form class='form-horizontal' name=form1 action='' method=get>
	<div class='form-group'>
    <label for='input_date' class='col-sm-1 control-label'>月份：</label>
		<div class='col-sm-2'>
			$inputs->date
		</div>
		<div class='col-sm-1'>
			<input class='btn btn-default' type=submit value='確定'>
		</div>
	</div>
	<div class='form-group'>
    <label for='input_day' class='col-sm-1 control-label'>日期：</label>
		<div class='col-sm-2'>
			$inputs->day
		</div>
    <label for='input_duration' class='col-sm-1 control-label'>並列日數：</label>
		<div class='col-sm-2'>
			$inputs->duration
		</div>
    <label for='input_filter_value' class='col-sm-1 control-label'>地點</label>
		<div class='col-sm-3'>
			$inputs->filter_value
		</div>
		<div class='col-sm-1'>
			<input class='btn btn-default' type=submit value='確定'>
		</div>
	</div>
</form>

EOS;




$date			= $year . "-" . padding($month, 2) . "-" . padding($day, 2);


$filename		= "record/inventory-$date.csv";
$filename		= str_replace(" ", "", $filename);

if (!is_file($filename)) {

	echo "<table class='table table-borderless table_form'>
				<tr height=50 bgcolor=white><td colspan=20 align=center>請選擇日期和地點。</td></tr>\r\n
		  </table>";
	exit;

}


if ($duration > 0) {
	include_once "inventory_oldrecord_table.php";
	exit;
}


include "bin/class_csv.php";
$csv		= new CSV_Reader($filename);
$csv->getRow();

$data		= array();
while ($row = $csv->getRow()) {
	if ($row[1] != $site_name)	continue;
	$branditem[$row[2]]++;
	$data[]	= $row;

}


if (empty($data)) {

	echo "<table class='table table-borderless table_form'>
				<tr height=50 bgcolor=white><td colspan=20 align=center>沒有記錄。</td></tr>\r\n
		  </table>";
	exit;
}



echo <<<EOS

<table class='table table-borderless'>
	<tr>
    <form id=form2 name=form2 method=post action='inventory_oldrecord_export.php' style='margin:0px;'>
		<td align=right>
            <input type=hidden name=filename value='$filename' />
            <input type=hidden name=site_name value='$site_name' />
            <input type=hidden name=page value='inventory_oldrecord' />
		</td>
      </form>
	</tr>
</table>


<table class='table table-borderless table_form'>

EOS;

echo "<tr><td valign=top>";
echo "	<table class='table table-borderless'>";

echo "		<colgroup>";
echo "			<col width=10% align=left>";
echo "			<col width=5% align=left>";
echo "			<col width=5% align=left>";
echo "			<col width=10% align=left>";
echo "			<col width=5%  align=left>";
echo "			<col width=5% align=left>";
echo "			<col width=10% align=left>";
echo "			<col width=5% align=left>";
echo "			<col width=5% align=left>";
echo "			</colgroup>\r\n";

echo "		<tr>";
echo "			<th>產品</th>";
echo "			<th>貨存</th>";
echo "			<th></th>";
echo "			<th>產品</th>";
echo "			<th>貨存</th>";
echo "			<th></th>";
echo "			<th>產品</th>";
echo "			<th>貨存</th>";
echo "			<th></th>";
echo "		</tr>\r\n";





foreach ($branditem as $brand_name => $count_item) {

	echo "<tr><td colspan=9><strong><font color=#3377dd>$brand_name</font></strong></td></tr>";

	$count		= 0;
	echo "<tr>";
	foreach ($data as $item) {

		if ($item[2] != $brand_name)  continue;

		$item_name		= $item[3];
		$stock			= $item[4];


		$stock_color	= ($stock < 0) ? "red" : "#444444";

		echo "<td>$item_name</td>";
		echo "<td align=left><input type=text value='$stock' size=1 readonly style='color:$stock_color' class=number></td>";
		echo "<td width=10></td>";

		$count++;

		if ($count++ % 3 == 0)
			echo "</tr>";

	}

	if($count > $count_item) echo "</tr>";

	echo "<tr><td width=1 colspan=20 bgcolor='#999999'></td></tr>";

}
echo "</table></td>";

?>
	</tr>
</table>


<script>

shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });


</script>

<?php

include "footer.php";

?>
