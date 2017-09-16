<?php

	


if (isset($_POST)) {
	
	$send_email		= ($_POST['action'] == '肚癳祏癟の筿秎' || $_POST['action'] == '肚癳筿秎');
	$send_sms		= ($_POST['action'] == '肚癳祏癟の筿秎' || $_POST['action'] == '肚癳祏癟');
	
	$tos			= array();
	foreach ($_POST as $name => $value) {
		if (substr($name, 0, 3) == "to_")
			$tos[]		= substr($name, 3);
	}
	
	if ($send_email) {
		
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";
		$headers .= "From: $MAILTO_WEBMASTER\r\n";
	    $headers .= "Reply-To: $MAILTO_WEBMASTER\r\n";

		$emails		= sql_getArray("select concat(Chi_Name, ' <', Email, '>') from members where Email != '' and id in (" . implode(',', $tos) . ")");
		foreach ($emails as $email) {
   			set_time_limit(10);
			$result		= mail($email, $_POST['email_title'], nl2br($_POST['email_content']), $headers);
			if ($result != true)
				echo "Error sending email to : $email<br>";
		}
		
		echo '<p><font color=blue>肚癳筿秎ЧΘ</font></p>';
		
	}
	
	if ($send_sms) {
		
		set_time_limit(60);
		include "sms_client.php";
		
		$phones		= sql_getArray("select Mobile from members where Mobile != '' and id in (" . implode(',', $tos) . ")");
		dump($phones);
		$sms		= new SMS_CLIENT('rock-medical', 'healthcare');
		$result		= $sms->send($phones, $_POST['sms_content']);
		$results	= explode(', ', $result);
		foreach ($results as $result) {
			if (substr($result, 0, 5) == 'Error')
				echo $result . "<br>";
		}

		echo '<p><font color=blue>肚癳祏癟ЧΘ</font></p>';

	
	}
	
	exit;
	
}



$content	= trim(html_entity_decode($content, ENT_QUOTES, BIG5));
$content	= str_replace("&nbsp;", " ", $content);
$content	= strip_tags($content);
$content	= trim($content);

$word_length	= strlen($content);



$to					= '';
$groups				= sql_getArray("select id, name from groups order by name");
$to					.= "<option value=''></option>";
foreach ($groups as $id => $name) {
	$to			.= "<option value='$id'>$name</option>";
}

echo <<<EOS
<TABLE border=0 cellPadding=4 cellSpacing=1 width='100%' bgcolor='$COLOR_TABLE_BORDER'>
	<form action='' method=post>
	<TR><TD noWrap class=header align=middle colspan=2>祏癟筿秎祇癳</TD></tr>

	<tr bgcolor=white>
		<td>Μン</td>
		<td>
			<select name=select_group onchange='CSI_load(to_users, "sms_user.php", "group=" + this.value)' style='width:200px'>
				$to
			</select>
			<div id=to_users></div>
			<div id=to_users_loading></div>
		</td>
	</tr>
	<tr bgcolor=white>
		<td>祏癟ず甧</td>
		<td>

			<textarea id=sms_content name=sms_content onkeyup='CSI_load(sms_word_count, "sms_word_count.php", "word=" + encodeURI(this.value));' cols=60 rows=5>$content</textarea>
			<br>
			<table cellpadding=0 cellspacing=0 border=0><tr><td class=size13>计</td><td><span id=sms_word_count class=size13>$word_length</span></td></tr></table>
			<table cellpadding=0 cellspacing=0 border=0><tr><td class=size13>祏癟緇肂</td><td><span id=sms_quota class=size13></span></td></tr></table>
			<span id=sms_word_count_loading></span>
			<span id=sms_quota_loading></span>
		</td>
	</tr>
	<tr bgcolor=white>
		<td>筿秎夹肈</td>
		<td>
			<input type=text name=email_title value='結ホ硄癟 - $title' style='width:360px'>
		</td>
	</tr>
	<tr bgcolor=white>
		<td>
			筿秎ず甧<br>
		</td>
		<td>
			<textarea id=email_content name=email_content cols=60 rows=5>$content</textarea>
		</td>
	</tr>
	<tr bgcolor=white>
		<td></td>
		<td>
			<input type=submit name=action value='肚癳祏癟の筿秎'>
			<input type=submit name=action value='肚癳祏癟'>
			<input type=submit name=action value='肚癳筿秎'>
		</td>
	</tr>
	</form>
</table>
EOS;





require_once "bin/class_csi.php";

$csi	= new CSI();
$csi->load('sms_quota', 'sms_quota.php');


?>