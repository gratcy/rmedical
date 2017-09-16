<?php
include_once "inc_common.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='transaction.php'");

if (empty($privilege->print))	{	gotoURL("index.php"); exit; }

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$id) {	gotoURL("index.php"); exit; }

$detail = sql_getTable("select a.*,b.sname,c.name,d.cname FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid where (a.tstatus=1 OR a.tstatus=2) AND a.tid=" . $id);
$Items = sql_getTable("select a.*,b.id,b.name,b.price from transaction_detail_tab a LEFT JOIN item b ON a.tpid=b.id where a.tstatus=1 AND a.ttid=".$id);
?>
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Print Sales Order</title>
	</head>
	<style>
	body{font-size:14px}
	.content{width:600px;margin:0 auto}
	table {border-collapse: collapse;font-size:14px}
	th, td {padding:3px}
	table.items {width:100%}
	.ttotal {width:50%;float:right}
	.ttotal > table {width:100%;font-weight:bold}
	table.items th {border:1px solid #333;background:#ccc;}
	.slogan{text-align:center}
	.slogan h2{font-size:18px;margin:0;padding:0}
	.slogan p{font-size:16px;margin:2px;padding:2px}
	.clear{clear:both;padding:5px}
	hr{border-top:1px solid #3}
	</style>
<body>
	<div class="content">
		<div class="slogan">
		<h2>Rock Medical</h2>
		<p>Solution of your health care</p>
		</div>
	<hr />
	<div style="float:left;">
	<table border="0">
	<tr><td>SO No.</td><td>: <?php echo $detail[0]['tno']; ?></td></tr>
	<tr><td>Customer</td><td>: <?php echo $detail[0]['cname']; ?></td></tr>
	<tr><td>Sales</td><td>: <?php echo $detail[0]['name']; ?></td></tr>
	</table>
	</div>
	<div style="float:right;">
	<table border="0">
	<tr><td>Date Order</td><td>: <?php echo date('Y-m-d',strtotime($detail[0]['tdate']));?></td></tr>
	<tr><td>Date Warranty</td><td>: <?php echo date('Y-m-d',strtotime("+1 year", strtotime($detail[0]['tdate']))); ?></td></tr>
	</table>
	</div>
	<div class="clear"></div>
	<table border="0" class="items">
	<thead>
	<tr>
	<th>Products</th>
	<th style="width:15%;">Price</th>
	<th style="width:5%;">QTY</th>
	<th style="width:15%;">Total</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($Items as $k => $v) :?>
	<tr>
	<td><?php echo $v['name'];?></td>
	<td style="text-align:right;">$<?php echo $v['price'];?></td>
	<td style="text-align:right;"><?php echo $v['tqty'];?></td>
	<td style="text-align:right;">$<?php echo $v['price']*$v['tqty'];?></td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	<hr />
	<div class="ttotal">
	<table border="0">
		<tr><td style="text-align:right;">Total</td><td style="text-align:right;">$<?php echo $detail[0]['tammount'];?></td></tr>
		<tr><td style="text-align:right;">Discount</td><td style="text-align:right;"><?php echo ($detail[0]['tdiscount'] ? $detail[0]['tdiscount'].'%' : '-');?></td></tr>
		<tr><td style="text-align:right;">Grand Total</td><td style="text-align:right;">$<?php echo $detail[0]['ttotal'];?></td></tr>
	</table>
	</div>
	<div class="clear"></div>
	<div class="note">
	<p><i>Note: Do not lose the invoice for claim your product warranty </i></p>
	</div>
	</div>
</body>
</html>
