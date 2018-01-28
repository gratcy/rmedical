<?php
require_once "bin/inc_config.php";
@session_name('rock');
@session_start();


define(DOCROOT, '/');

$DOCUMENT_ROOT	= $_SERVER['DOCUMENT_ROOT'];



if (is_file("mail/mailer_proxy.php"))
	include_once "mail/mailer_proxy.php";



//Ignore_User_Abort(False);
Ignore_User_Abort(true);


require_once "class_setting.php";
$setting		= new Setting();

//$setting->reload();



$user					= sql_getObj("select * from service_user where id='" . ($_SESSION['user_id'] * 1) . "'");
unset($user->password);



//
//	Check for doubled process
//

if (!empty($_POST)) {

	$post_hash								= md5(date("Hi") . serialize($_POST));

	include_once	"bin/class_log.php";
	$hashlog		= new Log("record/hash.txt");
	$hashlog->add("Hash - $post_hash");
	$hashlog->save("append");

	if (isset($_SESSION['post_hash'][$post_hash]) && $_SESSION['post_hash'][$post_hash] === true) {
		//~ exit;// kacau
	}

	$hashlog		= new Log("record/hash.txt");
	$hashlog->add("Passed doubled process checking");
	$hashlog->save("append");

	$_SESSION['post_hash'][$post_hash]	= true;
	if (count($_SESSION['post_hash']) > 5)
		array_shift($_SESSION['post_hash']);

}




function debug_dump($value, $exit = false) {
	global $user;
	if ($user->user == 'admin')
		dump($value, $exit);
}

function debug_dump_table($value, $exit = false) {
	global $user;
	if ($user->user == 'admin')
		dump_table($value, $exit);
}

function delete_record($sql) {
	global $user;
	$query		= mysql_query($sql);
	$table		= mysql_field_table($query, 0);

	$primarykey	= "";
	for ($i=0; $i < mysql_num_fields($query); $i++) {
		$meta			= mysql_fetch_field($query, $i);
		if ($meta->primary_key == 1) {
			$primarykey = $meta->name;
			break;
		}
	}
	if (empty($primarykey)) {
		echo "<font color=red>Error : Can not delete record. Primary Key not found.</font><br>";
		return;
	}


	$data		= array();
	while ($row	= mysql_fetch_assoc($query)) {
		$data[]	= $row;
		mysql_query("delete from $table where $primarykey='" . $row[$primarykey] . "'");
	}
	$data		= serialize($data);
	mysql_query("insert into deleted_record (`table`, `data`, `user`, `date`) values ('$table', '" . str_replace(array("\\", "'"), array("\\\\", "\'"), $data) . "', '$user->id', now())");

}



//	Directory
$domain		= $_SERVER['SERVER_NAME'];
$url		= pathinfo($_SERVER["REQUEST_URI"]);
if ($url['dirname'] == '/')
	$url['dirname'] = '';




function lang($key) {
	$sesLang = $_SESSION['lang'] == 'hk' ? 'hk' : 'en';
	include('./bin/lang.php');
	return isset($lang[$sesLang][$key]) ? $lang[$sesLang][$key] : $key;
}

function get_unique_id() {
	if (empty($_SESSION['UNIQUE_ID']))
		$_SESSION['UNIQUE_ID']		= 0;
	$_SESSION['UNIQUE_ID']++;
	return $_SESSION['UNIQUE_ID'];
}



/////////////////////////////////////
///output data to excel
////////////////////////////////////

class Excel{

    var $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?\>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">";


    var $footer = "</Workbook>";

    var $lines = array ();

    var $worksheet_title = "Table1";
    function addRow ($array) {

        $cells = "";

        foreach ($array as $k => $v):

        	// 加个字符串与数字的判断 避免生成的 excel 出现数字以字符串存储的警告
        	if(is_numeric($v)) {
        		// 防止首字母为 0 时生成 excel 后 0 丢失
        		if(substr($v, 0, 1) == 0) {
        			$cells .= "<Cell><Data ss:Type=\"String\">" . $v . "</Data></Cell>\n";
        		} else {
        			$cells .= "<Cell><Data ss:Type=\"Number\">" . $v . "</Data></Cell>\n";
        		}
        	} else {
            	$cells .= "<Cell><Data ss:Type=\"String\">" . $v . "</Data></Cell>\n";
        	}

        endforeach;

        // transform $cells content into one row
        $this->lines[] = "<Row>\n" . $cells . "</Row>\n";

    }


    function addArray ($array) {

        // run through the array and add them into rows
        foreach ($array as $k => $v):
            $this->addRow ($v);
        endforeach;

    }

    function setWorksheetTitle ($title) {

        // strip out special chars first
        $title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);

        // now cut it to the allowed length
        $title = substr ($title, 0, 31);

        // set title
        $this->worksheet_title = $title;

    }


    function generateXML ($filename) {

        // deliver header (as recommended in php manual)
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo stripslashes ($this->header);
        echo "\n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n";
        echo "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
        echo implode ("\n", $this->lines);
        echo "</Table>\n</Worksheet>\n";
        echo $this->footer;

    }

}



?>
