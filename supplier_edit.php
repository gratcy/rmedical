<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='supplier.php'");
if (empty($privilege->edit))	{	gotoURL("supplier.php"); exit; }


echo "<h3 class='pull-left'>編輯供應商</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />";

$id		= sql_secure($_GET['id']);
$from_query			= $_GET['from_query'];

if ($_POST['action'] == 'edit') {

	$error						= array();

	$fields						= sql_secure($_POST, "id, name, address, tel, fax, email, website, attention, discount, remark, class, modify_user");

	$fields['modify_user']		= $user->id;

	if (empty($error)) {

		sql_query(sql_update("supplier", $fields, "id='$id'"));

		alert("編輯記錄成功。");

		gotoURL("supplier.php?$from_query", 0);
		exit;

	} else {
		foreach ($error as $err)
			echo $err;
	}

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'id'							, 'text_readonly'	, '編號'						, '100%',
			'name'							, 'text'			, '名稱'						, '100%',
			'class'							, 'select'	, '類別'						, '100%',
			'tel'							, 'text'			, '電話'						, '100%',
			'fax'							, 'text'			, '傳真'						, '100%',
			'email'							, 'text'			, '電郵'						, '100%',
			'website'						, 'text'			, '網站'						, '100%',
			'address'						, 'textarea'		, '地址'						, '100%',
			'sep1'							, ''				, '---'							, '100%',
			'attention'						, 'text'			, '聯絡人'						, '100%',
			'payday'						, 'text'			, '付款期限'					, '100%',
			'paymentterms'					, 'text'			, '付款條款'					, '100%',
			'discount'						, 'text'			, '折扣 (%)'					, '100%',
			'remark'						, 'textarea'		, '備註'						, '100%',
			'sep2'							, ''				, '---'							, '100%',
			'submit_button'					, 'submit'			, '確定(S)'						, '100%'
				);



if ($_POST['action'] == 'edit')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from supplier where id='$id'");



$inputs->options['class']					= sql_getArray("select description, id from class_supplier");

$inputs->desc2['discount']					= " %";

$inputs->tag['id']							= "class='form-control'";
$inputs->tag['name']							= "class='form-control'";
$inputs->tag['class']							= "class='form-control'";
$inputs->tag['tel']							= "class='form-control'";
$inputs->tag['fax']							= "class='form-control'";
$inputs->tag['email']							= "class='form-control'";
$inputs->tag['website']							= "class='form-control'";
$inputs->tag['address']							= "class='form-control' rows='4'";
$inputs->tag['attention']							= "class='form-control'";
$inputs->tag['payday']							= "class='form-control'";
$inputs->tag['paymentterms']							= "class='form-control'";
$inputs->tag['discount']							= "class='form-control' style='width: 95%;display: inline-block;margin-right: 10px;'";
$inputs->tag['remark']							= "class='form-control'";
$inputs->tag['submit_button']							= "class='button btn btn-default'";

?>

<form class='form-horizontal name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=edit>
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
