<?php 

Class Excel {

	var	$filename;	
	var	$file;
	var	$sheet;
	var	$excel;
	var	$active_sheet;

	var $error_report	= true;
	
	
	function Excel($source = '') {
		set_time_limit(30);

		$this->excel		= new COM("Excel.Application") or Die ("Excel could not been initialized.");
//		com_load_typelib('Excel.Application');

	
		register_shutdown_function('__excel_shutdown');
		global $_EXCEL_INSTANCE;
		if (!isset($_EXCEL_INSTANCE))
			$_EXCEL_INSTANCE	= array();
		$_EXCEL_INSTANCE[]	= $this;
		
		
		// Speed up excel application
		$this->excel->Application->ScreenUpdating	= false;
		$this->excel->Application->displayAlerts	= false;
		$this->excel->Application->Application->EnableEvents = false;
//		$this->excel->Application->Calculation = xlCalculationManual;
		
		if (empty($source))		return;
		if (is_string($source))
			$this->open($source);
		
	}
	
	
	
	function open($filename) {
		if (!file_exists($filename)) {
			$this->openNew($filename);
			echo "<p><font color=red>File not found. New spreadsheet is created.</font></p>\r\n";
			return;
		}
		
		$this->filename	= $filename;

		try {
			$this->excel->Application->WorkBooks->Open($filename) or Die ("Excel file could not open."); 
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}

		$this->sheet	= $this->excel->Application->Sheets(1);
		$this->sheet->Activate();
	}
	
	function openNew($filename) {
		$this->filename	= $filename;
		$this->excel->Application->SheetsInNewWorkbook	= 1;
		$this->excel->Application->Workbooks->Add();
		//$this->excel->Application->ActiveWorkBook->Name	= basename($filename, '.xls');		// Have error

		$this->sheet	= $this->excel->Application->Sheets(1);
		$this->sheet->Activate();
	}
	
	function xls_info() {
		$info	= array();
		$workbook	= $this->excel->Application->ActiveWorkBook;

		$info['name']		= $workbook->Name;
		$info['author']		= $workbook->Author;
		$info['readonly']	= $workbook->ReadOnly;
		
		return $info;
	}
		
	function selectSheet($sheet) {
		try {
			$this->sheet	= $this->excel->Application->Sheets($sheet);
			$this->sheet->Activate();
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}

	function newSheet($sheet_name) {
		$this->excel->Application->Sheets->Add();
		$this->sheet		= $this->excel->Application->ActiveSheet;
		$this->renameSheet($sheet_name);
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
		    $this->excel->Application->ActiveWorkbook->Save();
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
		    $this->excel->Application->ActiveWorkbook->SaveAs($filename);
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}
	
	function close($save = false) {
		if ($this->excel == null)	return;
		try {
		    $this->excel->Application->ActiveWorkbook->Close($save);
		    $this->excel->Application->Quit();
		    $this->excel = null;
		} catch (Exception $e) {
			if ($this->error_report)
				echo "<p><font color=red>Caught exception: ",  $e->getMessage(), "</font></p>\r\n";
		}
	}
	
	function writeSheet($array) {
		$i = 1;
		foreach ($array as $row) {
			$j = 1;
			foreach ($row as $value) {
				$cell = $this->sheet->Cells($i, $j);
				$cell->value = iconv('UTF-8', 'BIG-5', $value);
				$j++;
			}
			$i++;
		}
	}
	
	function writeRow($row, $array) {
		for ($col=1; $col <= count($array); $col++) {
			$cell = $this->sheet->Cells($row, $col);
			$cell->value = $array[$col-1];
		}
	}
	
	function writeCol($col, $array) {
		for ($row=1; $row <= count($array); $row++) {
			$cell = $this->sheet->Cells($row, $col);
			$cell->value = $array[$row-1];
		}
	}
	
	function writeCell($row, $col, $value) {
		$cell = $this->sheet->Cells($row, $col);
		$cell->value = $value;
	}
	
	function listSheets() {
		$sheets	= array();
		$count	= $this->excel->application->Sheets->Count;
			for ($i = 1; $i <= $count; $i++) {
			$sheets[]	= $this->excel->application->Sheets($i)->Name;
		}
		return $sheets;
	}
	
	function readSheet($sheet = '') {
		if (!empty($sheet))	$this->sheet	= $this->excel->application->Sheets($sheet);

		$data	= array();

		$this->sheet->Activate();
		$range		= $this->sheet->UsedRange;

		$total_row	= $range->Rows->Count;
		$total_col	= $range->Columns->Count;

		for ($row=1; $row <= $total_row; $row++)
			for ($col=1; $col <= $total_col; $col++)
				$data[$row][$col]	= $this->sheet->Cells($row, $col)->Value;

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
	
	function calculate() {
		$sheets->Calculate();
	}
	
	
	
	function download() {
		$delete		= true;
		$temp_file	= dirname($_SERVER['SCRIPT_FILENAME']) . '/dl_' . $this->filename;
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
			header("Content-Disposition: attachment; filename=$this->filename");
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
	

	
}



function __excel_shutdown() {
	global $_EXCEL_INSTANCE, $excel;
	if (is_array($_EXCEL_INSTANCE))
	foreach ($_EXCEL_INSTANCE as $obj) {
		$obj->close();
	}
	
//	file_put_contents($excel->filename . '.txt', "shutdowned at : " . date('H:i:s') . "\r\n" . serialize($_EXCEL_INSTANCE));
	
}


?> 