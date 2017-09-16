<?php


$data				= sql_getArray("select name from site where staff like '%$info->name%'");
$sell_address		= implode(", ", $data);

echo <<<EOS
<table class='table table-borderless'>
	<tr>
		<td>
			<b>$info->name 的概覽</b>
<!--			基本資料<br>
			<table class='table table-borderless' bgcolor=#aaaaaa>
				<tr bgcolor=white>
					<td height=30>姓名</td>
					<td>性別</td>
					<td>入職時間</td>
					<td>離職時間</td>
					<td>電話</td>
					<td>電郵</td>
					<td>銷售地點</td>
				</tr>
				<tr bgcolor=white>
					<td height=30>$info->name</td>
					<td>$info->gender</td>
					<td>$info->date_start</td>
					<td>$info->date_leave</td>
					<td>$info->mobile</td>
					<td>$info->email</td>
					<td>$sell_address</td>
				</tr>
			</table>
			-->
		</td>
	</tr>
	<tr><td height=10></td></tr>
	<tr>
		<td height=30>事件記錄<br>
			<table class='table table-borderless' bgcolor=#aaaaaa>
				<tr bgcolor=white>
					<td height=30>編號</td>
					<td>事件類形</td>
					<td>事件標題 (內容)</td>
					<td>狀態</td>
					<td>日期</td>
				</tr>
EOS;

		$items		= sql_getTable("select * from event where person like '%$info->name%' or person='all' order by date desc limit 10");
		if (empty($items)) {

			echo "<tr height=50 bgcolor=white><td colspan=15 align=center>暫時沒有事件。</td></tr>\r\n";
		}

		foreach ($items as $item) {

			array2obj($item);

			$item->content				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), $item->content);
			$item->content				= str_replace(array("\t", "  "), array("　　　", "&nbsp; "), nl2br($item->content));

			$item->class				= sql_getValue("select description from class_event where id='$item->class'");

			echo "<tr bgcolor=white >";
			echo "<td width=100 valign=top>$item->event_id</td>";
			echo "<td width=150 valign=top>$item->class</td>";

			echo "<td valign=top><a href='javascript:;' onclick='show_item(\"content_$item->id\")'><b>$item->title</b></a><br><div id='content_$item->id' style='display:none; margin-top:8px'>$item->content</div></td>";


			echo "<td width=100 valign=top>$item->status</td>";
			echo "<td width=100 valign=top>$item->date</td>";
			echo "</tr>";

		}


echo <<<EOS

				<tr bgcolor='#efefef'><td colspan=10 height=30 align=right>
					<input class='btn btn-default' type=button onclick="location.href='event.php?search_word=$info->name&search_field=person&all=Y'" value="所有記錄"></td></tr>
			</table>

		</td>
	</tr>
	<tr><td height=20></td></tr>
</table>


<script>

function show_item(item) {
	item		= document.getElementById(item);
	if (item.style.display == "none")
		item.style.display = "";
	else
		item.style.display = "none";
}

</script>

EOS;

?>