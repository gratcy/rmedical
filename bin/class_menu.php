<?php


class Menu {

	var $id			= '';
	var $list		= '';
	var $template	= '';
	var $options	= '';

	function Menu($list, $template = '', $options = '') {
		$this->id	= rand(0, 9999);
		$this->add($list);
		$this->template($template);
		$this->options	= $options;
	}
	
	function add($list) {
		$this->list	= $list;
	}
	
	function template($template) {
		if (is_numeric($template) and $template < 10)
			$template	= '0' . $template;
		$this->template	= $template;
	}
	
	function output() {
		ob_start();
		
		$id			= $this->id;
		$tempate	= $this->template;
		$list		= $this->list;
		$options	= $this->options;

		if (is_file("bin/menu_style_{$this->template}.php"))
			include "bin/menu_style_{$this->template}.php";
		
		$result	= ob_get_contents();
		ob_end_clean();
		return $result;
	}
	
	function __toString() {
		return $this->output();
	}

}



?>
