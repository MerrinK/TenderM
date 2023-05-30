<?php
require_once("config.php");
require_once('TCPDF/tcpdf.php');
require_once 'classes/BOQ.php';

$boq = new BOQ($dbc);

$boq->generatePDF($dbc);