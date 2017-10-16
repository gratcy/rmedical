<?php

include_once "header.php";

echo "<h3 class='pull-left'>使用者管理</h3><input class='btn btn-default pull-right' type=button value='返回 (B)' onclick='history.go(-1);'><br /><br /><br />";

$user_id			= "new";

if ($_POST['action'] == 'add') {
	$staff_id = isset($_POST['staff_id']) ? (int) $_POST['staff_id'] : 0;

	$error						= array();
	$fields						= sql_secure($_POST, 'name,user,staff_id,store_id');

	if (!$staff_id) 
		$error[]				= "<li style='padding-left:10px;'><font color=red>需要工作人员</font></li>\r\n";

	if (sql_check("select 1 from service_user where user='{$fields['user']}' "))
		$error[]				= "<li style='padding-left:10px;'><font color=red>此用戶已經存在，請使用其他用戶名稱。</font></li>\r\n";

	if (empty($error)) {
		sql_query(sql_insert("service_user", $fields));

		$user_id		= sql_insert_id();
		$password		= $_POST['password'];

		sql_query("update service_user set password=md5('$password') where id='$user_id'");

		$page					= array();

		foreach ($_POST as $name => $value) {
			if (!startWith($name, 'page__'))	continue;
			list($temp, $link, $control)		= explode("__", $name);
			$page[$link][$control]				= 'on';
		}

		sql_query("delete from service_user_privilege where user_id='$user_id'");
		foreach ($page as $link => $fields) {
			$page_info							= sql_getObj("select * from service_user_page where link='$link.php'");
			$fields['user_id']					= $user_id;
			$fields['link']						= "$link.php";
			$fields['group']					= $page_info->group;
			$fields['group_en']					= $page_info->group_en;
			$fields['name']						= $page_info->name;
			$fields['name_en']						= $page_info->name_en;
			$fields['order']					= $page_info->order;
			sql_query(sql_insert("service_user_privilege", $fields));
		}


		echo "<script>alert('使用者增加成功。');</script>";
		echo "<meta http-equiv='refresh' content='0;url=user_manage.php'>";
		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'user'					, 'text'			, '登入名稱'		, '100%',
			'name'					, 'text'			, '姓名'			, '100%',
			'store_id'				, 'select2'	, 'Store'			, '100%',
			'staff_id'				, 'select2'	, '員工'			, '100%',
			'password'				, 'password'		, '登入密碼 *'	, '100%',
			'password2'				, 'password'		, '確認密碼 *'	, '100%'
				);


$items		= sql_getTable("select * from service_user_page");
foreach ($items as $item) {
	array2obj($item);
	$value			= sql_getObj("select * from service_user_page where `link`='$item->link'");
	$link			= str_replace(".php", "", $item->link);
	$inputs->add("page__{$link}__view", "checkbox", "", "", 0, 0, 0);
	$inputs->add("page__{$link}__edit", "checkbox", "", "", 0, 0, 0);
	$inputs->add("page__{$link}__delete", "checkbox", "", "", 0, 0, 0);
	$inputs->add("page__{$link}__print", "checkbox", "", "", 0, 0, 0);
}

$inputs->options['staff_id']				= sql_getArray("select name, id from staff order by name asc");
$inputs->options['store_id']				= sql_getArray("select sname, sid from store_tab WHERE sstatus=1 order by sname asc");

$username = lang('登入名稱');
$staff = lang('員工');
$name = lang('姓名');
$store = lang('商店');
$pwd = lang('登入密碼');
$repwd = lang('確認密碼');

$previleges = lang('權限');
$show = lang('查看');
$edit = lang('編輯');
$delete = lang('刪除');
$rprint = lang('打印');

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
<input type=hidden name=action value=add>
	<div class='form-group'>
    <label for='user' class='col-sm-2 control-label'><font color="#FF0000">*</font> $username </label>
		<div class='col-sm-3'>
			$inputs->user
		</div>
		<br class='visible-xs' />
    <label for='staff_id' class='col-sm-2 control-label'>$staff</label>
		<div class='col-sm-3'>
			$inputs->staff_id
		</div>
	</div>
	<div class='form-group'>
    <label for='name' class='col-sm-2 control-label'><font color="#FF0000">*</font> $name </label>
		<div class='col-sm-3'>
			$inputs->name
		</div>
		<br class='visible-xs' />
    <label for='store_id' class='col-sm-2 control-label'>$store</label>
		<div class='col-sm-3'>
			$inputs->store_id
		</div>
	</div>
	<div class='form-group'>
    <label for='password' class='col-sm-2 control-label'><font color="#FF0000">*</font> $pwd </label>
		<div class='col-sm-3'>
			$inputs->password
		</div>
		<br class='visible-xs' />
    <label for='password' class='col-sm-2 control-label'><font color="#FF0000">*</font> $repwd </label>
		<div class='col-sm-3'>
			$inputs->password
		</div>
	</div
	<hr>
	<div class="table-responsive">
  	<table class='table table-bordered'>
  		<tr>
	    	<td width="100" height="30"></td>
        <td width="400"><b>$previleges</b></td>
        <td width="100"><b>$show</b></td>
        <td width="100"><b>$edit</b></td>
        <td width="100"><b>$delete</b></td>
        <td width="100"><b>$rprint</b></td>
	    </tr>
EOS;


$items			= sql_getTable("select * from service_user_page order by `order` asc");

$count = 0;
foreach ($items as $item) {

	array2obj($item);
	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';
	$link			= str_replace(".php", "", $item->link);
	$link_view		= "page__" . $link . "__view";
	$link_edit		= "page__" . $link . "__edit";
	$link_delete	= "page__" . $link . "__delete";
	$link_print		= "page__" . $link . "__print";
	echo "<tr bgcolor=$bgcolor>
	<td></td>";
        if ($_SESSION['lang'] == 'hk') {
			echo "<td>$item->group - $item->name</td>";
		}
		else {
			echo "<td>$item->group_en - $item->name_en</td>";
		}
	    echo "<td>" . $inputs->$link_view . "</td>
	    <td>" . $inputs->$link_edit . "</td>
	    <td>" . $inputs->$link_delete . "</td>
	    <td>" . $inputs->$link_print . "</td>
	</tr> ";
}


$inputs->synchronize_checkbox();


echo <<<EOS
	</table>
	</div>
	<div class='form-group'>
		<div class='col-sm-12 text-center'>
			<input type=submit value='確定' class='btn btn-default noprint'>
		</div>
	</div>
</form>

EOS;


include "footer.php";

?>
