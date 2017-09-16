<?php

include_once "inc_common.php";

$id					= $_GET['id'] * 1;

$item				= sql_getObj("select * from board where id = '$id'");

$MAILTO_WEBMASTER		= "test@rock.com";

$title					= $item->title;
$content				= $item->content;


include_once "notify_form.php";

//include_once "footer.php";

?>