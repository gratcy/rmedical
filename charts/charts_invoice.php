<?php
$charts = array();
foreach($items as $key => $val) {
	foreach($val as $k => $v) {
		$charts[$v['issueby']][] = $v;
	}
}
$charts2 = array();
foreach($charts as $key => $val) {
	$total = 0;
	foreach($val as $v) {
		$total += $v['amount_net'];
	}
	$charts2[$key] = $total;
}
arsort($charts2);
$charts2 = array_slice($charts2, 0, 10);
$page = isset($_GET['page']) ? $_GET['page'] : '';
?>

<div id="containerChart"></div>
<script>
Highcharts.chart('containerChart', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Report Invoice Top 10 Periode <?php echo $date_start; ?> to <?php echo $date_end; ?> (Amount)'
    },
    xAxis: {
        categories: [
            'Selling Amount',
        ],
        crosshair: true
    },
    tooltip: {
            pointFormat: '{series.name}: <b>$ {point.y:.2f} </b>'
    },
    <?php if ($page != 'staff_group') : ?>
    legend: {
        align: 'left',
        verticalAlign: 'middle',
        layout: 'vertical'
    },
    <?php endif; ?>
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [
		<?php
		foreach($charts2 as $k => $v) :
		?>
		<?php
		if ($v) {
			echo "{ name: '".str_replace("'","\\'",$k)."', data : [".$v."]},";
		}
		?>
		<?php endforeach; ?>
    ]
});
</script>
