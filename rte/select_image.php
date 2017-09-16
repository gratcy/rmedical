<?php

chdir("../");
include "inc_common.php";


$upload_path	= trim($_GET['path']);

$accept_image_type	= array(
						IMAGETYPE_JPEG,
						IMAGETYPE_GIF,
						IMAGETYPE_PNG,
						IMAGETYPE_SWF
							);

if (isset($_POST['submit'])) {
	

	$url			= $_POST['url'];
	$file			= $_FILES['picture'];
	$path			= '';
	
	if (!empty($url)) {
		
		$path		= $url;
		
	} else if (is_uploaded_file($file['tmp_name'])) {

		$info		= getimagesize($file['tmp_name']);
		if (in_array($info[2], $accept_image_type)) {
			
			$folder		= $upload_path;
			
			$filename	= $file['name'];
			
			if (is_file($folder . $filename)) {
				if ($_POST['overwrite'] == 'yes') {
					@unlink($folder . $filename);
					if (@move_uploaded_file($file['tmp_name'], $folder . $filename))
						$path	= $folder . $filename;
				} else {
					
					$error		= "<tr><td colspan=2><font color=red><b>Upload filed. File already exist.</b></font></td></tr>";
					
				}
			} else {
				if (@move_uploaded_file($file['tmp_name'], $folder . $filename))
					$path	= $folder . $filename;
			}
			
			$path		= str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);
		}
		
	}
	
	
	if ($path != '') {
		
		echo <<<EOS
<html>
<head>
<title>Select Image</title>
<link href="../style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script>
	self.parent.addImage('', '$path');
	location.href = 'select_image.php?path=$upload_path';
</script>
</head>
</html>
EOS;

		return;
		
	}
	
	
	
}


?>

<html>
<head>
<title>Select Image</title>
<link href="../style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body bgcolor=#ffffff style='margin:0px;'>

<form name=form action='' method=post enctype="multipart/form-data">
<table width="100%" height="100%" cellpadding="0" cellspacing="5" border="0" align="center" style='border:solid 1px #aaaaaa'>
	<?php echo $error; ?>
	<tr>
		<td>URL :</td>
		<td><input type=text name=url size=15></td>
	</tr>
	<tr>
		<td>Upload :</td>
		<td><input type=file name=picture size=15><br>
			<input type=checkbox name='overwrite' value='yes'> Overwrite old file</td>
	</tr>
	<tr>
		<td></td>
		<td><input type=submit name='submit' value='Submit'></td>
	</tr>

</table>
</form>
</body>
</html>
