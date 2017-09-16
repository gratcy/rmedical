<?php


function discuzcode($message, $smileyoff, $bbcodeoff, $htmlon = 0, $allowsmilies = 1, $allowbbcode = 1, $allowimgcode = 1, $allowhtml = 0, $jammer = 0) {
	global $discuzcodes, $credits, $tid, $discuz_uid, $highlight, $maxsmilies, $db, $tablepre;

	if(!$bbcodeoff && $allowbbcode) {
		$message = preg_replace("/\s*\[code\](.+?)\[\/code\]\s*/ies", "codedisp('\\1')", $message);
	}

	if(!$htmlon && !$allowhtml) {
		$message = $jammer ? preg_replace("/\r\n|\n|\r/e", "jammer()", dhtmlspecialchars($message)) : dhtmlspecialchars($message);
	}

	if(!$smileyoff && $allowsmilies && !empty($GLOBALS['_DCACHE']['smilies']) && is_array($GLOBALS['_DCACHE']['smilies'])) {
		$message = preg_replace($GLOBALS['_DCACHE']['smilies']['searcharray'], $GLOBALS['_DCACHE']['smilies']['replacearray'], $message, $maxsmilies);
	}

	if(!$bbcodeoff && $allowbbcode) {

		if(empty($discuzcodes['searcharray'])) {
			$discuzcodes['searcharray']['bbcode_regexp'] = array(
				"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
				"/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is",
				"/\[url\]\s*(www.|https?:\/\/|ftp:\/\/|gopher:\/\/|news:\/\/|telnet:\/\/|rtsp:\/\/|mms:\/\/|callto:\/\/|ed2k:\/\/){1}([^\[\"']+?)\s*\[\/url\]/ie",
				"/\[url=www.([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/is",
				"/\[email\]\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*\[\/email\]/i",
				"/\[email=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\](.+?)\[\/email\]/is",
				"/\[color=([^\[\<]+?)\]/i",
				"/\[size=([^\[\<]+?)\]/i",
				"/\[font=([^\[\<]+?)\]/i",
				"/\[align=([^\[\<]+?)\]/i"
			);
			$discuzcodes['replacearray']['bbcode_regexp'] = array(
				"<br><br><div class=\"smalltxt\" style=\"margin-left: 2em; font-weight: bold\">QUOTE:</div><div class=\"altbg2\" style=\"margin: 2em; margin-top: 3px; padding: 10px; border: ".INNERBORDERWIDTH."px solid ".BORDERCOLOR."; word-break: break-all\">\\1</div>",
				"<br><br><div class=\"smalltxt\" style=\"margin-left: 2em; font-weight: bold\">FREE:</div><div class=\"altbg2\" style=\"margin: 2em; margin-top: 3px; padding: 10px; border: ".INNERBORDERWIDTH."px solid ".BORDERCOLOR."; word-break: break-all\">\\1</div>",
				"cuturl('\\1\\2')",
				"<a href=\"http://www.\\1\" target=\"_blank\">\\2</a>",
				"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>",
				"<a href=\"mailto:\\1@\\2\">\\1@\\2</a>",
				"<a href=\"mailto:\\1@\\2\">\\3</a>",
				"<font color=\"\\1\">",
				"<font size=\"\\1\">",
				"<font face=\"\\1\">",
				"<p align=\"\\1\">"
			);

			$discuzcodes['searcharray']['bbcode_regexp'] = array_merge($discuzcodes['searcharray']['bbcode_regexp'], $discuzcodes['searcharray']['bbcode_regexp']);
			$discuzcodes['replacearray']['bbcode_regexp'] = array_merge($discuzcodes['replacearray']['bbcode_regexp'], $discuzcodes['replacearray']['bbcode_regexp']);

			$discuzcodes['searcharray']['bbcode_regexp'][] = "/\[payto\]\s*\(seller\)(.*)\(\/seller\)\s*(\(subject\)(.*)\(\/subject\))?\s*(\(body\)(.*)\(\/body\))?\s*(\(gross\)(.*)\(\/gross\))?\s*(\(price\)(.*)\(\/price\))?\s*(\(url\)(.*)\(\/url\))?\s*(\(type\)(.*)\(\/type\))?\s*(\(transport\)(.*)\(\/transport\))?\s*(\(ordinary_fee\)(.*)\(\/ordinary_fee\))?\s*(\(express_fee\)(.*)\(\/express_fee\))?\s*\[\/payto\]/iesU";
			$discuzcodes['replacearray']['bbcode_regexp'][] = "payto('\\1',array('subject'=>'\\3','body'=>'\\5','price'=>'\\7','price'=>'\\9','url'=>'\\11','type'=>'\\13','transport'=>'\\15','ordinary_fee'=>'\\17','express_fee'=>'\\19'))";

			$discuzcodes['searcharray']['bbcode_str'] = array(
				'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]',
				'[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
				'[list=A]', '[*]', '[/list]'
			);

			$discuzcodes['replacearray']['bbcode_str'] = array(
				'</font>', '</font>', '</font>', '</p>', '<b>', '</b>', '<i>',
				'</i>', '<u>', '</u>', '<ul>', '<ol type=1>', '<ol type=a>',
				'<ol type=A>', '<li>', '</ul></ol>'
			);
		}                

		@$message = str_replace($discuzcodes['searcharray']['bbcode_str'], $discuzcodes['replacearray']['bbcode_str'],
				preg_replace(
					($allowbbcode == 2 && $GLOBALS['_DCACHE']['bbcodes'] ? array_merge($discuzcodes['searcharray']['bbcode_regexp'], $GLOBALS['_DCACHE']['bbcodes']['searcharray']) : $discuzcodes['searcharray']['bbcode_regexp']),
					($allowbbcode == 2 && $GLOBALS['_DCACHE']['bbcodes'] ? array_merge($discuzcodes['replacearray']['bbcode_regexp'], $GLOBALS['_DCACHE']['bbcodes']['replacearray']) : $discuzcodes['replacearray']['bbcode_regexp']),
					$message));

		if(preg_match("/\[hide=?\d*\].+?\[\/hide\]/is", $message)) {
			if(stristr($message, '[hide]')) {
				global $language;
				include_once language('misc');

				$query = $db->query("SELECT pid FROM {$tablepre}posts WHERE tid='$tid' AND authorid='$discuz_uid' LIMIT 1");
				if($GLOBALS['forum']['ismoderator'] || $db->result($query, 0)) {
					$message = preg_replace("/\[hide\]\s*(.+?)\s*\[\/hide\]/is",
						'<span class="bold">'.$language['post_hide_reply'].'</span><br>'.
						'==============================<br><br>'.
						'\\1<br><br>'.
						'==============================',
						$message);
				} else {
					$message = preg_replace("/\[hide\](.+?)\[\/hide\]/is", '<b>'.$language['post_hide_reply_hidden'].'</b>', $message);
				}
			}
			$message = preg_replace("/\[hide=(\d+)\]\s*(.+?)\s*\[\/hide\]/ies", "creditshide(\\1,'\\2')", $message);
		}
	}

	if(!$bbcodeoff && $allowimgcode) {
		if(empty($discuzcodes['searcharray']['imgcode'])) {
			$discuzcodes['searcharray']['imgcode'] = array(
				"/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies",
				"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
				"/\[img=(\d{1,3})[x|\,](\d{1,3})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
			);
			$discuzcodes['replacearray']['imgcode'] = array(
				"bbcodeurl('\\1', ' <img src=\"images/attachicons/flash.gif\" align=\"absmiddle\"> <a href=\"%s\" target=\"_blank\">Flash: %s</a> ')",
				"bbcodeurl('\\1', '<img src=\"%s\" border=\"0\" onload=\"if(this.width>screen.width*0.7) {this.resized=true; this.width=screen.width*0.7; this.alt=\'Click here to open new window\\nCTRL+Mouse wheel to zoom in/out\';}\" onmouseover=\"if(this.width>screen.width*0.7) {this.resized=true; this.width=screen.width*0.7; this.style.cursor=\'hand\'; this.alt=\'Click here to open new window\\nCTRL+Mouse wheel to zoom in/out\';}\" onclick=\"if(!this.resized) {return true;} else {window.open(\'%s\');}\" onmousewheel=\"return imgzoom(this);\">')",
				"bbcodeurl('\\3', '<img width=\"\\1\" height=\"\\2\" src=\"%s\" border=\"0\">')"
			);
		}
		$message = preg_replace($discuzcodes['searcharray']['imgcode'], $discuzcodes['replacearray']['imgcode'], $message);
	}

	for($i = 0; $i <= $discuzcodes['pcodecount']; $i++) {
		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $discuzcodes['codehtml'][$i], $message);
	}

	if($highlight) {
		foreach(explode('+', $highlight) as $ret) {
			if($ret) {
				$message = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($ret, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", $message);
			}
		}
	}

	return $htmlon || $allowhtml ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}


?>