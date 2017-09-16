<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory_transaction.php'");
if (empty($privilege->edit))	{	gotoURL("index.php"); exit; }


$it_id			= $_GET['id'] * 1;

$from_query		= $_GET['from_query'];


if ($_SESSION["inventory_newrecord_$it_id"] === true) {
	echo "<h3 class='pull-left'>編輯倉存調整</h3>";
} else {
	echo "<h3 class='pull-left'>查看倉存調整</h3>";
}


if ($_POST['action'] == 'edit') {

	if ($_SESSION["inventory_newrecord_$it_id"] === true) {
		unset($_SESSION["inventory_newrecord_$it_id"]);
	} else {
		$fields['remark']			= sql_secure($_POST['remark']);
		sql_query(sql_update("inventory_transaction", $fields, "id='$it_id'"));
		alert("編輯記錄成功。");
		gotoURL("inventory_transaction.php?$from_query", 0);
		exit;
	}


	$fields						= array();
	$fields['date_modify']		= date("Y-m-d H:i:s");
	$fields['modify_user']		= $user->id;
	$fields['remark']			= sql_secure($_POST['remark']);

	sql_query(sql_update("inventory_transaction", $fields, "id='$it_id'"));

	include "inventory_library.php";


	$inventory_transaction		= sql_getObj("select * from inventory_transaction where id='$it_id'");

	$items_amount				= sql_getArray("select item_id, amount from inventory where site_id='$inventory_transaction->site_from'");

	$items						= array();

	foreach ($_POST as $name => $value) {

		if (!startWith($name, "item_id"))		continue;

		$item_id				= substr($name, 8) * 1;

		$value					= $value * 1;

		if ($value == $items_amount[$item_id])	continue;

		$items[$item_id]		= $items_amount[$item_id] - $value * 1;

	}


	inventory_transaction_detail_save($inventory_transaction, $items);
	inventory_stock_statistic_update($inventory_transaction->site_from);
	inventory_adjust_stock_recalculate($it_id);


	alert("編輯記錄成功。");
	gotoURL("inventory_transaction.php?$from_query", 0);
	exit;


}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'site_from'					, 'text_readonly'			, '銷售地點'					, '100%',
			'type'						, 'text_readonly'			, '事件類型'					, '100%',
			'date'						, 'text_readonly'			, '事件日期'					, '100%',
			'remark'					, 'textarea'				, '備註'						, '100%',
			'sep1'						, ''						, '---'							, '100%',
			'item'						, ''						, '產品數量'					, '100%',
			'sep3'						, ''						, '---'							, '100%',
			'submit_button'				, 'submit'					, '確定(S)'						, '100%'
				);

$inventory_transaction						= sql_getVar("select * from inventory_transaction where id='$it_id'");

$inputs->value								= $inventory_transaction;
$inputs->value['date']						= substr($inputs->value['date'], 0, 10);

$inputs->value['site_from']					= sql_getValue("select name from site where id='{$inputs->value['site_from']}'");


$inputs->tag['type']						= "readonly";


$inputs->tag['submit_button']				= "class=button";


array2obj($inventory_transaction);


?>
<link rel="stylesheet" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>

<input class="btn btn-default pull-right" style='width:80px;' type=button value='返回 (B)' onclick='history.go(-1);'>

<br>
<br>
<br>

<form name=form action='' method=post onsubmit='return form_check(this);' class='form-horizontal'>
<input type=hidden name=action value=edit>
<?php

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
		echo "			<col width=1% align=center>";
		echo "			<col width=5%>";
		echo "			<col width=25% align=left>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=5%>";
		echo "			<col width=25% align=left>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=1% align=center>";
		echo "			<col width=5%>";
		echo "		</colgroup>\r\n";

		echo "		<tr>";

		if ($_SESSION["inventory_newrecord_$it_id"] === true) {
			echo "			<th>產品</th>";
			echo "			<th>存</th>";
			echo "			<th>賣</th>";
			echo "			<th>數量</th>";
			echo "			<th></th>";
			echo "			<th>產品</th>";
			echo "			<th>存</th>";
			echo "			<th>賣</th>";
			echo "			<th>數量</th>";
			echo "			<th></th>";
			echo "			<th>產品</th>";
			echo "			<th>存</th>";
			echo "			<th>賣</th>";
			echo "			<th>數量</th>";
			echo "			<th></th>";
		} else {
			echo "			<th>產品</th>";
			echo "			<th>舊</th>";
			echo "			<th>新</th>";
			echo "			<th>變更</th>";
			echo "			<th></th>";
			echo "			<th>產品</th>";
			echo "			<th>舊</th>";
			echo "			<th>新</th>";
			echo "			<th>變更</th>";
			echo "			<th></th>";
			echo "			<th>產品</th>";
			echo "			<th>舊</th>";
			echo "			<th>新</th>";
			echo "			<th>變更</th>";
			echo "			<th></th>";
		}
		echo "		</tr>\r\n";

//		("select * from inventory a where site_id='$site_from' and exists (select item_id from inventory b where site_id='$site_to' and a.item_id=b.item_id)");
		$item_site_from	= sql_getArray("select item_id from inventory where site_id='$inventory_transaction->site_from'");

		$all_items		= $item_site_from;


		if (!empty($all_items)) {
			$items_amount	= sql_getArray("select item_id, amount from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and site_id='$inventory_transaction->site_from'");
			$brands			= sql_getArray("select distinct b.id, b.description from item a join class_brand b on a.brand=b.id where a.id in (" . implode(",", $all_items) . ") order by b.description asc");
		} else {
			echo "<tr height=50 bgcolor=white><td colspan=20 align=center>沒有記錄。</td></tr>\r\n";
			$brands			= array();
		}


		$sold_end_date		= date("Y-m-d", strtotime($inventory_transaction->date));
		$sold_start_date	= date("Y-m-d", strtotime($inventory_transaction->date) - 3600 * 24 * 30);

		$tabindex		= 1;

		sql_query("SELECT @row := 0");

		foreach ($brands as $brand_id => $brand_name) {

			$items		= sql_getTable("select @row := @row + 1 as item_order, id, name_short as name from item where brand='$brand_id' and id  in (" . implode(",", $all_items) . ") order by name asc");

			echo "<tr><td colspan=3><strong><font color=#3377dd>$brand_name</font></strong></td></tr>";

			array_split($items, 3);
			array_flip_2d($items);

			foreach ($items as $row) {

				echo "<tr>";

				foreach ($row as $item) {

					array2obj($item);

					$item_id		= $item->id;
					$item_name		= $item->name;

					if ($_SESSION["inventory_newrecord_$it_id"] === true) {
						$stock			= sql_getValue("select ifnull(amount, 0) from inventory where site_id='$inventory_transaction->site_from' and item_id='$item_id'");
						$sold			= sql_getValue("select 0 - ifnull(sum(b.amount), 0) from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where a.type='sold' and a.site_from='$inventory_transaction->site_to' and b.item_id='$item_id' and (date >= '$sold_start_date' and date < '$sold_end_date')");
						$amount			= $stock;
						echo "<td>$item_name</td>";
						echo "<td><input class=number type=text value='$stock' size=1 readonly style='color:#777777' tabindex=200$tabindex></td>";
						echo "<td><input class=number type=text value='$sold' size=1 readonly style='color:#777777' tabindex=300$tabindex></td>";
						echo "<td><input class=number type=text name='item_id_$item_id' value='$amount' size=1 onfocus='this.select();' tabindex=$item->item_order></td>";
						echo "<td></td>\r\n";
					} else {
						$stock			= sql_getObj("select * from inventory_transaction_detail where inventory_transaction_id='$it_id' and item_id='$item_id'");
						if (empty($stock))				$stock		= 0;
						if (empty($sold))				$sold		= 0;
						if (empty($amount))				$amount		= 0;
						$amount			= $items_amount[$item_id];

						echo "<td>$item_name</td>";
						echo "<td><input class=number type=text value='$stock->change_from' size=1 readonly style='color:#777777' tabindex=200$tabindex></td>";
						echo "<td><input class=number type=text value='$stock->change_to' size=1 readonly style='color:#777777' tabindex=300$tabindex></td>";
						echo "<td><input class=number type=text name='item_id_$item_id' value='$amount' size=1 readonly onfocus='this.select();' tabindex=$item->item_order></td>";
						echo "<td></td>\r\n";
					}

					$tabindex++;

				}

				echo "</tr>\r\n";

			}

			echo "<tr><td width=1 colspan=20 bgcolor='#ccc' style='padding: 2px;'></td></tr>\r\n";

		}
		echo "</table></div></div></div>";
		echo "<div class='form-group'><label class='col-sm-2 control-label'></label><div class='col-sm-8'>* 「賣」即是三十日內賣出的產品數量</div></div>";
		echo "</div>";
		continue;
	}

	if ($desc == '---')
		echo "<hr width=100% size=1 align=left class=noprint>";
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