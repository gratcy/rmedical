<?php

include "header_print.php";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='invoice.php'");
if (empty($privilege->print))	{	gotoURL("invoice.php"); exit; }



$id					= sql_secure($_GET['id']);

if(!empty($_GET['page'])){
	$page				= $_GET['page'];
}else{
	$page				= 1;
}

$num_s					= sql_getValue("select count(*) from invoice_detail where invoice_id='$id'");

if($num_s < 30 || $num_s == 30){
	$page_count			= 1;
}
if($num_s % 30){
	$page_count			=(int)($num_s / 30) + 1; 
}else{
	$page_count			= $num_s /  30; 
}

$limits					=($page-1)*30;

if($page_count==1){
	$page_str 			= '' ;
}else{
	$page_str			= "<a href=invoice_edit_print.php?id=$id&page=".($page-1).">上一頁</a>/<a href=invoice_edit_print.php?id=$id&page=".($page+1).">下一頁</a>&nbsp;";
}
if($page == $page_count && $page!=1){
   $page_str 			= "<a href=invoice_edit_print.php?id=$id&page=".($page-1).">上一頁</a>/尾页";
}

$from_query			= $_GET['from_query'];

include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->prefix		= "cms::$id::";
$inputs->add(
			'invoice_id'					, 'text'			, '資料'						, '50',
			'customer_info'					, 'textarea'		, '資料'						, '420',
			'date_issue'					, 'text'			, '資料'						, '10',
			'date_begin'					, 'text'			, '資料'						, '10',
			'date_end'						, 'text'			, '資料'						, '10',
			'amount_gross'					, 'text'			, '資料'						, '10',
			'amount_net'					, 'text'			, '資料'						, '10',
			'deposit'						, 'text'			, '資料'						, '10',
			'balance'						, 'text'			, '資料'						, '10',
			'remark'						, 'textarea'		, '備註'						, '420',
			'submit_button'					, 'submit'			, '確定'						, '100'
				);



$invoice_info								= sql_getVar("select * from invoice where id='$id'");
$customer_info								= sql_getObj("select * from customer where id='{$invoice_info['customer_id']}'");
$staff_info									= sql_getObj("select * from staff where id='{$invoice_info['staff_id']}'");
//~ $invoice_info['customer_info']				= "$customer_info->name\r\n$customer_info->address\r\n\r\n$customer_info->attention\r\nTel:$customer_info->tel\r\nFax:$customer_info->fax";
$invoice_info['customer_info']				= "$customer_info->name<br />$customer_info->address<br /><br />$customer_info->attention<br />Tel:$customer_info->tel<br />Fax:$customer_info->fax";

$npa = "$customer_info->name<br />";
$npa .= ($customer_info->address ? $customer_info->address."<br />" : "");
$npa .= ($customer_info->attention ? $customer_info->attention."<br />" : "");
$npa .= ($customer_info->tel ? $customer_info->tel."<br />" : "");
$npa .= ($customer_info->fax ? $customer_info->fax."<br />" : "");

$inputs->value 	= $invoice_info;

array2obj($invoice_info);

$inputs->options['issueby_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");
$inputs->options['staff_id']				= sql_getArray("select a.name, a.id from staff a join class_staff b on a.`class`=b.id order by a.`class`, a.name");


$inputs->tag['invoice']						= "class=number style='border-bottom:solid 1px #333333'";
$inputs->tag['commission']					= "class=number style='border-bottom:solid 1px #333333'";
$inputs->tag['amount']						= "class=number style='border-bottom:double 3px #333333'";
$inputs->tag['amount_gross']				= "class=number";
$inputs->tag['amount_net']					= "class=number";
$inputs->tag['deposit']						= "class=number";
$inputs->tag['balance']						= "class=number";
$inputs->tag['remark']						= "style='border:0px; font-family:Arial;'";
$inputs->tag['customer_info']				= "style='border:0px; font-family:Arial;'";
$inputs->tag['submit_button']				= "class=button";

$staff_id									= $inputs->value['staff_id'];
$staff_info									= sql_getObj("select * from staff where id='$staff_id'");
$staff_class								= sql_getValue("select description from class_staff where id='$staff_info->class'");

if ($staff_class == 'Promoter')			$staff_type			= "推廣員";
elseif ($staff_class == 'Sales')		$staff_type			= "推廣員";
elseif ($staff_class == 'Salesman')		$staff_type			= "推廣員";
else									$staff_type			= "受益人";



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
.logo {text-align:center;border-bottom: 2px solid #000;width: 800px;margin: 0 auto;}

</style>



<div class="logo"><img src="/images/print_header_logo.jpg"></div>
<div class=height20></div>
<div class=height20></div>

EOS;

if ($_GET['noreturn'] == 1) {
	echo "	<center>
				<div style='width: 800px;text-align:right;display:block'>第 $page 頁，共 $page_count 頁<br></div>
				<div class=backbutton>$page_str</div>
				<div class=report_title><input type=text value='INVOICE' class=size15 style='text-align:center'></div>
			</center>";

}else {
	echo "	<center>
				<div style='width: 800px;text-align:right;display:block'>第 $page 頁，共 $page_count 頁<br></div>
				<div class=backbutton>$page_str</div>
				<div class=report_title><input type=text value='INVOICE' class=size15 style='text-align:center'></div>
			</center>";
}


echo <<<EOS

<div class=height20></div>
<div class=height20></div>



<table width=800 height=300 cellpadding=2 cellspacing=5 border=0 align=center>
	<colgroup>
		<col width=15% valign=top />
		<col width=60% valign=top />
		<col width=25% valign=top align=right />
		<col width=20% valign=top align=right />
	</colgroup>
	<tr>
		<td>Customer :</td>
		<td>$npa</td>
		<td>Invoice No : <br>Invoice Date :<br>Delivery Terms : <br>Payment Terms : <br>Issue By : </td>
		<td align=left>$invoice_info->invoice_id<br>$invoice_info->date_order<br>$invoice_info->deliveryterms<br>$invoice_info->paymentterms<br>$staff_info->name<br></td>
	</tr>

	<tr>
		<td colspan=4 height=600 valign=top style='border-top:solid 2px #000000; padding:0px;'>
		

<table width=100% cellspacing=0 cellpadding=3 border=0 bgcolor=#dddddd style='border:solid 0px #cccccc'>

	<colgroup>
		<col width=10% valign=top />
		<col width=50% valign=top />
		<col width=10% valign=top align=right />
		<col width=10% valign=top align=right />
		<col width=10% valign=top align=right />
	</colgroup>

	<tr height=30 style='font-weight:bold;'>
		<td>Product No.</td>
		<td>Description</td>
		<td style='padding-right:6px;'>PCS.</td>
		<td style='padding-right:6px;'>Unit Price (\$)<br>(HKD)</td>
		<td style='padding-right:6px;'>Amount (\$)<br>(HKD)</td>
	</tr>

EOS;


$items				= sql_getTable("select * from invoice_detail where invoice_id='$id' limit $limits,30");
$item_count			= count($items);

$count				= 0;

if (empty($items)) {
	echo "<tr id=empty_item bgcolor=#ffffff height=100><td colspan=20 align=center>No records.</td></tr>";
}


foreach ($items as $item) {
	
	array2obj($item);
	
	$total						= $item->commission + $item->invoice;
	
	$item_info					= sql_getObj("select * from item where id='$item->item_id'");
	
	$item->price				= number_format($item->price, 2);
	$item->amount				= number_format($item->amount, 2);
	
	echo "
			<tr height=1 bgcolor=#cccccc><td style='padding:0px' colspan=20></td></tr>
			<tr bgcolor=#ffffff>
				<td>$item_info->item_id</td>
				<td>$item->name</td>
				<td><input class=number type=text name=cms_item::$item->id::amount_sales	value='$item->quantity'	style='width:70px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::commission		value='$item->price'	style='width:70px;' ></td>
				<td><input class=number type=text name=cms_item::$item->id::invoice			value='$item->amount'	style='width:70px;' ></td>
			</tr>
		";
	
	
	$count++;
	
}





echo <<<EOS

</table>

		
		</td>
	</tr>

	<tr height=2 style='padding:0px;'>
		<td rowspan=5>Remark : </td>
		<td rowspan=5>$inputs->remark</td>
		<td colspan=2 bgcolor=#000000></td>
	</tr>
	<tr>
		<td>Gross Total : HKD $</td>
		<td>$inputs->amount_gross</td>
	</tr>
	<tr>
		<td>Net Total : HKD $</td>
		<td>$inputs->amount_net</td>
	</tr>
	<tr>
		<td>Deposit : HKD $</td>
		<td>$inputs->deposit</td>
	</tr>
	<tr>
		<td>Balance : HKD $</td>
		<td>$inputs->balance</td>
	</tr>

	<tr height=10>
		<td colspan=4></td>
	</tr>
	
	<tr height=100>
		<td colspan=2 class=size15>
			Cheque should be crossed and made payable to<br>
			<b>"ROCK TRADING COMPANY LIMITED"</b><br>
			<b>　　　磐 石 貿 易 有 限 公 司</b>
		</td>
		
		<td colspan=2 class=size15>
			<b>ROCK TRADING COMPANY LIMITED</b>
			<br><br><br><br>
		</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td colspan=2 style='border-top:solid 2px #000000;'> Authorized Signature</td>
	</tr>

	<tr height=10>
		<td colspan=4 align=center><b>E. & O. E.</b></td>
	</tr>
	


</table>




EOS;


include "footer.php";



?>
