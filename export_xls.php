<?php
ob_start();
include_once "inc_common.php";

Ignore_User_Abort(False);

$search_field		= $_POST['filter_field'];
$search_word		= $_POST['filter_word'];
$date_start			= $_POST['date_start'];
$date_end			= $_POST['date_end'];

if ($_POST['page'] == "invoice") {
	
	if ($search_field == "(select name from staff where id=invoice.staff_id)")
		$search_field			= "b.name";
		
	if ($search_field == "(select name from customer where id=invoice.customer_id)")
		$search_field			= "c.name";
	
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
		
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
	
	$titles		= array('出單編號','出單日期','顧客','總數','銷售總額','出單人');
	
	$sql		= "select a.invoice_id, a.date_order, c.name as customer_name,a.amount_gross as total,a.sales_record,b.name as staff_name 
					from invoice as a,staff as b ,customer as c 
					where a.staff_id=b.id and a.customer_id=c.id and $filter order by a.date_order desc";
}

if ($_POST['page'] == "item") {
	
	if ($search_field == "(select description from class_brand where id=item.brand)")
		$search_field			= "b.description";
		
	if ($search_field == "(select description from class_item where id=item.class)")
		$search_field			= "c.description";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
		

	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.item_id, b.description as class_brand, c.description as class_item, a.name, a.name_series, a.price
					from item as a,class_brand as b ,class_item as c 
					where a.brand=b.id and a.class=c.id and $filter order by a.item_id";
					
	$titles		= array('產品編號','品牌','種類','產品名稱','產品系列','產品價格');


}

if ($_POST['page'] == "customers") {
	
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	if ($search_field == 'cbirthday')
		$filter					= "$search_field = '".strtotime($search_word)."'";
	$sql		= "select cid,cname,from_unixtime(cbirthday,'%Y-%m-%d'),cemail,REPLACE(cphone,'*', ' / '),caddr FROM customer_tab WHERE $filter order by cid";
					
	$titles		= array('Customer ID','Name','Birthday','Email','Phone ','Address');
}

if ($_POST['page'] == "reporting") {
	$to = date('Y-m-d');
	$from = date('Y-m-d', strtotime('-1 month'));

	$store_id = isset($_GET['store_id']) ? $_GET['store_id'] : array();
	$manager_id = isset($_GET['managet_id']) ? $_GET['managet_id'] : array();
	$customer_id = isset($_GET['managet_id']) ? $_GET['customer_id'] : array();
	$staff_id = isset($_GET['staff_id']) ? $_GET['staff_id'] : array();
	$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : array();
	$reporttype = isset($_GET['reporttype']) ? (int) $_GET['reporttype'] : 0;
	$tpayment = isset($_GET['tpayment']) ? (int) $_GET['tpayment'] : '';
	$tstatus = isset($_GET['tstatus']) ? (int) $_GET['tstatus'] : '';
	$daterange = isset($_GET['daterange']) ? $_GET['daterange'] : $from.' - '.$to;

	$filter = "a.tstatus!=0";
	if ($daterange) {
		$daterange = explode(' - ', $daterange);
		$filter .= " AND (DATE(a.tdate)>='".$daterange[0]."' AND DATE(a.tdate)<='".$daterange[1]."')";
	}
	if (count($manager_id) > 0) $filter .= " AND e.id IN (".implode(",",$manager_id).")";
	if (count($staff_id) > 0) $filter .= " AND c.id IN (".implode(",",$staff_id).")";
	if (count($customer_id) > 0) $filter .= " AND d.cid IN (".implode(",",$customer_id).")";
	if (count($store_id) > 0) $filter .= " AND b.sid IN (".implode(",",$store_id).")";
	if ($tpayment != '') $filter .= " AND a.tpayment=" . $tpayment;
	if ($tstatus != '') $filter .= " AND a.tstatus=" . $tstatus;
	if (count($item_id) > 0 && $reporttype == 1) $filter .= " AND f.tpid IN (".implode(",",$item_id).")";
	$sql = array();
	if ($reporttype == 0) {
		$rsql = sql_getTable("select a.tdate,a.tno,e.name as manager,b.sname,c.name,d.cname,a.tqty,a.tammount,a.tdiscount,a.ttotal,a.tpayment,a.tstatus FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid JOIN staff e ON b.smanager=e.id where ".$filter." ORDER BY a.tid DESC");
		$titles = array('Date', 'SO No.', 'Manager', 'Store', 'Sales', 'Customer', 'QTY', 'Ammount', 'Discount (%)', 'Total', 'Payment', 'Status');
	}
	else {
		$rsql = sql_getTable("select a.tdate,a.tno,g.name,b.sname,d.cname,f.tqty,f.tprice,a.tdiscount,'' as ttotal,a.tpayment,a.tstatus FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid JOIN staff e ON b.smanager=e.id JOIN transaction_detail_tab f ON a.tid=f.ttid JOIN item g ON f.tpid=g.id where ".$filter." ORDER BY a.tid DESC");
		$titles = array('Date', 'SO No.', 'Products', 'Store', 'Customer', 'QTY', 'Price', 'Discount (%)', 'Total', 'Payment', 'Status');
	}
	
	foreach($rsql as $k => $v) {
		$v['tpayment'] = get_payment_type($v['tpayment']);
		$v['tstatus'] = $v['tstatus'] == 1 ? 'Active' : 'Approved';
		if ($reporttype == 1) $v['ttotal'] = $v['tprice'] - ($v['tprice'] * $v['tdiscount'] / 100);
		$sql[$k] = $v;
	}
}

if ($_POST['page'] == "transaction") {
	$filter = "(a.tstatus=1 OR a.tstatus=2)";
	if (!empty($search_word) && !empty($search_field)) {
		if ($search_field == 'tpayment') {
			if (strtolower($search_word) == 'debit') $search_word = 1;
			elseif (strtolower($search_word) == 'credit card') $search_word = 2;
			else $search_word = 0;
			$filter .= " AND $search_field=$search_word";
		}
		else {
			$filter .= " AND $search_field LIKE '%$search_word%'";
		}
	}
	$rsql = sql_getTable("select a.tdate,a.tno,b.sname,c.name,d.cname,a.tqty,a.tammount,a.tdiscount,a.ttotal,a.tpayment,a.tcardno,a.tstatus FROM transaction_tab a JOIN store_tab b ON a.tstore=b.sid JOIN staff c ON c.id=a.tsid JOIN customer_tab d ON a.tcid=d.cid where ".$filter." ORDER BY a.tid DESC");

	$sql = array();
	$i = 0;
	foreach($rsql as $k => $v) {
		$v['tpayment'] = get_payment_type($v['tpayment']);
		$v['tstatus'] = $v['tstatus'] == 1 ? 'Active' : 'Approved';
		$sql[$k] = $v;
		++$i;
	}
	$titles = array('Date','SO No.', 'Store', 'Sales', 'Customer', 'QTY', 'Ammount', 'Discount (%)', 'Total', 'Payment', 'Card No.', 'Status');
}

if ($_POST['page'] == "sales") {
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	$sql = "select c.sname,d.name as manager,a.staff_id,a.name,a.gender,a.mobile FROM staff a JOIN service_user b ON a.id=b.staff_id JOIN store_tab c ON b.store_id=c.sid JOIN staff d ON c.smanager=d.id WHERE $filter ORDER BY a.id DESC";
	$titles = array('Store', 'Manager','Staff ID', 'Name', 'Gender', 'Mobile');
}

if ($_POST['page'] == "store") {
	
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	$sql		= "select a.sid,b.name,a.sname,REPLACE(a.sphone,'*', ' / '),a.saddr FROM store_tab a LEFT JOIN usercontrol b ON a.smanager=b.sid WHERE $filter order by a.sid";
					
	$titles		= array('Store ID','Store Manager','Name','Phone ','Address');
}

if ($_POST['page'] == "customer") {

	if ($search_field == "(select description from class_customer where id=customer.class")
		$search_field			= "b.description";
	if ($search_field == "(select name from site where id=customer.site_id")
		$search_field			= "c.name";
	if ($search_field == "(select name from staff where id=customer.staff_id")
		$search_field			= "d.name";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.customer_id, a.name, b.description,a.address,a.tel,a.fax,a.email,a.website,a.payday,a.discount,c.name as site_name ,d.name as staff_name
					from customer as a,class_customer as b,site as c,staff as d 
					where a.class=b.id and a.site_id=c.id and a.staff_id=d.id and $filter order by a.customer_id";
					
	$titles		= array('客戶編號','客戶名稱','類別','地址 ','電話','傳真','電郵','網站','付款日期','折扣','銷售地點','銷售員');

}

if ($_POST['page'] == "customer_payment") {

	if ($search_field == "(select name from customer where customer.id=customer_payment.customer_id)")
		$search_field			= "b.description";
		
	if ($search_field == "(select description from class_customer join customer on class_customer.id=customer.class where customer_payment.id = customer.id)")
		$search_field			= "c.description";
	
	if ($search_field == "(select attention from customer where id=customer_payment.customer_id)")
		$search_field			= "b.description";
		
	if ($search_field == "(select name from staff where id=customer_payment.staff_id)")
		$search_field			= "c.description";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.payment_id, a.date, a.refno,b.name,c.description,b.attention,a.amount,d.name 
					from customer_payment as a,customer as b,class_customer as c ,staff as d 
					where a.customer_id=b.id and b.class=c.id and a.staff_id=d.id and $filter order by a.payment_id desc";
					
	$titles		= array('付款編號','付款日期','參考資料','客戶名稱','客戶類型 ','聯絡人 ','總額 ','收款人 ');

}

if ($_POST['page'] == "inventory") {
	if ($search_field == "(select name from staff where id=site.manager_staff_id)")
		$search_field			= "b.name";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.id, a.name, a.date_start,b.name as staff_name,a.quantity_3day_sold,a.quantity_need
					from site as a,staff as b
					where a.manager_staff_id=b.id and $filter order by a.id";
					
	$titles		= array('編號','名稱','開始日期','總負責人','三天內售出','需補貨產品');


}

if ($_POST['page'] == "inventory_transaction") {
	if ($search_field == "(select name from site where id=inventory_transaction.site_from)")
		$search_field			= "b.name";
		
	if ($search_field == "(select name from site where id=inventory_transaction.site_to)")
		$search_field			= "b.name";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.inventory_id, a.date, b.name, (select name from site where id=a.site_to) as site_to_name,a.type,a.remark
					from inventory_transaction as a,site as b
					where a.site_from=b.id and $filter order by a.id";
				 
	$titles		= array('編號','日期','出貨地點','收貨地點',' 類型',' 備注');


}

if ($_POST['page'] == "purchase_order") {
	if ($search_field == "(select name from supplier where id=purchase_order.supplier_id)")
		$search_field			= "b.name";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.purchase_order_id, a.date_order, b.name, a.quantity_sum,a.amount_gross 
					from purchase_order as a,supplier as b 
					where a.supplier_id=b.id  and $filter order by a.purchase_order_id";
					
	$titles		= array('採購單號','出單日期','供應商','貸件數量','總數');

}

if ($_POST['page'] == "salary") {
	if ($search_field == "(select name from staff where id=salary.staff_id)")
		$search_field			= "b.name";
		
	if ($search_field == "(select description from class_staff join staff on class_staff.id=staff.class where staff.id=salary.staff_id)")
		$search_field			= "c.description";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.salary_id, a.date_issue, concat(a.date_begin,' ~ ' , a.date_end) as date_between,a.amount,b.name,c.description 
					from salary as a,staff as b ,class_staff as c 
					where a.staff_id=b.id and b.class=c.id and $filter order by a.date_issue desc";
					
	$titles		= array('編號','日期','時期','總數','姓名','類別');

}

if ($_POST['page'] == "site") {
	if ($search_field == "(select name from staff where id=site.manager_staff_id")
		$search_field			= "b.name";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
					
	$sql		= "select a.id, a.name as site, a.date_start,a.date_end,b.name,a.remark 
					from site as a,staff as b
					where a.manager_staff_id=b.id and $filter order by a.id";
					
	$titles		= array('編號','名稱','開始日期','結束日期','總負責人','備注');

}

if ($_POST['page'] == "staff") {
	if ($search_field == "(select description from class_staff where id=staff.class)")
		$search_field			= "b.description";
		
	if ($search_field == "(select description from class_staff_group where id=staff.group)")
		$search_field			= "c.description";
		
	if ($search_field == "(select name from commission where id=staff.commission_id)")
		$search_field			= "d.name";
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select a.staff_id, a.name as staff_name, a.gender,a.birthday,a.address,a.idcard,a.tel,a.mobile ,a.email,b.description,c.description as group2,d.name,a.date_start
					from staff as a,class_staff as b ,class_staff_group as c ,commission as d
					where a.class=b.id and a.group=c.id and a.commission_id=d.id and $filter order by a.staff_id";
					
	$titles		= array('員工編號','姓名','性別','生日','地址','ID卡號碼','聯絡電話','手提電話','電郵','職位','組別','佣金計劃','入職日期');

}

if ($_POST['page'] == "supplier") {
		
	if (empty($search_word) || empty($search_field))	
		$filter					= 1;	
	else
		$filter					= "$search_field like '%$search_word%'";
	
	if ($date_start && $date_end) 
		 $filter				.= " and (date_order>= '$date_start' and date_order<= '$date_end')";
		
	$sql		= "select supplier_id,name,address,tel,fax,email,website,attention,payday,discount,date_modify 
				from supplier where $filter order by supplier_id";
					
	$titles		= array('供應商編號','名稱','地址','電話','傳真','電郵','網站','聯絡人','付款日期','折扣','更新日期');

}

/*
echo $sql;
exit;	
*/

/////////////////////////////////////
///output title to excel
////////////////////////////////////



/////////////////////////////////////
///output data to excel
////////////////////////////////////

ini_set('memory_limit', '128M');

if (!is_array($sql)) {
	foreach ($titles as $id => $title ) {
		$doc[0][$id]		=$title;
	}
	$data				= mysql_query($sql);

	$i			= 1;
	while($row = mysql_fetch_assoc($data)){

	  $doc[$i]			= htmlentities($row);
	  $i++;

	}
}
else {
	$data = array();
	foreach($sql as $key => $val) {
		foreach($val as $k => $v)
			$data[$key][$k] = htmlentities($v);
	}
	$doc = array_merge(array($titles),$data);
}
$xls 		= new Excel();


$xls->addArray ( $doc );

$filename	 = date('YmdHis');

$xls->generateXML ($_POST['page'].'-'.$filename);



ob_end_flush();
?>
