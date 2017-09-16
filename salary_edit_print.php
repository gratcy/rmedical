<?php

include "header_print.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='salary.php'");
if (empty($privilege->print))	{	gotoURL("salary.php"); exit; }



$id					= sql_secure($_GET['id']);
if(!empty($_GET['page'])){
	$page				= $_GET['page'];
}else{
	$page				=1;
}


$limits					=($page-1)*30;

$num_s					= sql_getValue("select count(*) from salary_detail where salary_id='$id'");

if($num_s<=30){
	$page_str			= "";
	$page_count			= 1;
}else{
	$page_str			= "<a href=salary_edit_print.php?id=$id&page=1>上一頁</a>/<a href=salary_edit_print.php?id=$id&page=2>下一頁</a>&nbsp;";
	$page_count			= 2;
}

$from_query				= $_GET['from_query'];



include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->prefix		= "cms::$id::";
$inputs->add(
			'salary_id'						, 'text'			, '編號'						, '50',
			'date_issue'					, 'text'			, '簽發日期'					, '10',
			'date_begin'					, 'text'			, '開始日期'					, '10',
			'date_end'						, 'text'			, '完結日期'					, '10',
			'issueby_id'					, 'select2'	, '簽發人'						, '50',
			'staff_id'						, 'select2'	, '受益人'						, '50',
			'amount_sales'					, 'text'			, '總銷售額'					, '10',
			'salary'						, 'text'			, '基本薪金'					, '10',
			'commission'					, 'text'			, '佣金'						, '10',
			'amount'						, 'text'			, '佣金總額'					, '10',
			'remark'						, 'text'			, '備註'						, '10',
			'submit'						, 'submit'			, '確定'						, '100'
				);



$salary_info								= sql_getVar("select * from salary where id='$id'");
$salary_info['amount_sales']				= number_format($salary_info['amount_sales'], 2);
$salary_info['commission']					= number_format($salary_info['commission'], 2);
$salary_info['salary']						= number_format($salary_info['salary'], 2);
$salary_info['amount']						= number_format($salary_info['amount'], 2);

$inputs->value 								= $salary_info;

array2obj($salary_info);

$inputs->options['issueby_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");
$inputs->options['staff_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");


$inputs->tag['salary']						= "class=number style='border-bottom:solid 1px #333333'";
$inputs->tag['commission']					= "class=number style='border-bottom:solid 1px #333333'";
$inputs->tag['amount']						= "class=number style='border-bottom:double 3px #333333'";
$inputs->tag['date_issue']					= "class=number";
$inputs->tag['date_begin']					= "class=number";
$inputs->tag['date_end']					= "class=number";
$inputs->tag['remark']						= "style='border:0px;'";
$inputs->tag['submit']						= "class=button";

$staff_id									= $inputs->value['staff_id'];
$staff_info									= sql_getObj("select * from staff where id='$staff_id'");
$staff_class								= sql_getValue("select description from class_staff where id='$staff_info->class'");

if ($staff_class == 'Promoter')				$staff_type			= "推廣員";
elseif ($staff_class == 'Sales')			$staff_type			= "推廣員";
elseif ($staff_class == 'Salesman')			$staff_type			= "推廣員";
else										$staff_type			= "受益人";




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
	<div style="width: 800px;text-align:right;">
		 第 $page 頁，共 $page_count 頁
	</div>

	<div class=backbutton>
		$page_str<input type=button value='返回' onclick='history.go(-1)'>
	</div>
	<div class=report_title>Notice of Service Fee</div>
</center>

<div class=height20></div>
<div class=height20></div>



<table width=800 height=300 cellpadding=2 cellspacing=5 border=0 align=center>
	<colgroup>
		<col width=20% />
		<col width=60% />
		<col width=20% align=right />
		<col width=20% align=right />
	</colgroup>
	<tr>
		<td>記錄編號</td>
		<td>$inputs->salary_id</td>
		<td>簽發日期</td>
		<td>$inputs->date_issue</td>
	</tr>
	<tr>
		<td>$staff_type</td>
		<td>$inputs->staff_id</td>
		<td colspan=2 align=right>$inputs->date_begin 至 $inputs->date_end</td>
	</tr>

	<tr>
		<td colspan=4 height=600 valign=top style='border-top:solid 2px #000000; border-bottom:solid 2px #000000; padding:0px;'>
		

<table width=100% cellspacing=0 cellpadding=3 border=0 bgcolor=#dddddd style='border:solid 1px #cccccc'>

	<colgroup>
		<col width=10% />
		<col width=50% />
		<col width=10% align=right />
		<col width=10% align=right />
		<col width=10% align=right />
		<col width=10% align=right />
	</colgroup>

	<tr height=30 style='font-weight:bold'>
		<td>日期</td>
		<td>客戶</td>
		<td style='padding-right:6px;'>銷售額</td>
		<td style='padding-right:6px;'>銷售費用</td>
		<td style='padding-right:6px;'>其他費用</td>
		<td style='padding-right:6px;'>總額</td>
	</tr>

EOS;

$items				= sql_getTable("select * from salary_detail where salary_id='$id' order by date_sales asc limit $limits,30");

$item_count			= count($items);

$count				= 0;

if (empty($items)) {
	echo "<tr id=empty_item bgcolor=#ffffff height=100><td colspan=20 align=center>暫時沒有記錄。</td></tr>";
}

foreach ($items as $item) {
	
	array2obj($item);
	
	$total							= $item->commission + $item->salary;
	
	$item->amount_sales				= number_format($item->amount_sales, 2);
	$item->commission				= number_format($item->commission, 2);
	$item->salary					= number_format($item->salary, 2);
	$total							= number_format($total, 2);
	
//	if ($total == 0)
//		continue;

	$customer_name					= sql_getValue("select name from customer where id='$item->customer_id'");
	
	echo "
			<tr height=1 bgcolor=#cccccc><td style='padding:0px' colspan=20></td></tr>
			<tr bgcolor=#ffffff>
				<td>$item->date_sales</td>
				<td>$customer_name</td>
				<td><input class=number type=text name=cms_item::$item->id::amount_sales	value='$item->amount_sales'	style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::commission		value='$item->commission'	style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::salary			value='$item->salary'		style='width:60px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::salary			value='$total'				style='width:60px;' ></td>
			</tr>
		";
	
	if ($count == 30) {
		echo "<tr style='page-break-after	: always;'><td colspan=20></td></tr>";
	}
	

	$count++;
	
}


$count_all							=sql_getValue("select count(*) from salary_detail where salary_id='$id' order by date_sales asc");

echo <<<EOS

</table>

		
		</td>
	</tr>

	<tr height=30>
		<td>記錄數</td>
		<td>$count_all</td>
		<td>銷售費用</td>
		<td>$inputs->commission</td>
	</tr>
	<tr height=30>
		<td>總銷售額</td>
		<td>$inputs->amount_sales</td>
		<td>其他費用</td>
		<td>$inputs->salary</td>
	</tr>
	<tr height=30>
		<td>備註</td>
		<td>$inputs->remark</td>
		<td>總額</td>
		<td>$inputs->amount</td>
	</tr>

	<tr height=100>
		<td></td>
		<td colspan=2></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td colspan=2 style='border-top:solid 2px #000000;'> Authorized Signature</td>
	</tr>


</table>




EOS;



include "footer.php";

?>
