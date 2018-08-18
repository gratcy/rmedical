<?php

include_once "inc_common.php";
// include "bin/class_log.php";

$log = new Log();
$record_per_page = 30;
$user_name = $_SESSION['user_name'];

?>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<meta name="description" content="磐石管理系統 Rock Management System">
<meta name="author" content="磐石管理系統 Rock Management System">

<title>磐石管理系統 Rock Management System</title>

<link href="bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="style.css?<?php echo time(); ?>" rel="stylesheet" type="text/css">
<link href="style_print.css" rel="stylesheet" type="text/css" media="print">
<script type="text/javascript" src="js/shortcut.js"></script>
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<link rel="stylesheet" href="js/daterangepicker/daterangepicker.css">
<script type="text/javascript" src="js/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="js/daterangepicker/daterangepicker.js"></script>
<link rel="stylesheet" href="js/select2/select2.min.css">
<script src="js/select2/select2.full.min.js"></script>
<link rel="stylesheet" href="fontawesome/css/font-awesome.min.css">
<link rel="stylesheet" href="fontawesome/css/ionicons.min.css">
<script src="js/highcharts/highcharts.js" type="text/javascript"></script>
<script src="js/highcharts/modules/exporting.js" type="text/javascript"></script>
</head>

<div class="brand">
  <div class="container">
	  <a href="/" title="Rock Medical"><img class="img-responsive pull-left" src="images/print_header_logo.jpg"></a>
    <img class="img-responsive pull-right hidden-xs" src="images/print_header_year.jpg">
  </div>
</div>
<nav class="navbar navbar-default navbar-static-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <?php
		$logout = '登出';
        $list_menu = array();
        if ($_SESSION['lang'] == 'hk') {
			$menu1 = sql_getArray("select distinct `group`  from service_user_privilege where user_id='$user->id' ORDER BY `order`");
			foreach ($menu1 as $group1) {
			  $menu1_list = sql_getArray("select name , link from service_user_privilege where user_id='$user->id' and `group`='$group1' ORDER BY `order`");
			  if (count($menu1_list) > 0)
			  $list_menu[$group1] = $menu1_list;
			  else
			  $list_menu[$group1] = $menu1_list[$group1];
			}
        }
        else {
			$logout = 'Logout';
			$menu1 = sql_getArray("select distinct `group_en`  from service_user_privilege where user_id='$user->id' ORDER BY `order`");
			foreach ($menu1 as $group1) {
			  $menu1_list = sql_getArray("select name_en , link from service_user_privilege where user_id='$user->id' and `group_en`='$group1' ORDER BY `order`");
			  if (count($menu1_list) > 0)
			  $list_menu[$group1] = $menu1_list;
			  else
			  $list_menu[$group1] = $menu1_list[$group1];
			}
		}

        require "bin/class_menu.php";
        $menu = new Menu($list_menu, 1);
        echo $menu->output();
      ?>
      <?php
        require_once  "login.php";
        echo '<ul class="nav navbar-nav navbar-right">';
        if ($_SESSION['lang'] == 'hk') {
			echo '<li><a href="./lang.php?lang=en">ENG</a></li>';
		}
		else {
			echo '<li><a href="./lang.php?lang=hk">HKG</a></li>';
		}
        echo '<li class="dropdown">';
        echo '<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$user_name.' <span class="caret"></span></a>';
        echo '<ul class="dropdown-menu">';
        echo '<li><a href="login_logout.php">'.$logout.'</a></li>';
        echo '</ul>';
        echo '</li>';
        echo '</ul>';
      ?>
    </div>
  </div>
</nav>

<?php

echo <<<EOS
<div class='container'>
  <script>
    function stopRKey(evt) {
      var evt = (evt) ? evt : ((event) ? event : null);
      var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);

      if ((evt.keyCode == 13) && (node.type=="text"))  {
        if (nextTab)
          nextTab();
        return false;
      }
    }

    var stopRKey_onkeypress_previous  = document.onkeypress;

    function stopRKey_onkeypress(evt) {
      var evt = (evt) ? evt : ((event) ? event : null);
      if (stopRKey_onkeypress_previous)
        stopRKey_onkeypress_previous(evt);
      return stopRKey(evt);
    }

    document.onkeypress = stopRKey_onkeypress;

    function nextTab() {
      var nextinput = document.activeElement.getAttribute('nextinput');

      if (!nextinput)
        return false;

      document.getElementById("form").elements.namedItem(nextinput).focus();
      if ($('select[name="'+nextinput+'"]').is("select")) {
        $('form select[name="'+nextinput+'"]').select()
        // document.getElementById("form").elements.namedItem(nextinput).select();
      }
    }

    function roundNum (strFloat,v){
      if (v == undefined)
        v = 2;
        var num = Math.pow(10,v);
        return Math.round(strFloat*num)/num;
    }
  </script>
EOS;

?>
