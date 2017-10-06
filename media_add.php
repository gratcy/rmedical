<?php
include_once "header.php";
$name = isset($_POST['name']) ? $_POST['name'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';

if ($submit) {
	$error = null;
	if (!$name) {
		$error = 'Data you input is incomplete !!!';
	}
	else {
		$fname = $_FILES["file"]["name"];
		$ext = pathinfo($fname, PATHINFO_EXTENSION);
		$rename = time().str_replace($ext,'',__clean_string($fname)).'.'.$ext;
		
		$target_file = './upload/'.$rename;
		if (preg_match('/\.(exe|bin|sh)/', $fname)) {
			$error = 'Invalid format file !!!';
		}
		else {
			$type = 2;
			if (preg_match('/image\/(png|jpg|jpeg|gif|svg)/i',$_FILES["file"]["type"])) $type = 1;
			if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
				$fields['mname'] = $name;
				$fields['mfile'] = $rename;
				$fields['mtype'] = $type;
				$fields['mstatus'] = 1;
				$fields['mcreated'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
				sql_query(sql_insert("media_tab", $fields));
			}
			else {
				$error = 'Failed upload data !!!';
			}
		}
	}
	if (!empty($error)) {
		echo "<p><font color=red>Error :</font></p>";
		echo "<p>( ".$error." )</p>";
		gotoURL(-1, 3);
		exit;
	}
	else {
		echo "<p><font color=blue>File successfully uploaded :</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='media.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-2, 3);
		exit;
	}
}
?>

<h3 class='pull-left'>Upload File</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />
<form class='form-horizontal' name="form" action="" method="post" enctype="multipart/form-data">

				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Name</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="name" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>File</label>
					<div class='col-sm-8'>
						<input type="file" class='form-control' name="file" />
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
