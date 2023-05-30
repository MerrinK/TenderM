<?php
require_once('config.php');
require_once('phpMailer/PHPMailerAutoload.php');
require_once("classes/CommonFunction.php");

define('SITE_NAME','devengineers.com');
define('SITE_USER','purchase@devengineers.com');
define('SITE_PASS','gvBL3_wxV');
define('SITE_PORT','2525');
define('SITE_HEADER_TITLE','Devengineers');

$host = SITE_NAME;
$from = SITE_USER;
$password = SITE_PASS;
$port = SITE_PORT;

$subject ="Test Subject";

$body = " my text";

// $to = "richard@florix.net";
$to = "vijimonvkattela@gmail.com";


$Common=new CommonFunction($this->dbc);
$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);


?>

ALTER TABLE `tenders` CHANGE `SiteSupervisor` `SiteSupervisor` TEXT NOT NULL;
ALTER TABLE `expenses` ADD `site_supervisor` INT NOT NULL AFTER `tender_id`;