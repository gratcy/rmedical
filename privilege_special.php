<?php


$SPECIAL_PASSWORD		= "keung";


if (!isset($_SESSION['privilege_special'] )) {

	if (isset($_POST['chkpw'])) {

		if ($_POST['password']==$SPECIAL_PASSWORD) {
			$_SESSION['privilege_special']	= $_POST['password'];
			gotoURL(-1);
		}
	}
?>

<br><br><br>
<div class="row">
  <div class="col-sm-3"></div>
  <div class="col-sm-6">
    <form class="form-horizontal" method="post" action="" onSubmit="return chk_out()">
      <input type="hidden" name="chkpw" value="login">
      <div class="form-group">
        <label for="password" class="col-sm-4 control-label"><?php echo lang('請輸入管理員密碼'); ?> :</label>
        <div class="col-sm-8">
          <input class="form-control loginbox_inputbox" id="special_password" type="password" name="password" onkeydown="if (event.keyCode==13) if (chk_out()) login.submit();" />
        </div>
      </div>
      <hr width="100%" size="1" align="left">
      <div class="form-group">
        <label for="" class="col-sm-2 control-label"></label>
        <div class="col-sm-8">
          <input class="btn btn-default" name="submit" type="submit" value="確定">
        </div>
      </div>
    </form>
  </div>
  <div class="col-sm-3"></div>
</div>
<br><br><br>

<script>
	document.getElementById('special_password').focus();
</script>

<?php

	include "footer.php";

	exit();

}





?>
