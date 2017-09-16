<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='birthday.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$lang = lang('生日');
$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('bid' => 'ID Template', 'btype' => 'Template Type', 'bname' => 'Template Name', 'bsubject' => 'Subject');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$rcolumns = $columns;
?>
<h3 class="pull-left"><?php echo $lang; ?></h3>
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
    	<input class='btn btn-default' type=button value='Reset' onclick="window.location.href='birthday.php'">
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
<th>Edit</th>
</tr>
</thead>
<tbody>
<?php
$filter = "btype=1 AND bstatus=1";
if (!empty($search_word) && !empty($search_field)) {
	$filter .= " AND $search_field LIKE '%$search_word%'";
}
$sql = sql_getTable("select * FROM blasting_template_tab where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo $v['bid']; ?></td>
<td><?php echo __get_template_type($v['bmtype'],1); ?></td>
<td><?php echo $v['bname']; ?></td>
<td><?php echo $v['bsubject']; ?></td>
<td><?php if (!empty($privilege->edit)) : ?><a href="birthday_edit.php?id=<?php echo $v['bid']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
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
<th>Edit</th>
</tr>
</tfoot>
</table>
</div>
<?php
include_once "footer.php";
?>
