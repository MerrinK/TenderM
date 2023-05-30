<?php
require_once("classes/DBC.php");

date_default_timezone_set ("Asia/Calcutta");
$today		=	Date('Y-m-j');
$nowM		=	Date('m');
$nowD		=	Date('d');
$nowY		=	Date('Y');
$xdays		=	10;
// $GLOBALS['today']=$today;

define('DB_HOST', "localhost");

// define('DB_USER','rootFS');
// define('DB_PASS','ais2012');
// define('DB_NAME','newproject');

// Local Merrin DB Config
// define('DB_USER','rootFS');
// define('DB_PASS','ais2012');
// define('DB_NAME','newproject');

// Local Mahesh DB Config
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','tender');

// Server DB Config
// define('DB_USER','c61devengineers');
// define('DB_PASS','KZbEha_5wW5');
// define('DB_NAME','c61tenderM');

// define('SITE_NAME','smtp.gmail.com');
// define('SITE_USER','ttender487@gmail.com');
// define('SITE_PASS','passme2022');
// define('SITE_PORT','587');
// define('SITE_HEADER_TITLE','Devengineers');

// define('SITE_NAME','devengineers.com');
// define('SITE_USER','purchase@devengineers.com');
// define('SITE_PASS','gvBL3_wxV');
// define('SITE_PORT','2525');
// define('SITE_HEADER_TITLE','Devengineers');


define('SITE_NAME','devengineers.com');
define('SITE_USER','purchase@devengineers.com');
define('SITE_PASS','gvBL3_wxV');
define('SITE_PORT','2525');
define('SITE_HEADER_TITLE','Devengineers');

session_start();

$dbc = new DBC();

function url(){
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'].'/tender/';
}

$url=url();

define('SITE_HEADER_LOGO',$url.'/assets/img/logo.png');
// die(SITE_HEADER_LOGO);


?>
