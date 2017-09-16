<?php

include_once "header.php";
$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='customer.php'");
if (empty($privilege->edit))	{	gotoURL("customer.php"); exit; }

echo "<h3 class='pull-left'>新增客戶</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />";



if ($_POST['action'] == 'add') {

	$error						= array();

	$fields						= sql_secure($_POST, "staff_id, date_modify, name, class, site_id, address, delivery_address, tel, fax, email, website, attention, attention_tel,payday,discount,paymentterms");

	$fields['date_modify']		= date("Y-m-d H:i:s");
	$fields['modify_user']		= $user->id;


	if (empty($error)) {
		sql_query(sql_insert("customer", $fields));


		echo "<p><font color=blue>新增客戶成功 :</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='customer.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
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
			'name'							, 'text'			, '客戶名稱'					, '100%',
			'class'							, 'select'	, '類別'						, '100%',
			'staff_id'						, 'select2'	, '組別'						, '100%',
			'site_id'						, 'select2'	, '銷售地點'					, '100%',
			'sep2'							, ''				, '---'							, '100%',
			'address'						, 'textarea'		, '地址'						, '100%',
			'delivery_address'				, 'textarea'		, '送貨地址'					, '100%',
			'tel'							, 'text'			, '電話'						, '100%',
			'fax'							, 'text'			, '傳真'						, '100%',
			'email'							, 'text'			, '電郵'						, '100%',
			'website'						, 'text'			, '網址'						, '100%',
			'sep2'							, ''				, '---'							, '100%',
			'attention'						, 'text'			, '聯絡人'						, '100%',
			'attention_tel'					, 'text'			, '聯絡人電話'					, '100%',
			'payday'						, 'text'			, '付款期限'					, '100%',
			'paymentterms'					, 'text'			, '付款條款'					, '100%',
			'discount'						, 'text'			, '淨利 (%)'					, '100%',
			'sep3'							, ''				, '---'							, '100%',
			'submit_button'					, 'submit'			, '確定(S)'						, '100%'
				);

if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;

$inputs->options['class']					= sql_getArray("select description, id from class_customer order by description asc");
$inputs->options['staff_id']				= sql_getArray("select name, id from staff order by name asc");
$inputs->options['site_id']					= sql_getArray("select name, id from site order by name asc");

$inputs->tag['name']							= "class='form-control'";
$inputs->tag['class']							= "class='form-control'";
$inputs->tag['staff_id']							= "class='form-control'";
$inputs->tag['site_id']							= "class='form-control'";
$inputs->tag['address']							= "class='form-control'";
$inputs->tag['delivery_address']							= "class='form-control'";
$inputs->tag['tel']							= "class='form-control'";
$inputs->tag['fax']							= "class='form-control'";
$inputs->tag['email']							= "class='form-control'";
$inputs->tag['website']							= "class='form-control'";
$inputs->tag['attention']							= "class='form-control'";
$inputs->tag['attention_tel']							= "class='form-control'";
$inputs->tag['payday']							= "class='form-control'";
$inputs->tag['paymentterms']							= "class='form-control'";
$inputs->tag['discount']							= "class='form-control'";
$inputs->tag['submit_button']							= "class='btn btn-default'";

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

include_once "footer.php";

?>
