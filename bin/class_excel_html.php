<?php

Class Excel_HTML {

	var	$filename;	
	var	$file;
	var	$sheet;
	var	$excel;
	var	$active_sheet;
	var	$range;
	
	var $data;

	var $error_report	= true;
	
	var $timeout_writePerRecord	= 0.01;
	
	
	//////////////////////////////////////////////////////////////
	//	All read/write sheet column and row start from 1
	//	All php data array start from 0
	//////////////////////////////////////////////////////////////
	
	
	function Excel_HTML($source = '') {

		if (empty($source))		return;
		if (is_string($source))
			$this->open($source);
		
	}
	
	
	//////////////////////////////////////////////////////////////
	// File Operation											//
	//////////////////////////////////////////////////////////////
	
	function open($filename) {

		if (basename($filename) == $filename) {
			// Attempt to find the full path
			$fullpath	= realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '\\' . $filename;
			//if (file_exists($fullpath))
				$filename	= $fullpath;
		}

		if (!file_exists($filename)) {
			$this->openNew($filename);
			if ($this->error_report)
				echo "<p><font color=red>File not found. New spreadsheet is created.</font></p>\r\n";
			return;
		}
		
		$this->filename	= $filename;

		try {
//			$this->excel->Application->WorkBooks->Open($filename) or Die ("Excel file could not open."); 
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
/*
		$this->sheet	= $this->excel->Application->Sheets(1);
		$this->sheet->Activate();
		*/
	}
	
	function openNew($filename) {
		if (basename($filename) == $filename) {
			// Attempt to find the full path
			$fullpath	= realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '\\' . $filename;
			//if (file_exists($fullpath))
				$filename	= $fullpath;
		}

		$this->filename	= $filename;
/*		$this->excel->Application->SheetsInNewWorkbook	= 1;
		$this->excel->Application->Workbooks->Add();
		//$this->excel->Application->ActiveWorkBook->Name	= basename($filename, '.xls');		// Have error

		$this->sheet	= $this->excel->Application->Sheets(1);
		$this->sheet->Activate();
		*/
	}
	
	function xls_info() {
		$info	= array();
		$workbook	= $this->excel->Application->ActiveWorkBook;

		$info['name']		= $workbook->Name;
		$info['author']		= $workbook->Author;
		$info['readonly']	= $workbook->ReadOnly;
		
		return $info;
	}
	
	function about() {
		#Get the application name and version     
		$info	= array();
		$info['application name']	= $this->excel->Application->value;
		$info['loaded version']		= $this->excel->Application->version;
		return $info;
	}
	
	function save($filename = '') {
		if (!file_exists($this->filename)) {
			$this->saveAs($this->filename);
			return;
		}
		
		try {
		    $this->_save($this->filename);
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}
	
	function saveAs($filename) {
		if ($filename == '')	$filename	= $this->filename;
		if ($filename == '')	Die ("File name is not specified.");

		if (file_exists($filename)) {
			if (!is_writable($filename)) {
				echo "<p><font color=red>File is not writable !</font></p>\r\n";
				return false;
			}
		}
				
		try {
			set_time_limit(10);
			$this->_save($filename);
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}
	
	function _save($file) {
		
		$fp		= fopen($file, 'w');
		
		fwrite($fp, "<html>
			<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
			<meta name=ProgId content=Excel.Sheet>
			</head>
			<body>
			<table border=1 bordercolor='#cccccc' style='font-size:13px; font-family:Arial, 新細明體'>
				");
		
		foreach ($this->data as $row) {
			fwrite($fp, "<tr>\r\n");
			foreach ($row as $col) {
				fwrite($fp, "<td>$col</td>\r\n");
			}
			fwrite($fp, "</tr>\r\n");
		}

		fwrite($fp, "</table></body></html>");
		fclose($fp);

	}
	
	function close($save = false) {
		if ($this->excel == null)	return;
		try {
			if ($save)			$this->save();
		    $this->excel->Application->ActiveWorkbook->Close(false);
		    $this->excel->Application->Quit();
		    $this->excel = null;
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}
	
	
	//////////////////////////////////////////////////////////////
	// Sheet Operation											//
	//////////////////////////////////////////////////////////////
		
	function newSheet($sheet_name) {
		$this->excel->Application->Sheets->Add();
		$this->sheet		= $this->excel->Application->ActiveSheet;
		$this->renameSheet($sheet_name);
	}

	function getSheets() {
		$sheets	= array();
		$count	= $this->excel->application->Sheets->Count;
			for ($i = 1; $i <= $count; $i++) {
			$sheets[]	= $this->excel->application->Sheets($i)->Name;
		}
		return $sheets;
	}
	
	function selectSheet($sheet) {
		try {
			$this->sheet	= $this->excel->Application->Sheets($sheet);
			$this->sheet->Activate();
		} catch (Exception $e) {
			$this->newSheet($sheet);
			if ($this->error_report) {
				echo "<p><font color=red>Caught exception at selecting sheet : ",  $e->getMessage(), ", possibly sheet does not exist. New sheet created.</font></p>\r\n";
			}
		}
	}

	function renameSheet($newname) {
		try {
			$this->sheet->Name	= $newname;
		} catch (Exception $e) {
			if ($e->getCode() == -2147352567)
				$this->renameSheet($newname . '~');
			elseif ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}
	
	function removeSheet($sheet) {
		$this->excel->Application->Sheets($sheet)->Delete();
	}
	
	

	//////////////////////////////////////////////////////////////
	// Write Operation											//
	//////////////////////////////////////////////////////////////

	function writeSheet($array) {
		foreach ($array as $row_no => $row) {
			$j = 1;
			foreach ($row as $value) {
				$this->data[$row_no + 1][$j]	= $value;
				$j++;
			}
		}
	}
	
	function writeRow($row, $array) {
		set_time_limit(count($array) * $this->timeout_writePerRecord + 10);
		for ($col=1; $col <= count($array); $col++) {
			$cell = $this->sheet->Cells($row, $col);
			$cell->value = $array[$col-1];
		}
	}
	
	function writeCol($col, $array) {
		set_time_limit(count($array) * $this->timeout_writePerRecord + 10);
		for ($row=1; $row <= count($array); $row++) {
			$cell = $this->sheet->Cells($row, $col);
			$cell->value = $array[$row-1];
		}
	}
	
	function writeCell($row, $col, $value) {
		$cell = $this->sheet->Cells($row, $col);
		$cell->value = $value;
	}
	
	//////////////////////////////////////////////////////////////
	// Read Operation											//
	//////////////////////////////////////////////////////////////

	function readSheet($sheet = '') {
		
		if (!empty($sheet))	
			$this->selectSheet($sheet);
			
		if (!empty($this->data))
			return $this->data;


		$html	= file_get_contents($this->filename);

//		set_memory_limit(max(strlen($html) * 10, 10485760));

		$rows	= explode('</tr>', subString($html, "<table", "</table>"));
		unset($rows[count($rows) - 1]);

		$data	= array();
		foreach ($rows as $row) {
			preg_match_all('/<td(.*?)>(.*?)<\/td>/is', $row, $col);
			foreach ($col[1] as $pos => $string) {
				$col[2][$pos]	= trim(strip_tags($col[2][$pos]));
//				$col[2][$pos]	= htmlentities($col[2][$pos]);
//				dump(htmlentities($col[1][$pos]));
//				$col[1][$pos]	= html_entity_decode($col[1][$pos]);
				if (strpos($string, "colspan") !== false) {
					$span			= preg_replace('/.*colspan=[\'"]?([0-9]+)/s', "$1", $string);
					$empty_array	= array();
					for ($i = 1; $i < $span; $i++)
						$empty_array[]	= '';
					array_splice($col[2], $pos, 0, $empty_array);
				}
			}
			$data[]	= $col[2];
		}

		$this->data	= &$data;
		
		return $data;
	}


	function readRow($row) {
		$data	= array();

		$range		= $this->sheet->UsedRange;

		$total_col	= $range->Columns->Count;

		for ($col=1; $col <= $total_col; $col++) {
			$data[$col - 1] = $this->sheet->Cells($row, $col)->Value;
		}

		return $data;
	}
	
	function readCol($col) {
		$data	= array();

		$range		= $this->sheet->UsedRange;

		$total_row	= $range->Rows->Count;

		for ($row=1; $row <= $total_row; $row++)
			$data[$row - 1] = $this->sheet->Cells($row, $col)->Value;

		return $data;
	}
	
	function readCell($row, $col) {
		return $this->sheet->Cells($row, $col)->Value;
	}
	


	//////////////////////////////////////////////////////////////
	// Sort and Calculation										//
	//////////////////////////////////////////////////////////////
	
	function setIndex($col) {
		if (is_numeric($col)) {
			$index_data		= $this->readCol($col);
		} else {
			$titles			= $this->readRow(1);
			$col			= array_search($col, $titles);
			$index_data		= $this->readCol($col + 1);
		}
		if ($index_data[0] == '')
			$this->index_data	= array();
		else
			$this->index_data	= @array_flip($index_data);
	}
	
	function sortWithIndex(&$data, $index_col = 0) {
		$result		= array();
		$append		= array();
		$index		= $this->index_data;

		foreach ($data as $key => $value) {
			$index_value	= $value[$index_col];
			if (isset($index[$index_value])) {
				$result[$index[$index_value]]	= $value;
				echo "$key / $index_value / " . $index[$index_value] . " - " . join (', ', $value) . "<br>\r\n";
				unset($index[$index_value]);
			} else {
				$append[]	= $value;
				echo "$key / $index_value / add  - " . join (', ', $value) . "<br>\r\n";
			}
		}
		ksort($result);
		$data	= array_merge($result, $append);
/*
		foreach ($append as $key => $value) {
			$result[] = $value;
		}
		$data	= $result;
*/
		return $data;
	}
	

	function calculate() {
		$sheets->Calculate();
	}
	
	
	//////////////////////////////////////////////////////////////
	// Range and Cell format									//
	//////////////////////////////////////////////////////////////
	
	function range($range	= 'UsedRange') {
		if ($range	== 'UsedRange')
			$this->range		= $this->sheet->UsedRange;
		else
			$this->range		= $this->sheet->Range($range);
	}
	
	function font($font, $size, $colorIndex	= 0) {
		if ($this->range != null) {
			$this->range->Font->Name	= $font;
			$this->range->Font->Size	= $size;
			$this->range->Font->ColorIndex	= $colorIndex;
		}
	}

	
	function download() {
		$delete		= true;
	
		$temp_file	= realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '\\dl_' . basename($this->filename);

		@unlink($temp_file);
	
		if (file_exists($temp_file)) {
			if (!is_writable($temp_file)) {
				echo "<p><font color=red>File '$temp_file' is not writable !</font></p>";
				$this->close();
				exit();
			}
		}

		if (!headers_sent()) {
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=" . basename($this->filename));
		} else {
			echo "<p>Alert : Headers sent !</p>";
			$delete	= false;
		}

		$this->saveAs($temp_file);
		
		$this->close();
		
		$fp	= fopen($temp_file, 'r');
		fpassthru($fp);
		fclose($fp);
		
//		if ($delete)
			@unlink($temp_file);

		exit();
	}
	

	var $xlAddIn				= 18;
	var $xlCSV					= 6;
	var $xlCSVMac				= 22;
	var $xlCSVMSDOS				= 24;
	var $xlCSVWindows			= 23;
	var $xlCurrentPlatformText	= -4158;
	var $xlDBF2					= 7;
	var $xlDBF3					= 8;
	var $xlDBF4					= 11;
	var $xlDIF					= 9;
	var $xlExcel2				= 16;
	var $xlExcel2FarEast		= 27;
	var $xlExcel3				= 29;
	var $xlExcel4				= 33;
	var $xlExcel4Workbook		= 35;
	var $xlExcel5				= 39;
	var $xlExcel7				= 39;
	var $xlExcel9795			= 43;
	var $xlHtml					= 44;
	var $xlIntlAddIn			= 26;
	var $xlIntlMacro			= 25;
	var $xlSYLK					= 2;
	var $xlTemplate				= 17;
	var $xlTextMac				= 19;
	var $xlTextMSDOS			= 21;
	var $xlTextPrinter			= 36;
	var $xlTextWindows			= 20;
	var $xlUnicodeText			= 42;
	var $xlWebArchive			= 45;
	var $xlWJ2WD1				= 14;
	var $xlWJ3					= 40;
	var $xlWJ3FJ3				= 41;
	var $xlWK1					= 5;
	var $xlWK1ALL				= 31;
	var $xlWK1FMT				= 30;
	var $xlWK3					= 15;
	var $xlWK3FM3				= 32;
	var $xlWK4					= 38;
	var $xlWKS					= 4;
	var $xlWorkbookNormal		= -4143;
	var $xlWorks2FarEast		= 28;
	var $xlWQ1					= 34;
	var $xlXMLData				= 47;
	var $xlXMLSpreadsheet		= 46;


	
}


/*
function __excel_shutdown() {
	global $_EXCEL_INSTANCE, $excel;
	if (is_array($_EXCEL_INSTANCE))
	foreach ($_EXCEL_INSTANCE as $obj) {
		$obj->close();
	}
	
//	file_put_contents($excel->filename . '.txt', "shutdowned at : " . date('H:i:s') . "\r\n" . serialize($_EXCEL_INSTANCE));
	
}
*/

?>