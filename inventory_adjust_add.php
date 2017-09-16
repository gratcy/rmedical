<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory_transaction.php'");
if (empty($privilege->edit))	{	gotoURL("index.php"); exit; }


$site_id			= $_GET['site_id'] * 1;

echo "<h3 class='pull-left'>新增倉存調整</h3>";


if ($_POST['action'] == 'add') {

	$fields						= sql_secure($_POST, "type, date, remark");

	$fields['site_from']		= $site_id;
	$fields['modify_user']		= $user->id;
	$fields['date_create']		= date("Y-m-d H:i:s");

	sql_query(sql_insert("inventory_transaction", $fields));

	$it_id						= sql_insert_id();

	$_SESSION["inventory_newrecord_$it_id"]		= true;


	if ($fields['type'] == '調整')
		gotoURL("inventory_adjust_edit.php?id=$it_id");
	else
		gotoURL("inventory_supply_edit.php?id=$it_id");

	exit;

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'site_id'					, 'select2'			, '銷售地點'					, '100%',
			'type'						, 'select'					, '事件類型'					, '100%',
			'date'						, 'text'					, '事件日期'					, '100%',
			'remark'					, 'textarea'				, '備註<br>( 調整或返貨原因 )'	, '100%',
			'sep1'						, ''						, '---'							, '100%',
			'submit_button'				, 'submit'					, '繼續(S)'						, '100%'
				);

if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;

$inputs->value['date']						= date("Y-m-d");
$inputs->value['site_id']					= $site_id;
$inputs->value['type']						= "調整";


$inputs->options['site_id']					= sql_getArray("select name, id from site order by name");
$inputs->options['type']					= array("調整", "返貨");


$inputs->desc2['date']						= "&nbsp; <i class='fa fa-calendar-o' onclick=\"show_cal(this, 'date');\"></i>";


$inputs->tag['site_id']					= "class='form-control'";
$inputs->tag['type']					= "class='form-control'";
$inputs->tag['date']					= "class='form-control' style='width:75%;display:inline-block;'";
$inputs->tag['remark']					= "class='form-control'";
$inputs->tag['submit_button']				= "class='btn btn-default button'";

?>
<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

		<div class='pull-right'>
			<input class='btn btn-default' type=button value='返回 (B)' onclick='history.go(-1);'>
		</div>
		<br><br><br>

<form class='form-horizontal' name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=add>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '繼續(S)')	$desc = '';

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

function form_check(form) {

	if (form.elements.namedItem('remark') == '') {
		alert("必須填寫備註。");
		return false;
	}
	return true;
}


shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+S", function () {document.getElementById("form").submit(); });
shortcut.add("Ctrl+C", function () {calculate(); });


</script>
<?php

include "footer.php";

?>
