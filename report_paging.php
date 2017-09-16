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

	$topage					= 1;

	$page_start				= 1;
	$page_end				= $page_total;

	if ($page_total > 1) {
		echo "<table class='table table-borderless noprint'><tr><td valign=top>總共 $record_count 記錄 &nbsp;，&nbsp;共 $page_total 頁</td>
		<td align=right style='white-space:normal' valign=top>";

		$url							= urldecode(getURL());

		echo "<a class='page_number' href='#bottom' onclick='showpage(1);'>&lt;&lt;</a>";
		echo "<a class='page_number' href='#bottom' onclick='showpage(current_page-1);'>&lt;</a>";

		for ($i=$page_start; $i<=$page_end; $i++) {
			echo "<a class='page_number' href='#bottom' onclick='showpage($i);'>$i</a>";
		}

		echo "<a class='page_number' href='#bottom' onclick='showpage(current_page+1);'>&gt;</a>";
		echo "<a class='page_number' href='#bottom' onclick='showpage($page_end);'>&gt;&gt;</a>";

		echo "當前第 $topage 頁</td><td width='110' valign='top' style=\"padding:0!important;\">";
		echo "<form id=paging_form action='' method=get style='margin:0px; float:right;'>";
		echo "<div class='input-group'><input class=\"form-control\" type=text name=topage size=2 onkeydown='if (event.keyCode == 13) showpage(this.value);'> ";
		echo "<span class='input-group-btn'><input class=\"btn btn-default\" type=button value='跳頁' onclick='showpage(document.getElementById(\"paging_form\").elements.namedItem(\"topage\").value);'></span>";
		echo "</div>";
		echo "</form>";

		echo "</td></tr></table>";
	}

	$PAGING_STRING			= ob_get_contents();
	ob_end_flush();

	echo <<<EOS
<script>

if (document.getElementById('paging_header') && document.getElementById('paging_footer'))
	document.getElementById('paging_header').innerHTML	= document.getElementById('paging_footer').innerHTML;


var current_page = 1;

function showpage(page) {

	if (document.getElementById('page' + page)) {
		obj						= document.getElementById('page' + current_page);
		obj.setAttribute("class", " hiddenpage");
		obj.setAttribute("className", " hiddenpage");

		obj						= document.getElementById('page' + page);
		obj.setAttribute("class", "");
		obj.setAttribute("className", "");


		current_page			= page;
	}

}


</script>

EOS;

}


?>
