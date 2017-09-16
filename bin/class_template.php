<?php

//////////////////////////////////////////////////////////////////////////////////
//                                Built by Hyman                                //
//////////////////////////////////////////////////////////////////////////////////

// Template Class (Abstract)

class Template {

	var $template;
	var $result			= '';

	var $content		= array();
	
	var $capturing		= false;
	var $captureField	= '';

	var $loopTemplate	= array();
	var $loopStack		= array();

	function Template($file, $tag = 'template') {
		$this->template = preg_replace("/.*<!--$tag-->(.*)<!--$tag-->.*/s", "$1", file_get_contents($file));
	}

/*	function replace($find, $newvalue, &$string) {
		$findlen	= strlen($find);
		$pos		= strpos($string, $find);
		while ($pos !== false) {
			$string = substr($string, 0, $pos) . $newvalue . substr($string, $pos + $findlen);
			$pos		= strpos($string, $find);
		}
	}*/

	function setArray($array) {
		if (is_array($array))
			foreach ($array as $name => $value)
				$this->set($name, $value);
	}

	function set($field, $value) {
		$this->getTemplate();
		$this->result = str_replace("::$field::", "$value", $this->result);
//$this->replace("::$field::", "$value", $this->result);
	}

	function add($field, $value) {
		$this->getTemplate();
		$this->content[$field] = "::$field::";			// Record field for being delete in output()
		$this->result = str_replace("::$field::", "$value::$field::", $this->result);
	}
	
	function getTemplate() {
		if ($this->result == '')
			$this->result = $this->template;
	}
	
	function storeTemplate() {
		$this->template = $this->result;
	}
	
	function capture() {
		list($field) = func_get_args();
		
		if ($this->capturing) {		// If capture end
			$captureContent .= ob_get_contents();		// Get the capture
			ob_end_clean();					// Stop capturing
			$this->capturing = false;
			
			$return_value = true;
			if ($this->captureField != '') {		// Store the capture
				$this->add($this->captureField, $captureContent);
				$this->captureField = '';
				$return_value = false;
			}
			
			if ($field != '')				// If capture next field
				$this->capture($field);
			
			if ($return_value)	return $captureContent;

		} else {			// If capture start
			ob_start();					// Start capturing
			$this->capturing = true;
			
			$this->captureField = $field;			// Specify field to capture
		}
	}
	
	function loop($field = 'loop') {
		$this->getTemplate();

		// Store the loop template and delete from the template
		if ($this->loopTemplate[$field] == '') {
			$this->loopTemplate[$field] = preg_replace("/.*::$field::(.*)::$field::.*/s", "$1", $this->result);
			$this->result = ereg_replace("::$field::.*::$field::", "::$field::", $this->result);

			// delete from the loop template
			foreach ($this->loopTemplate as $subfield => $subvalue)
				if (strpos($this->loopTemplate[$subfield], "::$field::") !== false)
					$this->loopTemplate[$subfield] = ereg_replace("::$field::.*::$field::", "::$field::", $this->loopTemplate[$subfield]);
		}
		
		// Delete replaced symbol from 'add' operation and loop in the previous loop
		foreach (array_merge($this->content, $this->loopTemplate) as $subfield => $subvalue)
			if (strpos($this->loopTemplate[$field], "::$subfield::") !== false)
				$this->clear($subfield);

/*		// Delete sub-nested loop symbol in the previous loop (using stack)
		if (array_search("$field", $this->loopStack) === false)
			array_push($this->loopStack, "$field");
		else
			for ($i = array_search($field, $this->loopStack); $i < count($this->loopStack); $i++)
			alert($i.'/'.count($this->loopStack).'/'.$this->loopStack[$i]);
				$this->clear($this->loopStack[$i]);		*/
		
		// Duplicate the loop part
		$this->add($field, $this->loopTemplate[$field]);
	}
	

	// Reset the current result back to template
	function reset() {
		if ($this->capturing)	$this->capture();
		$this->result 			= $this->template;
		$this->content			= array();
		$this->captureField		= '';
		$this->loopTemplate		= array();
		$this->loopStack		= array();
	}
	
	// Clear all symbol in the template
	function clear($field = "") {
		$this->getTemplate();
		if ($this->capturing) $this->capture();
		if ($field == "")
			$this->result = ereg_replace("::[a-zA-Z0-9_]*::", "", $this->result);
		else 
			$this->result = str_replace("::$field::", "", $this->result);
	}
	
	// Clear unused field/loop
	function hide($field = 'loop') {
		$this->clearfield($field);
	}
	
	function clearfield($field = 'loop') {
		$this->getTemplate();
		$this->result = preg_replace("/(::$field::.*::$field::)/s", "", $this->result);
	}
	
	function applyTheme($theme) {
		foreach ($theme as $name => $var) {
			$this->set($name, $var);
		}
	}

	function output() {
		$this->getTemplate();

		// Terminate any capture
		if ($this->capturing) $this->capture();
		
		// Get the result
		$result = $this->result;

		// Delete replaced symbol from 'add' operation
		foreach ($this->content as $key => $field)
			$result = str_replace($field, '', $result);

//		$result = preg_replace("/::(.*)=(.*)::/s", "$2", $result);

		echo $result;
	}
	
	function getResult() {
		$this->getTemplate();

		// Terminate any capture
		if ($this->capturing) $this->capture();
		
		// Get the result
		$result = $this->result;

		// Delete replaced symbol from 'add' operation
		foreach ($this->content as $key => $field)
			$result = str_replace($field, '', $result);

//		$result = preg_replace("/::(.*)=(.*)::/s", "$2", $result);

		return $result;
		
	}
	
}



/*********************************************************************************
//////////////////////////////////////////////////////////////////////////////////
//                   This is an example of using the template	                //
//////////////////////////////////////////////////////////////////////////////////

class FrameBorder extends Template {

	function FrameBorder() {

$this->template = <<<EOD
	<table width="100%" height="160" cellpadding="0" cellspacing="0">
	  <tr height="30">
	    <td width="12" background="::image_prefix::_top::top_style::_left.gif"></td>
	    <td background="::image_prefix::_top::top_style::_mid.gif">
	      <div style="height:30px; padding-top:2px; overflow:hidden">
		<h2>::title::</h2>
	      </div>
	    </td>
	    <td width="12" background="::image_prefix::_top::top_style::_right.gif"></td>	  
	  </tr>  
	  <tr>
	    <td width="12" background="::image_prefix::_mid_left.gif"></td>
	    <td valign="top">
		<table width="100%" cellpadding="0" cellspacing="0">
		  <tr height="1"><td style="line-height:1px">&nbsp;</td></tr>
		</table>
		  
		  ::content::
		  
	    </td>
	    <td width="12" background="::image_prefix::_mid_right.gif"></td>
	  </tr>  
	  <tr height="20">
	    <td width="12" background="::image_prefix::_btm_left.gif"></td>
	    <td background="::image_prefix::_btm_mid.gif"></td>
	    <td width="12" background="::image_prefix::_btm_right.gif"></td>	  
	  </tr>
	</table>
EOD;

		$args		= func_get_args();
		
		$this->set('image_prefix'	, "images/frame{$args[0]}");
		$this->set('top_style'		, "$args[1]");
		$this->set('title'		, "$args[2]");
	}
}



{
	$frame = new FrameBorder(1, 2, 'Product');
	$frame->capture('content');

	echo '<table width="100%" height="150" cellpadding="0" cellspacing="0" valign="top">';

	while (list($id, $name, $model, $info, $price) = mysql_fetch_row($db_product)) {
	
		echo "<tr onclick='location.href=\"index.php?p=viewproduct&id=$id\"' style='cursor:hand'>";
		$info = nl2br($info);
		echo "<td>$id  <td>$name <td>$model <td>$info <td>$price \n";		
		echo "</tr>\n\n";
	}

	echo '</table>';
	
	echo $frame->output();
}

*********************************************************************************/
