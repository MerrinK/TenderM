<?php

require_once("config.php");
require_once("classes/Employees.php");//userClass


// require_once("classes/addClasses.php");
require_once('TCPDF/tcpdf.php');
// require_once("classes/CommonFunction.php");
require_once("classes/Vendors.php");
require_once("classes/Tender.php");
require_once("classes/BOQ.php");

require_once("classes/Inventory.php");
require_once("classes/TenderDetails.php");


$function=$_POST['function'];
$method=$_POST['method'];


$fn = new $function($dbc);
$fn->$method($dbc);



function ajaxResponse($status,$data){
	$resp = array();
	$resp["status"]=$status;
	$resp["data"]=$data;
	echo json_encode($resp);
	die();
}	

?>
