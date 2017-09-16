<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='user_setting.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }



$lang = lang('個人設定');
echo "<h3>$lang</h3><br>";

if ($_POST['action'] == 'edit') {

	$error						= array();
	$fields						= sql_secure($_POST, 'name,user');



	if (sql_check("select 1 from service_user where user='{$fields['user']}' and id!='$user->id'  "))
		$error[]				= "<li style='padding-left:10px;'><font color=red>此用戶已經存在，請使用其他用戶名稱。</font></li>\r\n";

	if (empty($error)) {
		sql_query(sql_update("service_user", $fields, "id='$user->id'"));

		$password		= $_POST['password'];
        if (!empty($password)) {
		sql_query("update service_user set password=md5('$password') where id='$user->id'");
		}


		echo "<script>alert('會員資料修改成功。');</script>";
		echo "<meta http-equiv='refresh' content='0;url=user_setting.php'>";
		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'user'					, 'text'		, '登入名稱'		, '100%',
			'name'					, 'text'		, '姓名'			, '100%',
			'password'				, 'password'	, '登入密碼 *'	, '100%',
			'password2'				, 'password'	, '確認密碼 *'	, '100%'
				);


if ($_POST['action'] == 'edit')
	$inputs->value	= $_POST;
else
	$inputs->value	= sql_getVar("select * from service_user where id='$user->id'");


$inputs->value['password']	= $inputs->value['password2']	= '';

echo <<<EOS

<script>
function form_check(form) {

	if (form.password2.value!=form.password.value) {
		alert('確認密碼與密碼不一致');
		return false;
	}
	return true;
}
</script>

<form class='form-horizontal' action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=edit>
EOS;

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '---')
		echo "<hr width=100% size=1 align=left>";
	else
		echo "
			<div class='form-group'>
		    <label for='input_$name' class='col-sm-2 control-label'>$desc</label>
				<div class='col-sm-8'>
					{$inputs->$name}
				</div>
			</div>
			";

}

echo <<<EOS

	<div class='form-group'>
    <label class='col-sm-2 control-label'></label>
		<div class='col-sm-8'>
			<input type=submit value='確定' class='btn btn-default'>
		</div>
	</div>
	<div class='form-group'>
    <label class='col-sm-2 control-label'></label>
		<div class='col-sm-8'>
			<p class="help-block">註 : 如不更改密碼則不用填寫。</p>
		</div>
	</div>
</form>

EOS;

include "footer.php";

?>
