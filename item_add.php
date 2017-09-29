<?php

include_once "header.php";


echo "<h3 class='pull-left'>新增產品</h3><span class='pull-right'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp; <a href='javascript:history.go(-1)'>返回</a></span><br /><br /><br />";

$privilege			= sql_getObj("select * from service_user_privilege where user_id='$user->id' and `link`='item.php'");
if (empty($privilege->edit))	{	gotoURL("item.php"); exit; }

if ($_POST['action'] == 'add') {
	$_POST['warranty'] = $_POST['warranty'] == 'on' ? 1 : 0;

	$error						= array();

	$fields						= sql_secure($_POST, "date_modify, name,name_short, name_series, name_subitem, color, size, barcode, price, safelimit, purprice, supplier_id, brand, class, user_sid, edit_time, qty,dqty , remark, warranty");

	$fields['date_modify']		= date("Y-m-d H:i:s");
	$fields['user_sid']			= $user->id;

	$prefix_item_id				= "PD";
	$suffix_item_id				= substr(sql_getValue("select item_id from item where item_id like '$prefix_item_id%' order by item_id desc limit 1"), 2);
	$new_item_id				= $prefix_item_id . padding($suffix_item_id+1, 9);

	$fields['item_id']			= $new_item_id;


	if (is_uploaded_file($_FILES['photo']['tmp_name'])) {

		$info		= getimagesize($_FILES['photo']['tmp_name']);
		if ($info['mime'] == 'image/jpeg') {

			$folder		= 'content/product/';
			$filename	= strtolower($_FILES['photo']['name']);

			@unlink($folder . $filename);

			if (move_uploaded_file($_FILES['photo']['tmp_name'], $folder . $filename)) {

				$fields['photo']			= $filename;

				require_once "bin/inc_images.php";

				create_thumbnail($folder . $filename, $folder . "250_".$filename, 250, 500);
			}
		} else

			echo "alert('圖片必需是 JPEG 格式。');\r\n";
	}



	if (empty($error)) {
		sql_query(sql_insert("item", $fields));


		echo "<p><font color=blue>新增記錄成功 : $item_no</font></p>";
		echo "<p>( 3 秒內會自動反回前面，或按 <a href='item.php'> &lt; 這裡 &gt; </a> 返回。 )</p>";
		gotoURL(-2, 3);
		exit;
	} else {
		foreach ($error as $err)
			echo $err;
	}

}


include_once "bin/class_inputs.php";
$inputs		= new Inputs();
$inputs->add(
			'name'						, 'text'			, '產品名稱'					, '100%',
			'name_short'				, 'text'			, '產品簡稱'					, '100%',
			'name_series'				, 'text'			, '產品系列'					, '100%',
			'name_subitem'				, 'text'			, '產品品種'					, '100%',
			'brand'						, 'select2'	, '品牌'						, '100%',
			'class'						, 'select'	, '種類'						, '100%',
			'supplier_id'				, 'select2'	, '供應商'						, '100%',
			'price'						, 'text'			, '價格 (HKD)'					, '100%',
			'color'						, 'text'			, '顏色'						, '100%',
			'barcode'					, 'text'			, '條碼'						, '100%',
			'size'						, 'text'			, '尺寸'						, '100%',
			'photo'						, 'file'			, '圖片'							, '100%',
//			'photo'						, 'hidden'			, '圖片'						, '100%',
			'sep1'						, ''				, '---'							, '100%',
			'remark'					, 'textarea'		, '備註'						, '100%',
			'warranty'					, 'checkbox'		, '保证'						, '100%',
			'sep2'						, ''				, '---'							, '100%',
			'submit_button'				, 'submit'			, '確定(S)'						, '100%'
				);





if ($_POST['action'] == 'add')
	$inputs->value	= $_POST;



$inputs->options['brand']					= sql_getArray("select description, id from class_brand order by description asc");
$inputs->options['class']					= sql_getArray("select description, id from class_item order by description asc");
$inputs->options['supplier_id']				= sql_getArray("select name, id from supplier order by name asc");

//$inputs->desc2['photo']						= "<iframe src='photo_upload.php?folder=purchase&bgcolor=f0f0f0&return_value=form.photo' width=440 height=150 frameborder=no></iframe>";

//$inputs->exclude[]							= 'cost_currency';

$inputs->tag['warranty']							= "class=''";
$inputs->tag['name']							= "class='form-control'";
$inputs->tag['name_short']							= "class='form-control'";
$inputs->tag['name_series']							= "class='form-control'";
$inputs->tag['name_subitem']							= "class='form-control'";
$inputs->tag['brand']							= "class='form-control'";
$inputs->tag['class']							= "class='form-control'";
$inputs->tag['supplier_id']							= "class='form-control'";
$inputs->tag['price']							= "class='form-control'";
$inputs->tag['color']							= "class='form-control'";
$inputs->tag['barcode']							= "class='form-control'";
$inputs->tag['size']							= "class='form-control'";
$inputs->tag['photo']							= "class='form-control'";
$inputs->tag['remark']							= "class='form-control'";
$inputs->tag['submit_button']							= "class='btn btn-default'";

?>


<form class='form-horizontal' name=form action='' method=post onsubmit='return form_check(this);'  enctype="multipart/form-data">
<input type=hidden name=action value=add>
<?php

foreach ($inputs->collection() as $name => $desc) {
	if ($desc == '確定(S)')	$desc = '';

	if ($desc == '---')
		echo "<hr width=100% size=1 align=left>";
	else
		echo "
			<div class='form-group'>
		    <label for='input_$name' class='col-sm-2 control-label'>$desc</label>
				<div class='col-sm-8'>
					{$inputs->$name}
				</div>
			</div>
			";

}

?>
</form>
<script>

shortcut.add("Ctrl+B", function () {history.go(-1); });
shortcut.add("Ctrl+S", function () {document.getElementById("form").submit(); });
shortcut.add("Ctrl+C", function () {calculate(); });


</script>

<?php

include "footer.php";

?>
