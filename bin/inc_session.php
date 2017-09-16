<?php

//	Before include/instantiate this class :
//		Must first setup database connection
//		Must have $DB_DATABASE for setting session name


class Session implements Iterator {
	
	var	$session_name;
	var $session_id;
	
	var $expire_period	= 86400;
	
	function Session($name = '', $id = '') {

		if (empty($name))	$name	= $GLOBALS['DB_DATABASE'];
		
		if (empty($id)) 	$id		= $_COOKIE["session_$name"];
		if (empty($id)) {
			$id		= md5(date('Y-m-d H:i:s ') . rand(0, 104857600));
			setcookie("session_$name", $id);
		}

		$this->session_name		= $name;

		$this->session_id		= $id;
		$this->initialize();
	}
	
	function __get($name) {
		return unserialize(sql_getValue("select value from session where session_name='$this->session_name' and session_id='$this->session_id' and name='$name'"));
	}
	
	function __set($name, $value) {
		$value	= addslashes(serialize($value));
		if (sql_check("select value from session where session_name='$this->session_name' and session_id='$this->session_id' and name='$name'"))
			sql_query("update session set value='$value' where session_name='$this->session_name' and session_id='$this->session_id' and name='$name'");
		else
			sql_query("insert into session values('$this->session_name', '$this->session_id', '$name', '$value')");
	}
	
	function __isset($name) {
		return sql_check("select 1 from session where session_name='$this->session_name' and session_id='$this->session_id' and name='$name'");
	}
	
	function __unset($name) {
		sql_query("delete from session where session_name='$this->session_name' and session_id='$this->session_id' and name='$name'");
	}
	
	function initialize() {
		sql_query("
			CREATE TABLE IF NOT EXISTS `session` ( 
			    `session_name` varchar(32) NOT NULL default '', 
			    `session_id` varchar(32) NOT NULL default '', 
			    `name` varchar(255) NOT NULL default '',
			    `value` text NOT NULL
			) ENGINE=MyISAM; ");
		
		//	clean expired sessions
		$expired	= &sql_getArray("select session_id, session_name from session where name='SESSION_EXPIRE_TIME' and substring(value, 7, 19) < now()");
		foreach ($expired as $id => $name) {
			sql_query("delete from session where session_name='$name' and session_id='$id'");
		}
		
		//	set current session time
		$this->__set(SESSION_EXPIRE_TIME, date('Y-m-d H:i:s', time() + $this->expire_period));
		
	}
	
	
	
	//	Functions for iteration
	
	var $iterator;
	
    public function rewind() {
		$this->iterator	= $this->getArray();
    }

    public function current() {
		return current($this->iterator);
    }

    public function key() {
		return key($this->iterator);
    }

    public function next() {
		return next($this->iterator);
    }

    public function valid() {
		return $this->current() !== false;
    }


	public function getArray() {
		$query	= mysql_query("select name, value from session where session_name='$this->session_name' and session_id='$this->session_id'");
		$result	= array();
		while (list($name, $value) = mysql_fetch_row($query)) {
			$result[$name]	= unserialize($value);
		}
		unset($result['SESSION_EXPIRE_TIME']);
		return $result;
	}
	
	public function add($name) {
		return null;
	}

}


$session	= new Session();


?>