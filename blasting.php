<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='blasting.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$type = isset($_GET['type']) ? $_GET['type'] : '';

if (isset($_GET['cancel'])) {
	$id		= sql_secure($_GET['cancel']);
	if ($type == 'sms')
	sql_query(sql_update("sms_queue_tab", array('sstatus' => 0, 'smodified' => json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")))), "sid='$id'"));
	elseif ($type == 'email')
	sql_query(sql_update("email_queue_tab", array('estatus' => 0, 'emodified' => json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")))), "eid='$id'"));
	else
	sql_query(sql_update("blasting_tab", array('bstatus' => 3, 'bmodified' => json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")))), "eid='$id'"));
	gotoURL(-1);
	exit;
}

$lang = lang('电子邮件和短信爆破');

$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;

if ($type == 'sms')
$columns = array('sid' => 'ID Queue', 'sphone' => 'Phone', 'sdate' => 'Date Created', 'sschedule' => 'Date Schedule', 'sdatesent' => 'Date Send', 'sstatus' => 'Status');
else if ($type == 'email')
$columns = array('eid' => 'ID Queue', 'eemail' => 'Email', 'edate' => 'Date Created', 'eschedule' => 'Date Schedule', 'edatesent' => 'Date Send', 'estatus' => 'Status');
else
$columns = array('bid' => 'ID Blasting', 'bsubject' => 'Subject', 'bdate' => 'Date Created', 'bschedule' => 'Date Schedule', 'bstatus' => 'Status');
?>
<h3><?php echo $lang; ?></h3>
<br />
<div class="row">
	<div class="col-sm-9">
		<input class="btn btn-default" type="button" value="Blasting Project" onclick="window.location.href='blasting.php'">
		<input class="btn btn-default" type="button" value="Email Blast" onclick="window.location.href='blasting.php?type=email'">
		<input class="btn btn-default" type="button" value="SMS Blast" onclick="window.location.href='blasting.php?type=sms'">
	</div>
	<div class="col-sm-3 text-right">
		<input class="btn btn-default" type="button" value="Create Blast" onclick="window.location.href='blasting_new.php'">
	</div>
</div>
<br />

<div id="paging_header"></div>
<?php
if ($type == 'sms' || $type == 'email') {
?>
<?php
$idnya = ($type == 'sms' ? 'sid' : 'eid');
if (empty($name))
	$orderby = $idnya;
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
<th <?php echo ($k == $idnya ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?type=<?php echo $type; ?>&ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th>Cancel</th>
</tr>
</thead>
<tbody>
<?php
$filter = ($type == "sms" ? "(sstatus=1 OR sstatus=2)" : "(estatus=1 OR estatus=2)");
$sql = sql_getTable("SELECT * FROM ".($type == "sms" ? "sms_queue_tab" : "email_queue_tab")." WHERE ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo ($type == 'sms' ? $v['sid'] : $v['eid']); ?></td>
<td><?php echo ($type == 'sms' ? $v['sphone'] : $v['eemail']); ?></td>
<td><?php echo ($type == 'sms' ? $v['sdate'] : $v['edate']); ?></td>
<td><?php echo date('Y-m-d H:i',strtotime($type == 'sms' ? $v['sschedule'] : $v['eschedule'])); ?></td>
<td><?php echo ($type == 'sms' ? $v['sdatesent'] : $v['edatesent']); ?></td>
<td><?php echo get_status_queue($type == 'sms' ? $v['sstatus'] : $v['estatus']); ?></td>
<td><a href="blasting.php?cancel=<?php echo ($type == 'sms' ? $v['sid'] : $v['eid']); ?>" onclick="return confirm('Are you sure you want to cacncel this proccess?');"><i class='fa fa-times'></i></a></td>
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
<th <?php echo ($k == $idnya ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?type=<?php echo $type; ?>&ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th>Cancel</th>
</tr>
</tfoot>
</table>
</div>
<?php
$record_sql				= "SELECT COUNT(*) FROM ".($type == "sms" ? "sms_queue_tab" : "email_queue_tab")." WHERE $filter";
}
else {
$idnya = 'bid';
if (empty($name))
	$orderby = $idnya;
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
<th <?php echo ($k == $idnya ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?type=<?php echo $type; ?>&ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th class="noprint">Edit</th>
<th class="noprint">Delete</th>
</tr>
</thead>
<tbody>
<?php
$filter = "(bstatus=1 OR bstatus=0 OR bstatus=2)";
$sql = sql_getTable("SELECT * FROM blasting_tab WHERE ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo $v['bid']; ?></td>
<td><?php echo $v['bsubject']; ?></td>
<td><?php echo $v['bdate']; ?></td>
<td><?php echo $v['bschedule']; ?></td>
<td><?php echo get_status_blasting($v['bstatus']); ?></td>
<td><?php if (!empty($privilege->edit) && $v['bstatus'] == 0) : ?><a href="blasting_edit.php?id=<?php echo $v['bid']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
<td><?php if (!empty($privilege->delete) && $v['bstatus'] == 0) : ?><a href="blasting.php?delete=<?php echo $v['bid']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
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
<th <?php echo ($k == $idnya ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?type=<?php echo $type; ?>&ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th class="noprint">Edit</th>
<th class="noprint">Delete</th>
</tr>
</tfoot>
</table>
</div>
<?php
$record_sql				= "SELECT COUNT(*) FROM blasting_tab WHERE $filter";
}
echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
