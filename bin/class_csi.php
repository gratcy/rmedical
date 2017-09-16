<?php

if (!headers_sent()) {
	header( "Cache-Control: no-cache, must-revalidate" );
	header( "Pragma: no-cache" );
}


//////////////////////////////////////////////////////////////////////////////////
//                                Built by Solomon                              //
//////////////////////////////////////////////////////////////////////////////////

// Client-Server Interaction
class CSI {

	
	var	$id				= '';
	var $disable		= false;


	function CSI($id	= '') {
		
		if ($id == '')		$id	= session_id();
		$this->id	= $id;
		
	}
	
	
	function load($object, $link, $parameter = '') {
		
		list($url, $param)	= $this->split_url($link);
		
		if ($parameter != '' and $param != '')
			$parameter	.= "&" . $param;
		else
			$parameter	.= $param;
		
		echo "<script> CSI_load($object, '$url', '$parameter'); </script>";
		
	}
	
	function write_message($object, $content, $reload_time = 0, $reload_link = '') {
		
		$message	= $content;

		if ($reload_time != 0) {
			
			list($reload_url, $reload_param)	= $this->split_url($reload_link);
			
			$message	.= "
				<script>
					setTimeout(function () {CSI_load($object, \"$reload_url\", \"$reload_param\")}, $reload_time * 1000);
				</script>
							";
			
		}
		
		$fields		= array(
					'id'		=> $this->id,
					'object'	=> $object,
					'message'	=> addslashes($message),
					'date'		=> date('Y-m-d H:i:s')
							);
		
		$sql		= sql_update('csi_message', $fields, "id='$this->id' and object='$object'");
		
		$result		= sql_query($sql);
		
		if (!$result) {
			
			sql_query($this->db_table);
			
			$this->write_message($object, $content, $reload_time, $reload_link);
			
		}
			
		return;
		
	}
	
	function read_message($object) {
		
		return sql_getValue("select message from csi_message where id='$this->id' and object='$object' order by date desc limit 1");
		
	}
	
	function split_url($link) {
		
		if (contain($link, '?'))
			return explode('?', $link);
		else {
			return array($link, '');
		}
		
	}
	
	
	var $db_table		= "
			CREATE TABLE `csi_message` (
				`id` VARCHAR( 64 ) NOT NULL ,
				`object` VARCHAR( 50 ) NOT NULL ,
				`message` TEXT NOT NULL ,
				`date` DATETIME NOT NULL 
			)
							";


}



$javascript = <<<EOS

<script language="JavaScript">

var IE7	= false;
if (navigator.appName == "Microsoft Internet Explorer") {
	version = parseFloat(navigator.appVersion.split("MSIE")[1].split(";")[0]);
	IE7	= (version >= 7);
}


var AJAX_http_request	= false;		// Request object

var AJAX_Requests		= new Array();	// Request Queue

var AJAX_busy			= false;
var AJAX_cur_ojbect		= false;
var AJAX_cur_command	= false;
var AJAX_cur_url		= false;
var AJAX_cur_params		= false;
var AJAX_redo_command	= false;
var AJAX_result;

var AJAX_event_run		= null;
var AJAX_event_stop		= null;
var AJAX_event_copmlete	= null;

var AJAX_initialized	= false;

function AJAX_initialization() {
	AJAX_http_request	= false;
	if (window.XMLHttpRequest && !IE7) {
		AJAX_http_request = new XMLHttpRequest();
		if (AJAX_http_request.overrideMimeType) {
			AJAX_http_request.overrideMimeType('text/xml');
		}
	} else if (window.ActiveXObject) {
		ClassIDs = ["Msxml2.XMLHTTP.6.0","Msxml2.XMLHTTP.5.0","Msxml2.XMLHTTP.4.0","Msxml2.XMLHTTP.3.0","Msxml2.XMLHTTP.2.6","Microsoft.XMLHTTP.1.0","Microsoft.XMLHTTP.1","Microsoft.XMLHTTP"];
		i = 0;
		while (!AJAX_http_request) {
			try {
				AJAX_http_request = new ActiveXObject(ClassIDs[i]);
			} catch (e) {
				if (++i>=ClassIDs.length) {
					break;
				}
			}
		}
	}

	if (AJAX_http_request)
		AJAX_http_request.onreadystatechange = AJAX_response;
}

function AJAX_run(Command) {
	if (!AJAX_initialized)	AJAX_initialization();
	
	AJAX_busy				= true;
	AJAX_redo_command		= Command;
	
	AJAX_cur_object			= Command[0];
	if (AJAX_cur_object) {
//		AJAX_cur_object.style.display = 'none';
		if (document.getElementById(AJAX_cur_object.id + "_loading") != null)
			eval(AJAX_cur_object.id + "_loading.style.display = 'block';");
	}
	AJAX_cur_command		= Command[1];
	AJAX_cur_url			= Command[2];
	AJAX_cur_params			= Command[3];
	if (Command.length > 3)
		AJAX_cur_response		= Command[Command.length-1];
//	AJAX_http_request.clear_cache();
	if (Command.length > 4 && Command[4].toUpperCase()=='POST') {
		if (AJAX_cur_params == undefined)
			AJAX_cur_params	= "";
		AJAX_http_request.open('POST', AJAX_cur_url, true);
		AJAX_http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		AJAX_http_request.setRequestHeader("Content-length", AJAX_cur_params.length);
		AJAX_http_request.setRequestHeader("Connection", "close");
		AJAX_http_request.send(AJAX_cur_params);
	} else {
		url		= (AJAX_cur_params == null) ? AJAX_cur_url : (AJAX_cur_url + '?' + AJAX_cur_params);
		AJAX_http_request.open('GET', url, true);
		AJAX_http_request.setRequestHeader("If-Modified-Since","0");
		AJAX_http_request.send(null);
	}
}

function AJAX_response() {
	
	if (AJAX_http_request.readyState==4) {
		if (AJAX_http_request.status==200) { 		// Response okay

			if (AJAX_cur_response == 'XML') {
				AJAX_result = AJAX_http_request.responseXML;
			} else {
				AJAX_result = AJAX_http_request.responseText;
			}

		} else { //if (AJAX_http_request.status >= 400) {
			AJAX_result = "Error on loading data!";
			AJAX_run(AJAX_redo_command);
			return;
		}
		
		// Evaluate command first in order to 'writeObject' (result) to document
		eval(AJAX_cur_command);

		result_script	= AJAX_result.split("<" + "script>");
		for (var i=1; i<result_script.length; i++) {
			result_script_command = result_script[i].split("<" + "/script>");
			eval(result_script_command[0]);
		}
		

		if (AJAX_Requests.length != 0) {
			AJAX_run(AJAX_Requests.shift());
		} else {
			AJAX_busy = false;
		}
	}
}

function AJAX(objectID, Command, URL, Parameters) {
	AJAX_Requests.push(AJAX.arguments);
	if (!AJAX_busy) {
		AJAX_busy = true;
		AJAX_run(AJAX_Requests.shift());
	}
}

function AJAX_instant(objectID, Command, URL, Parameters) {
	AJAX_Requests.unshift(AJAX_instant.arguments);
	if (!AJAX_busy) {
		AJAX_busy = true;
		AJAX_run(AJAX_Requests.shift());
	}
}

function AJAX_stop() {
	AJAX_Requests	= new Array();
}

////////////////////////////////////////////
//	Standard load data to container
////////////////////////////////////////////
function CSI_load(objectID, URL, parameters, writeMethod) {
	AJAX(objectID, "CSI_load_complete(" + objectID.id + ", AJAX_result, '" + writeMethod + "');", URL, parameters);
}

function CSI_load_instant(objectID, URL, parameters) {
	AJAX_instant(objectID, "CSI_load_complete(" + objectID.id + ", AJAX_result, '" + writeMethod + "');", URL, parameters);
}

function CSI_load_complete(objectID, result, writeMethod) {
	if (objectID != null) {
		if (writeMethod == 'append')
			appendObject(objectID, result);
		else
			writeObject(objectID, result);
		objectID.style.display = 'block';
		if (document.getElementById(objectID.id + "_loading") != null)
			eval(objectID.id + "_loading.style.display = 'none';");
	}
}

function CSI_submit(URL, parameters) {
	AJAX(null, "", URL, parameters, 'POST');
}

function CSI_submit_load(objectID, URL, parameters) {
	AJAX(objectID, "CSI_load_complete(" + objectID.id + ", AJAX_result);", URL, parameters, 'POST');
}


////////////////////////////////////////////
//	Progressively load data to container
////////////////////////////////////////////
function CSI_load_progressive(objectID, URL, parameters) {
	
	
	
}

function CSI_load_progressive_complete(objectID, URL, parameters) {



}

////////////////////////////////////////////
//	Connection control
////////////////////////////////////////////
function CSI_stop() {
	AJAX_stop();
}



function writeObject(ObjectID, HTML_Code) {
	ObjectID.innerHTML = unescape(HTML_Code);
}

function appendObject(ObjectID, HTML_Code) {
	ObjectID.innerHTML += unescape(HTML_Code);
}



</script>


EOS;

if (!$CSI_JAVASCRIPT_DISABLE) {
	$orig			= array("AJAX"	, " + "	, " = "	, ", "	, "\n}"	, "\n\n", "\r"	, "\t"	, "////", "  "	);
	$replace		= array("aj"	, "+"	, "="	, ","	, "}"	, "\n"	, ""	, ""	, ""	, ""	);
	echo str_replace($orig, $replace, $javascript);
}

?>
