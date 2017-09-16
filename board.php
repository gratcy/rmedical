<?php

include_once "header.php";

include_once "bin/class_csi.php";


if (isset($_GET['read'])) {

	$id		= $_GET['read'] * 1;

	if (!sql_check("select 1 from board where (`from`='$user->user' or concat(',', `to`, ',') like '%,$user->user,%' or `to` = 'all') and id='$id'"))	die("Error");

	sql_query("update board set `highlight`=replace(`highlight`, '$user->user,', '') where id='$id'");
	gotoURL(-1);
	exit;

}

if (isset($_GET['delete'])) {

	$id		= $_GET['delete'] * 1;

	if (!sql_check("select 1 from board where (concat(',', `to`, ',') like '%,$user->user,%' or `to` = 'all' or `to` = 'all,') and id='$id'"))	die("Error");

	sql_query("update board set status='deleted' where id='$id'");
	gotoURL(-1);
	exit;

}
if (isset ($_GET['movefolder'])) {
	$id					= $_GET['id'] * 1;
	$folder_id			= $_GET['movefolder'] *1;
	$folder				= sql_getValue("select name from board_folder where id = $folder_id");

	sql_query("update board set folder='$folder' where id='$id'");
	gotoURL(-1);
	exit;
}

$lang = lang('公告版');

echo <<<EOS
<h3>$lang</h3>
<br>

<script type="text/javascript" src="js/dialog.js"></script>

EOS;

?>
<?php


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;



echo "
	<div class='pull-left'>
	<a href='board_folder.php'><i class='fa fa-folder'></i></a> &nbsp;&nbsp;&nbsp;
	<a href=index.php>[ 預設資料夾 ]</a> &nbsp;&nbsp;&nbsp;";
	$folders		= sql_getArray("select distinct name from board_folder");
	foreach ($folders as $folder) {
		if ($folder == '')		continue;
		echo "<a href=index.php?folder=$folder>[ $folder ]</a> &nbsp;&nbsp;&nbsp;";
	}
	$folder_id		=sql_getArray("select id from board_folder where name='$folder'");

	echo "</div><div class='pull-right'><i class='fa fa-plus'></i><a href='board_add.php'> 新增公告</a></div><div class='clearfix'></div><br />";


$columns		= array(
			"#2"				=> "更新",
			"#1"				=> "置頂",
//			"from"				=> "發送者",
			"content"			=> "內容",
			"date_modify"		=> "更新時間",
			"read"				=> "已閱讀",
			"notify"			=> "通知",
			"move"				=> "移動",
			"edit"				=> "編輯",
			"del"				=> "刪除"
						);


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'search_word'				, 'text'		, '字眼'					, '30',
			'search_field'				, 'select'		, '欄位'					, '80'
			);

$search_field							= array_flip($columns);
unset($search_field['']);

$inputs->options['search_field']		= $search_field;
$inputs->value							= $_GET;


echo "
<div class='table-responsive'>
<table class='table table-bordered'>
	<tr>";

$default_order				= "date_modify@desc";


if (empty($_SESSION['sort_order'][getURL('file')]))
	$_SESSION['sort_order'][getURL('file')]		= $default_order;

if (isset($_GET['orderby']))
	$_SESSION['sort_order'][getURL('file')]		= sql_secure(popURL('orderby'));


list($orderby, $ordertype)						= explode("@", $_SESSION['sort_order'][getURL('file')]);


	// Print Column Names
	foreach ($columns as $field => $column) {
			if ($field === 'read' || $field === 'notify' || $field === 'move' || $field === 'edit' || $field === 'del') {
    		echo "<td class='text-center'><b>$column</b><br></td>\n";
			} else {
    		echo "<td><b>$column</b><br></td>\n";
    	}
	}

echo "	</tr> ";


$orderby			= "`$orderby`";




$search_word		= sql_secure($_GET['search_word']);
$search_field		= sql_secure($_GET['search_field']);

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";


if ($user->user != 'admin')
	$filter				.= " and (concat(',', `to`, ',') like '%,$user->user,%' or `to` = 'all' or `to` = 'all,')";

$filter				.= " and status != 'deleted'";

$folder				= $_GET['folder'];
//if (!empty($folder))
$filter				.= " and folder ='$folder'";


$tos				= sql_getArray("select distinct `to` from board where $filter order by `to`");
foreach ($tos as $to) {
	$items				= sql_getTable("select * from board where $filter and `to`='$to' order by priority asc, $orderby $ordertype");


	if ($to == 'all' || $to == 'all,')
		$to						= '所有人';
	else
		$to						= implode(", ", sql_getArray("select name from service_user where `user` in ('" . str_replace(",", "','", substr($to, 0, -1)) . "')"));

	echo "<tr height=1 bgcolor=#aaaaaa><td colspan=15 style='padding:0px;'></td></tr>\r\n";
	echo "<tr height=30 bgcolor=#eeeeee><td colspan=15><h3><font color=#0070ff>收件者 &nbsp; : &nbsp; $to</font></h3></td></tr>";

	foreach ($items as $item) {

		array2obj($item);

		$bgcolor 					= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';
		$bgcolor 					= '#ffffff';

		$modify_time		= date("Y-m-d h:i A",strtotime($item->date_modify));
	//	$item->date_create			= printdate($item->date_create);
	//	$item->date_modify			= printdate($item->date_modify);

		$from						= sql_getValue("select name from service_user where `user` = '$item->from'");

//		$item->content				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), nl2br($item->content));
		$item->content				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), $item->content);

		$up							= "";
		$new						= "";
		$read						= "";

		if ($item->priority < 10)
			$up						.= "<i class='fa fa-arrow-up'><span>$item->priority</span></i>";
		else
			$up						.= "";




		if (contain(",$item->highlight,", ",$user->user,")) {
			$new			.= "<i class='fa fa-flag' id=flag$item->id></i>";

			$read			.= "<i class='fa fa-check' onclick='CSI_submit(\"board_status.php?read=$item->id&mid=$item->id\",\"$item->id\"); this.style.display=\"none\";' style='cursor:pointer;'></i>";
		}
		if (!contain(",$item->highlight,", ",$user->user,") and !empty($item->highlight))
			$new			.= "<i class='fa fa-flag-checkered' id=flag$item->id></i>";

		if(!empty($item->file)) {

			$files				= explode("<br>", $item->file);
			$document			= "";
			foreach ($files as $file) {

				$document		.= "<a href='#' onclick='location.href=\"download.php?file=archive/$file\"'>$file</a> &nbsp; &nbsp; ";

			}

			$str_document			= "<b>附件:</b> $document  <br>";
		}

		$delete						= " <a href='javascript:if (confirm(\"確定要刪除訊息 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";

		$notify						= "<a href='#' onclick=\"dialog_popup('dialog_pm'); document.getElementById('dialog_content').src='notify_board.php?id=$item->id'; \"><i class='fa fa-dot-circle-o'></i>";

		echo "<tr height=1 bgcolor=#aaaaaa><td colspan=15 style='padding:0px;'></td></tr>\r\n";

		echo "<tr bgcolor=$bgcolor>";
		echo "<td valign=top width=50>$new</td>";
		echo "<td valign=top width=50>$up</td>";

		echo "<td valign=top><a href='javascript:;' onclick='show_item(\"content_$item->id\")'><b>$item->title</b></a><br><div id='content_$item->id' style='display:none; margin-top:8px'> $item->content  $str_document</div></td>";

		echo "<td valign=top width=180>$modify_time</td>";

		echo "<td valign=top width=45> $read </td>

			  <td valign=top width=45> $notify </td>

			  <td valign=top width=30>
							<i class='fa fa-arrows'

								onclick='return clickreturnvalue()'
								onmouseover='current_item=$item->id; dropdownmenu(this, event, move_menu, \"#eeeeee\");'
								onmouseout='delayhidemenu();'
							></i>
			  </td>
			  <td valign=top width=30> <a href='board_edit.php?id=$item->id'><i class='fa fa-pencil'></i></a>  </td>
			  <td valign=top width=30>$delete </td>";

		echo "</tr>";
	}


}
echo "<tfoot>";
echo "<tr>";
foreach ($columns as $field => $column) {
	if ($field === 'read' || $field === 'notify' || $field === 'move' || $field === 'edit' || $field === 'del') {
		echo "<td class='text-center'><b>$column</b><br></td>\n";
	} else {
		echo "<td><b>$column</b><br></td>\n";
	}
}
echo "</tr>";
echo "</tfoot>";
echo "</table>";
echo "</div>";




?>



<div id=dialog_pm class=simple_dialog style='position:absolute; width:700; height:600; z-index:300; display:none;'>
<h1>發送通知</h1>
<iframe id=dialog_content src='notify_board.php?id=$item->id' width=698 height=563 frameborder=0></iframe>

</div>

<script>


function show_item(item) {
	item		= document.getElementById(item);
	if (item.style.display == "none")
		item.style.display = "";
	else
		item.style.display = "none";
}
</script>



<script type="text/javascript">


var move_menu		= new Array();

move_menu[0] = "<a href='#' onclick='location.href=\"board.php?movefolder=&id=\" + current_item;'> 預設資料夾 </a>";
<?php

$data			= sql_getTable("select * from board_folder");
$count			= 1;
foreach ($data as $item) {
	array2obj($item);
	if ($item->name == '')		continue;
	echo  "move_menu[$count] = \"<a href='#' onclick='location.href=\\\"board.php?movefolder=$item->id&id=\\\" + current_item;'> $item->name </a>\";";

	$count ++;
}

?>


</script>

<?php

include_once "footer.php";

?>
