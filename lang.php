<?php

include_once "inc_common.php";
$lang = isset($_GET['lang']) ? $_GET['lang'] : '';
$lang = $lang == 'en' ? 'en' : 'hk';


$_SESSION['lang']	= $lang;

echo "<meta http-equiv='refresh' content='0;URL=".($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '/')."'>";
die;
