<?php

require_once "inc_setting.php";

/*	網頁名稱			*/		$SiteName       = 'SearchWide - Administration Platform';

/*	MySQL 伺服器		*/		$MySQL_Server   = $DB_SERVER;
/*	登入名稱			*/		$MySQL_LoginID  = $DB_USERNAME;
/*	密碼				*/		$MySQL_Password = $DB_PASSWORD;
/*	數據庫名稱			*/		$MySQL_Database = $DB_DATABASE;

/*	網頁緩衝儲存		*/		$Cache          = 'Default';	// { Default, No Cache, 秒數 (如：60, 300) }

/*	SMTP 伺服器		*/	//	$SMTP_Server    = '';
/*	登入名稱			*/	//	$SMTP_LoginID   = '';
/*	密碼				*/	//	$SMTP_Password  = '';

/*	電子郵件地址		*/		$eMail_Address   = 'admin@searchwide.com.hk';

/*	詳細瀏覽記錄		*/		$BrowseRecord 	= 3;			// 如：2 (最少保留2個月的詳細瀏覽記錄)


?>