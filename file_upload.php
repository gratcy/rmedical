<?php

include_once "inc_common.php";

$id					= $_GET['id'] * 1;

$bgcolor			= substr($_GET['bgcolor'], 0, 6);

$folder				= sql_secure($_GET['folder'])."/";

$udb				= sql_secure($_GET['udb']);

list($db_table, $db_field)		= explode('.', $udb);


//	Prevent Hacking to database
if (!empty($id) && !contain($db_field, 'file'))		exit;


if (empty($id))
	$files			= urldecode(sql_secure($_POST['files']));
else
	$files			= sql_getValue("select $db_field from $db_table where id='$id'");


$error				= array();


if ($_POST['action'] == 'add') {

	if (is_uploaded_file($_FILES['file']['tmp_name'])) {

		$folder					= "archive/";
		$filename				= str_replace(" ","",$_FILES['file']['name']);
		$filepath				= $folder . $filename;

		@unlink($folder . $filename);

		if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {



			$files			= explode("<br>", $files);

			array_delete_empty($files);

			$files[]		= $filename;
			$files			= implode("<br>", $files);

			if (!empty($id))
				sql_query("update $db_table set $db_field='$files' where id='$id'");

		} else {
			@unlink($filepath);
			$error[]			= "<p><font color=red>上載檔案失敗。</font></p>";
		}

	}

}



if ($_POST['action'] == 'manage') {
	$file			= sql_secure($_POST['file']);
	$do				= sql_secure($_POST['do']);

	$files			= explode('<br>', $files);

	$fis			= array_search($file, $files);
	array_splice($files, $fis, 1);

	if ($do == 'delete') {
		unlink($folder . $file);
	}

	$files			= implode('<br>', $files);

	if (!empty($id))
		sql_query("update $db_table set $db_field='$files' where id='$id'");
}



$files_input				= urlencode($files);

echo <<<EOS
<html>
<head>
<title>System</title>
<link href="bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>


<body bgcolor=#$bgcolor style='margin:0 15px;'>
				<form class='form-horizontal' name=form1 action='' method=post enctype="multipart/form-data">
				<input type=hidden name=action value="add">
				<input type=hidden name=files value="$files_input">
				<div class='form-group'>
					<label for="file" class="control-label">新增附件</label>
					<div class="">
						<input class='form-control' type=file name='file' size=20 style='font-size:13px'>
					</div>
					<br />
					<input class="btn btn-default" type=submit value='確定'>
				</div>
				</form>

				<form name=form2 action='' method=post>
				<input type=hidden name=action	value="manage">
				<input type=hidden name=files	value="$files_input">
				<input type=hidden name='file'	value="">
				<input type=hidden name=do		value="">


EOS;


foreach ($error as $err)
	echo $err;


$cols			= 4;
$cols_percent	= ceil(100 / $cols);

$files			= explode("<br>", $files);
array_split($files, 0, $cols);

foreach ($files as $row) {



	foreach ($row as $file) {

		if (empty($file))	continue;

		$filesize	= filesize("archive/$file");
		$filesize	= round($filesize / 1024, 2);

		echo "<tr>";
		echo "
			<td width=70%>
				$file &nbsp; - {$filesize}kb
			</td>
			<td width=30%>
				<input type=button name=d value='下載' class=size10 onclick='location.href=\"download.php?file=archive/$file\"'>
				<input type=button name=c value='刪除' class=size10 onclick='form2.elements.namedItem(\"file\").value=\"$file\"; form2.elements.namedItem(\"do\").value=\"delete\";	form2.submit();'>
			</td>";
		echo "</tr>";
	}

//	for ($i = 0; $i < $cols - count($row); $i++)
//		echo "<td width={$cols_percent}%>&nbsp;</td>";




}


if (isset($_GET['return_value'])) {

	list($form, $var)		= explode('.', $_GET['return_value']);

	$files_input			= urldecode($files_input);

	echo <<<EOS
<script>

	parent.document.getElementById('$form').elements.namedItem('$var').value="$files_input";

</script>
EOS;

}


echo <<<EOS
                </form>
</body>
</html>

EOS;


?>