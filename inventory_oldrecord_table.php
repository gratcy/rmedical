<?php

$dates			= array();
for ($i = 0; $i < $duration; $i++) {
	$dates[$i]	= date("n 月 d 日", strtotime("+$i day", strtotime($date)));
}


echo "<table class='table table-borderless table_form'>";


/*		This will ruin the row background color
echo "		<colgroup>";
echo "			<col align=left />";
foreach ($dates as $thisdate) {
	echo "			<col align=right />";
}
echo "		</colgroup>\r\n";
*/



echo "		<tr>";
echo "			<th align=left>產品</th>";
foreach ($dates as $thisdate) {
	echo "			<th align=right>$thisdate</th>";
}
echo "		</tr>\r\n";




include_once "bin/class_csv.php";

$data			= array();

for ($i = 0; $i < $duration; $i++) {

	$thisdate	= date("Y-m-d", strtotime("+$i day", strtotime($date)));

	$filename	= "record/inventory-$thisdate.csv";

	if (!is_file($filename))		continue;

	$csv		= new CSV_Reader($filename);
	$csv->getRow();

	while ($row = $csv->getRow()) {
		if ($row[1] != $site_name)	continue;
		$data["<b>" . $row[2] . "</b> - " . $row[3]][$i]	= $row[4];
	}
	$csv->close();

}



$count			= 0;

foreach ($data as $item => $row) {

	$skip		= true;
	foreach ($row as $amount) {
		if ($amount != 0)
			$skip		= false;
	}

	if ($skip)		continue;

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	echo "<tr bgcolor=$bgcolor>";
	echo "<td>$item</td>";

	for ($i = 0; $i < $duration; $i++) {
		$amount		= $row[$i];
//	foreach ($row as $amount) {
		if ($amount < 0)
			$amount		= "<font color=red>$amount</font>";
		if ($amount != "")
			echo "<td align=right>$amount</td>";
		else
			echo "<td align=right>&nbsp;</td>";
	}
	echo "</tr>";

}



?>

</table>


<script>

shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });


</script>

<?php

include "footer.php";

?>