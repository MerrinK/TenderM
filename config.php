<?php

// session_start();

require_once("classes/DBC.php");

// require_once("classes/DBConnection.php");

// require_once("lib/user_class.php");

// require_once("classes/CommonFunctions.php");



// echo url();

// die('Here');





date_default_timezone_set ("Asia/Calcutta");

$today		=	Date('Y-m-j');

$nowM		=	Date('m');

$nowD		=	Date('d');

$nowY		=	Date('Y');

$xdays		=	10;

// $GLOBALS['today']=$today;




 define('DB_HOST', "localhost");
 define('DB_USER','root');
 define('DB_PASS','');
 define('DB_NAME','tenderm');



// define('SITE_NAME','smtp.gmail.com');
// define('SITE_USER','ttender487@gmail.com');
// define('SITE_PASS','passme2022');
// define('SITE_PORT','587');
// define('SITE_HEADER_TITLE','Dev Engineers');



define('SITE_NAME','devengineers.com');
define('SITE_USER','purchase@devengineers.com');
define('SITE_PASS','gvBL3_wxV');
define('SITE_PORT','25');
define('SITE_HEADER_TITLE','Devengineers');


session_start();



$dbc = new DBC();

// $dbc = new DBConnection();





function url(){

    if(isset($_SERVER['HTTPS'])){

        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";

    }

    else{

        $protocol = 'http';

    }

    return $protocol . "://" . $_SERVER['HTTP_HOST'].'/tenderm/';

}

$url=url();
// die($url);
define('SITE_HEADER_LOGO',$url.'/assets/img/logo.png');



//die($_SERVER['DOCUMENT_ROOT']);

?>

