<?php
include "config.php";

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = "./request.log";

$opData = new stdClass();


$db = $data = array();


$data = json_decode(file_get_contents('php://input'), true);


$req_dump = print_r($data, true);
$fp = file_put_contents('./request.log', $req_dump, FILE_APPEND);

 
$path = "./uploaddoc";

if(isset($data['action']) &&  $data['action'] != ''){

    $action = $dbc->real_escape_string(trim($data['action']));

    // Upload Challans
    if($action == "listChallan") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';



        $userid = $dbc->real_escape_string(trim($data['userid']));        $userid = (is_numeric($userid) ? (int)$userid : 0);
      
        if($userid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "User ID is missing!!!";            print json_encode($opData);            exit;        }

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Tendor ID is missing !!!";            print json_encode($opData);            exit;        }



        $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_challan` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor_id=vendors.id order by tc.created_date desc limit 15";

		$result = $dbc->get_result($sql);

		if(count($result)) {
		    $opData->status     =   "success";
		    $opData->challans = $result;
        
        }else{
            $opData->status     =   "fail";
            $opData->challans = array();
        }

        print json_encode($opData);
        die();
    
    }

    if($action == "listLabor") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';


        
        $userid = $dbc->real_escape_string(trim($data['userid']));        $userid = (is_numeric($userid) ? (int)$userid : 0);
      
        if($userid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "User ID is missing!!!";            print json_encode($opData);            exit;        }

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Tendor ID is missing !!!";            print json_encode($opData);            exit;        }



        $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_labour_bills` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor=vendors.id order by tc.created_date desc limit 15";

		$result = $dbc->get_result($sql);

	    if(count($result)) {
            $opData->status     =   "success";
	        $opData->labor = $result;
	    }
        else{
            $opData->status     =   "fail";
            $opData->labor = array();
        }
    	print json_encode($opData);
        die();
    }

    if($action == "listProgress") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';


        
        $userid = $dbc->real_escape_string(trim($data['userid']));        $userid = (is_numeric($userid) ? (int)$userid : 0);
      
        if($userid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "User ID is missing!!!";            print json_encode($opData);            exit;        }

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Tendor ID is missing !!!";            print json_encode($opData);            exit;        }



        $sql = "SELECT pg.*, tenders.TenderName, 12 as images FROM `progress` as pg, tenders where pg.tender_id=$tenderid and pg.created_by=$userid and pg.deleted=0 and pg.tender_id=tenders.id order by pg.created_date desc limit 15";

        $result = $dbc->get_result($sql);

        
        if(count($result)) {
            
            for($i=0; $i< count($result); $i++){
                $progress_id = $result[$i]['id'];
                $sql= "SELECT id, image FROM progress_image WHERE deleted=0 AND progress_id =$progress_id";
                $result2 = $dbc->get_result($sql);               
                $result[$i]['images'] = $result2;
            }
        }

        
	    if(count($result)) {
		    $opData->progress = $result;
		    $opData->status     =   "success";
	    }
        else{
            $opData->progress = array();
            $opData->status     =   "fail";
        }

	    print json_encode($opData);
        die();
    }

    if($action == "listExpense") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';


        
        $userid = $dbc->real_escape_string(trim($data['userid']));        $userid = (is_numeric($userid) ? (int)$userid : 0);
      
        if($userid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "User ID is missing!!!";            print json_encode($opData);            exit;        }

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Tendor ID is missing !!!";            print json_encode($opData);            exit;        }

        
        $sql = "SELECT tc.*, tenders.TenderName, expense_type.type FROM `expenses` as tc, tenders, expense_type where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.expense_type=expense_type.id order by tc.created_date desc limit 15";
		        
		$result = $dbc->get_result($sql);

		$fp = file_put_contents('./request.log', $sql, FILE_APPEND);

	    if(count($result)) {
            $opData->status     =   "success";
	        $opData->expense = $result;
	    }
        else{
            $opData->status     =   "fail";
            $opData->expense = array();
        }
    	print json_encode($opData);
        die();
    }



    if($action == "listBOQ") {

        $userid = $tenderid = '';
        $userid = $dbc->real_escape_string(trim($data['userid']));        
        $userid = (is_numeric($userid) ? (int)$userid : 0);
      
        if($userid <= 0) {
            $opData->status = "fail";            
            $opData->message = "User ID is missing!!!";            
            print json_encode($opData);            
            exit;
        }

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));
        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);
        if($tenderid <= 0) {
            $opData->status = "fail";
            $opData->message = "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;
        }

        
        $sql = "SELECT a.tender_id, a.id as boq_id, a.item,a.total_qty,a.rate,a.unit FROM tender_boq_excel a INNER JOIN tenders b ON b.id= a.tender_id WHERE a.deleted=0 AND a.tender_id=".$tenderid;
                
        $result = $dbc->get_result($sql);

       
        if(count($result)) {
            $opData->status     =   "success";
            $opData->boq = $result;
        }
        else{
            $opData->status     =   "fail";
            $opData->boq = array();
        }
        print json_encode($opData);
        die();
    }

    if($action == "PO_Request") {
        
        $boq_id = $dbc->real_escape_string(trim($data['boq_id']));
        $boq_id = (is_numeric($boq_id) ? (int)$boq_id : 0);
        if($boq_id <= 0) {
            $opData->status = "fail";
            $opData->message = "BOQ id is missing !!!";
            print json_encode($opData);
            exit;
        }

        $sql = "SELECT mt.id AS mt_id ,mt.name AS mt_name, mst.id AS mst_id, mst.name AS mst_name FROM material_type mt LEFT JOIN material_sub_type mst ON mst.material_type_id=mt.id ORDER BY mt.name ASC , mst.name ASC";
                
        $result = $dbc->get_result($sql);
      
        $materialType = [];
        foreach ($result as $row) {
            if (!isset($materialType[$row['mt_id']])) {
                $materialType[$row['mt_id']] = [
                    'mt_id' => $row['mt_id'],
                    'mt_name' => $row['mt_name'],
                    'sub' => []
                ];
            }
            $materialType[$row['mt_id']]['sub'][] = [
                'mst_id' => $row['mst_id'],
                'mst_name' => $row['mst_name']
            ];
        }
        $materialType = array_values($materialType);

        $sql2 = "SELECT * FROM units";
        $units = $dbc->get_result($sql2);

        if(count($result)) {
            $opData->status     =   "success";
            $opData->boq = $boq_id;
            $opData->units = $units;
            $opData->materialType = $materialType;
        }else{
            $opData->status     =   "fail";
            $opData->boq = '';
            $opData->materialType = array();
            $opData->units = array();

        }
        print json_encode($opData);
        die();
    }





}
else{
    $opData->status     =   "fail";
    $opData->message     =   "Action details missing !!!";
}

print json_encode($opData);

function isDate($string) {
    $matches = array();
    $pattern = '/^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})$/';
    if (!preg_match($pattern, $string, $matches)) return false;
    if (!checkdate($matches[2], $matches[1], $matches[3])) return false;
    return true;
}


function struuid($entropy){
    $s=uniqid("",$entropy);
    $num= hexdec(str_replace(".","",(string)$s));
    $index = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base= strlen($index);
    $out = '';
        for($t = floor(log10($num) / log10($base)); $t >= 0; $t--) {
            $a = floor($num / pow($base,$t));
            $out = $out.substr($index,$a,1);
            $num = $num-($a*pow($base,$t));
        }
    return $out;
}

?>
