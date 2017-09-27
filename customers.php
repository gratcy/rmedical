<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customers.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$lang = lang('终端客户');
if (isset($_GET['delete']) && $privilege->delete == 'on') {
	$id		= sql_secure($_GET['delete']);
	$fields['cstatus'] = 0;
	$fields['cmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
	sql_query(sql_update("customer_tab", $fields, "cid='$id'"));
	gotoURL(-1);
	exit;
}
$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('cid' => 'ID Customer', 'cname' => 'Name','cbirthday' => 'Birthday', 'cemail' => 'Email', 'cphone' => 'Phone', 'caddr' => 'Address');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
?>
<h3 class="pull-left"><?php echo $lang; ?></h3>
		<div class='pull-right'>
			<?php if (!empty($privilege->edit))	{ ?>
			<input class="btn btn-default" type="button" value="New Customer (N)" onclick="location.href=&quot;customers_add.php&quot;;">
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
    	<input class='btn btn-default' type=submit value='Reset' onclick="window.location.href='customers.php'">
    </div>
  </div>
</form>
<div id="paging_header"></div>
<?php
if (empty($name))
	$orderby = 'cid';
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
<th <?php echo ($k == 'cid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<?php if (!empty($privilege->edit)) : ?>
<th class="noprint">Edit</th>
<?php endif; ?>
<?php if (!empty($privilege->delete)) : ?>
<th class="noprint">Delete</th>
<?php endif; ?>
</tr>
</thead>
<tbody>
<?php
$filter = "cstatus=1";
if (!empty($search_word) && !empty($search_field)) {
	if ($search_field == 'cbirthday')
		$filter .= " AND $search_field = '".strtotime($search_word)."'";
	else
		$filter .= " AND $search_field LIKE '%$search_word%'";
}
$sql = sql_getTable("select * from customer_tab where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
	$phone = explode('*', $v['cphone']);
?>
<tr>
<td><?php echo $v['cid']; ?></td>
<td><?php echo $v['cname']; ?></td>
<td><?php echo date('Y-m-d',$v['cbirthday']); ?></td>
<td><?php echo $v['cemail']; ?></td>
<td><?php echo $phone[0] . ($phone[1] ? ' / '.$phone[1] : ''); ?></td>
<td><?php echo $v['caddr']; ?></td>
<?php if (!empty($privilege->edit)) : ?>
<td><?php if (!empty($privilege->edit)) : ?><a href="customers_edit.php?id=<?php echo $v['cid']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
<?php endif; ?>
<?php if (!empty($privilege->delete)) : ?>
<td><?php if (!empty($privilege->delete)) : ?><a href="customers.php?delete=<?php echo $v['cid']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
<?php endif; ?>
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
<th <?php echo ($k == 'cid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<?php if (!empty($privilege->edit)) : ?>
<th class="noprint">Edit</th>
<?php endif; ?>
<?php if (!empty($privilege->delete)) : ?>
<th class="noprint">Delete</th>
<?php endif; ?>
</tr>
</tfoot>
</table>
</div>
    <form id="exportform" method="post" action="export_xls.php" style="margin:0px;">
            <input type="hidden" name="filter_field" value="<?php echo $search_field; ?>" />
            <input type="hidden" name="filter_word" value="<?php echo $search_word; ?>" />
            <input type="hidden" name="page" value="customers" />
      </form>
<?php

echo <<<EOS
<table class='table table-borderless'>
</table>
EOS;

//	Paging function
$record_sql				= "select count(*) from customer_tab where $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
