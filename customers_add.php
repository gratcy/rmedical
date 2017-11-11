<?php
include_once "header.php";
$name = isset($_POST['name']) ? $_POST['name'] : '';

$dd = isset($_POST['dd']) ? (int) $_POST['dd'] : 0;
$mm = isset($_POST['mm']) ? (int) $_POST['mm'] : 0;
$yyyy = isset($_POST['yyyy']) ? (int) $_POST['yyyy'] : 0;

$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : array();
$address = isset($_POST['address']) ? $_POST['address'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';

if ($submit) {
	$error = null;
	if (!$name || !$phone || !$dd || !$mm || !$yyyy) {
		$error = 'Data you input is incomplete !!!';
	}
	else if (!$phone[0]) {
		$error = 'Phone number must be filled !!!';
	}
	else if (!is_numeric($phone[0])) {
		$error = 'Invalid phone number format !!!';
	}
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = 'Invalid email number format !!!';
	}
	else {
		$ck = sql_check("SELECT * FROM customer_tab WHERE cstatus=1 AND cemail='".$email."'");
		if ($ck) {
			$error = 'Email exists !!!';
		}
		else {
			$birthday = $yyyy.'-'.$mm.'-'.$dd;
			
			$fields['cname'] = $name;
			$fields['cbirthday'] = strtotime($birthday);
			$fields['cemail'] = $email;
			$fields['cphone'] = implode('*',$phone);
			$fields['caddr'] = $address;
			$fields['cstatus'] = 1;
			$fields['ccreated'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
			sql_query(sql_insert("customer_tab", $fields));
		}
	}
	
	if (!empty($error)) {
		echo "<p><font color=red>Error :</font></p>";
		echo "<p>( ".$error." )</p>";
		gotoURL(-1, 3);
		exit;
	}
	else {
		echo "<p><font color=blue>Customer successfully added :</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='customers.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-2, 3);
		exit;
	}
}
?>

<link rel="STYLESHEET" type="text/css" href="js/rich_calendar/rich_calendar.css">
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rich_calendar.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_en.js"></script>
<script language="JavaScript" type="text/javascript" src="js/rich_calendar/rc_lang_ru.js"></script>
<script language="javascript" src="js/domready.js"></script>
<script language="javascript" src="js/show_cal.js"></script>
<h3 class='pull-left'>Add Customer</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />
<form class='form-horizontal' name="form" action="" method="post">

				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Name</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="name" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='birthday' class='col-sm-2 control-label'>Birthday</label>
					<div class='col-sm-8'>
						<?php echo get_date_dropdown('');?>
					</div>
				</div>
				<div class='form-group'>
			    <label for='email' class='col-sm-2 control-label'>Email</label>
					<div class='col-sm-8'>
						<input class='form-control' type="email" name="email" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='phone[0]' class='col-sm-2 control-label'>Phone I</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="phone[0]" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='phone[1]' class='col-sm-2 control-label'>Phone II</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="phone[1]" value="">
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Address</label>
					<div class='col-sm-8'>
						<textarea class='form-control' name="address"></textarea>
					</div>
				</div>
				<hr width=100% size=1 align=left>
				<div class='form-group'>
			    <label for='' class='col-sm-2 control-label'></label>
					<div class='col-sm-8'>
						<input class='btn btn-default' name="submit" type="submit" value="Save (S)">
					</div>
				</div>
</form>
<script>
shortcut.add("Ctrl+B", function () {history.go(-1); });
</script>
<?php
include_once "footer.php";
?>
