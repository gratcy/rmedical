<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='site.php'");
if (empty($privilege->edit))	{	gotoURL("site.php"); exit; }


echo "<h3>編輯銷售地點</h3><br>";

$id			= sql_secure($_GET['id']);
$site_id	= $id;
$from_query			= $_GET['from_query'];

if ($_POST['action'] == 'edit') {
	
	$fields						= sql_secure($_POST, "name, date_start, date_end, manager_staff_id, staff, remark");
	
	sql_query(sql_update("site", $fields, "id='$id'"));
	
	
	
	sql_query("delete from inventory where site_id='$site_id' and amount=0");
	$existing_item				= sql_getArray("select item_id, amount from inventory where site_id='$site_id'");

	foreach ($_POST as $name => $value) {
		if (!startWith($name, 'item_id_'))
			continue;
		
		$item_id				= substr($name, 8);
		$item_ids				= sql_getArray("select id from item where name_series=(select name_series from item where id='$item_id')");
		
		foreach ($item_ids as $item_id) {
			if (isset($existing_item[$item_id]))
				continue;
			sql_query("insert into inventory (site_id, item_id, amount) values ('$site_id', '$item_id', 0)");
		}
		
	}
	
	
		alert("編輯記錄成功。");
		
		gotoURL("site.php?$from_query", 0);
		exit;

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'name'						, 'text'			, '名稱'							, '50',
			'date_start'				, 'text'			, '開始日期'						, '30',
			'date_end'					, 'text'			, '終束日期'						, '30',
			'manager_staff_id'			, 'pulldownmenu'	, '總負責人'						, '50',
			'staff'						, 'textarea'		, '推廣員'						, '400',
			'remark'					, 'textarea'		, '備註'							, '400',
			'sep1'						, ''				, '---'							, '20',
			'item'						, 'textarea'		, '銷售產品'						, '400',
			'sep2'						, ''				, '---'							, '20',
			'submit_button'				, 'submit'			, '確定(S)'						, '100'
				);



if ($_POST['action'] == 'edit')
	$inputs->value	= $_POST;
else
	$inputs->value 	= sql_getVar("select * from site where id='$id'");

$inputs->desc2['date_start']							= "<img src='js/calendar.gif' style='vertical-align:top' onclick=\"show_cal(this, 'date_start');\" />";

$inputs->desc2['date_end']							= "<img src='js/calendar.gif' style='vertical-align:top' onclick=\"show_cal(this, 'date_end');\" />";

$inputs->tag['submit_button']				= "class=button";

$options									= sql_getArray("select id, name from staff order by name");
foreach ($options as $id => $name) {
	$options[$id]							= "<option value='$name'>$name</option>";
}
$options									= implode("", $options);

$inputs->desc2['staff']						= <<<EOS
<select id=person_select name=person_select style='width:200px'>
	$options
</select>
<input type=button value='加入' onclick='document.getElementById("staff").value += document.getElementById("person_select").value + "\\r\\n";'>
<input type=button value='清除' onclick='document.getElementById("staff").value = "";'>
EOS;



$options									= sql_getArray("select id, name from item order by name");
foreach ($options as $id => $name) {
	$options[$id]							= "<option value='$name'>$name</option>";
}
$options									= implode("", $options);

$inputs->desc2['item']						= <<<EOS
<select id=item_select name=item_select style='width:200px'>
	$options
</select>
<input type=button value='加入' onclick='document.getElementById("item").value += document.getElementById("item_select").value + "\\r\\n";'>
<input type=button value='清除' onclick='document.getElementById("item").value = "";'>
EOS;


$inputs->options['manager_staff_id']				= sql_getArray("select name, id from staff order by name asc");

$inputs->tag['submit_button']				= "class=button";


?>
<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<table width=100% cellpadding=0 border=0 class=noprint>
	<tr>
		<td align=right>
			<input class=size12 style='width:80px;' type=button value='返回 (B)' onclick='history.go(-1);'>
		</td>
	</tr>
</table>

<table class=table_form width=100% height=300 cellpadding=2 cellspacing=5 border=0>

<form name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=edit>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

	if ($name == 'item') {
	
		echo "<tr><td align=right valign=top>銷售產品</td><td valign=top>";
		echo "	<table width=100% cellpadding=0 cellspacing=3 border=0>";

		$brand			= sql_getArray("select id ,description from class_brand order by description asc");
		$existing_item	= sql_getArray("select distinct name_series from item where id in (select item_id from inventory where site_id='$site_id')");

		foreach ($brand as $brand_id => $name) {

			$items		= sql_getArray("select id, name_series from item where brand='$brand_id' group by name_series order by name_series");

			echo "<tr><td colspan=3><input type=checkbox name='$name' value='$name' onclick='toogle_checkbox_$brand_id(this.checked);'> <strong><font color=#3377dd>$name (全選)</font></strong></td></tr>";
			echo "<tr>";
			$count		= 1;
			foreach ($items as $item_id => $item_name) {
				$checked	= (in_array($item_name, $existing_item)) ? "checked" : "";
				echo "<td width=33%><input type=checkbox name='item_id_$item_id' value='1' $checked> $item_name</td>";
				$count++;
				if ($count > 3)	{
					$count	= 1;		  
					echo "	 </tr><tr>";
				}
			}	
			echo "</tr>";
			echo "<tr><td width=1 colspan=3 bgcolor='#999999'></td></tr>";

			echo "
				<script>
				
				function toogle_checkbox_$brand_id(check) {
				";
			
			foreach ($items as $item_id => $item_name) {
				echo "	if (document.getElementById('item_id_$item_id')) document.getElementById('item_id_$item_id').checked = check;\r\n";
			}
			
			echo "
				}
				</script>
				";	


		}	
		echo "</table></td>";	
		continue;		
	}

	if ($desc == '---')
		echo "<tr><td colspan=2><hr width=100% size=1 align=left class=noprint></td></tr>";
	else
		echo "
				<tr>
					<td width=150 align=right>$desc</td>
					<td width=550><span id='input_$name'>{$inputs->$name}</span></td>
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

include_once "footer.php";

?>