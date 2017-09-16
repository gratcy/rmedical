<?php

include_once "inc_common.php";

$record_per_page		= 30;
$user_name				= $_SESSION['user_name'];

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>磐石管理系統 Rock Management System</title>

<link href="style.css" rel="stylesheet" type="text/css">
<link href="style_print.css" rel="stylesheet" type="text/css">

</head>


<body bgcolor=#ffffff>

<?php

require_once	"login.php";

?>


<script>


function roundNum (strFloat,v){
	if (v == undefined)
		v	= 2;
    var num = Math.pow(10,v);
    return Math.round(strFloat*num)/num;
}


</script>