<?php
include_once "header.php";
$name = isset($_POST['name']) ? $_POST['name'] : '';
$manager = isset($_POST['manager']) ? (int) $_POST['manager'] : 0;
$phone = isset($_POST['phone']) ? $_POST['phone'] : array();
$address = isset($_POST['address']) ? $_POST['address'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';

if ($submit) {
	$error = null;
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
		$fields['screated'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
		sql_query(sql_insert("store_tab", $fields));
	}
	if (!empty($error)) {
		echo "<p><font color=red>Error :</font></p>";
		echo "<p>( ".$error." )</p>";
		gotoURL(-1, 3);
		exit;
	}
	else {
		echo "<p><font color=blue>Store successfully added :</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='store.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-2, 3);
		exit;
	}
}
?>

<h3 class='pull-left'>Add Store</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />
<form class='form-horizontal' name="form" action="" method="post">

				<div class='form-group'>
			    <label for='manager' class='col-sm-2 control-label'>Store Manager</label>
					<div class='col-sm-8'>
						<select class='form-control' name="manager">
							<?php echo select_manager(0); ?>
						</select>
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Name</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="name" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='phone[0]' class='col-sm-2 control-label'>Phone I</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="phone[0]" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='phone[1]' class='col-sm-2 control-label'>Phone II</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="phone[1]" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Address</label>
					<div class='col-sm-8'>
						<textarea class='form-control' name="address"></textarea>
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
