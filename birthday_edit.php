<?php
include_once "header.php";
$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
$bmtype = isset($_REQUEST['bmtype']) ? (int) $_REQUEST['bmtype'] : 0;
$name = isset($_POST['name']) ? $_POST['name'] : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';
if ($submit) {
	$error = null;
	if ($id) {
		if (!$name && !$content) {
			$error = 'Data you input is incomplete !!!';
		}
		else if (!$name) {
			$error = 'Name must be filled !!!';
		}
		else if (!$content) {
			$error = 'Content email must be filled !!!';
		}
		else if ($bmtype != 1 && !$subject) {
			$error = 'Subject email must be filled !!!';
		}
		else {
			$fields['bname'] = $name;
			$fields['bsubject'] = $subject;
			$fields['bcontent'] = $content;
			$fields['bmodified'] = json_encode(array('uid' => $user -> id, 'date' => date("Y-m-d H:i:s")));
			sql_query(sql_update("blasting_template_tab", $fields, "bid='$id'"));
		}
		if (!empty($error)) {
			echo "<p><font color=red>Error :</font></p>";
			echo "<p>( ".$error." )</p>";
			gotoURL(-1, 3);
			exit;
		}
		else {
			echo "<p><font color=blue>Template successfully updated :</font></p>";
			echo "<p>( 3 秒內會自動反回前面，或按 <a href='birthday.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
			gotoURL(-2, 3);
			exit;
		}
	}
	else {
		$error = 'Invalid input data !!!';
	}
}
	$detail 	= sql_getVar("select * from blasting_template_tab where bid='$id'");
?>

<script src="js/tinymce/tinymce.min.js" type="text/javascript"></script>
<h3 class='pull-left'><?php echo lang('更新'); ?> <?php echo lang('生日'); ?></h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回 (B)</a></span><br /><br /><br />
<form class='form-horizontal' name="form" action="" method="post">
<input type="hidden" name="bmtype" value="<?php echo $detail['bmtype']; ?>">
				<div class='form-group'>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Name</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="name" value="<?php echo $detail['bname']; ?>">
					</div>
				</div>
				<div class="form-group subject" style="<?php echo ($detail['bmtype'] == 1 ? "display:none" : "display:block")?>">
			    <label for="subject" class='col-sm-2 control-label'>Subject</label>
					<div class='col-sm-8'>
						<input class='form-control' type="text" name="subject" value="<?php echo $detail['bsubject']; ?>">
					</div>
				</div>
				<div class='form-group'>
			    <label for='name' class='col-sm-2 control-label'>Content</label>
					<div class='col-sm-8'>
						<textarea class='form-control content-messege' name="content"><?php echo $detail['bcontent']; ?></textarea>
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
<?php if ($detail['bmtype'] == 2) : ?>
tinymce.init({
  selector: '.content-messege',
  height: 500,
  menubar: false,
  plugins: [
    'advlist autolink lists link image charmap print preview anchor',
    'searchreplace visualblocks code fullscreen',
    'insertdatetime media table contextmenu paste code'
  ],
  toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
  content_css: '//www.tinymce.com/css/codepen.min.css'
});
<?php endif; ?> 
shortcut.add("Ctrl+B", function () {history.go(-1); });
</script>
<?php
include_once "footer.php";
?>
