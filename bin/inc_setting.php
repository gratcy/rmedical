<?php
error_reporting(0);
$DB_SERVER		= "localhost";
$DB_DATABASE	= "rockmedi2_db"; /*rock_medical_com_2*/
$DB_USERNAME	= "dev"; /*rock-medical.com*/
$DB_PASSWORD	= "palma";

$DB_ENCRYPT_KEY = "lsfchawp9fhnaw4on";

$MAILTO_WEBMASTER	= "Rock Medical <rock.service@yahoo.com.hk>"; /*"Rock Medical <info@rock-medical.com>";*/

$NOTIFY_EMAIL		= "rock.service@yahoo.com.hk"; /* "info@rock-medical.com"; */

$root['root'] = array(6);
$DOMAIN = 'localhost';
if ($_SERVER['HTTP_HOST'] != parse_url($DOMAIN)['host']) {
        // header('location: ' . $DOMAIN);
}



