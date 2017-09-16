<?php
include_once "header.php";
$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
$name = isset($_POST['name']) ? $_POST['name'] : '';
$desc = isset($_POST['desc']) ? $_POST['desc'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';

if ($submit) {
	$error = null;
	if ($id) {
		if (!$name) {
			$error = 'Bank name must be filled !!!';
		}
		else {
			$fields['bname'] = $name;
			$fields['bdesc'] = $desc;
			$fields['bmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
			sql_query(sql_update("bank_tab", $fields, "bid='$id'"));
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
	$detail 	= sql_getVar("select * from bank_tab where bid='$id'");
?>

<h3 class='pull-left'>Edit Store</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />
<form class='form-horizontal' name="form" action="" method="post">
	<input type="hidden" name="id" value="<?php echo $id; ?>">
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Name</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="name" value="<?php echo $detail['bname']; ?>">
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Description</label>
					<div class='col-sm-8'>
						<textarea class='form-control' name="desc"><?php echo $detail['bdesc']; ?></textarea>
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
