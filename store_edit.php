<?php
include_once "header.php";
$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$manager = isset($_POST['manager']) ? (int) $_POST['manager'] : 0;
$phone = isset($_POST['phone']) ? $_POST['phone'] : array();
$address = isset($_POST['address']) ? $_POST['address'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';

if ($submit) {
	$error = null;
	if ($id) {
		if (!$name || !$phone || !$address || !$manager) {
			$error = 'Data you input is incomplete !!!';
		}
		else if (!is_numeric($phone[0]) || isset($phone[1]) && !is_numeric($phone[1])) {
			$error = 'Invalid phone number format !!!';
		}
		else {
			$fields['sname'] = $name;
			$fields['smanager'] = $manager;
			$fields['sphone'] = implode('*',$phone);
			$fields['saddr'] = $address;
			$fields['sstatus'] = 1;
			$fields['smodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
			sql_query(sql_update("store_tab", $fields, "sid='$id'"));
		}
	}
	else {
		$error = 'Invalid input data !!!';
	}

	if (!empty($error)) {
		echo "<p><font color=red>Error :</font></p>";
		echo "<p>( ".$error." )</p>";
		gotoURL(-1, 3);
		exit;
	}
	else {
		alert("編輯記錄成功。");
		gotoURL(-2, 1);
		exit;
	}
}
	$detail 	= sql_getVar("select * from store_tab where sid='$id'");
	$phone = explode('*', $detail['sphone']);
?>

<h3 class='pull-left'>Edit Store</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />
<form class='form-horizontal' name="form" action="" method="post">
	<input type="hidden" name="id" value="<?php echo $id; ?>">

				<div class='form-group'>
			    <label for='manager' class='col-sm-2 control-label'>Store Manager</label>
					<div class='col-sm-8'>
						<select class='form-control' name="manager">
							<?php echo select_manager($detail['smanager']); ?>
						</select>
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Name</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="name" value="<?php echo $detail['sname']; ?>">
					</div>
				</div>
				<div class='form-group'>
			    <label for='phone[0]' class='col-sm-2 control-label'>Phone I</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="phone[0]" value="<?php echo $phone[0]; ?>">
					</div>
				</div>
				<div class='form-group'>
			    <label for='phone[1]' class='col-sm-2 control-label'>Phone II</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="phone[1]" value="<?php echo $phone[1]; ?>">
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Address</label>
					<div class='col-sm-8'>
						<textarea class='form-control' name="address"><?php echo $detail['saddr']; ?></textarea>
					</div>
				</div>
				<hr width=100% size=1 align=left>
				<div class='form-group'>
			    <label for='' class='col-sm-2 control-label'></label>
					<div class='col-sm-8'>
						<input class='btn btn-default' name="submit" type="submit" value="Save (S)">
					</div>
				</div>
</form>
<script>
shortcut.add("Ctrl+B", function () {history.go(-1); });
</script>
<?php
include_once "footer.php";
?>
