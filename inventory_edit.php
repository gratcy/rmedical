<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory.php'");
if (empty($privilege->edit))	{	gotoURL("site.php"); exit; }


$id					= $_GET['id'] * 1;
$site_id			= $_GET['id'] * 1;


if ($_POST['action'] == 'add' && false) {

	$error						= array();

	$fields						= sql_secure($_POST, "remark");

	$fields['date_modify']		= date("Y-m-d H:i:s");

	if (empty($error)) {

		sql_query(sql_insert("site", $fields));

		alert("編輯記錄成功。");
		gotoURL("inventory_transaction.php?$from_query", 0);

		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'day'						, 'select'			, '天數'						, '100%'
				);

$day							= $_POST['day'];
if (empty($day))   $day			= 30;

$inputs->value['day']			= $day;
$inputs->options['day']			= array (3=>3,5=>5,7=>7,15=>15,30=>30);

echo <<<EOS



<h3 class='pull-left'>查看倉存</h3>
<input class='btn btn-default pull-right' type=button value='返回 (B)' onclick='history.go(-1);'>

<br><br><br>


<form id=search_box action='inventory_edit.php?id=$id' method='POST' class='form-horizontal'>
	<div class='form-group'>
    <label for='input_day' class='col-sm-2 control-label'>賣出量計算天數</label>
		<div class='col-sm-8'>
			{$inputs->day}
		</div>
		<div class='col-sm-2'>
	    <input class='btn btn-default' type=submit value='確定'>
	  </div>
	</div>
</form>



EOS;


$inputs		= new Inputs();
$inputs->add(
			'name'						, 'text_readonly'			, '地點'						, '100%',
//			'type'						, 'text_readonly'			, '類型'						, '100%',
			'sep1'						, ''						, '---'							, '100%',
			'item'						, ''						, '產品數量'					, '100%',
			'sep3'						, ''						, '---'							, '100%'
//			'submit_button'				, 'submit'					, '確定(S)'						, '100%'
				);

$site										= sql_getVar("select * from site where id='$site_id'");
$inputs->value								= $site;
array2obj($site);

$inputs->value['site_from']					= sql_getValue("select name from site where id='{$inputs->value['site_from']}'");
$inputs->value['site_to']					= sql_getValue("select name from site where id='{$inputs->value['site_to']}'");


$inputs->tag['site_from']					= "readonly";
$inputs->tag['site_to']						= "readonly";
$inputs->tag['type']						= "readonly";


$inputs->tag['submit_button']				= "class=button";

echo <<<EOS

<form name=form action='' method=post onsubmit='return form_check(this);' class='form-horizontal'>
<input type=hidden name=action value=add>


EOS;


foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

	if ($name == 'item') {

		echo "<div class='form-group'>";
		echo "<label class='col-sm-2 control-label'>銷售產品</label>";
		echo "<div class='col-sm-8'>";
		echo "<div class='table-responsive'>";
		echo "	<table class='table table-borderless'>";

		echo "		<colgroup>";
		echo "			<col width=25% align=left>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=6%>";
		echo "			<col width=25% align=left>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=6%>";
		echo "			<col width=25% align=left>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=6%>";
		echo "		</colgroup>\r\n";

		echo "		<tr>";
		echo "			<th>產品</th>";
		echo "			<th>存</th>";
		echo "			<th>賣</th>";
		echo "			<th></th>";
		echo "			<th>產品</th>";
		echo "			<th>存</th>";
		echo "			<th>賣</th>";
		echo "			<th></th>";
		echo "			<th>產品</th>";
		echo "			<th>存</th>";
		echo "			<th>賣</th>";
		echo "			<th></th>";
		echo "		</tr>\r\n";


		$all_items		= sql_getArray("select item_id from inventory where site_id='$id'");


		if (!empty($all_items))
			$brands			= sql_getArray("select distinct b.id, b.description from item a join class_brand b on a.brand=b.id where a.id in (" . implode(",", $all_items) . ") order by b.description asc");
		else {
			echo "<tr height=50 bgcolor=white><td colspan=20 align=center>沒有記錄。</td></tr>\r\n";
			$brands			= array();
		}


		foreach ($brands as $brand_id => $brand_name) {

			$items		= sql_getTable("select id, name_short as name from item where brand='$brand_id' and id  in (" . implode(",", $all_items) . ") order by name asc");

			echo "<tr><td colspan=3><strong><font color=#3377dd>$brand_name</font></strong></td></tr>";

			array_split($items, 3);
			array_flip_2d($items);

			foreach ($items as $row) {

				echo "<tr>";

				foreach ($row as $item) {

					array2obj($item);

					$item_id		= $item->id;
					$item_name		= $item->name;

					$stock			= sql_getValue("select ifnull(amount, 0) from inventory where site_id='$site_id' and item_id='$item_id'");
					$sold			= sql_getValue("select ifnull(sum(b.amount), 0) from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where a.type='sold' and a.site_from='$site_id' and b.item_id='$item_id' and (adddate(a.date, interval $day day) > now())");

					$sold			= $sold * -1;

					$stock_color	= ($stock < 0) ? "red" : "#444444";
					$sold_color		= ($stock < 0) ? "blue" : "#444444";

					echo "<td>$item_name</td>";
					echo "<td><input type=text value='$stock' size=1 readonly style='color:$stock_color' class=number></td>";
					echo "<td><input type=text value='$sold' size=1 readonly style='color:$sold_color' class=number></td>";
					echo "<td align=center><a href='inventory_check.php?site=$site_id&item=$item_id' target=_blank>查看</a></td>";

				}

				echo "</tr>\r\n";

			}

			echo "<tr><td width=1 colspan=20 bgcolor='#ccc' style='padding: 2px;'></td></tr>";

		}
		echo "</table></div></div>";
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


</script>
<?php

include "footer.php";

?>