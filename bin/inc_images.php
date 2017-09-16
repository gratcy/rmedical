<?php


function all2jpg($file, $quality=100) {	
	if (file_exists($file)) {
   
		$info			= getimagesize($file);

		if ($info[2] != IMAGETYPE_JPEG) {
			
			create_thumbnail($file, $file, $info[0], $info[1], $quality);
			
		}
		
	}
	
}

function create_thumbnail($file_name_src, $file_name_dest, $width, $height, $quality=100) {	

	@ini_set('memory_limit', '128M');

	// Destinatoin File must be JPG or png
	if (file_exists($file_name_src)) {
   
		$est_src		= pathinfo(strtolower($file_name_src));
		$est_dest		= pathinfo(strtolower($file_name_dest));
		$size			= getimagesize($file_name_src);

		if ($width == $size[0] && $height == $size[1] && $size[2] == IMAGETYPE_JPEG) {
			file_copy($file_name_src, $file_name_dest, true);
			return true;
		}

		if ($size[1] == 0) {
			trigger_error("Error : Can not resize image - Image width is zero.");
			return false;
		}
		
		if (($size[0] / $size[1]) >= 1)
			$height		= ceil($size[1] * $width / $size[0]);
		else
			$width		= ceil($size[0] * $height / $size[1]);
		
		$w				= number_format($width , 0, ',', '');
		$h				= number_format($height, 0, ',', '');


		// IMPOSTAZIONE STREAM DESTINAZIONE
		if ($est_dest['extension'] == "gif" || $est_dest['extension'] == "jpg") {
			$file_name_dest = substr_replace($file_name_dest, 'jpg', -3);
			$dest = imagecreatetruecolor($w, $h);
			imageantialias($dest, TRUE);
		} elseif ($est_dest['extension'] == "png") {
			$dest = imagecreatetruecolor($w, $h);
			imageantialias($dest, TRUE);
		} else {
			return FALSE;
		}
       
		// IMPOSTAZIONE STREAM SORGENTE
		switch ($size[2]) {
			case 1:       //GIF
				$src = @imagecreatefromgif($file_name_src);
				break;
			case 2:       //JPEG
				$src = @imagecreatefromjpeg($file_name_src);
				break;
			case 3:       //PNG
				$src = @imagecreatefrompng($file_name_src);
				break;
			default:
				return FALSE;
				break;
		}
		
		$error		= error_get_last();
		if (contain($error['message'], 'imagecreatefromjpeg')) {
			return false;
		}
		
		imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
		
		switch ($size[2]) {
			case 1:
			case 2:
				return imagejpeg($dest,$file_name_dest, $quality);
				break;
			case 3:
				return imagepng($dest,$file_name_dest);
		}
		
		return false;
   }

   return false;
}



//////////////////////////////////////////////////////////////////
// Function to download remote file								//
//////////////////////////////////////////////////////////////////

function file_copy($remote, $local, $overwrite = false) {
	if (is_file($local) and !$overwrite)	return true;
	/*
	$content		= file_get_contents($remote);
	file_put_contents($local, $content);
	return;
	*/
	$fp1 = @fopen($remote, "r");
	
	if ($fp1 === false)						return false;
	
	$fp2 = fopen($local, "w");
	while(!feof($fp1)) {
		$buffer = fread($fp1, 1024 * 4);
		fwrite($fp2, $buffer);
	} 
	fclose($fp1);
	fclose($fp2);
}




?>