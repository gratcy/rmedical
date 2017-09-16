<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='event.php'");
//if (empty($privilege->add))	{	gotoURL("event.php"); exit; }


echo "<h3 class='pull-left'>新增事件</h3><input class='btn btn-default pull-right' type=button value='返回 (B)' onclick='history.go(-1);'><br /><br /><br />";

if ($_POST['action'] == 'add') {

	$error						= array();

	$fields						= sql_secure($_POST, "title,class,group, person,content,file,status, remark");

	$prefix_event_id			= "EN";
	$suffix_event_id			= substr(sql_getValue("select event_id from event where event_id like '$prefix_event_id%' order by event_id desc limit 1"), 2);
	$new_event_id				= $prefix_event_id . padding($suffix_event_id+1, 9);

	$fields['event_id']			= $new_event_id;

	$fields['date']		= date("Y-m-d H:i:s");

	if ($fields['person'] != 'all') {
		$person							= explode(",", $fields['person']);
		array_delete_empty($person);
		asort($person);
		$fields['person']				= implode(",", $person) . ",";
	}


	if (empty($error)) {

		sql_query(sql_insert("event", $fields));

		alert("新增事件記錄成功。");

		gotoURL("event.php", 0);
		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'title'						, 'text'			, '標題'						, '100%',
			'class'						, 'text'			, '類別'						, '100%',
			'group'						, 'select'			, '組別'						, '100%',
			'person'					, 'textarea'		, '事件人物'					, '100%',
			'content'					, 'textarea'		, '事件內容'					, '100%',
			'file'						, 'hidden'			, '附件'						, '100%',
			'status'					, 'select'			, '狀態'						, '100%',
			'remark'					, 'textarea'		, '備註'						, '100%',
			'submit_button'				, 'submit'			, '確定(S)'					, '100%'
				);



if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;

//$inputs->options['class']					= sql_getArray("select description, id from class_event order by description asc");
$inputs->options['group']					= sql_getArray("select description, id from class_staff_group order by description asc");
$inputs->options['status']					= array("待處理" , "處理中" , "完結");

$inputs->tag['title']							= "class='form-control'";
$inputs->tag['class']							= "class='form-control'";
$inputs->tag['group']							= "class='form-control'";
$inputs->tag['person']							= "class='form-control' id=to_person readonly";
$inputs->tag['content']						= "class='form-control' style='height:200px;'";
$inputs->tag['status']							= "class='form-control'";
$inputs->tag['remark']							= "class='form-control'";
$inputs->tag['submit_button']				= "class='button btn btn-default'";


$inputs->desc2['file']						= "<iframe src='file_upload.php?id=$id&bgcolor=ffffff&folder=event_file&udb=event.file&return_value=form.file' width=440 height=150 frameborder=no></iframe>";


$options									= sql_getArray("select id,name from staff order by `class`,name");
foreach ($options as $id => $name) {
	$options[$name]							= "<option value='$name'>$name</option>";
}
$options									= implode("", $options);


?>

<form class='form-horizontal' name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=add>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

	if ($desc == '事件人物') {
		echo <<<EOS
				<div class='form-group'>
			    <label for='input_$name' class='col-sm-2 control-label'>$desc</label>
					<div class='col-sm-8'>
						<select class='form-control' id=person_select name=person_select style='width:300px;display: inline-block;'>
							<option value='所有人'>所有人</option>
							$options
						</select>
						<input class='btn btn-default' type=button value='加入' onclick='
							if (document.getElementById("to_person").value == "所有人")
								document.getElementById("to_person").value = "";
							if (document.getElementById("person_select").value == "所有人")
								document.getElementById("to_person").value = "所有人";
							else
								document.getElementById("to_person").value += document.getElementById("person_select").value + "\\r\\n";
							'>
						<input class='btn btn-default' type=button value='清除' onclick='
							document.getElementById("to_person").value = "";
							'><br><br>
						<span id='input_$name'>{$inputs->$name}</span>
					</div>
				</div>
EOS;
		continue;

	}

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