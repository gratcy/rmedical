<?php

$DB_SERVER		= "localhost";
$DB_DATABASE	= "rockmedi_db"; /*rock_medical_com_2*/
$DB_USERNAME	= "root"; /*rock-medical.com*/
$DB_PASSWORD	= "";

$DB_ENCRYPT_KEY = "lsfchawp9fhnaw4on";

$MAILTO_WEBMASTER	= "Rock Medical <rock.service@yahoo.com.hk>"; /*"Rock Medical <info@rock-medical.com>";*/

$NOTIFY_EMAIL		= "rock.service@yahoo.com.hk"; /* "info@rock-medical.com"; */

$smtp['host']           = "smtp.mailgun.org";
$smtp['username']               = "basmi@rockhkmedical.com";
$smtp['password']               = "444ACVwW";
$smtp['port']           = 587;

$root['root'] = array(6);

if ($_SERVER['HTTP_HOST'] != parse_url($DOMAIN)['host']) {
        // header('location: ' . $DOMAIN);
}



