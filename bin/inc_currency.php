<?php


function currency_exchange($dollar1, $dollar2, $price) {
	global $CURRENCY_RATE;
	if (empty($CURRENCY_RATE)) {
		$CURRENCY_RATE		= sql_getArray("select id, price from currency");
	}
	return round($price * $CURRENCY_RATE[$dollar1] / $CURRENCY_RATE[$dollar2], 2);
} 




$update_time	= 24 * 1;		// Unit : hour


$last_update	= sql_getValue("select max(last_update) from currency limit 1");
$overtime		= (time() - strtotime($last_update)) / 60 / 60;

if ($overtime > $update_time) {
	
	$update_url		= "http://www.x-rates.com/d/HKD/table.html";
	

	$name_mapping	= array(
				"American Dollar"			=> "USD",
				"Australian Dollar"			=> "AUD",
				"British Pound"				=> "GBP",
				"Canadian Dollar"			=> "CAD",
				"Chinese Yuan"				=> "RMB",
				"Euro"						=> "EUR",
				"Taiwan Dollar"				=> "TWD"
							);
	
	$html 			= file_get_contents($update_url);
	$html			= subString($html, "click on values to see graphs", "</table>");

	$rows			= explode('</tr>', $html);
	foreach ($rows as $row) {
		$cols		= explode('</td>', $row);
		$name		= $cols[0];
		$price		= $cols[2]; 
		
		$name 		= trim(str_replace('&nbsp;', '', strip_tags($name)));
		$price 		= trim(str_replace('&nbsp;', '', strip_tags($price)));
		$code		= $name_mapping[$name];

		if (!empty($code)) {
			
			sql_query("update currency set price='$price' where id='$code'");
			
		}

	} 
	
	$today			= date('Y-m-d');
	
	unset($update_url , $name_mapping , $html , $rows , $cols , $today);
	
	sql_query("update currency set last_update = now()");
	
}

unset($overtime , $last_update , $update_time);



?>