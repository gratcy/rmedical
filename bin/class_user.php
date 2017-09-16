<?php

////////////////////////////////////////////////////////////////////////////
//	General Class
////////////////////////////////////////////////////////////////////////////

class User {

	var $RDF = array('department');
	var $id;
	var $name;
	var $name_chi;
	var $name_eng;
	var $password;
	var $privilege;
	var $gender;
	var $email;
	var $email_personal;
	var $phone;
	var $phone_home;
	var $usergroup;
	var $level;
	var $isLogin;
	
	var $type	= 'user';

	function User($source = '') {
		
		$this->usergroup	= array();
		$this->level		= 0;

		if (is_array($source)) {
			$this->loadArray($source);
		} else if ($source != '') {
			$this->loadDB($source);
		}

		$this->privilege	= split(',|, | ', $this->privilege);

	}
	
	// Static function
	function initialize() {
		global $user, $staff;

		if ($session->user_type == 'staff')
			$user	= new Staff($session->user_id);
		else if ($session->user_type == 'student')
			$user	= new Student($session->user_id);
		else if ($session->user_type == 'parents')
			$user	= new Parents($session->user_id);
		else
			$user	= new User();

		$staff	= new Staff($session->staff_id);
	}
	
	// Static function
	function validate_login($id, $password) {
		$id			= strtoupper($id);
		$password	= $password;
		$result = @mysql_query("select id from staff where upper(id)='$id' and aes_decrypt(password, '{$GLOBALS['DB_ENCRYPT_KEY']}')='$password' and status not like '%freeze%'");
		return (@mysql_num_rows($result) != 0);
	}
	
	function loadArray($source) {
		foreach ($source as $name => $value) {
			$this->$name = sql_secure($value);
		}
		
		if (!isset($this->reg_date))	$this->reg_date		= date("Y-m-d H:i:s");
		if (!isset($this->level))		$this->level		= 10;

	}

	function loadDB($id) {
		$info = @mysql_fetch_assoc(@mysql_query("select id, name_chi as name, name_chi, name_eng, privilege, aes_decrypt(password, '{$GLOBALS['DB_ENCRYPT_KEY']}') as password, gender, email, email_personal, phone, phone_home from staff where id='$id'"));

		if (is_array($info)) {
			foreach ($info as $name => $value) {
				$this->$name = $value;
			}
			$this->isLogin 			= true;
		}
	}
	
	function isGroup($group) {
		return (trim($group) == '' || in_array(trim($group), $this->usergroup));
	}
	
	function addGroup($group) {
		if (!in_array($group, $this->usergroup))
			array_push($this->usergroup, $group);
	} 
	
	function privilege($privilege) {
		if (is_array($privilege)) {
			foreach ($privilege as $p)
				if (in_array($p, $this->privilege))
					return true;
			return false;
		}
		return @in_array($privilege, $this->privilege);
	}
	
	function login() {
		$session->user_id		= $this->id;
		$session->user_type		= $this->type;
		$this->isLogin 			= true;
	}
	
	function logout() {
		$session->user_id		= null;
		$session->user_type		= null;
		$this->isLogin			= false;

		unset($session->user_id);
	}
	
	function isLogin() {
		return $this->isLogin;
	}
	
	function save() {
		
		$update = sql_check("select 1 from staff where id='{$this->id}'");
		$result = false;
		$usergroup	= implode(',', $this->usergroup);
		
		if ($update) {
			$result = mysql_query("update member set fullname='$this->fullname', name='$this->name', password=aes_encrypt('$this->password', '{$GLOBALS['DB_ENCRYPT_KEY']}'), gender='$this->gender', email='$this->email', phone='$this->phone', usergroup='$usergroup', level='$this->level', status='$this->status' where id='$this->id'");
		} else {
			$result = mysql_query("insert into member values ('$this->id', '$this->fullname', '$this->name', aes_encrypt('$this->password', '{$GLOBALS['DB_ENCRYPT_KEY']}'), '$this->gender', '$this->email', '$this->phone', '$usergroup', '$this->level', '$this->reg_date', '$this->status')");
		}
		
		$row = array('id' => $this->id);
		
		return $result;	
		
	}
	
}



////////////////////////////////////////////////////////////////////////////
//	Staff Class
////////////////////////////////////////////////////////////////////////////

class Staff extends User {
	
	var $phone_mobile;
	
	var $type	= 'staff';
	
	function loadDB($id) {
		$this->id = $id;
		$info = @mysql_fetch_assoc(@mysql_query("select id, name_chi as name, name_chi, name_eng, privilege, aes_decrypt(password, '{$GLOBALS['DB_ENCRYPT_KEY']}') as password, gender, email, email_personal, phone, phone_home from staff where id='$id'"));

		if (is_array($info)) {
			foreach ($info as $name => $value) {
				$this->$name = $value;
			}
			$this->isLogin 			= true;
		}
	}
	
	function validate_data($exclude) {
		$error = array();
		
		$exclude = explode(',', $exclude);

		// Validate member info
		$exist_id			= sql_check("select 1 from staff where id='{$this->id}'");
		$exist_name			= sql_check("select 1 from staff where name='{$this->name}'");
		
		if (in_array('exist_id', $exclude))				$exist_id = false;
		if (in_array('exist_name', $exclude))			$exist_name = false;

		if ($exist_id)									array_push($error, "這個登入名稱已被使用，請改用其他登入名稱");
		if ($exist_name)								array_push($error, "這個暱稱已被使用，請改用其他暱稱");
		if (!validate_id($this->id))					array_push($error, "登入名稱只能用英文字或數字，不能有其他符號或空格");
		if (!validate_password($this->password))		array_push($error, "密碼只能用英文字或數字，並且必須最少５位字");
		if (!validate_mail($this->email))				array_push($error, "電郵地址格式不正確");
		if (!validate_phone($this->phone))				array_push($error, "電話號碼格式不正確");
		

		return $error;
	}
	
	// Static function
	function validate_login($id, $password) {
		$id			= strtoupper($id);
		$password	= $password;
		$result = @mysql_query("select id from staff where upper(id)='$id' and aes_decrypt(password, '{$GLOBALS['DB_ENCRYPT_KEY']}')='$password'");;
		return (@mysql_num_rows($result) != 0);
	}

	
	function login() {
		$session->staff_id		= $this->id;
		$session->staff_type	= $this->type;
		$this->isLogin 			= true;
	}
	

	function logout() {
		$session->staff_id		= null;
		$session->staff_type	= null;
		$this->isLogin			= false;

		unset($session->staff_id);
		unset($session->staff_type);
	}

}




////////////////////////////////////////////////////////////////////////////
//	Student Class
////////////////////////////////////////////////////////////////////////////

class Student extends User {
	
	var $class;
	var $class_no;
	var $phone_mobile;
	
	var $type	= 'student';
	
	function loadDB($id) {
		$this->id = strtoupper($id);
		
		$ref_id	= sql_getValue("select reference_id from member where id='$id'");
		
		$this->reference_id	= $ref_id;

		$info = @mysql_fetch_assoc(@mysql_query("select class, class_no, name_chi as name, name_chi, name_eng, gender, birthday, address, email, phone_mobile, phone_home from student where id='$ref_id'"));

		if (is_array($info)) {
			foreach ($info as $name => $value) {
				$this->$name = $value;
			}
			$this->isLogin 			= true;
		}
	}
	
	// Static function
	function validate_login($id, $password) {
		$id			= strtoupper($id);
		$password	= $password;
		$result = @mysql_query("select id from member where upper(login)='$id' and aes_decrypt(password, '{$GLOBALS['DB_ENCRYPT_KEY']}')='$password'");
		return (@mysql_num_rows($result) != 0);
	}
	
}



////////////////////////////////////////////////////////////////////////////
//	Parent Class
////////////////////////////////////////////////////////////////////////////

class Parents extends User {
	
	var $class;
	var $class_no;
	var $phone_mobile;
	
	var $type	= 'parents';
	
	function loadDB($id) {
		$this->id = strtoupper($id);

		$ref_id	= sql_getValue("select reference_id from member where id='$id'");
		
		$this->reference_id	= $ref_id;

		$info = @mysql_fetch_assoc(@mysql_query("select class, class_no, name_chi as name, name_chi, name_eng, gender, birthday, address, email, phone_mobile, phone_home from student where id='$ref_id'"));

		if (is_array($info)) {
			foreach ($info as $name => $value) {
				$this->$name = $value;
			}
			$this->isLogin 			= true;
		}
	}
	
	// Static function
	function validate_login($id, $password) {
		$id			= strtoupper($id);
		$password	= $password;
		$result = @mysql_query("select id from member where upper(login)='$id' and aes_decrypt(password, '{$GLOBALS['DB_ENCRYPT_KEY']}')='$password'");
		return (@mysql_num_rows($result) != 0);
	}
	
}

?>