<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='media.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$lang = lang('商店');
if (isset($_GET['delete']) && $privilege->delete == 'on') {
	$id		= sql_secure($_GET['delete']);
	$fields['sstatus'] = 0;
	$fields['smodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
	sql_query(sql_update("media_tab", $fields, "sid='$id'"));
	gotoURL(-1);
	exit;
}
$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('mid' => 'ID Media', 'mname' => 'Media Name', 'mfile' => 'File');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$rcolumns = $columns;
unset($rcolumns['mfile']);
?>
<h3 class="pull-left"><?php echo $lang; ?></h3>
		<div class='pull-right'>
			<?php if (!empty($privilege->edit))	{ ?>
			<input class="btn btn-default" type="button" value="New media (N)" onclick="location.href=&quot;media_add.php&quot;;">
			<?php } ?>
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
			<?php echo select_choose($rcolumns, $search_field); ?>
			</select>
		</div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=submit value='確定'>
    </div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=button value='Reset' onclick="window.location.href='media.php'">
    </div>
  </div>
</form>
<div id="paging_header"></div>
<?php
if (empty($name))
	$orderby = 'mid';
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
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
$filter = "mstatus=1";
if (!empty($search_word) && !empty($search_field)) {
	$filter .= " AND $search_field LIKE '%$search_word%'";
}
$sql = sql_getTable("select * FROM media_tab where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
	$phone = explode('*', $v['sphone']);
?>
<tr>
<td><?php echo $v['mid']; ?></td>
<td><?php echo $v['mname']; ?></td>
<td><a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/upload/<?php echo $v['mfile']; ?>">Download File</a></td>
<td style="text-align:left;float;left;"><?php if (!empty($privilege->delete)) : ?><a href="media.php?delete=<?php echo $v['mid']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
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
<th>Delete</th>
</tr>
</tfoot>
</table>
</div>
<?php
//	Paging function
$record_sql				= "select count(*) from media_tab where $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
