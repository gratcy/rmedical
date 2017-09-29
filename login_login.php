<?php

include_once "inc_common.php";




$id			= addslashes(sql_secure($_POST['login_id']));
$password	= addslashes(sql_secure($_POST['login_password']));

unset($_SESSION['privilege']);
unset($_SESSION['user_name']);
unset($_SESSION['user_id']);

$user						= sql_getObj("select a.id,a.staff_id,a.store_id,a.name,a.privilege,b.class as class_staff,b.group from service_user a LEFT JOIN staff b ON a.staff_id=b.id where a.user='$id' and md5('$password')=a.password");

if (!empty($user->id)) {
	$_SESSION['user_id']	= $user->id;
	$_SESSION['staff_id']	= $user->staff_id;
	$_SESSION['store_id']	= $user->store_id;
	$_SESSION['user_name']	= $user->name;
	$_SESSION['privilege']	= $user->privilege;
	$_SESSION['class_staff']	= $user->class_staff;
	if ($user->class_staff == 1 || $user->class_staff == 8) {
		$store = sql_getTable("SELECT sid FROM store_tab WHERE smanager=" . $user->staff_id);
		$rs = array();
		foreach($store as $k => $v) {
			$rs[] = $v['sid'];
		}
		$_SESSION['stores'] = $rs;
	}
	$_SESSION['group']	= $user->group;
	$_SESSION['root']	= __is_root($user->id);
	$_SESSION['lang']	= 'hk';
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<?php

// validate user id and password
if (isset($_SESSION['privilege'])) {
	if ($_SESSION['class_staff'] == 2) {
		echo "<meta http-equiv='refresh' content='0;URL=/transaction_add.php'>";
	}
	else {
		echo "<meta http-equiv='refresh' content='0;URL=/'>";
	}
} else { 

	alert("登入名稱不存在或密碼不正確。請重新登入。");
	echo "<meta http-equiv='refresh' content='0;URL=/'>";
	exit;

}


?>

</head>
</html>
