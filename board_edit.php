<?php

include_once "header.php";

echo "<h3 class='pull-left'>編輯訊息</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-2)'>返回</a></span><br /><br /><br />";

$id		= $_GET['id'] * 1;
$folder_content			= 'images/content/';

if ($user->user != 'admin')
	if (!sql_check("select 1 from board where (`from`='$user->user' or concat(',', `to`, ',') like '%,$user->user,%' or `to` = 'all') and id='$id'"))
	die("Error");

if ($_POST['action'] == 'edit') {
	$error						= array();
	$fields						= sql_secure($_POST, "folder, to, title, content, priority ,file");

	if ($fields['to'] != 'all') {
		$to							= explode(",", $fields['to']);
		array_delete_empty($to);
		asort($to);
		$fields['to']				= implode(",", $to) . ",";
	}

	$from						= sql_getValue("select `from` from board where id='$id'");

	if (empty($fields['priority']))
		$fields['priority']		= 10;

	if($_POST['subscribe']=="on") {
		$fields['highlight']		= $fields['to'];
		if ($fields['highlight'] == 'all') {
			$highlights				= sql_getArray("select user from service_user where user != 'admin'");
			$fields['highlight']	= implode(",", $highlights) . ",";
		}
	}

//	$fields['highlight']		= "$from," . $fields['highlight'];
//	$fields['highlight']		= str_replace("$user->user,", "", $fields['highlight']);

	$fields['date_modify']		= date("Y-m-d H:i:s");
	$fields['modify_user']		= $user->id;

	if (empty($error)) {
		sql_query(sql_update("board", $fields, "id='$id'"));

		echo "<p><font color=blue>新增訊息成功</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='board.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
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
			'highlight'				, 'text_readonly'	, '未閱讀者'				, '100%',
			'to'							, 'text'					, '收件者'					, '100%',
			'priority'				, 'select'				, '置頂'						, '100%',
			'folder'					, 'select'				, '文件夹'					, '100%',
			'title'						, 'text'					, '標題'						, '100%',
			'content'					, 'hidden'				, '內容'						, '100%',
			'editor'					, 'editor'				, '內容'						, '100%',
			'subscribe'				, 'checkbox'			, ''							, '100%',
			'sep2'						, ''							, '---'						, '100%',
			'file'						, 'hidden'				, '附件'						, '100%',
			'submit_button'		, 'submit'				, '確定'						, '100%'
);

$inputs->options['priority']				= array(1, 2, 3, 4, 5);
$inputs->options['folder']					= sql_getArray("select distinct name from board_folder");
$inputs->tag['submit_button']				= "class=button";
$options									= sql_getArray("select user, name from service_user where user != 'admin'");
foreach ($options as $user => $name) {
	$options[$user]							= "<option value='$user'>$name</option>";
}
$options									= implode("", $options);
$inputs->desc2['to']						= <<<EOS
<select class='form-control' id=person_select name=person_select style='width:100px;display:inline-block'>
	<option value='all'>所有人</option>
	$options
</select>
<input class='btn btn-default' type=button value='加入' onclick='
	if (document.getElementById("to_person").value == "all")
		document.getElementById("to_person").value = "";
	if (document.getElementById("person_select").value == "all")
		document.getElementById("to_person").value = "all";
	else
		document.getElementById("to_person").value += document.getElementById("person_select").value + ",";
	'>
<input class='btn btn-default' type=button value='清除' onclick='
	document.getElementById("to_person").value = "";
	'>
EOS;

$inputs->tag['highlight']				= "class='form-control' style='width:50%;'";
$inputs->tag['to']							= "class='form-control' style='width:50%;display:inline-block;' id=to_person readonly";
$inputs->tag['priority']				= "class='form-control'";
$inputs->tag['folder']					= "class='form-control'";
$inputs->tag['title']						= "class='form-control'";
$inputs->tag['submit_button']		= "class='btn btn-default'";
$inputs->tag['content']					= "cols='40' rows='3' style='height:400px;' onclick='textarea_resize(this);' onkeyup='textarea_resize(this)'";
$inputs->desc2['file']					= "<iframe class='form-control' src='file_upload.php?id=$id&bgcolor=ffffff&folder=document&udb=board.file&return_value=form1.file' style='height: auto;' frameborder=no></iframe>";

if ($_POST['action'] == 'edit')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from board where id='$id'");
?>

<script>

function textarea_resize(obj) {
	a					= obj.value.split('\n');
	b					= 1;
	for (x=0;x < a.length; x++) {
 		if (a[x].length >= obj.cols)
 			b			+= Math.floor(a[x].length/obj.cols);
	}
	b					+= a.length;
	//if (b > obj.rows) obj.rows = b;
	obj.style.height	= Math.max(400, 20 * b) + "px";

}

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


function stopRKey(evt) {
  var evt = (evt) ? evt : ((event) ? event : null);
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
  if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
}
document.onkeypress = stopRKey;


</script>

<form class='form-horizontal' name=form1 action='' method=post onsubmit='updateRTE("content"); return true;' enctype="multipart/form-data">
<input type=hidden name=action value=edit>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定')	$desc = '';

	if ($name == 'editor') {
		$content 		= str_replace(array("\r\n", "\n"), '', str_replace('\'', '\\\'', str_replace('\\', '\\\\', $inputs->value['content'])));
		$content 		= str_replace("</script>", "</scr' + 'ipt>", $content);

		echo <<<EOS
		<div class='form-group'>
			<div class='col-sm-2'></div>
			<div class='col-sm-8' style='margin-top: 0'>
<script language="JavaScript" type="text/javascript" src="rte/richtext.js"></script>
<script language="JavaScript" type="text/javascript">

initRTE("rte/images/", "rte/", "style.css", "$folder_content");
writeRichText('content', '$content', '', '', true, false);
</script>
</div></div><br />
		<div class='form-group'>
			<div class='col-sm-2'></div>
			<div class='col-sm-8'>
				<div class="checkbox">
				  <label>
				$inputs->subscribe <font color="#3366FF">編輯后所有 [收件者] 設為未閱讀！</font>
				</label>
</div>
</div></div><br />
EOS;
		continue;
	}

	if ($desc == '---')
		echo "<hr width=100% size=1 align=left>";
	else if ($name == subscribe)
    	continue;
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

<?php
include "footer.php";
?>