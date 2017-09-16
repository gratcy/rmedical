<?php

include_once "header.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='system_setting.php'");

if (empty($privilege->view))	{	gotoURL("index.php"); exit; }

$table			= $_GET['folder'];
if (empty($table)) $table	= "class_brand";

if (isset($_GET['delete'])) {

	$id		= $_GET['delete'] * 1;
	sql_query("delete from $table where id='$id'");
	gotoURL(-1);
	exit;
}
if (isset($_GET['add'])) {

	sql_query("insert into $table (description) values ('')");
	gotoURL(-1);
	exit;

}



echo "<h3>系統設定</h3><br>";

echo "
	<div class='pull-right'>
		<i class='fa fa-plus fa-exit'></i>&nbsp;&nbsp;<a href='system_setting.php?folder=$table&add'>新增描述</a>
	</div>
";

echo "
<div class='table-responsive'>
<table class='table table-borderless'>
	<tr>
		<td align=left><i class='fa fa-cog fa-exit'></i> &nbsp;&nbsp;&nbsp;";

	echo "[ <a href=system_setting.php?folder=class_brand>牌子類別</a> ] &nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_customer>顧客類別</a> ] &nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_item>產品種類</a> ]&nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_money>貨幣種類</a> ]&nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_staff>員工類別</a> ] &nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_staff_group>員工組別</a> ] &nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_supplier>供應商類別</a> ] &nbsp;&nbsp;&nbsp;";
	echo "[ <a href=system_setting.php?folder=class_event>事件類別</a> ] &nbsp;&nbsp;&nbsp;";


echo "
		</td>
	</tr>
</table>
</div>
";


if ($table == "class_brand"){
	echo "<font style='font-size:18px;font-weight:bold'>牌子類別</font>";
}
if ($table == "class_customer"){
	echo "<font style='font-size:18px;font-weight:bold'>顧客類別</font>";
}
if ($table == "class_item"){
	echo "<font style='font-size:18px;font-weight:bold'>產品種類</font>";
}
if ($table == "class_money"){
	echo "<font style='font-size:18px;font-weight:bold'>貨幣種類</font>";
}
if ($table == "class_staff"){
	echo "<font style='font-size:18px;font-weight:bold'>員工類別</font>";
}
if ($table == "class_staff_group"){
	echo "<font style='font-size:18px;font-weight:bold'>員工組別</font>";
}
if ($table == "class_supplier"){
	echo "<font style='font-size:18px;font-weight:bold'>供應商類別</font>";
}
if ($table == "class_event"){
	echo "<font style='font-size:18px;font-weight:bold'>事件類別</font>";
}




$columns		= array(
			"#1"					=> "#",
			"description"			=> "描述",
			"#2"					=> "刪除"
						);
$columns_class	= array(
			"#2"				=> "text-center",

						);


include_once "bin/class_inputs.php";
$inputs			= new Inputs();


echo "
<div class='table-responsive'>
<table class='table table-bordered'>
	<tr>";

	// Print Column Names
	foreach ($columns as $field => $column) {

			$class			= (isset($columns_class[$field])) ? "class=" . $columns_class[$field] : "";
    	echo "<td $class><b>$column</b><br></td>\n";
	}

echo "	</tr> ";




$items				= sql_getTable("select * from $table order by id asc ");

if (empty($items)) {

	echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有記錄。</td></tr>\r\n";

}



echo "<form action='' method=post>";
echo "<input type=hidden name=update value=1>";

$count = 0;
foreach ($items as $item) {

	array2obj($item);

	$bgcolor 		= ($count++ % 2 == 0) ? '#ffffff' : '#eeeeee';


	//	Prepare inputs
	$inputs->clear();
	$inputs->add(
			"item::$item->id::id"						, 'text'		, 'id'							, '15',
			"item::$item->id::description"				, 'text'		, 'description'					, '80'
			);

	$inputs->value["item::$item->id::id"]						= $item->id;
	$inputs->value["item::$item->id::description"]				= $item->description;

	$arr_id[]							=$item->id;
	$arr_description[]					=$item->description;
	$id									= $inputs->toString("item::$item->id::id");
	$description						= $inputs->toString("item::$item->id::description");



	echo "<tr bgcolor=$bgcolor>";
	echo "<td width='10%' align=center>$id</a>";
	echo "<td width='80%'>$description</td>";


if (!empty($privilege->delete))
	echo "<td width='10%' align=center>
					<a href='javascript:if (confirm(\"確定要刪除 ?\")) location.href=\"" . getURL() . "&delete=$item->id\";'><i class='fa fa-times' style='padding: 10px;'></i></a>
					</td>";
else echo "<td width=70></td>";
	echo "</tr>";


}


echo "<tr bgcolor=#eeeeee><td colspan=20 align=center><input class='btn btn-default' type=submit value='確定' name='save'></td></tr>";

echo "</form>";
echo "</table>";


if (isset($_POST['save'])) {
	$lastid=$arr_id[count($arr_id)-1];
	$lastdescription=$_POST["item::$lastid::description"];
	sql_query("update $table  set description='$lastdescription' where id=$lastid");
	gotoURL(-1);
	exit;

}
include "footer.php";

?>