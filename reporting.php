<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='reporting.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$columns = array('sid' => 'ID Store', 'name' => 'Store Manager', 'sname' => 'Name', 'salestotal' => 'Sales', 'sphone' => 'Phone', 'saddr' => 'Address');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$rcolumns = $columns;

$to = date('Y-m-d');
$from = date('Y-m-d', strtotime('-1 month'));

$store_id = isset($_GET['store_id']) ? $_GET['store_id'] : array();
$manager_id = isset($_GET['manager_id']) ? $_GET['manager_id'] : array();
$customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : array();
$staff_id = isset($_GET['staff_id']) ? $_GET['staff_id'] : array();
$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : array();
$reporttype = isset($_GET['reporttype']) ? (int) $_GET['reporttype'] : 0;
$tpayment = isset($_GET['tpayment']) ? (int) $_GET['tpayment'] : '';
$tstatus = isset($_GET['tstatus']) ? (int) $_GET['tstatus'] : '';
$daterange = isset($_GET['daterange']) ? $_GET['daterange'] : $from.' - '.$to;
if ($reporttype == 0)
$columns = array('tdate' => 'Date', 'tno' => 'SO No.', 'manager' => 'Manager', 'sname' => 'Store', 'name' => 'Sales', 'cname' => 'Customer', 'tqty' => lang('數量'), 'tammount' => 'Ammount', 'tdiscount' => 'Discount (%)', 'ttotal' => 'Total', 'tpayment' => lang('付款方式'), 'tstatus' => 'Status');
else
$columns = array('tdate' => 'Date', 'tno' => 'SO No.','product' => 'Products', 'sname' => 'Store', 'cname' => 'Customer', 'tqty' => lang('數量'), 'tprice' => lang('價錢'), 'tdiscount' => 'Discount (%)', 'ttotal' => 'Total', 'tpayment' => lang('付款方式'), 'tstatus' => 'Status');

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'store_id__'				, 'select_multiple'	, 'Store'		, '100%',		
			'staff_id__'				, 'select_multiple'	, 'Staff'		, '100%',		
			'customer_id__'			, 'select_multiple'	, 'Customer'	, '100%',
			'manager_id__'				, 'select_multiple'	, 'Manager'	, '100%',
			'item_id__'				, 'select_multiple'	, 'Products'	, '100%',
			'tpayment'				, 'radio'	, 'Payment Type'	, '100%',
			'tstatus'				, 'radio'	, 'Status'	, '100%',
			'reporttype'				, 'radio'	, 'Report Type'	, '100%'
				);
if ($_SESSION['root'] == 1)
$inputs->options['staff_id__'] = sql_getArray("select a.name, a.id from staff a JOIN service_user b ON a.id=b.staff_id WHERE a.class IN (2,3) AND b.store_id>0 order by a.name asc");
else if ($_SESSION['class_staff'] == 1 || $_SESSION['class_staff'] == 8)
$inputs->options['staff_id__'] = sql_getArray("select a.name, a.id from staff a JOIN service_user b ON a.id=b.staff_id WHERE a.class IN (2,3) AND b.store_id IN(".implode(',',$_SESSION['stores']).") order by a.name asc");
else
$inputs->options['staff_id__'] = sql_getArray("select a.name, a.id from staff a JOIN service_user b ON a.id=b.staff_id WHERE a.class IN (2,3) AND b.store_id=".$_SESSION['store_id']." order by a.name asc");

$inputs->options['store_id__'] = sql_getArray("select sname, sid from store_tab WHERE sstatus=1 order by sname asc");
$inputs->options['customer_id__'] = sql_getArray("select cname, cid from customer_tab order by cname asc");
$inputs->options['manager_id__'] = sql_getArray("select name, id from staff WHERE `class` IN (1,8) order by name asc");
$inputs->options['item_id__'] = sql_getArray("select name, id from item order by name asc");

$inputs->options['tpayment']				= array(lang('現金') => 0,'Debit' => 1,lang('信用咭') => 2);
$inputs->options['reporttype']				= array('Budle' => 0,'Package' => 1);
$inputs->options['tstatus']				= array('Active' => 1,'Approve' => 2);

if (count($store_id) > 0) $inputs->value['store_id__'] = implode(',',$store_id);
if (count($manager_id) > 0) $inputs->value['manager_id__'] = implode(',',$manager_id);
if (count($staff_id) > 0) $inputs->value['staff_id__'] = implode(',',$staff_id);
if (count($customer_id) > 0) $inputs->value['customer_id__'] = implode(',',$customer_id);
if (count($item_id) > 0) $inputs->value['item_id__'] = implode(',',$item_id);
$inputs->value['tpayment'] = $tpayment;
$inputs->value['reporttype'] = $reporttype;

?>
<h3>Reporting</h3>
<br />
<form id="search_box" action="" method="GET">
<table border="0" class="table_filter">
<?php if ($_SESSION['root'] == 1) { ?>
<tr><td style="width:20%">Manager</td><td><?php echo $inputs->manager_id__; ?></td></tr>
<tr><td>Store</td><td><?php echo $inputs->store_id__; ?></td></tr>
<tr><td>Sales</td><td><?php echo $inputs->staff_id__; ?></td></tr>
<?php } else if ($_SESSION['class_staff'] == 1 || $_SESSION['class_staff'] == 8) { ?>
<input type="hidden" name="store_id[]" value="<?php echo $_SESSION['store_id']; ?>">
<tr><td>Sales</td><td><?php echo $inputs->staff_id__; ?></td></tr>
<?php } else { ?>
<input type="hidden" name="store_id[]" value="<?php echo $_SESSION['store_id']; ?>">
<input type="hidden" name="staff_id[]" value="<?php echo $_SESSION['staff_id']; ?>">
<?php } ?>
<tr><td></td><td><hr /></td></tr>
<tr><td>Date Range</td><td>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" id="daterange" name="daterange" style="width:30%;letter-spacing: 3px;" class="form-control" autocomplete="off" value="<?php echo $from . ' - ' . $to; ?>" />
                                        </div><!-- /.input group --></td></tr>
<tr><td><?php echo lang('付款方式'); ?></td><td><?php echo $inputs->tpayment; ?></td></tr>
<tr><td>Status</td><td><?php echo $inputs->tstatus; ?></td></tr>
<tr><td>Customer</td><td><?php echo $inputs->customer_id__; ?></td></tr>
<tr><td></td><td><hr /></td></tr>
<tr><td>Report Type</td><td><?php echo $inputs->reporttype; ?></td></tr>
<tr class="products" <?php echo ($reporttype == 1 ? "" : "style=\"display:none\"");?>><td>Product</td><td><?php echo $inputs->item_id__; ?></td></tr>
<tr><td></td><td><br /><hr /><br /></td></tr>
<tr><td></td><td> <input type="submit" value="OK" class="btn btn-default"> <input type="button" value="Reset" onclick="window.location.href='reporting.php'" class="btn btn-default"> <input class="btn btn-default" type="button" value="Export (E)" onclick="exportform.submit()"></td></tr>
</table>
</form>
<div id="paging_header"></div>

<?php
if (empty($name))
	$orderby = 'a.tid';
else
	$orderby = $name;
?>
<table width=100% cellpadding=4 cellspacing=0 border=0 class="simple_list">
<thead>
<tr>
<?php
foreach($columns as $k => $v) {
$arrow = "";
if ($orderby == $k) {
	if ($ordertype == 'asc') {
		$order = "desc";
		$arrow = " &uarr;";
	} else {
		$order = "asc";
		$arrow = " &darr;";
	}
} else {
	$order = "asc";
}
?>
<th <?php echo ($k == 'tid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
</tr>
</thead>
<tbody>
<?php
if ($_SESSION['root'] == 1)
$filter = "a.tstatus!=0";
else if ($_SESSION['class_staff'] == 1 || $_SESSION['class_staff'] == 8)
$filter = "a.tstatus!=0 AND b.smanager=".$_SESSION['staff_id'];
else
$filter = "a.tstatus!=0 AND b.sid=".$_SESSION['store_id'];

if ($daterange) {
	$daterange = explode(' - ', $daterange);
	$filter .= " AND (DATE(a.tdate)>='".$daterange[0]."' AND DATE(a.tdate)<='".$daterange[1]."')";
}
if (count($manager_id) > 0) $filter .= " AND e.id IN (".implode(",",$manager_id).")";
if (count($staff_id) > 0) $filter .= " AND c.id IN (".implode(",",$staff_id).")";
if (count($customer_id) > 0) $filter .= " AND d.cid IN (".implode(",",$customer_id).")";
if (count($store_id) > 0) $filter .= " AND b.sid IN (".implode(",",$store_id).")";
if ($tpayment != '') $filter .= " AND a.tpayment=" . $tpayment;
if ($tstatus != '') $filter .= " AND a.tstatus=" . $tstatus;

if ($reporttype == 0) {
$sql = sql_getTable("select a.*,b.sname,c.name,d.cname,e.name as manager FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid JOIN staff e ON b.smanager=e.id where ".$filter." ORDER BY $orderby $ordertype");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo date('Y-m-d',strtotime($v['tdate'])); ?></td>
<td><?php echo $v['tno']; ?></td>
<td><?php echo $v['manager']; ?></td>
<td><?php echo $v['sname']; ?></td>
<td><?php echo $v['name']; ?></td>
<td><?php echo $v['cname']; ?></td>
<td><?php echo $v['tqty']; ?></td>
<td>$<?php echo $v['tammount']; ?></td>
<td><?php echo $v['tdiscount']; ?></td>
<td>$<?php echo $v['ttotal']; ?></td>
<td><?php echo get_payment_type($v['tpayment']); ?></td>
<td><?php echo ($v['tstatus'] == 1 ? 'Active' : 'Approved'); ?></td>
</tr>
<?php
}
}
else {
if (count($item_id) > 0) $filter .= " AND f.tpid IN (".implode(",",$item_id).")";
$sql = sql_getTable("select a.tno,a.tdate,a.tdiscount,a.tpayment,a.tstatus,b.sname,d.cname,f.tqty,f.tprice,g.name as product FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid JOIN staff e ON b.smanager=e.id JOIN transaction_detail_tab f ON a.tid=f.ttid JOIN item g ON f.tpid=g.id where ".$filter." ORDER BY $orderby $ordertype");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo date('Y-m-d',strtotime($v['tdate'])); ?></td>
<td><?php echo $v['tno']; ?></td>
<td><?php echo $v['product']; ?></td>
<td><?php echo $v['sname']; ?></td>
<td><?php echo $v['cname']; ?></td>
<td><?php echo $v['tqty']; ?></td>
<td>$<?php echo $v['tprice']; ?></td>
<td><?php echo $v['tdiscount']; ?></td>
<td>$<?php echo $v['tprice'] - ($v['tprice'] * $v['tdiscount'] / 100); ?></td>
<td><?php echo get_payment_type($v['tpayment']); ?></td>
<td><?php echo ($v['tstatus'] == 1 ? 'Active' : 'Approved'); ?></td>
</tr>
<?php
}
}
?>
</tbody>
<tfoot>
<tr>
<?php
foreach($columns as $k => $v) {
$arrow = "";
if ($orderby == $k) {
	if ($ordertype == 'asc') {
		$order = "desc";
		$arrow = " &uarr;";
	} else {
		$order = "asc";
		$arrow = " &darr;";
	}
} else {
	$order = "asc";
}
?>
<th <?php echo ($k == 'tid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
</tr>
</tfoot>
</table>
<p>&nbsp;</p>
<?php if ($_SESSION['root'] == 1) { ?>
<div id="container"></div>
<?php } ?>
<p>&nbsp;</p>
<div id="container2"></div>
    <form id="exportform" method="post" action="export_xls.php?<?php echo http_build_query($_GET); ?>" style="margin:0px;">
            <input type="hidden" name="page" value="reporting" />
      </form>
    <?php
		$filter = preg_replace('/AND\s\(DATE.*\)/', '', $filter);
		$begin = new DateTime( $daterange[0] );
		$end = new DateTime( $daterange[1] );
		$end = $end->modify( '+1 day' ); 
		$interval = new DateInterval('P1D');
		$ranges = new DatePeriod($begin, $interval ,$end);
		$cat = array();
		$rr = array();
		$l1 = '';
		if ($_SESSION['root'] == 1)
		$str = sql_getTable("SELECT sid,sname FROM store_tab WHERE sstatus=1");
		else if ($_SESSION['class_staff'] == 1 || $_SESSION['class_staff'] == 8)
		$str = sql_getTable("SELECT sid,sname FROM store_tab WHERE sstatus=1 AND smanager=".$_SESSION['staff_id']."");
		else
		$str = sql_getTable("SELECT sid,sname FROM store_tab WHERE sstatus=1 AND sid=" . $_SESSION['store_id']);
		foreach($str as $k1 => $v1 ) {
			$l1 .= "{ name: '".$v1['sname']."',data: ";
			foreach($ranges as $K => $v) {
				$cat[$v->format("Y")][$v->format("m")][$v->format("d")] = $v->format("Y-m-d");
				$chats2 = sql_getTable("SELECT SUM(a.ttotal) as total FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid JOIN staff e ON b.smanager=e.id WHERE ".$filter." AND DATE(a.tdate)='".$v->format("Y-m-d")."' AND a.tstore=" . $v1['sid']);
				//~ var_dump($v->format("Y-m-d"),$chats2);
				$wew = '';
				foreach($chats2 as $k2 => $v2)  {
					$wew .= ($v2['total'] ? $v2['total'] : 0).',';
				}
				//~ $wew = rtrim($wew, ',');
				$l1 .= "[".$wew . "]";
			}
			$l1 .= "},";
		}
		//~ die;
		$l1 = rtrim($l1, ",");
		$l1 = str_replace("][","",$l1);
		$l1 = str_replace(",]","]",$l1);
		
		$cate = '';
		foreach($cat as $key => $val) {
			if (is_array($val)) {
				foreach($val as $k => $v) {
					if (is_array($v)) {
						foreach($v as $k2 => $v2) {
							if (is_array($v2)) {
								foreach($v2 as $k3 => $v3) {
									$cate .= "'".$v3."',";
								}
							}
							else
								$cate .= "'".$v2."',";
						}
					}
					else
						$cate .= "'".$v."',";
				}
			}
			else {
				$cate .= "'".$val."',";
			}
		}
		$cate = rtrim($cate, ',');
$chats = sql_getTable("SELECT SUM(a.ttotal) as total, b.sname FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid JOIN staff e ON b.smanager=e.id WHERE ".$filter." GROUP BY a.tstore");

if (count($chats) > 0) {
	?>
      <script>
      $('input[name="reporttype"]').click(function(){
		if ($(this).val() == 1) {
			$('.products').show();
		}
		else {
			$('.products').hide();
		}
	  });
	  
	  $(document).ready(function () {

<?php if ($_SESSION['root'] == 1) { ?>
    // Build the chart
    Highcharts.chart('container', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Stores Income Period <?php echo $daterange[0] . ' to ' . $daterange[1]; ?>'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>${point.y:.2f}</b>'
        },
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
            <?php foreach($chats as $k => $v) : ?>
            <?php echo "{ name: '".$v['sname']."', y : ".$v['total']."}" . ($k+1 == count($chats) ? "" : ","); ?>
            <?php endforeach; ?>
            ]
        }]
    });
 <?php } ?>
    Highcharts.chart('container2', {
    chart: {
        type: 'area'
    },
    title: {
        text: 'Stores Income Period <?php echo $daterange[0] . ' to ' . $daterange[1]; ?>'
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        categories: [<?php echo $cate; ?>],
        tickmarkPlacement: 'on',
        title: {
            enabled: false
        }
    },
    yAxis: {
        title: {
            text: ''
        },
        labels: {
            formatter: function () {
                return this.value / 1000;
            }
        }
    },
    tooltip: {
        split: true,
		pointFormat: '{series.name}: <b>${point.y:.2f}</b>'
    },
    plotOptions: {
        area: {
            stacking: 'normal',
            lineColor: '#666666',
            lineWidth: 1,
            marker: {
                lineWidth: 1,
                lineColor: '#666666'
            }
        }
    },
    series: [<?php echo $l1; ?>]
});
});
      </script>
<?php
}
include_once "footer.php";
?>
