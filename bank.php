<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='bank.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

if (isset($_GET['delete']) && $privilege->delete == 'on') {
	$id		= sql_secure($_GET['delete']);
	$fields['bstatus'] = 0;
	$fields['bmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
	sql_query(sql_update("bank_tab", $fields, "bid='$id'"));
	gotoURL(-1);
	exit;
}
$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('bid' => 'ID Bank', 'bname' => 'Bank Name', 'bdesc' => 'Description');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$rcolumns = $columns;
?>
<h3 class="pull-left">Bank</h3>
		<div class='pull-right'>
			<input class="btn btn-default" type="button" value="New Bank (N)" onclick="location.href=&quot;bank_add.php&quot;;">
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
    	<input class="btn btn-default" type="button" value="Reset" onclick="window.location.href='bank.php'">
    </div>
  </div>
</form>
<div id="paging_header"></div>
<?php
if (empty($name))
	$orderby = 'bid';
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
<th <?php echo ($k == 'bid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th class="noprint">Edit</th>
<th class="noprint">Delete</th>
</tr>
</thead>
<tbody>
<?php
$filter = "bstatus=1";
if (!empty($search_word) && !empty($search_field)) {
	$filter .= " AND $search_field LIKE '%$search_word%'";
}
$sql = sql_getTable("select * from bank_tab where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
	$phone = explode('*', $v['sphone']);
?>
<tr>
<td><?php echo $v['bid']; ?></td>
<td><?php echo $v['bname']; ?></td>
<td><?php echo $v['bdesc']; ?></td>
<td><?php if (!empty($privilege->edit)) : ?><a href="bank_edit.php?id=<?php echo $v['bid']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
<td><?php if (!empty($privilege->delete)) : ?><a href="bank.php?delete=<?php echo $v['bid']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
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
<th <?php echo ($k == 'bid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<th class="noprint">Edit</th>
<th class="noprint">Delete</th>
</tr>
</tfoot>
</table>
</div>
    <form id="exportform" method="post" action="export_xls.php" style="margin:0px;">
            <input type="hidden" name="filter_field" value="<?php echo $search_field; ?>" />
            <input type="hidden" name="filter_word" value="<?php echo $search_word; ?>" />
            <input type="hidden" name="page" value="bank" />
      </form>
<?php
//	Paging function
$record_sql				= "select count(*) from bank_tab where $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
