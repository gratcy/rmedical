<?php

include_once "header.php";

if ($_GET['action']=='add') {

	$fields				= sql_secure($_GET,"name");

	if (empty($error)) {
		sql_query(sql_insert("board_folder", $fields));

		echo "<p><font color=blue>新增文件夾成功！</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='board_folder.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
	gotoURL('board_folder.php', 3);
	exit;
	}
}

if ($_GET['action']=='del') {

	$id		= $_GET[select] * 1;

	sql_query("delete from board_folder where id='$id'");
		echo "<p><font color=blue>刪除文件夾成功！</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='board_folder.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
	gotoURL('board_folder.php', 3);
	exit;
}

if ($_GET['action']=='rename') {

	$id							= $_GET[select] * 1;
	$fields						= sql_secure($_GET, "name");

	if (empty($error)) {
		sql_query(sql_update("board_folder", $fields, "id='$id'"));

		echo "<p><font color=blue>重命名文件成功！</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='board_folder.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL('board_folder.php', 3);
		exit;
	}
}



echo <<<EOS
<h3 class='pull-left'>文件夾管理</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='index.php'>返回</a></span><br /><br /><br />

  	<form class="form-horizontal" id="form1" name="form1" method="get" action="">
  	<input type=hidden name=action value=add>
  	<div class="form-group">
	    <label for="name" class="col-xs-12 col-sm-3 control-label">增加文件夾：</label>
	    <div class="col-xs-8 col-sm-6">
	      <input type="text" class="form-control" id="name" name="name">
	    </div>
	    <div class="col-xs-4 col-sm-2">
	    	<input class='btn btn-default' type="submit" name="add"  value="增加" />
	    </div>
	  </div>
  </form>
  <hr />
  	<form class="form-horizontal" id="form2" name="form2" method="get" action="">
  	<input type=hidden name=action value=del>
  	<div class="form-group">
	    <label for="select" class="col-xs-12 col-sm-3 control-label">文件夾刪除：</label>
	    <div class="col-xs-8 col-sm-6">
	      <select class="form-control" id="select" name="select" >
EOS;

$data		= sql_getArray("select id, name from board_folder");
echo "<option value='0'>請選擇要刪除的文件夾</option>";
foreach ($data as $id => $name) {
	echo "<option value='$id'>$name</option>";
}

echo <<<EOS

	  </select>
	    </div>
	    <div class="col-xs-4 col-sm-2">
	    	<input class='btn btn-default' type="submit" name="del"  value="刪除" />
	    </div>
	  </div>
  </form>
  <hr />

  	<form class="form-horizontal" id="form3" name="form3" method="get" action="">
  	<input type=hidden name=action value=rename>
  	<div class="form-group">
	    <label for="select" class="col-xs-12 col-sm-3 control-label">文件夾重命名：</label>
	    <div class="col-xs-8 col-sm-6">
	      <select class="form-control" id="select" name="select" >
EOS;

$data		= sql_getArray("select id, name from board_folder");
echo "<option value='0'>請選擇要刪除的文件夾</option>";
foreach ($data as $id => $name) {
	echo "<option value='$id'>$name</option>";
}

echo <<<EOS

	  </select>
	    </div>
	    <div class="col-xs-4 col-sm-2"></div>
	  </div>
  	<div class="form-group">
	    <label for="name" class="col-xs-12 col-sm-3 control-label">新文件夾名稱：</label>
	    <div class="col-xs-8 col-sm-6">
	      <input type="text" class="form-control" id="name" name="name">
	     </div>
	    <div class="col-xs-4 col-sm-2">
	    	<input class='btn btn-default' type="submit" name="rename"  value="重命名" />
	    </div>
	  </div>
  </form>
  <br/><br/><br/>
EOS;




include_once "footer.php";

?>