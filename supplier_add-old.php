<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='supplier.php'");
if (empty($privilege->edit))	{	gotoURL("supplier.php"); exit; }
echo "<h3>新增供應商</h3><br>";



if ($_POST['action'] == 'add') {

	$error						= array();

	$fields						= sql_secure($_POST, "id, date_modify, name,address, tel, fax, email, website, attention, discount, remark, class, modify ");

	$fields['date_modify']		= date("Y-m-d H:i:s");
	$fields['modify']			= $user->id;


	if (empty($error)) {
		sql_query(sql_insert("supplier", $fields));


		echo "<p><font color=blue>新增記錄成功</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='supplier.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
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
			'name'							, 'text'			, '名稱'						, '50',
			'class'							, 'pulldownmenu'	, '類別'						, '50',
			'tel'							, 'text'			, '電話'						, '50',
			'fax'							, 'text'			, '傳真'						, '50',
			'email'							, 'text'			, '電郵'						, '50',
			'website'						, 'text'			, '網站'						, '50',
			'address'						, 'textarea'		, '地址'						, '400',
			'sep1'							, ''				, '---'							, '20',
			'attention'						, 'text'			, '聯絡人'						, '50',
			'payday'						, 'text'			, '付款期限'					, '50',
			'paymentterms'					, 'text'			, '付款條款'					, '50',
			'discount'						, 'text'			, '折扣'						, '50',
			'remark'						, 'textarea'		, '備註'						, '400',
			'sep2'							, ''				, '---'							, '20',
			'submit_button'					, 'submit'			, '確定(S)'						, '100'
				);





if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;



$inputs->options['class']					= sql_getArray("select description, id from class_supplier");

$inputs->desc2['discount']					= " %";


//$inputs->exclude[]							= 'cost_currency';





?>

<table class='table table-borderless noprint'>
	<tr>
		<td align=right><input class=size12 style='width:80px;' type=button value='返回 (B)' onclick='history.go(-1);'></td>
	</tr>
</table>

<table class='table table-borderless table_form'>

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

<form name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=add>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

	if ($desc == '---')
		echo "<tr><td colspan=2><hr width=100% size=1 align=left></td></tr>";
	else
		echo "
				<tr>
					<td width=150 align=right>$desc</td>
					<td width=450><span id='input_$name'>{$inputs->$name}</span></td>
				</tr>
			";

}

?>
</form>
</table>
<script>

shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+S", function () {document.getElementById("form").submit(); });
shortcut.add("Ctrl+C", function () {calculate(); });


</script>
<?php

include "footer.php";

?>