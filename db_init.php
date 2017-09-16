<?php


include "inc_common.php";

/*
sql_query("
CREATE TABLE `service_user` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`user` varchar( 100 ) NOT NULL ,
`email` varchar( 100 ) NOT NULL ,
`password` varchar( 100 ) NOT NULL ,
`name` varchar( 100 ) NOT NULL ,
`group` text NOT NULL ,
`privilege` text NOT NULL ,
`extra_privilege` text NOT NULL ,
`edit_password` varchar( 100 ) NOT NULL ,
`remark` text NOT NULL ,
`status` varchar( 30 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ENGINE = MYISAM DEFAULT CHARSET = utf8;
		");
		
		
sql_query("
INSERT INTO `service_user` 
SELECT * 
FROM `everyone_is`.`service_user` ;
		");

sql_query("
delete from service_user where user !='admin'
		");


sql_query("insert into service_user (id, user, email, password, name, edit_password, remark) select id, loginname, '', md5('123456'), name, 'Y', id from usercontrol");


$privilege			= sql_getValue("select privilege from service_user where user='admin'");
sql_query("update service_user set privilege='$privilege'");
*/

$sqls				= <<<EOS

---
DROP TABLE `pg_ts_cfg`, `pg_ts_cfgmap`, `pg_ts_dict`, `pg_ts_parser`;
---
DROP TABLE `paymentsup`, `paymentsup_dtl`, `stockadj`, `stockadj_dtl`, `setting` ;
---
DROP TABLE `invoicereturned`, `invoicereturned_dtl`, `invoicereturned_free`, `warranty`;
---

RENAME TABLE `product` TO `item` ;
---
RENAME TABLE `product_brand` TO `item_brand` ;
---
RENAME TABLE `product_class` TO `item_class` ;
---






alter TABLE `item` 
			  DROP `specification`,
			  DROP `capacity`,
			  DROP `unit`,
			  DROP `purpose`,
			  DROP `supplier_pid`,
			  DROP `pic_name`;

---

ALTER TABLE `item` CHANGE `description` `name` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 
---
ALTER TABLE `item` CHANGE `salesprice` `price` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `item` CHANGE `brand_product` `brand_product` VARCHAR( 200 ) NOT NULL 
---
ALTER TABLE `item` CHANGE `class_product` `class_product` VARCHAR( 200 ) NOT NULL 
---
ALTER TABLE `item` CHANGE `brand_product` `brand` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `item` CHANGE `class_product` `class` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `item` CHANGE `bdate` `date_modify` DATETIME NULL DEFAULT NULL 
---
ALTER TABLE `item` ADD `status` VARCHAR( 50 ) NOT NULL AFTER `dqty` ;
---
ALTER TABLE `item` ADD `modify` INT NOT NULL ;
---
ALTER TABLE `item` CHANGE `id` `item_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `item` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `item` CHANGE `supplier_sid` `supplier_id` INT NOT NULL 
---
ALTER TABLE `item` ADD `name_series` VARCHAR( 150 ) NOT NULL AFTER `name` ;
---
update item set name_series=substring(name, 1, length(name) - locate(' ', reverse(trim(name))))
---
ALTER TABLE `item` ADD `cost` DOUBLE NOT NULL AFTER `barcode` ;
---




---
DROP TABLE `item_cls` 
---




alter TABLE `supplier` 
			  DROP `delivery_address`,
			  DROP `line`,
			  DROP `other`,
			  DROP `attention_tel`,
			  DROP `bankname`,
			  DROP `bankac`,
			  DROP `paymentterms`,
			  DROP `payday`,
			  drop cls_money,
			  drop pic_name,
			  drop staff_sid,
			  drop user_sid,
			  drop edit_time;
			  
---
ALTER TABLE `supplier` CHANGE `cls_supplier` `class` INT NOT NULL 
---
ALTER TABLE `supplier` ADD `status` VARCHAR( 50 ) NOT NULL ;
---
RENAME TABLE `supplier_cls` TO `supplier_class` ;
---
ALTER TABLE `supplier` CHANGE `modify` `modify_user` INT( 11 ) NOT NULL
---
ALTER TABLE `supplier` CHANGE `id` `supplier_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `supplier` CHANGE `sid` `id` INT NOT NULL AUTO_INCREMENT 
---
update supplier set address=replace(address, '\\r\\n', '
')
---
ALTER TABLE `supplier` ADD `paymentterms` VARCHAR( 255 ) NOT NULL AFTER `attention` ,
ADD `payday` INT NOT NULL AFTER `paymentterms` ;
---
ALTER TABLE `supplier` ADD `date_modify` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 




ALTER TABLE `customer` CHANGE `bdate` `date_modify` DATETIME NULL DEFAULT NULL 
---
ALTER TABLE `customer` CHANGE `user_sid` `modify_user` INT NOT NULL
---
ALTER TABLE `customer`
  DROP `bankname`,
  DROP `bankac`,
  DROP `pic_name`,
  DROP `cls_money`,
  DROP `edit_time`;
---
ALTER TABLE `customer` CHANGE `cls_customer` `class` VARCHAR( 100 ) NOT NULL 
---
ALTER TABLE `customer` ADD `status` VARCHAR( 50 ) NOT NULL ;
---
update customer set remark=concat(remark, "Direct line : ", line, "
") where line != ''
---
update customer set remark=concat(remark, "Other tel : ", `other`, "
") where `other` != ''
---
ALTER TABLE `customer`
  DROP `line`,
  DROP `other`;
---
update customer set address=replace(address, '\\r\\n', '
'), delivery_address=replace(delivery_address, '\\r\\n', '
')
---
ALTER TABLE `customer` CHANGE `id` `customer_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `customer` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `customer` CHANGE `payday` `payday` INT NULL DEFAULT NULL 
---
ALTER TABLE `customer` CHANGE `staff_sid` `staff_id` INT NOT NULL 
---





//	Rename Tables (do not change the SQL orders !)
---


RENAME TABLE `item_class` TO `class_item` ;
---
RENAME TABLE `customer_cls` TO `class_customer` ;
---
RENAME TABLE `money_cls` TO `class_money` ;
---
RENAME TABLE `item_brand` TO `class_brand`
---
RENAME TABLE `staff_cls` TO `class_staff` ;
---
RENAME TABLE `supplier_class` TO `class_supplier` ;
---
RENAME TABLE `staff_group` TO `class_staff_group` ;
---
RENAME TABLE `salary_dtl` TO `salary_detail` ;
---
RENAME TABLE `purchaseorder` TO `purchase_order` ;
---
RENAME TABLE `purchaseorder_dtl` TO `purchase_order_detail` ;
---
RENAME TABLE `commission_dtl` TO `commission_detail` ;
---





ALTER TABLE `class_customer` CHANGE `sid` `id` INT NOT NULL 
---




---

ALTER TABLE `staff` CHANGE `idcard` `idcard` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 
---
ALTER TABLE `staff`
  DROP `pager`,
  DROP `other`,
  DROP `pic_name`,
  DROP `edit_time`;
---
ALTER TABLE `staff` CHANGE `sex` `gender` VARCHAR( 10 ) NULL DEFAULT NULL 
---
ALTER TABLE `staff` CHANGE `bdate` `date_modify` DATETIME NULL DEFAULT NULL 
---
ALTER TABLE `staff` CHANGE `user_sid` `modify_user` INT NOT NULL 
---
ALTER TABLE `staff` CHANGE `cls_staff` `class` INT  NOT NULL 
---
ALTER TABLE `staff` CHANGE `group_staff` `group` INT  NOT NULL 
---
ALTER TABLE `staff` ADD `date_start` DATE NULL AFTER `remark` ;
---
ALTER TABLE `staff` CHANGE `leave` `date_leave` DATE NULL DEFAULT NULL ;
---
ALTER TABLE `staff` CHANGE `commission_cls` `commission_plan` VARCHAR( 20 ) NOT NULL 
---
update staff set date_leave=null
---
update staff set gender='¨k'
---
update staff set address=replace(address, '\\r\\n', '
')
---
ALTER TABLE `staff` CHANGE `id` `staff_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `staff` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `staff` CHANGE `commission_plan` `commission_id` INT NOT NULL 
---







ALTER TABLE `invoice`
  DROP `other`;
---
ALTER TABLE `invoice` CHANGE `bdate` `date_order` DATE NULL DEFAULT NULL 
---
ALTER TABLE `invoice` CHANGE `paydate` `date_pay` DATE NULL DEFAULT NULL 
---
update invoice set remark=replace(remark, '\\r\\n', '
')
---
ALTER TABLE `invoice` DROP `cls_money` 
---
ALTER TABLE `invoice` DROP `edit_time` 
---
ALTER TABLE `invoice` CHANGE `input_time` `date_modify` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
---
ALTER TABLE `invoice` CHANGE `statusmode` `status` VARCHAR( 50 ) NULL DEFAULT NULL 
---
ALTER TABLE `invoice` CHANGE `user_sid` `modify_user` INT NOT NULL 
---
ALTER TABLE `invoice` CHANGE `customer_sid` `customer_id` INT NOT NULL 
---
ALTER TABLE `invoice` CHANGE `id` `invoice_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `invoice` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `invoice` CHANGE `cls_staff` `staff_class` INT NOT NULL 
---
ALTER TABLE `invoice` CHANGE `staff_sid` `staff_id` INT NOT NULL 
---
ALTER TABLE `invoice` DROP `exchange`
---
ALTER TABLE `invoice` CHANGE `group_staff` `staff_group` INT NOT NULL 
---
ALTER TABLE `invoice` DROP `unit` 
---
ALTER TABLE `invoice` CHANGE `discountp` `discount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `invoice` CHANGE `qtytotal` `quantity_sum` INT NULL DEFAULT NULL 
---
ALTER TABLE `invoice` CHANGE `grosstotal` `amount_gross` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `invoice` CHANGE `nettotal` `amount_net` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `invoice` ADD `unpaid` DOUBLE NOT NULL AFTER `balance` ;
---
ALTER TABLE `invoice` CHANGE `status` `status` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---







RENAME TABLE `invoice_dtl` TO `invoice_detail` ;
---
ALTER TABLE `invoice_detail` CHANGE `sid` `invoice_id` INT NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `product_sid` `item_id` INT NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `qty` `quantity` INT NULL DEFAULT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `discountamount` `amount_discounted` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `invoice_detail` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
---
ALTER TABLE `invoice_detail` ADD `item_brand` INT NOT NULL AFTER `item_id` ;
---
ALTER TABLE `invoice_detail` ADD `name` VARCHAR( 250 ) NOT NULL AFTER `item_brand` ;
---
update invoice_detail set name=(select name from item where id=invoice_detail.item_id)
---
ALTER TABLE `invoice_detail` CHANGE `quantity` `quantity` INT( 11 ) NOT NULL
---
ALTER TABLE `invoice_detail` CHANGE `orgprice` `price_original` DOUBLE NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `price` `price` DOUBLE NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `discountp` `discount` DOUBLE NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `amount` `amount` DOUBLE NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `amount_discounted` `amount_discounted` DOUBLE NOT NULL 
---

ALTER TABLE `invoice_detail` CHANGE `recordnum` `rec_id` INT NULL DEFAULT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `realprice` `price_final` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `staff_sid` `staff_id` INT NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `cls_staff` `staff_class` INT NOT NULL 
---
ALTER TABLE `invoice_detail` CHANGE `group_staff` `staff_group` INT NOT NULL 
---
ALTER TABLE `invoice_detail` DROP `price_final` 
---
ALTER TABLE `invoice_detail` DROP `inqty` 
---



ALTER TABLE `invoice_free` CHANGE `recordnum` `rec_id` INT NULL DEFAULT NULL 
---
ALTER TABLE `invoice_free` DROP `coltext1` 
---
ALTER TABLE `invoice_free` CHANGE `coltext2` `name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 
---
ALTER TABLE `invoice_free` CHANGE `qty` `quantity` INT NULL DEFAULT NULL 
---
ALTER TABLE `invoice_free` CHANGE `sid` `invoice_id` INT NOT NULL 
---
update invoice_free set quantity=1, price=amount where quantity=0
---
insert into invoice_detail (invoice_id, rec_id, name, quantity, price, amount, staff_group, staff_id) (select invoice_id, rec_id, name, quantity, price, amount, group_staff, staff_sid from invoice_free)
---

ALTER TABLE `invoice_detail` ADD `customer_id` INT NOT NULL AFTER `amount_discounted` ;
---
ALTER TABLE `invoice_detail` ADD `date_order` DATE NOT NULL AFTER `amount_discounted` ;
---


update invoice_detail set staff_id=(select staff_id from invoice where id=invoice_detail.invoice_id) where item_id = '0'
---
update invoice_detail set staff_class=(select staff_class from invoice where id=invoice_detail.invoice_id) where item_id = '0'
---
update invoice_detail set staff_group=(select staff_group from invoice where id=invoice_detail.invoice_id) where item_id = '0'
---
update invoice_detail set item_brand=(select brand from item where item.id=invoice_detail.item_id)
---
update invoice_detail set date_order=(select date_order from invoice where invoice.id=invoice_detail.invoice_id)
---
update invoice_detail set customer_id=(select customer_id from invoice where invoice.id=invoice_detail.invoice_id)
---





ALTER TABLE `salesrecord`
  DROP `cls_money`,
  DROP `edit_time`;
---
ALTER TABLE `salesrecord` CHANGE `input_time` `date_modify` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
---
ALTER TABLE `salesrecord` CHANGE `user_sid` `modify_user` INT NOT NULL 
---
ALTER TABLE `salesrecord` CHANGE `customer_sid` `customer_id` INT NOT NULL 
---
ALTER TABLE `salesrecord` CHANGE `staff_sid` `staff_id` INT NOT NULL 
---
ALTER TABLE `salesrecord` CHANGE `bdate` `date` DATE NULL DEFAULT NULL 
---
ALTER TABLE `salesrecord` CHANGE `statusmode` `status` VARCHAR( 50 ) NULL DEFAULT NULL 
---
ALTER TABLE `salesrecord` CHANGE `salesamount` `amount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salesrecord` CHANGE `ottime` `overtime` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salesrecord` CHANGE `cashsale` `amount_cash` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salesrecord` CHANGE `id` `salesrecord_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `salesrecord` CHANGE `sid` `id` INT NOT NULL 





---
ALTER TABLE `customer` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `invoice` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `item` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `id` ) 
---
ALTER TABLE `item` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `salesrecord` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `staff` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---





ALTER TABLE `class_brand` CHANGE `sid` `id` INT NOT NULL  AUTO_INCREMENT 
---
ALTER TABLE `class_customer` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `class_item` CHANGE `sid` `id` INT NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `class_money` CHANGE `sid` `id` INT NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `class_staff` CHANGE `sid` `id` INT NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `class_staff_group` CHANGE `sid` `id` INT NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `class_supplier` CHANGE `sid` `id` INT NOT NULL AUTO_INCREMENT 
---











RENAME TABLE `paymentcust` TO `customer_payment` ;
---
RENAME TABLE `paymentcust_dtl` TO `customer_payment_detail` ;
---
ALTER TABLE `customer_payment` CHANGE `id` `payment_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `customer_payment` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `customer_payment` CHANGE `bdate` `date` DATE NULL DEFAULT NULL 
---
ALTER TABLE `customer_payment` CHANGE `totalamount` `amount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `customer_payment` CHANGE `customer_sid` `customer_id` INT NOT NULL 
---
ALTER TABLE `customer_payment` CHANGE `staff_sid` `staff_id` INT NOT NULL 
---
ALTER TABLE `customer_payment` CHANGE `user_sid` `modify_user` INT NOT NULL 
---
ALTER TABLE `customer_payment`
  DROP `cls_money`,
  DROP `edit_time`;
---
ALTER TABLE `customer_payment` CHANGE `statusmode` `status` VARCHAR( 50 ) NULL DEFAULT NULL 
---
ALTER TABLE `customer_payment` CHANGE `input_time` `date_modify` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
---
ALTER TABLE `customer_payment` CHANGE `paymentmethod` `method` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 
---
ALTER TABLE `customer_payment` CHANGE `status` `status` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---





ALTER TABLE `customer_payment_detail` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `customer_payment_detail` CHANGE `recordnum` `rec_id` INT NOT NULL
---
ALTER TABLE `customer_payment_detail` CHANGE `invoice_sid` `invoice_id` INT NOT NULL 
---
ALTER TABLE `customer_payment_detail` CHANGE `amount` `amount` DOUBLE NOT NULL
---
ALTER TABLE `customer_payment_detail` CHANGE `id` `customer_payment_id` INT( 11 ) NOT NULL
---
ALTER TABLE `customer_payment_detail` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
---








ALTER TABLE `salary` CHANGE `id` `salary_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `salary` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `salary` CHANGE `bdate` `date_issue` DATE NULL DEFAULT NULL 
---
ALTER TABLE `salary` CHANGE `salarybdate` `date_begin` DATE NULL DEFAULT NULL 
---
ALTER TABLE `salary` CHANGE `salaryedate` `date_end` DATE NULL DEFAULT NULL 
---
ALTER TABLE `salary` CHANGE `staff_sid` `issueby_id` INT NOT NULL 
---
ALTER TABLE `salary` CHANGE `salesman_sid` `staff_id` INT NOT NULL 
---
ALTER TABLE `salary` CHANGE `user_sid` `modify_user` INT NOT NULL 
---
ALTER TABLE `salary` CHANGE `input_time` `date_modify` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
---
ALTER TABLE `salary` CHANGE `totalamount` `amount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salary` CHANGE `totalcommission` `commission` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salary` CHANGE `totalsalary` `salary` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salary`
  DROP `cls_money`,
  DROP `edit_time`,
  DROP `statusmode`;
---
ALTER TABLE `salary` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---
ALTER TABLE `salary` ADD `amount_sales` DOUBLE NOT NULL AFTER `commission` ;
---
update salary set remark=replace(remark, '\\r\\n', '
')
---




ALTER TABLE `salary_detail` CHANGE `recordnum` `rec_id` INT NULL DEFAULT NULL 
---
ALTER TABLE `salary_detail` CHANGE `sid` `salary_id` INT NOT NULL 
---
ALTER TABLE `salary_detail` CHANGE `customer_sid` `customer_id` INT NOT NULL 
---
ALTER TABLE `salary_detail` CHANGE `ottime` `ot_time` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salary_detail` CHANGE `otsalary` `ot_salary` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salary_detail` CHANGE `salesdate` `date_sales` DATE NULL DEFAULT NULL 
---
ALTER TABLE `salary_detail` CHANGE `salesamount` `amount_sales` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `salary_detail` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
---
ALTER TABLE `salary_detail` CHANGE `salesrecord_id` `refno` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---





ALTER TABLE `purchase_order` CHANGE `id` `purchase_order_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 
---
ALTER TABLE `purchase_order` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `purchase_order` CHANGE `bdate` `date_order` DATE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order` CHANGE `paydate` `date_pay` DATE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order` CHANGE `discountp` `discount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order` DROP `exchange` 
---
ALTER TABLE `purchase_order` CHANGE `qtytotal` `quantity_sum` INT NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order` CHANGE `grosstotal` `amount_gross` DOUBLE NULL DEFAULT NULL  
---
ALTER TABLE `purchase_order` CHANGE `nettotal` `amount_net` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order` CHANGE `supplier_sid` `supplier_id` INT NOT NULL 
---
ALTER TABLE `purchase_order` CHANGE `staff_sid` `staff_id` INT NOT NULL 
---
ALTER TABLE `purchase_order` CHANGE `user_sid` `modify_user` INT NOT NULL 
---
ALTER TABLE `purchase_order` CHANGE `input_time` `date_modify` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
---
ALTER TABLE `purchase_order` DROP `cls_money` 
---
ALTER TABLE `purchase_order` DROP `edit_time` 
---
ALTER TABLE `purchase_order` CHANGE `statusmode` `status` VARCHAR( 50 ) NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT 
---




ALTER TABLE `purchase_order_detail` CHANGE `sid` `purchase_order_id` INT NOT NULL 
---
ALTER TABLE `purchase_order_detail` CHANGE `recordnum` `rec_id` INT NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order_detail` CHANGE `product_sid` `item_id` INT NOT NULL 
---
ALTER TABLE `purchase_order_detail` CHANGE `qty` `quantity` INT NULL DEFAULT NULL  
---
ALTER TABLE `purchase_order_detail` CHANGE `discountp` `discount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order_detail` CHANGE `orgprice` `price_original` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order_detail` CHANGE `amount` `amount_discounted` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order_detail` CHANGE `realprice` `amount` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `purchase_order_detail` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
---
ALTER TABLE `purchase_order_detail` ADD `date_order` DATE NOT NULL AFTER `amount_discounted` ,
ADD `supplier_id` INT NOT NULL AFTER `date_order` ,
ADD `staff_id` INT NOT NULL AFTER `supplier_id` ,
ADD `staff_class` INT NOT NULL AFTER `staff_id` ,
ADD `staff_group` INT NOT NULL AFTER `staff_class` ;
---
ALTER TABLE `purchase_order_detail` ADD `item_brand` INT NOT NULL AFTER `item_id` ,
ADD `name` VARCHAR( 250 ) NOT NULL AFTER `item_brand` ,
ADD `cost_original` DOUBLE NOT NULL AFTER `name` ;
---
ALTER TABLE `purchase_order_detail` DROP `discountamount` 
---







ALTER TABLE `commission` CHANGE `id` `commission_id` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 
---
ALTER TABLE `commission` CHANGE `sid` `id` INT NOT NULL 
---
ALTER TABLE `commission`
  DROP `comclass`,
  DROP `remainder`;

---







ALTER TABLE `commission_detail` CHANGE `sid` `commission_id` INT NOT NULL 
---
ALTER TABLE `commission_detail` CHANGE `recordnum` `rec_id` INT NULL DEFAULT NULL 
---
ALTER TABLE `commission_detail` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
---
ALTER TABLE `commission_detail` CHANGE `pnum` `money_percent` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `commission_detail` CHANGE `xmoney` `money_fixed` DOUBLE NULL DEFAULT NULL
---
ALTER TABLE `commission_detail` CHANGE `emoney` `range_end` DOUBLE NULL DEFAULT NULL 
---
ALTER TABLE `commission_detail` CHANGE `bmoney` `range_begin` DOUBLE NULL DEFAULT NULL 
---



update staff set gender='¤k'
---
update staff set gender='¨k' where id in (1 ,2 ,3 ,4 ,87 ,93 ,16 ,7 ,8 ,43 ,20 ,61 ,88 ,76 ,126 ,5 ,127 ,140 ,164 ,166 ,168 ,188 ,165 ,147 )
---



EOS;

$start			= 0;

if (isset($_GET['start']))	$start	= $_GET['start'];

$sqls				= explode("---\r\n", $sqls);
foreach ($sqls as $i => $sql) {
	
	if ($i < $start)	continue;
	
	$sql			= trim($sql);
	if (empty($sql))	continue;

	echo "<p><a href='db_init.php?start=$i'>$i</a> : $sql</p>";

	$result			= sql_query($sql);
	
	if ($result != true) {
//		echo "<br>Error on SQL : $sql<br>";
		break;
	}
}


?>