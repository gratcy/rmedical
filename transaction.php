<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='transaction.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$lang = lang('销售订单');
if (isset($_GET['delete']) && $privilege->delete == 'on') {
	$id		= sql_secure($_GET['delete']);
	$fields['tstatus'] = 0;
	$fields['tmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
	sql_query(sql_update("transaction_tab", $fields, "tid='$id'"));
	gotoURL(-1);
	exit;
}
$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('tdate' => 'Date', 'tno' => 'SO No.', 'sname' => 'Store', 'name' => 'Sales', 'cname' => 'Customer', 'tqty' => 'QTY', 'tammount' => 'Ammount', 'tdiscount' => 'Discount', 'ttotal' => 'Total', 'tpayment' => 'Payment', 'tcard' => 'Card No.', 'tstatus' => 'Status');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$ccolumns = $columns;
unset($ccolumns['tstatus'],$ccolumns['ttotal'],$ccolumns['tdiscount'],$ccolumns['tammount']);
?>
<h3 class="pull-left"><?php echo $lang; ?></h3>
		<div class='pull-right'>
			<?php if (!empty($privilege->edit))	{ ?>
			<input class="btn btn-default" type="button" value="Sales Order (N)" onclick="location.href=&quot;transaction_add.php&quot;;">
			<?php } ?>
			<input class="btn btn-default" type="button" value="Export (E)" onclick="exportform.submit()">
		</div>
		<br /><br /><br />

<form class='form-horizontal' id="search_box" action="" method="GET">
	<div class='form-group'>
		<label for='search_word' class='col-sm-1 control-label'><i class='fa fa-search'></i> &nbsp; 搜尋 :</label>
		<div class='col-sm-2'>
	 		<input class='form-control' type="text" name="search_word" value="<?php echo $search_word; ?>" size="30">
	 	</div>
		<div class='col-sm-2'>
			<select class='form-control' name="search_field" style="width:80px;">
			<option value=""></option>
			<?php echo select_choose($columns, $search_field); ?>
			</select>
		</div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=submit value='確定'>
    </div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=button value='Reset' onclick="window.location.href='customers.php'">
    </div>
  </div>
</form>
<div id="paging_header"></div>
<?php
if (empty($name))
	$orderby = 'tid';
else
	$orderby = $name;
?>
<div class='table-responsive'>
<table class="table table-borderless simple_list">
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
<th>Print</th>
<th>Edit</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
$filter = "(a.tstatus=1 OR a.tstatus=2)";
if (!empty($search_word) && !empty($search_field)) {
	if ($search_field == 'tpayment') {
		if (strtolower($search_word) == 'debit') $search_word = 1;
		elseif (strtolower($search_word) == 'credit card') $search_word = 2;
		else $search_word = 0;
		$filter .= " AND $search_field=$search_word";
	}
	else {
		$filter .= " AND $search_field LIKE '%$search_word%'";
	}
}
if ($_SESSION['class_staff'] == 1)
$sql = sql_getTable("select a.*,b.sname,c.name,d.cname FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
else if ($_SESSION['class_staff'] == 8)
$sql = sql_getTable("select a.*,b.sname,c.name,d.cname FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid where a.tstore=".$_SESSION['store_id']." AND  ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
else
$sql = sql_getTable("select a.*,b.sname,c.name,d.cname FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid where a.tsid=".$_SESSION['staff_id']." AND ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo date('Y-m-d H:i',strtotime($v['tdate'])); ?></td>
<td><?php echo $v['tno']; ?></td>
<td><?php echo $v['sname']; ?></td>
<td><?php echo $v['name']; ?></td>
<td><?php echo $v['cname']; ?></td>
<td><?php echo $v['tqty']; ?></td>
<td><?php echo $v['tammount']; ?></td>
<td><?php echo $v['tdiscount']; ?></td>
<td><?php echo $v['ttotal']; ?></td>
<td><?php echo get_payment_type($v['tpayment']); ?></td>
<td><?php echo ($v['tcardno'] ? $v['tcardno'] : '-'); ?></td>
<td><?php echo ($v['tstatus'] == 1 ? 'Active' : 'Approved'); ?></td>
<td><?php if (!empty($privilege->print)) : ?><a target="_blank" href="transaction_print.php?id=<?php echo $v['tid']; ?>"><i class='fa fa-print'></i></a><?php endif; ?></td>
<td><?php if (!empty($privilege->edit) && $v['tstatus'] != 2) : ?><a href="transaction_edit.php?id=<?php echo $v['tid']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
<td><?php if (!empty($privilege->delete) && $v['tstatus'] != 2) : ?><a href="transaction.php?delete=<?php echo $v['tid']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
</tr>
<?php
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
<th>Print</th>
<th>Edit</th>
<th>Delete</th>
</tr>
</tfoot>
</table>
</div>
    <form id="exportform" method="post" action="export_xls.php" style="margin:0px;">
            <input type="hidden" name="filter_field" value="<?php echo $search_field; ?>" />
            <input type="hidden" name="filter_word" value="<?php echo $search_word; ?>" />
            <input type="hidden" name="page" value="transaction" />
      </form>
<?php
$record_sql				= "select count(*) from transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid WHERE $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
