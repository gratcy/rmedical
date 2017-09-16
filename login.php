<?php

// 檢查是不是己登入使用者
if (!isset($_SESSION['privilege'])) {
// 未登入 或 正在登出

?>

</ul>
</div>
</div>
</nav>
<div class="container">

  <!-- Login Box Start  -->
  <form class="form-signin" name='login' method='post' action='login_login.php' target="_self" onSubmit="return chk_out()">
    <h2 class="form-signin-heading">使用者登入</h2>
    <label for="login_id" class="sr-only">使用者</label>
    <input type="text" id="login_id" name="login_id" class="form-control" maxlength="20" placeholder="使用者" required="" autofocus="" onFocus="this.style.background='#fff'" onBlur="this.style.background=''" onkeydown="if (event.keyCode==13) passwd.focus();">
    <label for="login_password" class="sr-only">密碼</label>
    <input type="password" id="login_password" name="login_password" class="form-control" maxlength="20" placeholder="密碼" required="" onFocus="this.style.background='#fff'" onBlur="this.style.background=''" onkeydown="if (event.keyCode==13) if (chk_out()) login.submit();">
    <input type="hidden" name="chkform" value="login">
    <button class="btn btn-lg btn-primary btn-block" type="submit">登入</button>
  </form>

  <!-- Check id & password  -->
  <script>
    <!-- 檢查是否空白資料 -->
    function chk_out() {
      if(!login.loginid.value){alert('請輸入登入名稱');login.loginid.focus();return false;}
      else if(!login.passwd.value){alert('請輸入登入密碼');login.passwd.focus();return false;}
      else return true;
    }
  </script>

</div>
<?php

include "footer.php";
exit();

} else {

}

?>
