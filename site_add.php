<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='site.php'");
if (empty($privilege->edit))	{	gotoURL("site.php"); exit; }

echo "<h3 class='pull-left'>增加銷售地點</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />";


if ($_POST['action'] == 'add') {

	$error						= array();

	$fields						= sql_secure($_POST, "name, date_start, date_end, manager_staff_id, staff, remark");

	$fields['date_create']		= date("Y-m-d H:i:s");

	if (empty($error)) {

		sql_query(sql_insert("site", $fields));

		$site_id				= sql_insert_id();


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


		echo "<p><font color=blue>增加記錄成功</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='site.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL("site.php", 3);
		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'name'						, 'text'					, '名稱'						, '100%',
			'date_start'				, 'text'					, '開始日期'					, '100%',
			'date_end'					, 'text'					, '終束日期'					, '100%',
			'manager_staff_id'			, 'select2'			, '總負責人'						, '100%',
			'staff'						, 'textarea'				, '推廣員'						, '100%',
			'remark'					, 'textarea'				, '備註'						, '100%',
			'sep1'						, ''						, '---'							, '100%',
			'item'						, ''						, '銷售產品'					, '100%',
			'sep3'						, ''						, '---'							, '100%',
			'submit_button'				, 'submit'					, '確定(S)'						, '100%'
				);

if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;

$inputs->desc2['date_start']				= " <i class='fa fa-calendar-o' onclick=\"show_cal(this, 'date_start');\"></i>";
$inputs->desc2['date_end']				= " <i class='fa fa-calendar-o' onclick=\"show_cal(this, 'date_end');\"></i>";

$inputs->tag['name']							= "class='form-control'";
$inputs->tag['date_start']							= "class='form-control' style='display:inline-block; width: 95%; margin-right: 10px;'";
$inputs->tag['date_end']							= "class='form-control' style='display:inline-block; width: 95%; margin-right: 10px';";
$inputs->tag['manager_staff_id']							= "class='form-control'";
$inputs->tag['staff']							= "class='form-control'";
$inputs->tag['remark']							= "class='form-control'";
$inputs->tag['submit_button']							= "class='button btn btn-default'";

$options									= sql_getArray("select id, name from staff order by name");
foreach ($options as $id => $name) {
	$options[$id]							= "<option value='$name'>$name</option>";
}
$options									= implode("", $options);

$inputs->desc2['staff']						= <<<EOS
<br>
<select class='form-control' id=person_select name=person_select style='width:75%;display:inline-block'>
	$options
</select>
<input class='btn btn-default' type=button value='加入' onclick='document.getElementById("staff").value += document.getElementById("person_select").value + "\\r\\n";'>
<input class='btn btn-default' type=button value='清除' onclick='document.getElementById("staff").value = "";'>
EOS;


$inputs->options['manager_staff_id']				= sql_getArray("select name, id from staff order by name asc");

?>
<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>


<form class='form-horizontal' name=form action='' method=post onsubmit='return form_check(this);'>
<input type=hidden name=action value=add>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

	if ($name == 'item') {


		echo "<div class='form-group'>
		    <label class='col-sm-2 control-label'>銷售產品</label>
				<div class='col-sm-8'>";
		echo "<div class='table-responsive'><table class='table table-borderless'>";

		$brand		= sql_getArray("select id ,description from class_brand order by description asc");

		foreach ($brand as $brand_id => $name) {

			$items	= sql_getArray("select id, name_series from item where brand='$brand_id' group by name_series order by name_series");

			echo "<tr><td colspan=3><div class='checkbox'><label><input type='checkbox' name='$name' value='$name' onclick='toogle_checkbox_$brand_id(this.checked);'> <strong><font color=#3377dd>$name (全選)</font></strong></label></div></td></tr>";
			echo "<tr>";
			$count		= 1;
			foreach ($items as $item_id => $item_name) {
				echo "<td width=33%><div class='checkbox'><label><input type='checkbox' name='item_id_$item_id' value='1'> $item_name</label></div></td>";
				$count++;
				if ($count > 3)	{
					$count = 1;
					echo "	 </tr><tr>";
				}
			}
			echo "</tr>";
			echo "<tr><td width=1 colspan=3 bgcolor='#ccc' style='padding: 2px;'></td></tr>";

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
		echo "</table></div></div></div>";
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

include_once "footer.php";

?>
