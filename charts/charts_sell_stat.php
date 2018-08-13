<?php
$page = isset($_GET['page']) ? $_GET['page'] : '';
?>


<div id="containerChart2"></div>
<p>&nbsp;</p>
<div id="containerChart"></div>
<script>

// Build the chart
Highcharts.chart('containerChart', {
	chart: {
		plotBackgroundColor: null,
		plotBorderWidth: null,
		plotShadow: false,
		type: 'pie'
	},
	title: {
		text: 'Stores Income Period <?php echo $date_start; ?> to <?php echo $date_end; ?> (Quantity)'
	},
	tooltip: {
		pointFormat: '{series.name}: <b>{point.y:.0f} pcs</b>'
	},
    <?php if ($page != 'staff_group') : ?>
    legend: {
        align: 'right',
        verticalAlign: 'middle',
        layout: 'vertical'
    },
    <?php endif; ?>
	plotOptions: {
		pie: {
			allowPointSelect: true,
			cursor: 'pointer',
			dataLabels: {
				enabled: false
			},
			showInLegend: true
		}
	},
	series: [{
		name: 'Store',
		colorByPoint: true,
		data: [
		<?php
		if ($page == 'item') $charts = array($itemsHighQ);
		sort($charts[0]);
		foreach($charts[0] as $k => $v) :
		?>
		<?php echo "{ name: '".str_replace("'","\\'",$v['name'])."', y : ".$v['quantity'] .($k == 1 ? ',sliced: true, selected: true' : '')."}". ($k+1 == count($chats) ? "" : ","); ?>
		<?php endforeach; ?>
		]
	}]
});


Highcharts.chart('containerChart2', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Report Selling Periode <?php echo $date_start; ?> to <?php echo $date_end; ?> (Amount)'
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
		if ($page == 'item') {
			$charts = array($itemsHighA);
			sort($charts[0]);
		}
		foreach($charts[0] as $k => $v) :
		?>
		<?php
		if ($v['name']) {
			echo "{ name: '".str_replace("'","\\'",$v['name'])."', data : [".$v['amount']."]}" . ($k+1 == count($chats) ? "" : ",");
		}
		?>
		<?php endforeach; ?>
    ]
});
</script>
