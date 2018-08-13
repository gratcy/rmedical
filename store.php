<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='store.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$lang = lang('商店');
$new = lang('新增');
$export = lang('導出表格');

if (isset($_GET['delete']) && $privilege->delete == 'on') {
	$id		= sql_secure($_GET['delete']);
	$fields['sstatus'] = 0;
	$fields['smodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
	sql_query(sql_update("store_tab", $fields, "sid='$id'"));
	gotoURL(-1);
	exit;
}

$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('sid' => 'ID Store', 'name' => 'Store Manager', 'sname' => 'Name', 'salestotal' => 'Sales', 'sphone' => 'Phone', 'saddr' => 'Address');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$rcolumns = $columns;
unset($rcolumns['salestotal']);
?>
<h3 class="pull-left"><?php echo $lang; ?></h3>
		<div class='pull-right'>
			<?php if (!empty($privilege->edit))	{ ?>
			<input class="btn btn-default" type="button" value="<?php echo $new . ' ' . $lang; ?> (N)" onclick="location.href=&quot;store_add.php&quot;;">
			<?php } ?>
			<input class="btn btn-default" type="button" value="<?php echo $export; ?> (E)" onclick="exportform.submit()">
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
    	<input class='btn btn-default' type=button value='Reset' onclick="window.location.href='store.php'">
    </div>
  </div>
</form>
<div id="paging_header"></div>
<?php
if (empty($name))
	$orderby = 'sid';
else
	$orderby = $name;
?>
<div class="table-responsive">
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
<th <?php echo ($k == 'sid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th>Edit</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
$filter = "sstatus=1";
if (!empty($search_word) && !empty($search_field)) {
	$filter .= " AND $search_field LIKE '%$search_word%'";
}
$sql = sql_getTable("select a.*,b.name,(SELECT COUNT(*) FROM service_user c WHERE c.store_id=a.sid) as salestotal from store_tab a LEFT JOIN staff b ON a.smanager=b.id where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
	$phone = explode('*', $v['sphone']);
?>
<tr>
<td><?php echo $v['sid']; ?></td>
<td><?php echo $v['sname']; ?></td>
<td><?php echo $v['name']; ?></td>
<td><?php echo $v['salestotal']; ?></td>
<td><?php echo $phone[0] . ($phone[1] ? ' / '.$phone[1] : ''); ?></td>
<td><?php echo $v['saddr']; ?></td>
<td><?php if (!empty($privilege->edit)) : ?><a href="store_edit.php?id=<?php echo $v['sid']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
<td><?php if (!empty($privilege->delete)) : ?><a href="store.php?delete=<?php echo $v['sid']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
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
<th <?php echo ($k == 'sid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th>Edit</th>
<th>Delete</th>
</tr>
</tfoot>
</table>
</div>
    <form id="exportform" method="post" action="export_xls.php" style="margin:0px;">
            <input type="hidden" name="filter_field" value="<?php echo $search_field; ?>" />
            <input type="hidden" name="filter_word" value="<?php echo $search_word; ?>" />
            <input type="hidden" name="page" value="store" />
      </form>
<?php
//	Paging function
$record_sql				= "select count(*) from store_tab a LEFT JOIN staff b ON a.smanager=b.id where $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
