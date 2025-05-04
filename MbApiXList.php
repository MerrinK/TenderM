<?php
include "config.php";
require_once("classes/CommonFunction.php");

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

    if ($action == "getRequest") {

        $userid = $roleid = $tenderid = 0;

        $userid = isset($data['userid']) ? (int)trim($data['userid']) : 0;
        $roleid = isset($data['roleid']) ? (int)trim($data['roleid']) : 0;
        $tenderid = isset($data['tenderid']) ? (int)trim($data['tenderid']) : 0;

        $Whr_role = '';
        if ($roleid > 1) {
            $Whr_role = ' AND r.created_by = ' . $userid;
        }

        $Whr_tender = '';
        if ($tenderid > 0) {
            $Whr_tender = ' AND r.tender_id = ' . $tenderid;

        }


        $sql = "SELECT r.id, r.tender_id, r.boq_id, r.required_by, t.TenderName, b.item AS boq, v.company_name AS vendor, CONCAT(u.first_name, ' ', u.last_name ) AS requested_by, r.approved,

                (
                    SELECT GROUP_CONCAT(mt.name SEPARATOR ', ')
                    FROM po_request_materials prm 
                    INNER JOIN material_type mt ON mt.id= prm.material_type_id
                    WHERE prm.po_request_id = r.id
                ) AS RequestMaterial,
    
                (
                    SELECT GROUP_CONCAT(mst.name SEPARATOR ', ')
                    FROM po_request_materials prm 
                    INNER JOIN material_sub_type mst ON mst.id= prm.material_sub_type_id
                    
                    WHERE prm.po_request_id = r.id
                ) AS RequestSubMaterial,
                (
                    SELECT GROUP_CONCAT(prm.quantity_requested SEPARATOR ', ')
                    FROM po_request_materials prm 
                    WHERE prm.po_request_id = r.id
                ) AS RequestQuantity,
                
                 (
                    SELECT GROUP_CONCAT(prm.unit_name SEPARATOR ', ')
                    FROM po_request_materials prm 
                    WHERE prm.po_request_id = r.id
                ) AS RequestMaterialUnitName,
                
                
                (
                    SELECT GROUP_CONCAT(prma.material_type SEPARATOR ', ')
                    FROM po_request_materials_additional prma 
                    WHERE prma.po_request_id = r.id
                ) AS RequestCustomMaterial,
                
                (
                  SELECT GROUP_CONCAT(prma.material_sub_type SEPARATOR ', ')
                    FROM po_request_materials_additional prma 
                    WHERE prma.po_request_id = r.id
                ) AS RequestCustomSubMaterial,
                (
                    SELECT GROUP_CONCAT(prma.quantity_requested SEPARATOR ', ')
                    FROM po_request_materials_additional prma 
                    WHERE prma.po_request_id = r.id
                ) AS RequestCustomQuantity,
                
                (
                    SELECT GROUP_CONCAT(prma.unit_name SEPARATOR ', ')
                    FROM po_request_materials_additional prma 
                    WHERE prma.po_request_id = r.id
                ) AS RequestCustomMaterialUnitName
                
                FROM po_request r 
                INNER JOIN tenders t ON t.id = r.tender_id
                INNER JOIN tender_boq_excel b ON b.id = r.boq_id 
                INNER JOIN vendors v ON v.id = r.vendor
                INNER JOIN users u ON u.id = r.created_by

                WHERE r.received = 0 " . $Whr_role . $Whr_tender . " 
                ORDER BY r.id DESC 
                LIMIT 20";

        $result = $dbc->get_result($sql);

        if (count($result)) {
            $opData->status = "success";
            $opData->requests = $result;
        } else {
            $opData->status = "fail";
            $opData->requests = array();
        }

        echo json_encode($opData);
        exit;
    }




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
        
        // $fp = file_put_contents('./request.log', $sql, FILE_APPEND);


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

        $userid = $tenderid =  '';


        
        $userid = $dbc->real_escape_string(trim($data['userid']));        $userid = (is_numeric($userid) ? (int)$userid : 0);
      
        if($userid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "User ID is missing!!!";            print json_encode($opData);            exit;        }

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Tendor ID is missing !!!";            print json_encode($opData);            exit;        }



        // $sql= "SELECT id, date,description FROM progress WHERE tender_id=$tenderid AND created_by=$userid and deleted=0 ORDER BY id DESC LIMIT 15";
        $sql="SELECT p.id, p.date,p.description, (SELECT GROUP_CONCAT(i.image) FROM progress_image i WHERE p.id=i.progress_id) AS images FROM progress p WHERE p.tender_id=$tenderid AND p.created_by=$userid and p.deleted=0 ORDER BY p.id DESC LIMIT 15";
// print json_encode($sql);
        $result = $dbc->get_result($sql);


        
        if(count($result)) {
            $opData->Progress = $result;
            $opData->status     =   "success";
        }
        else{
            $opData->Progress = array();
            $opData->status     =   "fail";
        }

        print json_encode($opData);
        die();
    }


    if($action == "ListProgressImage") {
        $user_id = $tender_id = $progress_id ='';


        
        $user_id = $dbc->real_escape_string(trim($data['user_id']));        $user_id = (is_numeric($user_id) ? (int)$user_id : 0);
      
        if($user_id <= 0) {            $opData->status     =   "fail";            $opData->message     =   "User ID is missing!!!";            print json_encode($opData);            exit;        }

        $tender_id = $dbc->real_escape_string(trim($data['tender_id']));        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);

        if($tender_id <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Tendor ID is missing !!!";            print json_encode($opData);            exit;        }

        $progress_id = $dbc->real_escape_string(trim($data['progress_id']));        $progress_id = (is_numeric($progress_id) ? (int)$progress_id : 0);

        if($progress_id <= 0) {            $opData->status     =   "fail";            $opData->message     =   "Progress ID is missing !!!";            print json_encode($opData);            exit;        }


        $sql= "SELECT id, image FROM progress_image WHERE deleted=0 AND progress_id =$progress_id";
        $result = $dbc->get_result($sql);    


        

        
        if(count($result)) {
            $opData->ProgressImage = $result;
            $opData->status     =   "success";
        }
        else{
            $opData->ProgressImage = array();
            $opData->status     =   "fail";
        }

        print json_encode($opData);
        die();
    }



    if($action == "listExpense") {

        $userid = $tenderid =   '';


        
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
        
        $tender_id = $dbc->real_escape_string(trim($data['tender_id']));
        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);
        if($tender_id <= 0) {
            $opData->status = "fail";
            $opData->message = "BOQ id is missing !!!";
            print json_encode($opData);
            exit;
        }

        $sql = "SELECT a.id, a.item,a.total_qty,a.rate,a.unit,

            ((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS RequestedQuantity,
            ((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ConfirmedQuantity,
            ((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ReceivedQuantity,

            ((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id))) AS PendingQuantity

            FROM tender_boq_excel a
                INNER JOIN tenders b ON b.id= a.tender_id 
                WHERE a.deleted=0 AND a.tender_id=".$tender_id ;
                
        $result = $dbc->get_result($sql);

        $BOQ=[];
        $ind=-1;
        foreach ($result as $row) {
            $ind++;
            $boq_id= $row['id'];
            $row['remaining_qty']=$row['total_qty']-$row['ReceivedQuantity'];
            if($row['remaining_qty']<=0){
                $ind--;
                continue;
            }
            $BOQ[$ind]=$row;
        }
     

    

        $sql1 = "SELECT * FROM material_type ORDER BY order_by";
        $materialType = $dbc->get_result($sql1);

        $sql1_1 = "SELECT * FROM material_sub_type ";
        $material_sub_type = $dbc->get_result($sql1_1);


        // $sql2 = "SELECT id as key, unit as value FROM units";
        $sql2 = "SELECT * FROM units";
        $units = $dbc->get_result($sql2);

        if(count($materialType)) {
            $opData->status     =   "success";
            $opData->boq = $BOQ;
            $opData->units = $units;
            $opData->materialType = $materialType;
            $opData->material_sub_type = $material_sub_type;
        }else{
            $opData->status     =   "fail";
            $opData->boq = '';
            $opData->materialType = array();
            $opData->units = array();

        }
        print json_encode($opData);
        die();
    }


    if($action == "SavePORequest_Mobile") {
// $fp = file_put_contents('./request.log', $data['Required_by'], FILE_APPEND);
// die();
        $Request = json_decode($data['Inputs'], true);

        $Required_by=$Request['date'];
        $tender_id=$Request['tender_id'];
        $boq_id=$Request['boq_id'];

        $User_id=$Request['user_id'];
        $ROLE_ID=$Request['role_id'];
        $Today=date("Y-m-d");
        $DateTime=date("Y-m-d H:i:s");

        $data=array();
        $data['boq_id']=$Request['boq_id'];
        $data['tender_id']=$Request['tender_id'];
        $data['required_by']=date("Y-m-d", strtotime($Request['date']));
        $data['request_note']=preg_replace('/[\x00-\x1F\x7F]/u', '', $Request['request_note']);
        $data['created_by']=$User_id;
        $data['created_date']=$DateTime;

        if($ROLE_ID==2){
            $data['approved']=1;
        }
            
        // $sql="SELECT * FROM po_request";
        // $result1=$dbc->get_array($sql);
        // print_r($_REQUEST);die;
        try {
        $dbc->insert_query($data,"po_request");
        
        $Insert_Id=$dbc->insert_id;

// file_put_contents('./request.log', print_r($Request['MaterialsOrdered'] , true), FILE_APPEND);

        if(isset($Request['MaterialsOrdered'])){
            $MaterialsOrdered=$Request['MaterialsOrdered'];
            for ($i=1; $i<=$MaterialsOrdered; $i++) { 
                if(isset($Request['material_type_'.$i])){

                // print_r("D");
                    $material_type=$Request['material_type_'.$i];
                    $sub_type=$Request['sub_material_type_'.$i];
                    $Quantity=$Request['Quantity_'.$i];
                    $unit_id=$Request['unit_type_'.$i];
                    $unit_name=$Request['unit_name_'.$i];

                    $met_data=array();
                    $met_data['po_request_id']=$Insert_Id;
                    $met_data['boq_id']=$boq_id;
                    $met_data['tender_id']=$tender_id;
                    $met_data['material_type_id']=$material_type;
                    $met_data['material_sub_type_id']=$sub_type;    
                    $met_data['quantity_requested']=$Quantity;  
                    $met_data['unit_id']=$unit_id;  
                    $met_data['unit_name']=$unit_name;  

                    $dbc->insert_query($met_data,"po_request_materials");

                }
            }
        } 

        if(isset($Request['AdditionalMaterialsOrdered'])){
            $AdditionalMaterialsOrdered=$Request['AdditionalMaterialsOrdered'];

            for ($j=1; $j<=$AdditionalMaterialsOrdered; $j++) { 
                // echo "asdfa";
                if(isset($Request['additional_material_type_'.$j])){

                    $material_type=$Request['additional_material_type_'.$j];
                    $sub_type=$Request['additional_sub_material_type_'.$j];
                    $Quantity=$Request['additional_Quantity_'.$j];
                    // echo ("material_type : ".$Quantity ."<br>");
                    $unit_id=$Request['additional_unit_type_'.$j];
                    $unit_name=$Request['additional_unit_name_'.$j];

                    $add_met_data=array();
                    $add_met_data['po_request_id']=$Insert_Id;
                    $add_met_data['boq_id']=$boq_id;
                    $add_met_data['tender_id']=$tender_id;
                    $add_met_data['material_type']=$material_type;
                    $add_met_data['material_sub_type']=$sub_type;   
                    $add_met_data['quantity_requested']=$Quantity;  
                    $add_met_data['unit_id']=$unit_id;  
                    $add_met_data['unit_name']=$unit_name;  

                    $dbc->insert_query($add_met_data,"po_request_materials_additional");

                }

            }

        }

        $data=array("boq_id"=>$boq_id,"tender_id"=>$tender_id );


        $User_id=$User_id;
        $Role_id=$ROLE_ID;

        $AdminQry= "SELECT * FROM users  WHERE is_admin=1";
        $AdminData = $dbc->get_result($AdminQry);
        $AdminName=$AdminData[0]['first_name'].' '.$AdminData[0]['last_name'];
        $AdminEmail=$AdminData[0]['email'];


        $User_qry= "SELECT * FROM users  WHERE id ='$User_id' ";
        $UserData = $dbc->get_result($User_qry);
        $created_by=$UserData[0]['first_name'].' '.$UserData[0]['last_name'];

        $boq_sql= "SELECT a.TenderName, b.item FROM tenders a INNER JOIN tender_boq_excel b ON a.id=b.tender_id  WHERE b.id=".$boq_id;
        $boqData = $dbc->get_result($boq_sql);



        $StdMaterials_Qry="SELECT mt.name AS material_type,mst.name as material_sub_type, pom.unit_name,pom.quantity_requested FROM po_request_materials pom
            INNER JOIN material_type mt ON pom.material_type_id=mt.id
            INNER JOIN material_sub_type mst ON pom.material_sub_type_id=mst.id
            WHERE po_request_id=". $Insert_Id;
        $StdMaterials = $dbc->get_result($StdMaterials_Qry);

        $AddMaterials_Qry="SELECT material_type,material_sub_type, unit_name,quantity_requested FROM po_request_materials_additional WHERE po_request_id=". $Insert_Id;
        $AddMaterials = $dbc->get_result($AddMaterials_Qry);

        $table='<table  style="border-collapse: collapse; " >
                    <thead>
                    <tr>
                        <th style="background-color: black;border: 1px solid #000000; align: center;">
                            <span style="color: #fff;  font-size:9px;">
                                S.No
                            </span>
                        </th>
                        <th style="background-color: black;border: 1px solid #000000;  font-size:9px; height: 20px">
                            <span style="color: #fff; text-align: center">
                                Material Type
                            </span>
                        </th>
                        <th style="background-color: black;border: 1px solid #000000;  font-size:9px;">
                            <span style="color: #fff; text-align: center">
                                Sub Type
                            </span>
                        </th>
                        <th style="background-color: black;border: 1px solid #000000;  font-size:9px;">
                            <span style="color: #fff; text-align: center">
                                Unit
                            </span>
                        </th>
                        <th style="background-color: black;border: 1px solid #000000;  font-size:9px;">
                            <span style="color: #fff; text-align: center">
                                Quantity
                            </span>
                        </th>
                    </tr></thead>';


        $i=0;
        foreach($StdMaterials as $row1){
            $i++;
            $table.='<tr><th  style="border: 1px solid #000000; font-weight: normal;">'.$i.'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row1['material_type'].'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row1['material_sub_type'].'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row1['unit_name'].'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row1['quantity_requested'].'</th></tr>';
        }
        foreach($AddMaterials as $row2){
            $i++;
            $table.='<tr><th  style="border: 1px solid #000000; font-weight: normal;">'.$i.'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row2['material_type'].'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row2['material_sub_type'].'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row2['unit_name'].'</th>
                <th  style="border: 1px solid #000000; font-weight: normal;">'.$row2['quantity_requested'].'</th></tr>';
        }

        $table.="</table>";




        $to=$AdminEmail;
        $user=$AdminName ;
        $subject="New PO Request - ".$boqData[0]['TenderName'];
        $body="<h4> Dear ".$AdminName .".</h4>";
        $body.='New material request is generated by '.$created_by .'. <br/><br/>';

        $body.='<span style="font-weight: bold;">Date : </span>'.date("Y-M-d H:i") .' <br/>';
        $body.='<span style="font-weight: bold;">Tender Name : </span>'.$boqData[0]['TenderName'].' <br/>';
        $body.='<span style="font-weight: bold;">BOQ Item : </span>'.$boqData[0]['item'].' <br/>';
        $body.='Request details : <br/>';
        $body.= $table . '<br/><br/>';
        $body.='Via Web ERP <br/>';

        $host = SITE_NAME;
        $from = SITE_USER;
        $password = SITE_PASS;
        $port = SITE_PORT;

        $Common=new CommonFunction($dbc);
        $send=$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);
        // $Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,'','','','','',$user );

        } catch(Exception $e) {
            var_dump($e);

        }

        if($send){
            $opData->status     =   "success";
            $opData->message    =   "Request Generated Successfully";

            


        }else{
            $opData->status     =   "fail";
            $opData->message    =   "Request Failed";
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
