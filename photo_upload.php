<?php

include_once "inc_common.php";

$id					= $_GET['id'] * 1;

$bgcolor			= substr($_GET['bgcolor'], 0, 6);

$folder				= sql_secure($_GET['folder']);

$udb				= sql_secure($_GET['udb']);

list($db_table, $db_field)		= explode('.', $udb);

//	Prevent Hacking to database
if (!empty($id) && !contain($db_field, 'photo'))		exit;


if (empty($id))
	$photos			= urldecode(sql_secure($_POST['photos']));
else
	$photos			= sql_getValue("select $db_field from $db_table where id='$id'");


$error				= array();


$folder				= "photo/$folder/";

if ($_POST['action'] == 'add') {

	$file					= $_FILES['photo'];
	$filename				= strtoupper(dechex(rand(4096, 65535))) . strtoupper(dechex(rand(1, 65535))) . ".jpg";
	$filepath				= $folder . $filename;
	
	@unlink($filepath);
	
	if (move_uploaded_file($file['tmp_name'], $filepath)) {
		$image		= getimagesize($filepath);
		
		if ($image['mime'] == 'image/jpeg') {
			
			@unlink($folder . "80_" . $filename);
			@unlink($folder . "640_" . $filename);
			
			require_once "bin/inc_images.php";
			$size			= getimagesize($filepath);
			if ($size[0] > 640 || $size[1] > 640)
				create_thumbnail($filepath, $folder . "640_" . $filename, 640, 640);
			else
				file_copy($filepath, $folder . "640_" . $filename, true);
			
			create_thumbnail($folder . "640_" . $filename, $folder . "80_" . $filename, 80, 80);
			
			$photos			= explode("<br>", $photos);
			
			array_delete_empty($photos);
			
			$photos[]		= $filename;
			$photos			= implode("<br>", $photos);
			
			if (!empty($id))
				sql_query("update $db_table set $db_field='$photos' where id='$id'");
			
		} else {
			@unlink($filepath);
			$error[]			= "<p><font color=red>上載圖片失敗。</font></p>";
		}
		
	} else {
		
		@unlink($file['tmp_name']);
		$error[]			= "<p><font color=red>上載圖片失敗。</font></p>";
		
	}
	
}



if ($_POST['action'] == 'manage') {

	$photo			= sql_secure($_POST['photo']);
	$do				= sql_secure($_POST['do']);
	
	$photos			= explode('<br>', $photos);
	
	$pos			= array_search($photo, $photos);
	array_splice($photos, $pos, 1);
	if ($do == 'prev')
		array_splice($photos, $pos-1, 0, $photo);
	if ($do == 'next')
		array_splice($photos, $pos+1, 0, $photo);

	if ($do == 'delete') {
		@unlink($folder . "" . $photo);
		@unlink($folder . "80_" . $photo);
		@unlink($folder . "640_" . $photo);
	}

	$photos			= implode('<br>', $photos);
	
	if (!empty($id))
		sql_query("update $db_table set $db_field='$photos' where id='$id'");
}



$photos_input				= urlencode($photos);

echo <<<EOS
<html>
<head>
<title>System</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>


<body bgcolor=#$bgcolor style='margin:0px;'>
<table width=100% cellpadding=3 cellspacing=0 border=0>
	<form name=form1 action='' method=post enctype="multipart/form-data">
	<input type=hidden name=action value="add">
	<input type=hidden name=photos value="$photos_input">
	<tr>
		<td>
			新增圖片 : 
			<input type=file name=photo size=20 style='font-size:13px'>
			<input type=submit value='確定' class=size10>
		</td>
	</tr>
	</form>
</table>

<table width=100% cellpadding=3 cellspacing=0 border=0>

	<form name=form2 action='' method=post>
	<input type=hidden name=action	value="manage">
	<input type=hidden name=photos	value="$photos_input">
	<input type=hidden name=photo	value="">
	<input type=hidden name=do		value="">
	

EOS;


foreach ($error as $err)
	echo $err;


$cols			= 4;
$cols_percent	= ceil(100 / $cols);

$photos			= explode("<br>", $photos);
array_split($photos, 0, $cols);

foreach ($photos as $row) {

	echo "<tr>";
	
	foreach ($row as $photo) {

		if (empty($photo))	continue;
		
		echo "
			<td width={$cols_percent}% valign=top><a href='$folder/640_$photo' target=_blank>" . displayImage("$folder/80_$photo", "", "", "", "", "style='border:solid 1px #aaaaaa'") . "</a><br>
				<input type=button name=a value='&lt;'	class=size10 onclick='form2.elements.namedItem(\"photo\").value=\"$photo\"; form2.elements.namedItem(\"do\").value=\"prev\";	form2.submit();' style='width:20px;'>
				<input type=button name=b value='&gt;'	class=size10 onclick='form2.elements.namedItem(\"photo\").value=\"$photo\"; form2.elements.namedItem(\"do\").value=\"next\";	form2.submit();' style='width:20px;'>
				<input type=button name=c value='刪除'	class=size10 onclick='form2.elements.namedItem(\"photo\").value=\"$photo\"; form2.elements.namedItem(\"do\").value=\"delete\";	form2.submit();'>
			</td>";
		
	}

	for ($i = 0; $i < $cols - count($row); $i++)
		echo "<td width={$cols_percent}%>&nbsp;</td>";

	
	echo "</tr>";

}


if (isset($_GET['return_value'])) {

	list($form, $var)		= explode('.', $_GET['return_value']);
	
	$photos_input			= urldecode($photos_input);
	
	echo <<<EOS
<script>
	
	parent.document.getElementById('$form').elements.namedItem('$var').value="$photos_input";
	
</script>
EOS;

}


echo <<<EOS
	</form>
</table>
</body>
</html>

EOS;


?>