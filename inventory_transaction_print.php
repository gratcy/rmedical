<?php

include "header_print.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='inventory_transaction.php'");
if (empty($privilege->print))	{	gotoURL("index.php"); exit; }


$it_id			= $_GET['id'] * 1;

$from_query		= $_GET['from_query'];



include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'date'						, 'text_readonly'			, '日期'						, '30',
			'site_from'					, 'text_readonly'			, '來源地'						, '30',
			'site_to'					, 'text_readonly'			, '目的地'						, '30',
			'type'						, 'text_readonly'			, '類型'						, '30',
			'remark'					, 'textarea'				, '備註'						, '400',
			'sep1'						, ''						, '---'							, '20',
			'item'						, ''						, '產品數量'					, '400',
			'sep3'						, ''						, '---'							, '20',
			'submit_button'				, 'submit'					, '確定(S)'						, '100'
				);

$inventory_transaction						= sql_getVar("select * from inventory_transaction where id='$it_id'");

$inputs->value								= $inventory_transaction;
$inputs->value['date']						= substr($inputs->value['date'], 0, 10);

$inputs->value['site_from']					= sql_getValue("select name from site where id='{$inputs->value['site_from']}'");
$inputs->value['site_to']					= sql_getValue("select name from site where id='{$inputs->value['site_to']}'");

array2obj($inventory_transaction);



echo <<<EOS

<style type="text/css">
@media screen {

	.print_page_header {
		display			: none;
	}
	
	.print_page_footer {
		display			: none;
	}
	
	.backbutton {
		display			: inline;
		width			: 800px;
		text-align		: right;
	}
	.backbutton input {
		background		: transparent;
		padding			: 3px;
		border			: 1px;
		border			: 1px solid;   
		background-color: #eeeeee;
		filter:progid:DXImageTransform.Microsoft.Gradient (GradientType=0,StartColorStr='#ffffffff',EndColorStr='#ffeeddaa');   

	}
}

@media print {
	.backbutton {
		display			: none;
	}
}
</style>



<div class=print_page_header></div>


<div class=height20></div>
<div class=height20></div>



<center>
	<div class=report_title><input type=text value='磐石出貨表' class=size15 style='text-align:center'></div>
</center>


<table width=800 cellpadding=0 cellspacing=0 border=0 align=center>
	<tr valign=top>
		<td style='padding : 0px 20px;' class=report_list_outer height=370px >




日期　　　： $inputs->date <br>
類型　　　： $inputs->type <br>
出貨來源地： $inputs->site_from <br>
出貨目的地： $inputs->site_to <br>



EOS;



	echo "	<table width=100%  border=1 cellspacing=0 cellpadding=0 style='border-collapse: collapse; border:solid 1px #aaaaaa;' >";
	
	echo "		<colgroup bgcolor=white>";
	echo "			<col width=25% align=left>";
	echo "			<col width=1% align=center>";
	echo "			<col width=2%>";
	echo "			<col width=25% align=left>";
	echo "			<col width=1% align=center>";
	echo "			<col width=2%>";
	echo "		</colgroup>\r\n";
	
	echo "		<tr>";
	echo "			<th>產品</th>";
	echo "			<th>件</th>";
	echo "			<th></th>";
	echo "			<th>產品</th>";
	echo "			<th>件</th>";
	echo "			<th></th>";
	echo "		</tr>\r\n";
	
	$item_site_from	= sql_getArray("select item_id from inventory where site_id='$inventory_transaction->site_from'");
	$item_site_to	= sql_getArray("select item_id from inventory where site_id='$inventory_transaction->site_to'");
	$all_items		= array_intersect($item_site_from, $item_site_to);
	
	
	$items_amount	= sql_getArray("select item_id, amount from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and site_id='$inventory_transaction->site_to'");		
	
	if (!empty($all_items)) {
		$brands			= sql_getArray("select distinct b.id, b.description from item a join class_brand b on a.brand=b.id where a.id in (" . implode(",", $all_items) . ") order by b.description asc");
	} else {
		echo "<tr height=50 bgcolor=white><td colspan=20 align=center>沒有記錄。</td></tr>\r\n";
		$brands			= array();
	}
	
	$total			= 0;
	$brand_count	= 0;
	$row_count		= 0;
	
	foreach ($brands as $brand_id => $brand_name) {

		$items		= sql_getTable("select @row := @row + 1 as item_order, id, name_short as name from item where brand='$brand_id' and id  in (" . implode(",", $all_items) . ") order by (name+0) asc");
		
		array_split($items, 2);
		array_flip_2d($items);
		
		foreach ($items as $row) {
			
			foreach ($row as $item) {
				
				array2obj($item);
				
				$item_id		= $item->id;
				$item_name		= $item->name;
				
				$amount			= $items_amount[$item_id];
				
				if (empty($amount))				$amount		= 0;
				
				if ($amount == 0)
					continue;
				
				if ($brand_count++ == 0)
					echo "<tr><td colspan=10><strong><font color=#3377dd>$brand_name</font></strong></td></tr>";
					
				if ($row_count == 0)
					echo "<tr>";

				
				echo "<td>$item_name</td>";
				echo "<td><input class=number type=text name='item_id_$item_id' value='$amount' size=1 onfocus='this.select();' tabindex=$item->item_order></td>";
				echo "<td></td>";
				
				$row_count++;
				
				
				if ($row_count == 2) {
					echo "</tr>";
					$row_count	= 0;
				}
				
				$total			= $total + $amount;
			}
							
		}
	
		if($row_count	== 1) {
			echo "<td colspan=3></td></tr>";
			$row_count	= 0;
			
		}
	$brand_count	= 0;
	}	

echo <<<EOS
				</tr>
				<tr><td colspan=5 align=right><b> 總共 :$total 件 </b></td><td></td></tr>	
			</table>
		</td>
	</tr>
	<tr><td height=50 colspan=20 bgcolor='#ffffff'></td></tr>
	<tr>
		<td colspan=20 style='padding : 0px 20px;' class=report_list_outer>
			<table width=100% cellpadding=2 cellspacing=1 border=0>
				<tr height=100>
					<td colspan=2 class=size15>
						<b>出貨人簽署</b>
						<br><br><br>
					</td>
					
					<td colspan=2 class=size15 align=right>
						<b>收貨人簽署</b>
						<br><br><br>
					</td>
				</tr>
				<tr>
					<td width=200 align=right style='border-top:solid 2px #000000;'>Authorized Signature</td><td></td>
					<td></td><td width=200 align=right style='border-top:solid 2px #000000;'>Authorized Signature</td>
				</tr>
				
				<tr><td height=20 colspan=20 bgcolor='#ffffff'></td></tr>
				<tr height=10>
					<td colspan=4 align=center><b>E. & O. E.</b></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
EOS;

	
include "footer.php";

?>