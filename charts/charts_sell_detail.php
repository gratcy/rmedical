
<div id="containerChart2"></div>
<p>&nbsp;</p>
<div id="containerChart"></div>
<script>
Highcharts.chart('containerChart', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Report Selling Periode <?php echo $date_start; ?> to <?php echo $date_end; ?> (Quantity)'
    },
    xAxis: {
        categories: [
            'Selling Quantity',
        ],
        crosshair: true
    },
    tooltip: {
            pointFormat: '{series.name}: <b>{point.y:.0f} pcs</b>'
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    legend: {
        align: 'left',
        verticalAlign: 'middle',
        layout: 'vertical'
    },
    series: [
		<?php foreach($charts[0] as $k => $v) : ?>
		<?php
		if ($v['name']) {
			echo "{ name: '".str_replace("'","\\'",$v['name'])."', data : [".$v['quantity']."]}" . ($k+1 == count($chats) ? "" : ",");
		}
		?>
		<?php endforeach; ?>
    ]
});

Highcharts.chart('containerChart2', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Report Selling Periode <?php echo $date_start; ?> to <?php echo $date_end; ?> (Netto)'
    },
    xAxis: {
        categories: [
            'Selling Netto',
        ],
        crosshair: true
    },
    tooltip: {
            pointFormat: '{series.name}: <b>$ {point.y:.2f} </b>'
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    legend: {
        align: 'right',
        verticalAlign: 'middle',
        layout: 'vertical'
    },
    series: [
		<?php foreach($charts[0] as $k => $v) : ?>
		<?php
		if ($v['name']) {
			echo "{ name: '".str_replace("'","\\'",$v['name'])."', data : [".$v['amount_net']."]}" . ($k+1 == count($chats) ? "" : ",");
		}
		?>
		<?php endforeach; ?>
    ]
});
</script>
