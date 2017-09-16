<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='staff.php'");
if (empty($privilege->edit))	{	gotoURL("staff.php"); exit; }

echo "<h3 class='pull-left'>新增員工</h3><input class='btn btn-default pull-right' type=button value='返回 (B)' onclick='history.go(-1);'><br /><br /><br />";

if ($_POST['action'] == 'add') {

	$error						= array();

	$fields						= sql_secure($_POST, "staff_id, name,gender, date_modify, birthday, address, idcard, tel, mobile, email, group, commission_id, date_start, date_leave, class, modify_user, remark");

	$fields['modify_user']		= $user->id;
	$fields['date_modify']		= date("Y-m-d H:i:s");

	if (empty($error)) {
		sql_query(sql_insert("staff", $fields));


		echo "<p><font color=blue>新增員工成功 : $item_no</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='staff.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-2, 3);
		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'staff_id'						, 'text'			, '員工編號'					, '100%',
			'name'							, 'text'			, '姓名'						, '100%',
			'gender'						, 'radio'			, '性別'						, '100%',
			'birthday'						, 'text'			, '出生日期'					, '100%',
			'idcard'						, 'text'			, '身份證號碼'					, '100%',
			'tel'							, 'text'			, '固定電話'					, '100%',
			'mobile'						, 'text'			, '移動電話'					, '100%',
			'email'							, 'text'			, '電郵'						, '100%',
			'address'						, 'textarea'		, '地址'						, '100%',
			'sep1'							, ''				, '---'							, '100%',
			'class'							, 'select'	, '職位'						, '100%',
			'group'							, 'select2'	, '組別'						, '100%',
			'commission_id'					, 'select2'	, '佣金計劃'					, '100%',
			'date_start'					, 'text'			, '入職日期'					, '100%',
			'date_leave'					, 'text'			, '離職日期'					, '100%',
			'sep2'							, ''				, '---'							, '100%',
			'remark'						, 'textarea'		, '備註'						, '100%',
			'submit_button'					, 'submit'			, '確定(S)'						, '100%'
				);





if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;

$prefix_staff_id			= "SF";
$suffix_staff_id			= substr(sql_getValue("select staff_id from staff where staff_id like '$prefix_staff_id%' order by staff_id desc limit 1"), 2);
$new_staff_id				= $prefix_staff_id .padding($suffix_staff_id+1, 5);



$inputs->value['staff_id']						= $new_staff_id;
$inputs->options['class']						= sql_getArray("select description, id from class_staff order by description asc");
$inputs->options['gender']						= array('男', '女');
$inputs->options['group']						= sql_getArray("select description, id from class_staff_group order by description asc");
$inputs->options['commission_id']				= sql_getArray("select name, id from commission order by name asc");

$inputs->tag['date_start']							= "yyyy-mm-dd";

//$inputs->exclude[]							= 'cost_currency';





?>

<script>

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
	do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
	}
	return [curleft,curtop];
}


</script>

<form class='form-horizontal' name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=add>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

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

?>
</form>

<script>

shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+S", function () {document.getElementById("form").submit(); });
shortcut.add("Ctrl+C", function () {calculate(); });


</script>
<?php

include "footer.php";

?>
