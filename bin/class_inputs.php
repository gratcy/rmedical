<?php



class Inputs {

	var $prefix				= '';
	var	$_type				= '';
	var	$value				= '';
	var $desc				= '';
	var $desc2				= '';
	var $options			= '';
	var $_size				= '';
	var $tag				= '';
	var $separator			= '';
	var $checkbox_count		= '';
	var $exclude			= '';
	var $conditional_input	= '';
	var $encoding			= 'UTF-8';


	function Inputs() {
		$this->_type				= array();
		$this->value				= array();
		$this->desc					= array();
		$this->options				= array();
		$this->_size				= array();
		$this->tag					= array();
		$this->desc2				= array();
		$this->separator			= array();
		$this->checkbox_count		= array();
		$this->exclude				= array();
		$this->conditional_input	= array();
	}


	//	e.g.
	//		$inputs->add($name, $type, $value, $desc, $options, $size, $tag, $desc2);
	//		$inputs->add('', $type, $value, $desc, $options, $size, $tag, $desc2);				<-- Parmeters are arrays
	//		$inputs->add($name, $type, $desc, $size, [$name2, $type2, $desc2, $size2 ..]);
	function add($name, $type, $value = '', $desc = '', $options = '', $size = '', $tag = '', $desc2 = '') {
		if (func_num_args() > 7) {
			$data	= func_get_args();
			for ($i = 0; $i < func_num_args();) {
				$name					= trim($data[$i++]);
				$this->_type[$name]		= trim($data[$i++]);
				$this->desc[$name]		= trim($data[$i++]);
				$this->_size[$name]		= trim($data[$i++]);
			}
		} else if (is_string($type)) {
			$name					= trim($name);
			$this->_type[$name]		= trim($type);
			$this->value[$name]		= trim($value);
			$this->desc[$name]		= trim($desc);
			$this->options[$name]	= $options;
			$this->_size[$name]		= trim($size);
			$this->tag[$name]		= trim($tag);
			$this->desc2[$name]		= trim($desc2);
		} elseif (is_array($type)) {
			if ($value == '')		$value		= array();
			if ($desc == '')		$desc		= array();
			if ($options == '')		$options	= array();
			if ($size == '')		$size		= array();
			if ($tag == '')			$tag		= array();
			foreach ($type as $name => $value_1) {
				$this->_type[$name]		= trim($type[$name]);
				$this->value[$name]		= trim($value[$name]);
				$this->desc[$name]		= trim($desc[$name]);
				$this->options[$name]	= $options[$name];
				$this->_size[$name]		= trim($size[$name]);
				$this->tag[$name]		= trim($tag[$name]);
				$this->desc2[$name]		= trim($desc2[$name]);
			}
		}
	}

	function set($var, $array) {
		$this->$var	= array_merge($this->$var, $array);
	}

/*	function __set($var, $array) {
		dump($var);
		$this->$var	= array_merge($this->$var, $array);
	}
	*/
	function __get($name) {
		if (array_key_exists($name, $this->_type))
			return $this->_toString($name);
		else
			return false;
	}

	function collection() {
		$collection	= array();
		foreach ($this->_type as $key => $value) {
			if (isset($this->exclude[$key]) || in_array($key, $this->exclude))	continue;
			$collection[$key]	= $this->desc[$key];
		}
		return $collection;
	}

	function toString($name = '') {

		$name				= trim($name);

		if ($name == '') {
			foreach ($this->_type as $vname => $vtype) {
				$this->$vname	= $this->_toString($vname);
			}
		} else {

			return $this->_toString($name);

		}

	}


	function _toString($name) {

		$type				= $this->_type[$name];
		$value				= trim($this->value[$name]);
		$options			= $this->options[$name];
		$size				= $this->_size[$name];
		$tag				= $this->tag[$name];
		$desc2				= $this->desc2[$name];
		$separator			= $this->separator[$name];
		$conditional_input	= $this->conditional_input[$name];

		if ($type == 'text') {

			return "<input class=\"form-control\" type=text name='$this->prefix$name' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' size='$size' $tag ".(!is_numeric($size) ? "style='width:$size'" : "")." /> $desc2";

		} elseif ($type == 'text_readonly2') {
			return "<input class=\"form-control\" readonly type=text name='$this->prefix$name' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' size=$size $tag style='width:$size' /> $desc2";
		} elseif ($type == 'hidden') {

			return "<input type=hidden name='$this->prefix$name' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' $tag /> $desc2";

		} elseif ($type == 'select_multiple') {
			$option		= '';
			$option		.= "<option value=''>--Choose One--</option>\r\n";
			if (is_array($options))
			foreach ($options as $key => $option_value) {
				$option_value	= trim($option_value);
				$selected		= ($value ? ($value == $option_value) ? 'selected' : '' : '');
				$key			= array_shift(explode("#", trim($key)));

				if (isset($conditional_input[$option_value]))
					$action	= "onselectstart=''";
				else
					$action	= '';

				if (is_numeric($key))
					$option	.= "<option value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $selected $action>$option_value</option>\r\n";
				else
					$option	.= "<option value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $selected $action>$key</option>\r\n";
			}
			return "<select multiple=\"multiple\" name='".(strpos($name,'__') ? str_replace("__","",$name)."[]" : $name)."' style='width:".(strpos($size,'%') ? $size : $size.'px').";' $tag>\r\n$option</select> $desc2";
		} elseif ($type == 'select') {

			$option		= '';
			$option		.= "<option value=''>--Choose One--</option>\r\n";
			if (is_array($options))
			foreach ($options as $key => $option_value) {
				$option_value	= trim($option_value);
				$selected		= ($value ? ($value == $option_value) ? 'selected' : '' : '');
				// $key			= array_shift(explode("#", trim($key)));

				if (isset($conditional_input[$option_value]))
					$action	= "onselectstart=''";
				else
					$action	= '';

				if (is_numeric($key))
					$option	.= "<option value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $selected $action>$option_value</option>\r\n";
				else
					$option	.= "<option value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $selected $action>$key</option>\r\n";
			}
			return "<select class=\"form-control\" name='$this->prefix$name' style='width:".(strpos($size,'%') ? $size : $size.'px').";' $tag>\r\n$option</select> $desc2";

		} elseif ($type == 'select2') {
			
			$option		= '';
			$option		.= "<option value=''>--Choose One--</option>\r\n";
			if (is_array($options))
			foreach ($options as $key => $option_value) {
				$option_value	= trim($option_value);
				$selected		= ($value ? ($value == $option_value) ? 'selected' : '' : '');
				// $key			= array_shift(explode("#", trim($key)));

				if (isset($conditional_input[$option_value]))
					$action	= "onselectstart=''";
				else
					$action	= '';

				if (is_numeric($key))
					$option	.= "<option value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $selected $action>$option_value</option>\r\n";
				else
					$option	.= "<option value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $selected $action>$key</option>\r\n";
			}
			return "<select class=\"form-control select2\" name='$this->prefix$name' style='width:".(strpos($size,'%') ? $size : $size.'px').";' $tag>\r\n$option</select> $desc2";

		} elseif ($type == 'textarea') {

			return "<textarea class=\"form-control\" name='$this->prefix$name' style='width:".(strpos($size,'%') ? $size : $size.'px').";' $tag>$value</textarea> $desc2";

		} elseif ($type == 'radio') {
			$option		= '';
			foreach ($options as $key => $option_value) {
				$option_value	= trim($option_value);
				$checked		= ($value == $option_value) ? 'checked' : '';

				if (isset($conditional_input[$option_value]))
					$action	= "onselectstart=''";
				else
					$action	= '';

				if (is_numeric($key))
					$option	.= "<div class='radio'><label><input type=radio name='$this->prefix$name' value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' style='border:0' $checked $tag $action>$option_value $separator\r\n</label></div>";
				else
					$option	.= "<div class='radio'><label><input type=radio name='$this->prefix$name' value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' style='border:0' $checked $tag $action>$key $separator\r\n</label></div>";
			}
			return $option . $desc2;

		} elseif ($type == 'checkbox') {

			$option		= '';
			if (is_array($options)) {
				foreach ($options as $key => $option_value) {
					$option_value	= trim($option_value);
					$checked		= ($value == $option_value) ? 'checked' : '';

					if (is_numeric($key))
						$option	.= "<input type=checkbox name='$this->prefix$name' value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $checked $tag>$option_value $separator\r\n";
					else
						$option	.= "<input type=checkbox name='$this->prefix$name' value='" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "' $checked $tag>$key $separator\r\n";
				}
			} else {
				$checked	= ($value == 'on') ? 'checked' : '';

				//	Synchronize all checked box
				$count		= ++$this->checkbox_count[$name];

				$option	.= "<input type=checkbox id='checkbox_{$this->prefix}{$name}_$count' name=$name $checked $tag onclick='inputs_sync_checkbox(\"$this->prefix$name\", this);'>$option_value $separator\r\n";
			}
			return $option . $desc2;

		} elseif ($type == 'pulldownmenu') {

			global $inputs_pulldownmenu_init;

			if (empty($inputs_pulldownmenu_init)) {
				echo '<script type="text/javascript" src="js/pulldownmenu.js"></script>';
				echo '<script type="text/javascript" src="js/pulldownmenu_lib.js"></script>';
				$inputs_pulldownmenu_init	= true;
			}

			$result		= "<script type='text/javascript'>\r\n";
			$result		.= "pulldownmenu_$name = new PulldownMenu();\r\n";
			if (is_array($options)) {
				$count	= 0;
				foreach ($options as $key => $option_value) {
					$result		.= "pulldownmenu_$name.item[$count]		= '" . htmlentities($key, ENT_QUOTES, $this->encoding) . "';\r\n";
					$result		.= "pulldownmenu_$name.value[$count]	= '" . htmlentities($option_value, ENT_QUOTES, $this->encoding) . "';\r\n";
					$count++;
					if ($value == $option_value)
						$default_value		= $key;

				}
			}
			$result		.= "</script>";

			$result		.= "<input name='$this->prefix$name' type='hidden' id='input_pulldownmenu_{$name}_value' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' autocomplete=off>
							<input class=\"form-control\" type='text' id='input_pulldownmenu_$name' name='input_pulldownmenu_$name' size='$size' value='" . htmlentities($default_value, ENT_QUOTES, $this->encoding) . "' autocomplete=off $tag
								onfocus='pulldownmenu_$name.showMenu(this, this.value)'
								onkeydown='pulldownmenu_$name.changeSelectItem(event, this);'
								onkeyup='pulldownmenu_$name.renewMenu(event, this, this.value);'/> $desc2
							";

			return $result;

		} elseif ($type == 'password') {

			return "<input class=\"form-control\" type=password name='$this->prefix$name' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' style='width:".(strpos($size,'%') ? $size : $size.'px').";' $tag /> $desc2";

		} elseif ($type == 'file') {

			return "<input class=\"form-control\" type=file name='$this->prefix$name' size=$size $tag /> $desc2";

		} elseif ($type == 'button') {

			return "<input class=\"btn btn-default\" type=button name='$this->prefix$name' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' size=$size $tag /> $desc2";

		} elseif ($type== 'submit') {
			$value	= $this->desc[$name];
			return "<input class=\"btn btn-default\" type=submit name='$this->prefix$name' value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' style='width:{$size}px;' $tag /> $desc2";

		} elseif ($type == 'text_readonly') {

			return "<input class=\"form-control\" type=text value='" . htmlentities($value, ENT_QUOTES, $this->encoding) . "' size=$size $tag readonly/> $desc2";

		} else {

			return "$value $desc2";

		}

	}


	function clear() {
		$this->_type			= array();
		$this->value			= array();
		$this->desc				= array();
		$this->options			= array();
		$this->_size			= array();
		$this->tag				= array();
		$this->desc2			= array();
	}


	function synchronize_checkbox() {
		echo "<script>\r\n";
		echo "var checkbox_count	= new Array();\r\n";
		foreach ($this->checkbox_count as $name => $count) {
			echo "checkbox_count['$this->prefix$name']	= $count;\r\n";
		}

		echo <<<EOS

		function inputs_sync_checkbox(name, obj) {
			eval("count		= checkbox_count['" + name + "'];");
			if (count > 1) {
				for (i=1; i <= count; i++) {
					eval("document.getElementById('checkbox_" + name + "_" + i + "').checked = obj.checked;");
				}
			}

		}

EOS;

		echo "</script>";

	}


}


?>
