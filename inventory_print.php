<?php

include_once "header_print.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory.php'");
if (empty($privilege->print))	{	gotoURL("index.php"); exit; }


$site_id		= $_GET['id'] * 1;
$id				= $_GET['id'] * 1;


$from_query		= $_GET['from_query'];



include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'date'						, 'text_readonly'			, '列印日期'						, '30',
			'site'						, 'text_readonly'			, '倉存地點'						, '30',
			'sep1'						, ''						, '---'							, '20',
			'item'						, ''						, '產品數量'					, '400',
			'sep3'						, ''						, '---'							, '20',
			'submit_button'				, 'submit'					, '確定(S)'						, '100'
				);

$inputs->value['date']						= date("Y-m-d");
$inputs->value['site']						= sql_getValue("select name from site where id='$id'");






echo <<<EOS


<div id='paging_header' class=noprint></div>
<div class=height20></div>
<div class=height20></div>


<link href='style_report.css' rel='stylesheet' type='text/css' media='print'>

</td></tr></table>

	<center>
		<div class=report_title><input type=text value='磐石倉存表' class=size15 style='text-align:center'></div>
	</center>

<table width=800 cellpadding=0 cellspacing=0 border=0 align=center><tr><td style='padding : 0px 20px;' class=report_list_outer>




列印日期： $inputs->date <br>
倉存地點： $inputs->site <br>



EOS;



	echo "	<table width=100% border=1 cellspacing=0 cellpadding=0 style='border-collapse: collapse; border:solid 1px #aaaaaa;' >";
	
	echo "		<colgroup bgcolor=white>";
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

		echo "<tr><td colspan=8><strong><font color=#3377dd>$brand_name</font></strong></td></tr>";
		
		array_split($items, 2);
		array_flip_2d($items);

		foreach ($items as $row) {
			
			foreach ($row as $item) {
				
				array2obj($item);
				
				$item_id		= $item->id;
				$item_name		= $item->name;
				
				$stock			= sql_getValue("select ifnull(amount, 0) from inventory where site_id='$site_id' and item_id='$item_id'");
				$sold			= sql_getValue("select ifnull(sum(b.amount), 0) from inventory_transaction a join inventory_transaction_detail b on a.id=b.inventory_transaction_id where a.type='sold' and a.site_from='$site_id' and b.item_id='$item_id'");
				
				if (empty($stock))				$stock		= 0;
				if (empty($sold))				$sold		= 0;
				
				if ($stock == 0 && $sold == 0)
					continue;
					
				if ($row_count++ == 0)
					echo "<tr bgcolor=#ffffff>";

				
				echo "<td>$item_name</td>";
				echo "<td><input class=number type=text value='$stock' size=1 readonly style='color:#777777'></td>";
				echo "<td><input class=number type=text value='$sold' size=1 readonly style='color:#777777'></td>";
				echo "<td>&nbsp;</td>";
				
				if ($row_count == 2){
					echo "</tr>";
					$row_count	= 0;
				}

			
			}
		}
		
		
		if($row_count	== 1) {
			echo "<td colspan=3></td></tr>";
			$row_count	= 0;
			
		}
	}
		

echo <<<EOS
				
			</table>
		</td>
	</tr>
	<tr><td height=50 colspan=20 bgcolor='#ffffff'></td></tr>
</table>
EOS;
	

?>