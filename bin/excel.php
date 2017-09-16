<?php 
function Excel($FileName, $Columns, $Data, $Header=false) {

	header("Cache-Control: private");
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$FileName.'.xls"');
	
	$Days = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
	
	echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Excel.Sheet>
<meta name=Generator content="Microsoft Excel 9">
<link rel=File-List href="./12.files/filelist.xml">
<link rel=Edit-Time-Data href="./12.files/editdata.mso">
<link rel=OLE-Object-Data href="./12.files/oledata.mso">
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>E-Admin System</o:Author>
  <o:LastAuthor>E-Admin System</o:LastAuthor>
  <o:Created>'.gmdate('Y-m-d').'T'.gmdate('H:i:s').'Z</o:Created>
  <o:LastSaved>'.gmdate('Y-m-d').'T'.gmdate('H:i:s').'Z</o:LastSaved>
  <o:Company>YLLC</o:Company>
  <o:Version>9.8961</o:Version>
 </o:DocumentProperties>
 <o:OfficeDocumentSettings>
  <o:DownloadComponents/>
  <o:LocationOfComponents HRef="file:X:\msowc.cab"/>
 </o:OfficeDocumentSettings>
</xml><![endif]-->
<style>
<!--table
	{mso-displayed-decimal-separator:"\.";
	mso-displayed-thousand-separator:"\,";}
@page
	{margin:1.0in .75in 1.0in .75in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;}
tr
	{mso-height-source:auto;
	mso-ruby-visibility:none;}
col
	{mso-width-source:auto;
	mso-ruby-visibility:none;}
br
	{mso-data-placement:same-cell;}
.style0
	{mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:windowtext;
	font-size:12.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:新細明體;
	mso-generic-font-family:auto;
	mso-font-charset:136;
	border:none;
	mso-protection:locked visible;
	mso-style-name:一般;
	mso-style-id:0;}
.xl24
	{mso-style-parent:style0;
	mso-number-format:"Short Date";}
.xl25
	{mso-style-parent:style0;
	font-family:"Times New Roman", serif;
	mso-font-charset:0;}
td
	{mso-style-parent:style0;
	padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:windowtext;
	font-size:12.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:新細明體;
	mso-generic-font-family:auto;
	mso-font-charset:136;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:none;
	mso-background-source:auto;
	mso-pattern:auto;
	mso-protection:locked visible;
	white-space:nowrap;
	mso-rotate:0;}
ruby
	{ruby-align:left;}
rt
	{color:windowtext;
	font-size:9.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:新細明體, serif;
	mso-font-charset:136;
	mso-char-type:none;
	display:none;}
-->
</style>
<!--[if gte mso 9]><xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Sheet1</x:Name>
    <x:WorksheetOptions>
     <x:DefaultRowHeight>330</x:DefaultRowHeight>
     <x:Selected/>
     <x:ProtectContents>False</x:ProtectContents>
     <x:ProtectObjects>False</x:ProtectObjects>
     <x:ProtectScenarios>False</x:ProtectScenarios>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
   <x:ExcelWorksheet>
    <x:Name>Sheet2</x:Name>
    <x:WorksheetOptions>
     <x:DefaultRowHeight>330</x:DefaultRowHeight>
     <x:ProtectContents>False</x:ProtectContents>
     <x:ProtectObjects>False</x:ProtectObjects>
     <x:ProtectScenarios>False</x:ProtectScenarios>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
   <x:ExcelWorksheet>
    <x:Name>Sheet3</x:Name>
    <x:WorksheetOptions>
     <x:DefaultRowHeight>330</x:DefaultRowHeight>
     <x:ProtectContents>False</x:ProtectContents>
     <x:ProtectObjects>False</x:ProtectObjects>
     <x:ProtectScenarios>False</x:ProtectScenarios>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
  <x:ProtectStructure>False</x:ProtectStructure>
  <x:ProtectWindows>False</x:ProtectWindows>
 </x:ExcelWorkbook>
</xml><![endif]-->
</head>

<body link=blue vlink=purple>

<table x:str border=0 cellpadding=0 cellspacing=0 style=\'border-collapse:collapse;table-layout:fixed;width:54pt\'>
';
	for($j=0;$j<count($Columns);$j++)
		if (isset($Columns[$j]['Width'])) echo ' <col width='.$Columns[$j]['Width'].'>
';
		else echo ' <col>
';
	if ($Header) {
		echo ' <tr>
';
		for($j=0;$j<count($Columns);$j++) echo '<td class=xl25>'.str_replace(' ', '_', $Columns[$j]['Name']).'</td>
';
		echo ' </tr>
';
	}
	echo ' <tr>
';
	for($j=0;$j<count($Columns);$j++) echo '<td class=xl25>'.$Columns[$j]['Comment'].'</td>
';
	echo ' </tr>
';

for($i=0;$i<count($Data);$i++) {
	echo ' <tr>
';
	for($j=0;$j<count($Columns);$j++) 
		switch ($Columns[$j]['Type']) {
			case "Integer" :
				echo '  <td align=right x:num>'.$Data[$i][$Columns[$j]['Name']].'</td>
';
				break;
			case "Date" :
				list($Date['D'], $Date['M'], $Date['Y']) = explode('/', $Data[$i][$Columns[$j]['Name']]);
				$Temp = ($Date['Y']-1900)*365+floor(($Date['Y']/4)-475+0.75);
				$Temp += $Days[$Date['M']-1]+$Date['D'];
				if ($Date['M']>2 && $Date['Y']%4==0) $Temp += 1;
				echo '  <td class=xl24 align=right x:num="'.$Temp.'">'.$Data[$i][$Columns[$j]['Name']].'</td>
';
				break;
			default:
				echo '  <td class=xl25>'.$Data[$i][$Columns[$j]['Name']].'</td>
';
				break;
		}
	echo ' </tr>
';
}

echo ' <![if supportMisalignedColumns]>
 <tr height=0 style=\'display:none\'>
';
 
echo str_repeat('  <td></td>
', count($Columns));

echo ' </tr>
 <![endif]>
</table>

</body>

</html>';
}
?>