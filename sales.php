<?php
include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='sales.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$ordertype = isset($_GET['ordertype']) ? $_GET['ordertype'] : 'desc';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$search_word = isset($_GET['search_word']) ? $_GET['search_word'] : '';
$search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
$columns = array('sname' => 'Store', 'manager' => 'Manager','staff_id' => 'Staff ID', 'name' => 'Name', 'gender' => 'Gender', 'mobile' => 'Mobile', 'email' => 'Email');
$topage				= (!empty($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;
$rcolumns = $columns;
$lang = lang('销售');
?>
<h3></h3>
<h3 class="pull-left"><?php echo $lang; ?></h3>
		<div class='pull-right'>
			<input class="btn btn-default" type="button" value="New Sales (N)" onclick="location.href=&quot;staff_add.php&quot;;">
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
	$orderby = 'sid';
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
<th <?php echo ($k == 'sid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<?php if (!empty($privilege->edit)) : ?>
<th>Edit</th>
<?php endif; ?>
<?php if (!empty($privilege->delete)) : ?>
<th>Delete</th>
<?php endif; ?>
</tr>
</thead>
<tbody>
<?php
$filter = "c.sstatus=1";
if (!empty($search_word) && !empty($search_field)) {
	$filter .= " AND $search_field LIKE '%$search_word%'";
}
$sql = sql_getTable("select a.id,a.staff_id,a.name,a.email,a.gender,a.mobile,c.sname,d.name as manager FROM staff a JOIN service_user b ON a.id=b.staff_id JOIN store_tab c ON b.store_id=c.sid JOIN staff d ON c.smanager=d.id where ".$filter." ORDER BY $orderby $ordertype limit $offset, $record_per_page");
foreach($sql as $k => $v) {
?>
<tr>
<td><?php echo $v['sname']; ?></td>
<td><?php echo $v['manager']; ?></td>
<td><?php echo $v['staff_id']; ?></td>
<td><?php echo $v['name']; ?></td>
<td><?php echo $v['gender']; ?></td>
<td><?php echo $v['mobile']; ?></td>
<td><?php echo $v['email']; ?></td>
<?php if (!empty($privilege->edit)) : ?>
<td><a href="staff_edit.php?id=<?php echo $v['id']; ?>"><i class='fa fa-pencil'></i></a><?php endif; ?></td>
<?php endif; ?>
<?php if (!empty($privilege->delete)) : ?>
<td><?php if (!empty($privilege->delete)) : ?><a href="staff.php?delete=<?php echo $v['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class='fa fa-times'></i></a><?php endif; ?></td>
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
<th <?php echo ($k == 'sid' ? 'class="ids"' : ''); ?>><a href="<?php echo getURL('file'); ?>?ordertype=<?php echo $order; ?>&name=<?php echo $k; ?>"><?php echo $v?></a> <?php echo $arrow; ?></th>
<?php } ?>
<?php if (!empty($privilege->edit)) : ?>
<th>Edit</th>
<?php endif; ?>
<?php if (!empty($privilege->delete)) : ?>
<th>Delete</th>
<?php endif; ?>
</tr>
</tfoot>
</table>
</div>
    <form id="exportform" method="post" action="export_xls.php" style="margin:0px;">
            <input type="hidden" name="filter_field" value="<?php echo $search_field; ?>" />
            <input type="hidden" name="filter_word" value="<?php echo $search_word; ?>" />
            <input type="hidden" name="page" value="sales" />
      </form>
<?php
//	Paging function
$record_sql				= "select a.staff_id,a.name,a.gender,a.mobile,c.sname,d.name as manager FROM staff a JOIN service_user b ON a.id=b.staff_id JOIN store_tab c ON b.store_id=c.sid JOIN staff d ON c.smanager=d.id where $filter";

echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";

include_once "footer.php";
?>
