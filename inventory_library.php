<?php


function inventory_transaction_detail_save(&$inventory_transaction, $items) {
//	echo "<p>inventory_transaction_detail_save</p>";

	//	Get all items' item_id
	$items_id_new		= array_keys($items);
	$items_id_old		= sql_getArray("select item_id from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id'");

	$items_id_add		= array_diff($items_id_new, $items_id_old);
	$items_id_change	= array_intersect($items_id_new, $items_id_old);
	$items_id_delete	= array_diff($items_id_old, $items_id_new);

	$items_add			= array();
	$items_change		= array();
	$items_delete		= array();	

	foreach ($items_id_add as $id)			$items_add[$id]		= $items[$id];
	foreach ($items_id_change as $id)		$items_change[$id]	= $items[$id];
	foreach ($items_id_delete as $id)		$items_delete[$id]	= $items[$id];

//dump($items_id_new);
//dump($items_id_old);

//dump($items_add);
	inventory_transaction_detail_add($inventory_transaction, $items_add);
//dump($items_change);
	inventory_transaction_detail_change($inventory_transaction, $items_change);
//dump($items_delete);
	inventory_transaction_detail_delete($inventory_transaction, $items_delete);
	
}

function inventory_transaction_detail_add($inventory_transaction, $items) {
	if (empty($items))			return;
	if (!empty($inventory_transaction->site_from)) {

		//	Do not overwrite new stock
		$update_stock		= !sql_check("select 1 from inventory_transaction where site_from='$inventory_transaction->site_from' and type='調整' and date(date)>date('$inventory_transaction->date')");
		
		$amount_originals	= sql_getArray("select item_id, amount from inventory where site_id='$inventory_transaction->site_from'");

		foreach ($items as $item_id => $amount) {
			$amount			= 0 - $amount;
			$amount_from	= $amount_originals[$item_id] * 1;
			$amount_to		= ($update_stock) ? $amount_from + $amount : $amount_from;

			sql_query("insert into inventory_transaction_detail (inventory_transaction_id, site_id, item_id, change_from, change_to, amount) values
						('$inventory_transaction->id', '$inventory_transaction->site_from', '$item_id', $amount_from, $amount_to, $amount)");
	
			if ($update_stock)
				sql_query("update inventory set amount = amount + ($amount) where site_id='$inventory_transaction->site_from' and item_id='$item_id'");
		}
	}
	if (!empty($inventory_transaction->site_to)) {

		//	Do not overwrite new stock
		$update_stock		= !sql_check("select 1 from inventory_transaction where site_from='$inventory_transaction->site_to' and type='調整' and date(date)>date('$inventory_transaction->date')");

		$amount_originals	= sql_getArray("select item_id, amount from inventory where site_id='$inventory_transaction->site_to'");

		foreach ($items as $item_id => $amount) {
			$amount			= $amount * 1;
			$amount_from	= $amount_originals[$item_id] * 1;
			$amount_to		= ($update_stock) ? $amount_from + $amount : $amount_from;

			sql_query("insert into inventory_transaction_detail (inventory_transaction_id, site_id, item_id, change_from, change_to, amount) values
						('$inventory_transaction->id', '$inventory_transaction->site_to', '$item_id', $amount_from, $amount_to, $amount)");
			if ($update_stock)
				sql_query("update inventory set amount = amount + ($amount) where site_id='$inventory_transaction->site_to' and item_id='$item_id'");
		}
	}
}

function inventory_transaction_detail_change($inventory_transaction, $items) {
	if (empty($items))			return;
	if (!empty($inventory_transaction->site_from)) {

		//	Do not overwrite new stock
		$update_stock		= !sql_check("select 1 from inventory_transaction where site_from='$inventory_transaction->site_from' and type='調整' and date(date)>date('$inventory_transaction->date')");

		foreach ($items as $item_id => $amount) {
			$amount			= 0 - $amount;
			$item_old		= sql_getObj("select id, amount from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and site_id='$inventory_transaction->site_from' and item_id='$item_id'");
			if ($update_stock) {
				sql_query("update inventory_transaction_detail set change_to=change_to-amount+$amount, amount = '$amount' where id='$item_old->id'");
				sql_query("update inventory set amount = amount - ($item_old->amount) + ($amount) where site_id='$inventory_transaction->site_from' and item_id='$item_id'");
			} else {
				sql_query("update inventory_transaction_detail set amount = '$amount' where id='$item_old->id'");
			}
				
		}
	}
	if (!empty($inventory_transaction->site_to)) {

		//	Do not overwrite new stock
		$update_stock		= !sql_check("select 1 from inventory_transaction where site_from='$inventory_transaction->site_to' and type='調整' and date(date)>date('$inventory_transaction->date')");

		foreach ($items as $item_id => $amount) {
			$item_old		= sql_getObj("select id, amount from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and site_id='$inventory_transaction->site_to' and item_id='$item_id'");
			if ($update_stock) {
				sql_query("update inventory_transaction_detail set change_to=change_to-amount+$amount, amount = '$amount' where id='$item_old->id'");
				sql_query("update inventory set amount = amount - ($item_old->amount) + ($amount) where site_id='$inventory_transaction->site_to' and item_id='$item_id'");
			} else {
				sql_query("update inventory_transaction_detail set amount = '$amount' where id='$item_old->id'");
			}

		}
	}
}

function inventory_transaction_detail_delete($inventory_transaction, $items) {
	if (empty($items))			return;
	
	$items_ids				= "'" . implode("','", array_keys($items)) . "'";

	if (!empty($inventory_transaction->site_from)) {

		//	Do not overwrite new stock
		$update_stock		= !sql_check("select 1 from inventory_transaction where site_from='$inventory_transaction->site_from' and type='調整' and date(date)>date('$inventory_transaction->date')");

		if ($update_stock) {
			$amounts				= sql_getArray("select item_id, amount from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and site_id='$inventory_transaction->site_from' and item_id in ($items_ids)");
			foreach ($items as $item_id => $amount) {
				$amount				= $amounts[$item_id];
				sql_query("update inventory set amount=amount - ($amount) where site_id='$inventory_transaction->site_from' and item_id='$item_id'");
			}
		}
	}
	if (!empty($inventory_transaction->site_to)) {

		//	Do not overwrite new stock
		$update_stock		= !sql_check("select 1 from inventory_transaction where site_from='$inventory_transaction->site_to' and type='調整' and date(date)>date('$inventory_transaction->date')");

		if ($update_stock) {
			$amounts				= sql_getArray("select item_id, amount from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and site_id='$inventory_transaction->site_to' and item_id in ($items_ids)");
			foreach ($items as $item_id => $amount) {
				$amount				= $amounts[$item_id];
				sql_query("update inventory set amount=amount - ($amount) where site_id='$inventory_transaction->site_to' and item_id='$item_id'");
			}
		}
	}

	sql_query("delete from inventory_transaction_detail where inventory_transaction_id='$inventory_transaction->id' and item_id in ($items_ids)");
	
}

function inventory_transaction_delete($it_id) {
	
	$inventory_transaction	= sql_getObj("select * from inventory_transaction where id='$it_id'");
	$items					= sql_getArray("select item_id, amount from inventory_transaction_detail where inventory_transaction_id='$it_id'");
	
	inventory_transaction_detail_delete($inventory_transaction, $items);
	
	sql_query("delete from inventory_transaction where id='$it_id'");
	
}




function inventory_stock_statistic_update($site_id) {
	
	if (empty($site_id))	return;
	
	$date_start_sold		= date("Y-m-d", time() - 3600 * 24 *4);
	
	$quantity_sold			= sql_getValue("select sum(amount)*-1 from inventory_transaction_detail itd join (select id from inventory_transaction where type='sold' and site_from='$site_id' and date>='$date_start_sold') it on (itd.inventory_transaction_id=it.id)");
	if (empty($quantity_sold))
		$quantity_sold		= 0;

	$sold_it_id				= sql_getArray("select id from inventory_transaction where type='sold' and site_from='$site_id' and date>='$date_start_sold'");
	$sold_it_id				= implode(",", $sold_it_id);
	$sold_it_id				= (empty($sold_it_id)) ? "0" : $sold_it_id;
	$stock					= sql_getArray("select item_id, 0 - sum(amount) from inventory_transaction_detail where inventory_transaction_id in ($sold_it_id) group by item_id");
	
	$quantity_need			= 0;
	foreach ($stock as $item_id => $amount) {
		$quantity_need		+= sql_getValue("select ifnull(1, 0) from inventory where site_id='$site_id' and item_id='$item_id' and amount < $amount");
	}
	
	
	sql_query("update site set quantity_3day_sold='$quantity_sold', quantity_need='$quantity_need' where id='$site_id'");

}





function inventory_adjust_stock_recalculate($it_id) {
	
	$tables		= sql_getArray("show tables");
	$cache_id	= rand(1000, 9000) . rand(1000, 9000);
	
	while (in_array("_cache_{$cache_id}_it", $tables)) {
		$cache_id	= rand(1000, 9000) . rand(1000, 9000);
	}
	
	$table_it				= "_cache_{$cache_id}_it";
	$table_it_detail		= "_cache_{$cache_id}_it_detail";
	
	
	$inventory_transaction	= sql_getObj("select * from inventory_transaction where id='$it_id' limit 1");


	sql_query("create table $table_it select * from inventory_transaction where (site_from = '$inventory_transaction->site_from' or site_to = '$inventory_transaction->site_from') and date >= '$inventory_transaction->date' order by date");
	sql_query("create table $table_it_detail select * from inventory_transaction_detail where inventory_transaction_id in (select id from $table_it) and site_id='$inventory_transaction->site_from' and change_from != change_to");
	
	sql_query("update $table_it set type='init' where id='$it_id'");
	sql_query("update $table_it_detail set amount=change_to where inventory_transaction_id='$it_id'");


	$items					= sql_getArray("select item_id from inventory_transaction_detail where inventory_transaction_id='$it_id'");
	
	$new_inventory			= array();
	foreach ($items as $item_id) {
		
		$data				= sql_getTable("select a.*, b.* from $table_it a join $table_it_detail b on a.id=b.inventory_transaction_id where item_id='$item_id' order by a.date asc");
		$skip				= false;
		$amount				= 0;
		
		foreach ($data as $rec) {
			
			array2obj($rec);
			
			if ($rec->type == '調整') {
				$skip		= true;
				break;
			}
			
			$amount			+= $rec->amount;
			
		}
		
		if ($skip)			continue;
		
		sql_query("update inventory set amount='$amount' where site_id='$inventory_transaction->site_from' and item_id='$item_id' limit 1");
		
	}
	
	sql_query("drop table $table_it, $table_it_detail");
	
}




?>