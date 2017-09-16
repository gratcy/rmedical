<?php

chdir("../");
include "inc_common.php";

$qid	= sql_secure($_GET['qid']);

$email	= sql_secure($_GET['cancel']);

sql_query("update me_mail_trace set unsubscript='Y' where queue_id='$qid' and email='$email'");
sql_query("update me_email set refuse='Y' where email='$email'");
sql_query("update member set subscribe='' where email='$email'");


//gotoURL("http://" . $_SERVER['HTTP_HOST'], 3);


?>

<html>
<head>
<title>Unsubscription</title>
<link href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>


<body bgcolor=#ffffff>
<table width="520" cellpadding="0" cellspacing="0" bgcolor=ffffff align=center>
  <tr>
	<td height=120 background='../images/2008bg3.jpg'>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>
		Your subscription of our newsletter is cancelled. Thanks for visit.
		<script>
			setTimeout("location.href='http://<?php echo $domain; ?>/';", 3000);
		</script>
	</td>
  </tr>
</table>


<br><br><br>
</body>
</html>