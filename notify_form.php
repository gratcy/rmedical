<?php

$content_email	= trim($content);

$content		= trim(html_entity_decode($content, ENT_QUOTES, "UTF-8"));
$content		= str_replace("&nbsp;", " ", $content);
$content		= strip_tags($content);
$content		= trim($content);

$word_length	= strlen($content);



$to					= '';
$groups				= sql_getArray("select id, description from class_staff_group order by description");
$to					.= "<option value=''></option>";
foreach ($groups as $id => $name) {
	$to			.= "<option value='$id'>$name</option>";
}

echo <<<EOS
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>磐石管理系統 Rock Management System</title>

<link href="style.css" rel="stylesheet" type="text/css">

<style>
textarea {
	overflow-y	: scroll;
	height		: 100px;
}
</style>

</head>


<body bgcolor=#ffffff style='margin:10px;'>
<TABLE border=0 cellPadding=4 cellSpacing=1 width='100%' bgcolor='$COLOR_TABLE_BORDER'>
	<form action='notify_process.php' method=post>
	<TR><TD noWrap class=header align=left colspan=2>傳送短信及電郵</TD></tr>

	<tr bgcolor=white>
		<td>收件人</td>
		<td>
			<select name=select_group onchange='CSI_load(to_users, "sms_user.php", "group=" + this.value)' style='width:200px'>
				$to
			</select>
			<input id=check_all_input type=checkbox checked onclick='person_check_all();'><b>全選</b> <br>
			<div id=to_users></div>
			<div id=to_users_loading></div>
		</td>
	</tr>
	<tr bgcolor=white>
		<td>短信內容</td>
		<td>

			<textarea id=sms_content name=sms_content onkeyup='CSI_submit_load(sms_word_count, "sms_word_count.php", "word=" + encodeURI(this.value));' cols=60 rows=5>$content</textarea>
			<br>
			<table cellpadding=3 cellspacing=0 border=0>
				<tr><td class=size13>字數 : </td><td><span id=sms_word_count class=size13>$word_length</span></td></tr>
				<tr><td class=size13>短信余額 : </td><td><span id=sms_quota class=size13></span></td></tr>
			</table>
			<span id=sms_word_count_loading></span>
			<span id=sms_quota_loading></span>
		</td>
	</tr>
	<tr bgcolor=white>
		<td>電郵標題</td>
		<td>
			<input type=text name=email_title value='磐石通訊 - $title' style='width:360px'>
		</td>
	</tr>
	<tr bgcolor=white>
		<td>
			電郵內容<br>
		</td>
		<td>
			<textarea id=email_content name=email_content cols=60 rows=5>$content</textarea>
		</td>
	</tr>
	<tr bgcolor=white>
		<td></td>
		<td>
			<input type=submit name=action value='傳送短信及電郵'>
			<input type=submit name=action value='傳送短信'>
			<input type=submit name=action value='傳送電郵'>
			<input type=button value='取消' onclick="parent.dialog_close('');">
		</td>
	</tr>
	</form>
</table>



<script>

var person_checkbox;

person_checkbox		= new Array();

function person_check_all() {
	
	set_value		= document.getElementById("check_all_input").checked;
	
	for (i in person_checkbox) {
		document.getElementById(person_checkbox[i]).checked		= set_value;
	}
	
}

</script>

</body>
</html>
EOS;





require_once "bin/class_csi.php";

$csi	= new CSI();
$csi->load('sms_quota', 'sms_quota.php');


?>