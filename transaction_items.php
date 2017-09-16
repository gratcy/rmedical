<?php
include_once "inc_common.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='transaction.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }
$act = isset($_GET['act']) ? $_GET['act'] : '';
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$pid = isset($_POST['pid']) ? (int) $_POST['pid'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 0;
$price = isset($_POST['price']) ? $_POST['price'] : 0;

if ($act == 'delete_detail') {
	$fields['tstatus'] = 0;
	$fields['tmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
	sql_query(sql_update("transaction_detail_tab", $fields, "ttid=".$id." AND tpid=" . $pid));
}

if ($act == 'save_detail') {
	$fields['ttid'] = $id;
	$fields['tpid'] = $pid;
	$fields['tqty'] = $qty;
	$fields['tprice'] = $price;
	$fields['tstatus'] = 1;
	sql_query(sql_insert("transaction_detail_tab", $fields));
}

if ($act == 'get_detail') {
	$data = sql_getTable("select a.*,b.id,b.name,b.price from transaction_detail_tab a LEFT JOIN item b ON a.tpid=b.id where a.tstatus=1 AND a.ttid=".$id);
	$res = '';
	foreach($data as $k => $v) :
		$res .= '<tr idnya="'.$v['id'].'">';
		$res .= '<td>'.$v['name'].'</td>';
		$res .= '<td><input type="hidden" value="'.$v['price'].'" name="price['.$v['id'].']">$'.$v['price'].'</td>';
		$res .= '<td><input type="number" style="width:80px" class="form-control" idnya="'.$v['id'].'" value="'.$v['tqty'].'" name="qty['.$v['id'].']"></td>';
		$res .= '<td style="display:none;"><input readonly type="text" style="width:150px" class="form-control" value="'.$v['tqty']*$v['price'].'" name="totalprice['.$v['id'].']"></td>';
		$res .= '<td><a class="delete_items" idnya="'.$v['id'].'" href="javascript:void(0);" onclick="return confirm(\'Are you sure you want to delete this item?\');"><i class="fa fa-times"></i></a></td>';
		$res .= '</tr>';
	endforeach;
}
echo $res;
