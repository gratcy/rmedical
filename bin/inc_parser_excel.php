<?php


class Excel_XML {
	
	var $depth			= array(); 
	var $data			= 0;
	var $parse_sheet	= 0;
	var $parse_row		= 0;
	var $parse_col		= 0;
	var $parse_tag		= 0;
	var $parse_merge	= 0;
	var $have_title		= false;
	var $col_name		= 0;
	
	var $current_table	= '';
	
	var $max_worksheet_column_search	= 8;


	
	function Excel_XML($source, $have_title = true) {
		if (is_uploaded_file($source)) {
			move_uploaded_file ($source, 'temp_excel.xml');
			$source = 'temp_excel.xml';
		}
		if (is_file($source)) {
			$this->import($source, $have_title);
		} else if (is_array($source)) {
			$this->data		= array($source);
		}
		@unlink($source);
	}
	


	////////////////////////////////////////////////////////////////////////////
	//	Excel XML Import Function
	////////////////////////////////////////////////////////////////////////////
	

	function _startElement($parser, $name, $attrs) {
		
//		$name = strtoupper($name);
		
		if ($name == "WORKSHEET") {
			$this->parse_sheet	= $attrs['SS:NAME'];
			$this->parse_row	= -2;
			$this->parse_col	= -2;
			//$this->parse_tag	= false;
			$this->col_name		= 0;
		}
		if ($name == "TABLE")	$this->parse_row	= -1;
		if ($name == "ROW") {
			if ($this->have_title && !is_array($this->col_name) && $this->parse_row == 0) {
				$this->col_name		= $this->data[$this->parse_sheet][0];
				$this->data[$this->parse_sheet][0]		= array();
				$this->parse_row	= -1;
			}
			$this->parse_col	= -1;
			$this->parse_row++;
		}
		if ($name == 'CELL') {
			$this->parse_col += 1 + $this->parse_merge;
			if (isset($attrs['SS:INDEX']))
				$this->parse_col = $attrs['SS:INDEX']-1;
			if (isset($attrs['SS:MERGEACROSS']))
				$this->parse_merge		= $attrs['SS:MERGEACROSS'];
			else 
				$this->parse_merge		= 0;
		}

		if ($name == "DATA" or $name == "SS:DATA")	$this->parse_tag	= true;
		//if ($name == "DATA" or $name == "SS:DATA")	echo " <br>true | ";
		
		$this->depth[$parser]++;

	}


	function _endElement($parser, $name) { 
		if ($name == "TABLE")
			$this->parse_row	= -1;
		if ($name == "DATA" or $name == "SS:DATA")
			$this->parse_tag	= false;
		//if ($name == "DATA" or $name == "SS:DATA")	echo " | false | <br>";
		$this->depth[$parser]--;
	}


	function _characterData($parser, $data) {
		if ($data == chr(10))	$data 	= "\r\n";
	//	$data	= iconv("UTF-8","BIG5", $data);
		if ($this->parse_row < 0)	return;
		if ($this->parse_col < 0)	return;
		
		//echo (($this->parse_tag) ? '/true/ ' : '/false/ ') . $data;
		if (!$this->parse_tag)		return;
		

		if ($this->have_title && is_array($this->col_name))
			if (empty($this->data[$this->parse_sheet][$this->parse_row][$this->col_name[$this->parse_col]]))
				$this->data[$this->parse_sheet][$this->parse_row][$this->col_name[$this->parse_col]]	= $data;
			else
				$this->data[$this->parse_sheet][$this->parse_row][$this->col_name[$this->parse_col]]	.= "\r\n$data";
		else
			if (empty($this->data[$this->parse_sheet][$this->parse_row][$this->parse_col] ))
				$this->data[$this->parse_sheet][$this->parse_row][$this->parse_col]						= $data;
			else 
				$this->data[$this->parse_sheet][$this->parse_row][$this->parse_col]						.= "$data";
	}


	function _defaultHandler($parser, $data) {
		//$this->data[$this->parse_row][$this->parse_col]	= $data;
		//echo $this->data[$this->parse_row][$this->parse_col] . " | " . $data;
	}
	
	
	
	function import($file, $have_title = true) {
		
		$this->data			= array();
		
		$this->have_title	= $have_title;
		
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 1); 
		xml_set_element_handler($this->parser, array('Excel_XML', '_startElement'), array('Excel_XML', '_endElement'));
		xml_set_character_data_handler($this->parser, array('Excel_XML', '_characterData')); 
		xml_set_default_handler($this->parser, array('Excel_XML', '_defaultHandler')); 
	
	
		if (!($fp = fopen($file, "r"))) { 
		   die("could not open Excel File"); 
		}
	
		$this->depth[$this->parser] = 0;
		while ($data = fread($fp, 4096)) { 
		   if (!xml_parse($this->parser, $data, feof($fp))) { 
		       die(sprintf("XML error: %s at line %d", 
		                   xml_error_string(xml_get_error_code($this->parser)), 
		                   xml_get_current_line_number($this->parser))); 
		   } 
		} 
		xml_parser_free($this->parser);
	
		return $this->data;
		
	}
	
	
	
	function dump($worksheet_name = '') {

		if ($worksheet_name != '') {
			$worksheet	= $this->data[$worksheet_name];
			$field		= count($worksheet[0]);
			
			$max_cols	= 0;
			$i			= -1;
			$max_search	= $this->max_worksheet_column_search;
			foreach ($worksheet as $row) {
				$i++;
				if (!is_array($worksheet[$i]))	{
					$max_search++;
					continue;
				}
				$max_cols = max(array_merge(array_keys(array_keys($worksheet[$i])), array($max_cols)));
				if ($i++ >= $max_search)
					break;
			}
			$max_cols++;

			
			echo "<table cellpadding=3 cellspacing=1 border=0 bgcolor=777777>\r\n";
			echo "<tr><th colspan=" . ($max_cols+1) ." bgcolor=f0ffff>Worksheet : $worksheet_name</td></tr>\r\n";

			if ($this->have_title) {
				echo "<tr bgcolor=ffffff><td>#</td>";
				foreach ($worksheet[0] as $key => $value) {
					echo "<td>$key</td>";
				}
				echo "</tr>\r\n";
			} else {
				echo "<tr bgcolor=ffffff><td>#</td>";
				for ($i=0; $i<($max_cols); $i++) {
					echo "<td>$i</td>";
				}
				echo "</tr>";
			}
			
			
				
			foreach ($worksheet as $key => $row) {
				echo "<tr bgcolor=ffffff><td>$key</td>";
				for ($i = 0; $i < $max_cols; $i++) {
					$data	= nl2br(array_shift($row));
					echo "<td> $data </td>";
				}
				echo "</tr>\r\n";
			}

			echo "</table>\r\n";
			echo "<br>";
		} else {
			if (is_array($this->data))
				foreach ($this->data as $worksheet_name => $worksheet)
					if ($worksheet_name != '')
						$this->dump($worksheet_name);
		}
	}
	
	
	
	function getTables() {
		return $this->data;
	}


	
	function nextTable() {
		
		if (!is_array($this->data))		return array();

		$tables		= array_keys($this->data);
		
		if (count($tables)	== 0)	return array(array());
		
		if (count($tables)	== 1)	return $this->data[$tables[0]];
		
		if ($this->current_table == '')
			$this->current_table = $tables[0];
		else {
			$i	= array_search($this->current_table, $tables) + 1;
			if ($i >= count($tables))
				return false;
			$this->current_table = $tables[i];
		}	
		
		return $this->data[$this->current_table];

	}



	////////////////////////////////////////////////////////////////////////////
	//	Excel XLS Export Function
	////////////////////////////////////////////////////////////////////////////

	function export($filename, $worksheet = '') {
		
		//$this->xml_export($filename, $worksheet);
		
		$this->xls_export($filename, $worksheet);
		
	}
	
	
	function xml_export($filename, $worksheet = '') {
		while (@ob_end_clean());
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-Type: text/plain; charset=utf8");
	    header("Content-Type: application/download");
	    header("Content-Disposition: attachment;filename=$filename");
		
		echo '<?xml version="1.0"?>';
//		echo '<?mso-application progid="Excel.Sheet"?			//>';
		
		echo '<Workbook xmlns="xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
		
		if ($worksheet == '')
			echo "<Worksheet name=\"Sheet1\">\r\n";
		else
			echo "<Worksheet name=\"$worksheet\">\r\n";
			
		echo "<Table>\r\n";
		
		if ($worksheet == '')
			$array			= current($this->data);
		else
			$array			= $this->data[$worksheet];

		foreach ($array as $row) {
		
			echo "<row>\r\n";
			
			foreach ($row as $cell) {
				echo "	<Cell><Data ss:Type=\"String\">$cell</Data></Cell>\r\n";
			}
			
			echo "</row>\r\n";
			
		
		}
		
		echo "</Table>";
		echo "</Worksheet>";
		echo "</Workbook>";
		
	}

	
	function xls_export($filename, $worksheet = '') {
		$this->_xls_header($filename);
		$this->_xlsBOF();

		if ($worksheet == '')
			$array			= current($this->data); 
		else
			$array			= $this->data[$worksheet];

		$total_row	= count($array);
		for ($i=0; $i < $total_row; $i++) {
			
			$total_col	= count($array[$i]);
			for ($j=0; $j < $total_col; $j++) {
	
				if (is_numeric($array[$i][$j]) and !startWith($array[$i][$j], '0'))
					$this->_xlsWriteNumber($i, $j, $array[$i][$j]);
				else
					$this->_xlsWriteLabel($i, $j, $array[$i][$j]);
	
			}
			
		}
		
		$this->_xlsEOF();
	}
	
	function _xls_header($filename) {
		while (@ob_end_clean());
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");
	    header("Content-Disposition: attachment;filename=$filename");
	    header("Content-Transfer-Encoding: binary");
	}
	
	function _xlsBOF() { 
	    echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
	} 
	
	function _xlsEOF() { 
	    echo pack("ss", 0x0A, 0x00); 
	} 
	
	function _xlsWriteNumber($Row, $Col, $Value) { 
	    echo pack("sssss", 0x203, 14, $Row, $Col, 0x0); 
	    echo pack("d", $Value); 
	} 
	
	function _xlsWriteLabel($Row, $Col, $Value ) { 
	    $L = strlen($Value); 
	    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L); 
	    echo $Value; 
	}


}

?>