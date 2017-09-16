<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='event.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }


$lang = lang('事件記錄');
?>

<h3 class="pull-left"><?php echo $lang; ?></h3>

<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>


<?php

if (isset($_GET['delete'])) {

	$id		= sql_secure($_GET['delete']);
	sql_query("update event set status='deleted' where id='$id'");
	gotoURL(-1);
	exit;

}


$topage				= (isset($_GET['topage'])) ? $_GET['topage'] * 1 : 1;
$offset				= ($topage-1) * $record_per_page;

$print_url			= str_replace(".php?", "_print.php?", getURL());

echo "
			<input class='btn btn-default pull-right' type=button value='新增事件 (N)' onclick='location.href=\"event_add.php\";'>
<!--	<input class='btn btn-default pull-right' type=button value='列印 (P)' onclick='window.open(\"$print_url\");'>
			<input class='btn btn-default pull-right' type=button value='".lang('導出表格')." (E)' onclick='exportform.submit()' /> -->
			<br /><br /><br />
";

$columns		= array(
			"event_id"			=> "編號",
			"class"				=> "類別",
			"title"				=> "事件標題",
			"person"			=> "事件人物",
			"status"			=> "狀態",
			"date"				=> "日期",
			"#2"				=> "編輯",
			"#3"				=> "刪除"
						);

$columns_sql	= array(

			"class"				=> "select description from class_event where id=event.class"
						);
$columns_class	= array(
			"#2"				=> "noprint",
			"#3"				=> "noprint"

						);

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'search_word'				, 'text'		, '字眼'					, '30',
			'search_field'				, 'select'		, '欄位'					, '80',
            'date_start'				, 'text'		, '1'					, '10',
			'date_end'					, 'text'		, '2'					, '10'

			);


$search_field							= array_flip($columns);
unset($search_field['']);
unset($search_field['編輯']);
unset($search_field['刪除']);

$inputs->tag['search_word']						= "class='form-control'";
$inputs->tag['search_field']						= "class='form-control'";
$inputs->options['search_field']		= $search_field;
$inputs->value['search_field']			= reset($search_field);
$inputs->tag['date_start']						= "class='form-control'";
$inputs->tag['date_end']						= "class='form-control'";
if (!empty($_GET))
	$inputs->value						= $_GET;


echo "
<form class='form-horizontal' id=search_box action='' method='GET'>
	<div class='form-group'>
		<label for='search_word' class='col-sm-1 control-label'><i class='fa fa-search'></i> &nbsp; 搜尋 :</label>
		<div class='col-sm-2'>
	 		$inputs->search_word
	 	</div>
		<div class='col-sm-2'>
			$inputs->search_field
		</div>
		<label for='date_start' class='col-sm-1 control-label'>開始 :</label>
		<div class='col-sm-2'>
			<div class='input-group'>
				$inputs->date_start
				<div class='input-group-addon' onclick=\"show_cal(this, 'date_start');\"><i class='fa fa-calendar-o'></i></div>
			</div>
		</div>
		<label for='date_end' class='col-sm-1 control-label'>結束 :</label>
		<div class='col-sm-2'>
			<div class='input-group'>
				$inputs->date_end
				<div class='input-group-addon' onclick=\"show_cal(this, 'date_end');\"><i class='fa fa-calendar-o'></i></div>
			</div>
		</div>
		<div class='col-sm-1'>
    	<input class='btn btn-default' type=submit value='確定'>
    </div>
  </div>
</form>
";


echo "<div id='paging_header'></div>";

echo "
<div class='table-responsive'>
<table class='table table-borderless simple_list' id='main_list'>

	<colgroup>
		<col valign=top />
		<col valign=top />
		<col valign=top />
		<col valign=top />
		<col valign=top />
		<col valign=top />
		<col valign=top />
		<col valign=top />
		<col valign=top />
	</colgroup>

<thead>
	<tr>";

$default_order				= "id@desc";


if (empty($_SESSION['sort_order'][getURL('file')]))
	$_SESSION['sort_order'][getURL('file')]		= $default_order;

if (isset($_GET['orderby']))
	$_SESSION['sort_order'][getURL('file')]		= sql_secure(popURL('orderby'));


list($orderby, $ordertype)						= explode("@", $_SESSION['sort_order'][getURL('file')]);


	// Print Column Names
	foreach ($columns as $field => $column) {
		$arrow = "";
		if ($orderby == $field) {

			if ($ordertype == 'asc') {
				$order = "desc";
				$arrow = " &uarr;";
			} else {
				$order = "asc";
				$arrow = " &darr;";
			}
		} else {
			$order = "asc";
		}

		$class			= (isset($columns_class[$field])) ? "class=" . $columns_class[$field] : "";

		if (!startWith($field, '#'))
			$column	= "<a href='" . getURL() . "&orderby=$field%40$order'>$column</a>";

    	echo "<th $class>$column$arrow</th>\n";
	}

echo "	</tr> </thead>";




if (isset($columns_sql[$orderby]))
	$orderby		= "(" . $columns_sql[$orderby] . ")";


$search_word		= sql_secure($_GET['search_word']);
$search_field		= sql_secure($_GET['search_field']);

$date_start			= $_GET['date_start'];
$date_end			= $_GET['date_end'];


if (empty($search_field))
	$search_word	= '';

if (isset($columns_sql[$search_field]))
	$search_field	= "(" . $columns_sql[$search_field] . ")";

if (empty($search_word))			$filter			= 1;
else								$filter			= "$search_field like '%$search_word%'";


if ($date_start && $date_end) {

        $date_start			= strtotime($date_start);
        $date_end			= strtotime($date_end);

        if ($date_end < $date_start)
            $date_end		= $date_start;

        $dates				= array();
        while ($date_start <= $date_end) {
            $dates[]		= date('Y-m-d', $date_start);
            $date_start		+= 86400;		// 60 x 60 x 24
        }

        $date_end			= end($dates);
        $date_start			= reset($dates);
        $filter				.= " and (`date`>= '$date_start' and `date`<= '$date_end')";

}

if (isset($_GET['all']))
	$filter			.= " or person='all'";




$filter				.= " and status!='deleted'";



$items				= sql_getTable("select * from event where $filter order by $orderby $ordertype limit $offset, $record_per_page");

if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有事件。</td></tr>\r\n";

}



$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';

	$item->cost_currency		= $currency_sign[$item->cost_currency];

	$item->date_modify			= printdate($item->date_modify);

	$photo						= array_shift(explode("<br>", $item->photo));
	$photo						= "<a href='photo/640_$photo' target=_blank>" . displayImage("photo/80_$photo", "", "", "", "", "style='border:solid 1px #aaaaaa'") . "</a>";


	$item->brand				= sql_getValue("select description from class_brand where id='$item->brand'");
//	$item->class				= sql_getValue("select description from class_event where id='$item->class'");

	$item->content				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), $item->content);
	$item->content				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), nl2br($item->content));

	$item->person				= str_replace(array(",", "  "), array("<br>", "&nbsp; "), nl2br($item->person));


	if(!empty($item->file)) {

		$files				= explode("<br>", $item->file);
		$document			= "";
		foreach ($files as $file) {

			$document		.= "<a href='#' onclick='location.href=\"download.php?file=archive/$file\"'>$file</a> &nbsp; &nbsp; ";

		}

		$str_document			= "<b>附件:</b> $document  <br>";
	}


	$item_link					= "#";

	$from_query					= urlencode($_SERVER['QUERY_STRING']);

	if ($item->person == 'all')
		$item->person			= "所有人";

	if (!empty($privilege->edit))	{
		$doubleclick			= "ondblclick='location.href=\"event_edit.php?id=$item->id&from_query=$from_query\";'";
		$edit_link				= "<a href='event_edit.php?id=$item->id&from_query=$from_query'><i class='fa fa-pencil'></i></a>";
	} else {
		$doubleclick			= "";
		$edit_link				= "";
	}

	if (!empty($privilege->delete))	{
		$delete_link			= "<a href='javascript:if (confirm(\"確定要刪除事件 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times'></i></a>";
	} else {
		$delete_link			= "";
	}

	echo "<tr bgcolor=$bgcolor $doubleclick>";
//	echo "<td width=30>$item->id</td>";
	echo "<td width=100>$item->event_id</td>";
	echo "<td width=150>$item->class</td>";

	echo "<td valign=top><a href='javascript:;' onclick='show_item(\"content_$item->id\")'><b>$item->title</b></a><br><div id='content_$item->id' style='display:none; margin-top:8px'> $str_document <br /> $item->content</div></td>";


	echo "<td width=300 valign=top>$item->person</td>";
	echo "<td width=100>$item->status</td>";
	echo "<td width=100>$item->date</td>";
	echo "<td class=noprint width=40>$edit_link</td>";
	echo "<td class=noprint width=40>$delete_link</td>";
	echo "</tr>";

}

echo "<tfoot>";
echo "<tr>";
	foreach ($columns as $field => $column) {
		$arrow = "";
		if ($orderby == $field) {

			if ($ordertype == 'asc') {
				$order = "desc";
				$arrow = " &uarr;";
			} else {
				$order = "asc";
				$arrow = " &darr;";
			}
		} else {
			$order = "asc";
		}

		$class			= (isset($columns_class[$field])) ? "class=" . $columns_class[$field] : "";

		if (!startWith($field, '#'))
			$column	= "<a href='" . getURL() . "&orderby=$field%40$order'>$column</a>";

    	echo "<th $class>$column$arrow</th>\n";
	}
echo "</tr>";
echo "</tfoot>";
echo "</table>";
echo "</div>";

echo <<<EOS
<table class='table table-borderless'>
	<tr>
    <form id=exportform method=post action='export_xls.php' style='margin:0px;'>
		<td align=right>
            <input type=hidden name=filter_field value='$search_field' />
            <input type=hidden name=filter_word value='$search_word' />
            <input type=hidden name=date_start value='$date_start' />
            <input type=hidden name=date_end value='$date_end' />
            <input type=hidden name=page value='item' />

		</td>
      </form>
	</tr>
</table>
EOS;

//	Paging function
$record_sql				= "select count(*) from event where $filter";
echo "<div id='paging_footer'>";
include "paging.php";
echo "</div>";




echo <<<EOS

<div class=print_page_footer></div>

<script>

function show_item(item) {
	item		= document.getElementById(item);
	if (item.style.display == "none")
		item.style.display = "";
	else
		item.style.display = "none";
}



shortcut.add("Ctrl+N", function () { location.href="event_add.php"; });
shortcut.add("Ctrl+P", function () { window.open("$print_url"); });
shortcut.add("Ctrl+E", function () { exportform.submit(); });


</script>

EOS;

include_once "footer.php";

?>
