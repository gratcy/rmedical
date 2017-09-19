<?php


///////////////////////////////////////////////////////
//	Inputs :
//		$record_sql
///////////////////////////////////////////////////////


if (isset($PAGING_STRING)) {

	echo $PAGING_STRING;
	return;

} else {

	ob_start();

	$record_count			= sql_getValue($record_sql);
	$page_total				= ceil($record_count / $record_per_page);
	$adjacents = 2;
	$page_start				= max($topage - $adjacents, 1);
	$page_end				= min($topage + $adjacents, $page_total);


	echo "<div class='row'><div class='col-sm-4'>".lang('總共')." <b>$record_count</b> ".lang('記錄').",&nbsp;".lang('共')." <b>$page_total</b> ".lang('頁')."</div><div class='col-sm-8'><div class='pages'>";

	$temp					= $topage;
	popURL('topage');
	$topage					= $temp;

	$url					= getURL();

	if ($page_start != 1)			echo "<a class='page_number' href='$url&topage=1'>&lt;&lt;</a>";
	if ($topage != 1)				echo "<a class='page_number' href='$url&topage=" . ($topage-1) . "'>&lt;</a>";
	if ($page_start != 1)			echo "<a class='page_number' href='$url&topage=1'>...</a>";

	if ($page_total < 7 + ($adjacents * 2)) {
		for ($i=$page_start; $i<=$page_end; $i++) {
			if ($i == $topage)
				echo "<a class='page_number' href='#'><b><font color=black>$i</font></b></a>";
			else
				echo "<a class='page_number' href='$url&topage=$i'>$i</a>";
		}
	}
	else if ($page_total > 5 + ($adjacents * 2)) {
		if($topage < 1 + ($adjacents * 2)) {
			for($i=1;$i<4 + ($adjacents * 2);++$i) {
				if ($i == $topage)
					echo "<a class='page_number' href='#'><b><font color=black>$i</font></b></a>";
				else
					echo "<a class='page_number' href='$url&topage=$i'>$i</a>";
			}
		}
		else if($page_total - ($adjacents * 2) > $topage && $topage > ($adjacents * 2)) {
			for ($i = $topage - $adjacents; $i < $topage + $adjacents; ++$i) {
				if ($i == $topage)
					echo "<a class='page_number' href='#'><b><font color=black>$i</font></b></a>";
				else
					echo "<a class='page_number' href='$url&topage=$i'>$i</a>";
			}
		}
		else {
			for ($i = $page_total - (2 + ($adjacents * 2)); $i <= $total_pages; ++$i) {
				if ($i == $topage)
					echo "<a class='page_number' href='#'><b><font color=black>$i</font></b></a>";
				else
					echo "<a class='page_number' href='$url&topage=$i'>$i</a>";
			}
		}
	}
	
	if ($page_end != $page_total)	echo "<a class='page_number' href='$url&topage=$page_end'>...</a>";
	if ($topage != $page_total)		echo "<a class='page_number' href='$url&topage=" . ($topage+1) . "'>&gt;</a>";
	if ($page_end != $page_total)	echo "<a class='page_number' href='$url&topage=$page_total'>&gt;&gt;</a>";

	echo "".lang('當前第')." <b>$topage</b> ".lang('頁')."</div> </td><td width=110>";
	//~ echo "<form id=paging_form action='' method=get>";
	//~ echo "<input type=hidden name='search_word' value='{$_GET['search_word']}'>";
	//~ echo "<input type=hidden name='search_field' value='{$_GET['search_field']}'>";
	//~ echo "<div class='input-group'><input class='form-control' type=text name=topage size=2 onkeydown='if (event.keyCode == 13) document.getElementById(\"paging_form\").submit();'> ";
	//~ echo "<span class='input-group-btn'><input class='btn btn-default' type=submit value='".lang('跳頁')."'></span></div>";
	//~ echo "</form>";

	echo "</div></div></div>";


	$PAGING_STRING			= ob_get_contents();
	ob_end_flush();

	echo <<<EOS
<script>

if (document.getElementById('paging_header') && document.getElementById('paging_footer'))
	document.getElementById('paging_header').innerHTML	= document.getElementById('paging_footer').innerHTML;

</script>

EOS;

}


?>
