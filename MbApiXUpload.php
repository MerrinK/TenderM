<?php
include "config.php";

/*
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$logFile = "./request.log";

$opData = new stdClass();


//$req_dump = print_r($_FILES, true);
//$fp = file_put_contents($logFile, $req_dump, FILE_APPEND);

//$req_dump = print_r($_REQUEST, true);
//$fp = file_put_contents($logFile, $req_dump, FILE_APPEND);

$db = $data = array();

$data = $_REQUEST;

 
$path = "./UploadDoc";

if(isset($data['action']) &&  $data['action'] != ''){

    $action = $dbc->real_escape_string(trim($data['action']));

    // Upload Challans
    if($action == "uploadChallan") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';

        $userid = $dbc->real_escape_string(trim($data['userid']));
        $userid = (is_numeric($userid) ? (int)$userid : 0);

      
        if($userid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $userid;
        }
        

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));
        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tenderid;
        }


        $vendorid = $dbc->real_escape_string(trim($data['vendorid']));
        $vendorid = (is_numeric($vendorid) ? (int)$vendorid : 0);

        if($vendorid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Vendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['vendor_id'] =  $vendorid;
        }

        $challannumber = $dbc->real_escape_string(trim($data['challannumber']));
        $challannumber = (is_numeric($challannumber) ? (int)$challannumber : 0);

        if($challannumber <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Challan Number is missing or not numeric !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['challan_no'] =  $challannumber;
        }

        
        $challandate = $dbc->real_escape_string(trim($data['challandate']));

        if (!isDate($challandate)) {
            $opData->status     =   "fail";
            $opData->message     =   "Challan Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['challan_date'] =  date("Y-m-d", strtotime($challandate));
        }


        $challandesc = $dbc->real_escape_string(trim($data['challandescription']));

        if($challandesc == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Challan description is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['challan_description'] =  $challandesc;
        }


        $MAXIMUM_FILESIZE = 4 * 1024 * 1024;

        $rEFileTypes =  "/^\.(jpg|jpeg|gif|png|pdf|zip|rar|mp4|avi|mpeg){1}$/i";

        $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tenderid ";
        $result1 = $dbc->get_result($sql1);
        $path = $path ."/". $result1[0]['WorkOrderNo']."/Challans";

                
        if (!is_dir($path)) {

            if(@mkdir($path,0755, true)){
                file_put_contents($logFile, "MKDIR Called :: $path \n", FILE_APPEND | LOCK_EX);
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not create upload directory !!!";    
                print json_encode($opData);
                file_put_contents($logFile, "MKDIR Failed :: $path \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }


        $isFile = is_uploaded_file($_FILES["photo"]['tmp_name']);
        
        if(isset($data['photo_str']) &&  $data['photo_str'] != '') {


            $base64Img = trim($data['photo_str']);
	        $size = getImageSizeFromString(base64_decode($base64Img));

	        if (empty($size['mime']) || strpos($size['mime'], 'image/') !== 0) {
	            
                $opData->status     =   "fail";
                $opData->message     =   "Base64 value is not a valid image !!!";    
                print json_encode($opData);
                file_put_contents($logFile, "Base64 value is not a valid image !!! ::\n", FILE_APPEND | LOCK_EX);
                exit;
        	}

            $ext = substr($size['mime'], 6);

            if (!in_array($ext, ['png', 'gif', 'jpeg'])) {
                $opData->status     =   "fail";
                $opData->message     =   "Unsupported image type !!!";    
                print json_encode($opData);
                file_put_contents($logFile, "Unsupported image type !!! ::\n", FILE_APPEND | LOCK_EX);
                exit;
            }

            $safe_filename = $path."/".struuid(true).'.'.$ext;

            $status = file_put_contents($safe_filename,base64_decode($base64Img));

            if($status) {
                $db['challan_image'] = $safe_filename;
                $dbc->insert_query($db,"tender_challan");


        $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_challan` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor_id=vendors.id order by tc.created_date desc";

		$result = $dbc->get_result($sql);

		if(count($result)) {
		    $opData->challans = $result;
		}

                $opData->status     =   "success";
                $opData->message     =   "Challan Uploaded Successfully !!!";
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not save base6 Image !!!";    
                file_put_contents($logFile, "Can not save base6 Image !!! :: $safe_filename \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }
     
        if(!$isFile &&  $db['challan_image'] == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Both images are empty !!!";    
            file_put_contents($logFile, "Both images are empty !!!\n", FILE_APPEND | LOCK_EX);
            print json_encode($opData);
            exit;    
        }
        
        if($isFile){

            $safe_filename = uniqid().preg_replace( array("/\s+/", "/[^-\.\w]+/"), array("_", ""), trim($_FILES["photo"]['name']));

            //if ($_FILES["photo"]['size'] <= $MAXIMUM_FILESIZE &&  preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
            if (preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
                $isMove = move_uploaded_file ($_FILES["photo"]['tmp_name'],$path."/".$safe_filename);
                
                $db['challan_image'] = $path.'/'.$safe_filename;

                $message = "Image file uploaded successfully !!\n$path/$safe_filename\n";
                file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);                        

                $dbc->insert_query($db,"tender_challan");
                $opData->status     =   "success";
                $opData->message     =   "Challan Uploadeds Successfully !!!";

		$sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_challan` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor_id=vendors.id order by tc.created_date desc";

        $result = $dbc->get_result($sql);

		if(count($result)) {
		    $opData->challans = $result;
		}

                file_put_contents($logFile, json_encode($opData), FILE_APPEND | LOCK_EX);                        

            }
            else{          
                $opData->status     =   "fail";
                $opData->message     =   "Uploaded file not supported or too large to handle !!!";
            }
        }

    }

    // Upload Progress
    if($action == "uploadProgress") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';
        
        $userid = $dbc->real_escape_string(trim($data['userid']));
        $userid = (is_numeric($userid) ? (int)$userid : 0);
        
        
        if($userid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $userid;
        }
        
        
        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));
        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);
        
        if($tenderid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;
        
        }
        else{
            $db['tender_id'] =  $tenderid;
        }
        
        
        
        $challandate = $dbc->real_escape_string(trim($data['challandate']));
        
        if (!isDate($challandate)) {
            $opData->status     =   "fail";
            $opData->message     =   "Progress Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;
        
        }
        else{
            $db['date'] =  date("Y-m-d", strtotime($challandate));
        }
        
        
        $challandesc = $dbc->real_escape_string(trim($data['challandescription']));
        
        if($challandesc == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Progress description is missing !!!";
            print json_encode($opData);
            exit;
        
        }
        else{
            $db['description'] =  $challandesc;
        }
        
        
        $MAXIMUM_FILESIZE = 4 * 1024 * 1024;
        
        $rEFileTypes =  "/^\.(jpg|jpeg|gif|png|pdf|zip|rar|mp4|avi|mpeg){1}$/i";
        
        $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tenderid ";
        $result1 = $dbc->get_result($sql1);
        $path = $path ."/". $result1[0]['WorkOrderNo']."/Progress";
        
                
        if (!is_dir($path)) {
        
            if(@mkdir($path,0755, true)){
                file_put_contents($logFile, "MKDIR Called :: $path \n", FILE_APPEND | LOCK_EX);
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not create upload directory !!!";    
                print json_encode($opData);
                file_put_contents($logFile, "MKDIR Failed :: $path \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }
        
        $pdb = array();
        
	    $isFile = 0;
        $progress_id = 0;

        if(isset($_FILES['photo'])) {
		    $isFile = is_uploaded_file($_FILES["photo"]['tmp_name']);
	    }
        
        if(isset($data['photo_str']) &&  $data['photo_str'] != '') {
        
            $safe_filename = $path."/".struuid(true).".png";
        
            $base64Img = trim($data['photo_str']);
        
            $status = file_put_contents($safe_filename,base64_decode($base64Img));
        
            if($status) {
                
                $dbc->insert_query($db,"progress");
                $progress_id = $dbc->get_insert_id();
        
                $pdb['progress_id'] = $progress_id;
                $pdb['image'] = $safe_filename;
                $pdb['tender_id'] = $tenderid;
                $pdb['created_by'] = $userid;
                
                $dbc->insert_query($pdb,"progress_image");
        
                $sql = "SELECT pg.*, tenders.TenderName, 12 as images FROM `progress` as pg, tenders where pg.tender_id=$tenderid and pg.created_by=$userid and pg.deleted=0 and pg.tender_id=tenders.id order by pg.created_date desc limit 5";
                $result = $dbc->get_result($sql);
        
                if(count($result)) {
                    
                    for($i=0; $i< count($result); $i++){
                        $progress_id = $result[$i]['id'];
                        $sql= "SELECT id, image FROM progress_image WHERE deleted=0 AND progress_id =$progress_id";
                        $result2 = $dbc->get_result($sql);               
                        $result[$i]['images'] = $result2;
                    }
                }

                $opData->progress = $result;

        
                $opData->status     =   "success";
                $opData->message     =   "Progress details uploaded successfully !!!";
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not save base6 Image !!!";    
                file_put_contents($logFile, "Can not save base6 Image !!! :: $safe_filename \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }
        
        if(!$isFile &&  $progress_id == 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Both images are empty !!!";    
            file_put_contents($logFile, "Both images are empty !!!\n", FILE_APPEND | LOCK_EX);
            print json_encode($opData);
            exit;    
        }
        
        if($isFile){
        
            $safe_filename = uniqid().preg_replace( array("/\s+/", "/[^-\.\w]+/"), array("_", ""), trim($_FILES["photo"]['name']));
        
            if (preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
                $isMove = move_uploaded_file ($_FILES["photo"]['tmp_name'],$path."/".$safe_filename);
                
                $dbc->insert_query($db,"progress");
                $progress_id = $dbc->get_insert_id();
        
                $pdb['progress_id'] = $progress_id;
                $pdb['image'] = $path.'/'.$safe_filename;
                $pdb['tender_id'] = $tenderid;
                $pdb['created_by'] = $userid;
                
                $dbc->insert_query($pdb,"progress_image");
        
        
                $message = "Progress details uploaded successfully !!! !!\n$path/$safe_filename\n";
                file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);                        
        
                $opData->status     =   "success";
                $opData->message     =   "Progress details uploaded successfully !!!";
        
        
                file_put_contents($logFile, $sql, FILE_APPEND | LOCK_EX);                        

                $sql = "SELECT pg.*, tenders.TenderName, 12 as images FROM `progress` as pg, tenders where pg.tender_id=$tenderid and pg.created_by=$userid and pg.deleted=0 and pg.tender_id=tenders.id order by pg.created_date desc limit 5";
                $result = $dbc->get_result($sql);
        
                if(count($result)) {
                    
                    for($i=0; $i< count($result); $i++){
                        $progress_id = $result[$i]['id'];
                        $sql= "SELECT id, image FROM progress_image WHERE deleted=0 AND progress_id =$progress_id";
                        $result2 = $dbc->get_result($sql);               
                        $result[$i]['images'] = $result2;
                    }
                }

                $opData->progress = $result;
        
        
                file_put_contents($logFile, json_encode($opData), FILE_APPEND | LOCK_EX);                        
        
            }
            else{          
                $opData->status     =   "fail";
                $opData->message     =   "Uploaded file not supported or too large to handle !!!";
            }
        }
    }


    //Upload Labor Bills
    if($action == "uploadLabor") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';

        $userid = $dbc->real_escape_string(trim($data['userid']));
        $userid = (is_numeric($userid) ? (int)$userid : 0);

      
        if($userid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $userid;
        }
        

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));
        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tenderid;
        }


        $vendorid = $dbc->real_escape_string(trim($data['vendorid']));
        $vendorid = (is_numeric($vendorid) ? (int)$vendorid : 0);

        if($vendorid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Vendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['vendor'] =  $vendorid;
        }

        
        $amount = $dbc->real_escape_string(trim($data['amount']));
        $amount = (is_numeric($amount) ? (float)$amount : 0);

        if($amount <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Amount is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_amount'] =  $amount;
        }


        
        $bill_number = $dbc->real_escape_string(trim($data['billnumber']));
        $bill_number = (is_numeric($bill_number) ? (int)$bill_number : 0);

        if($bill_number <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Bill Number is missing or not numeric !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_number'] =  $bill_number;
        }
        
        
        $billdate = $dbc->real_escape_string(trim($data['challandate']));

        if (!isDate($billdate)) {
            $opData->status     =   "fail";
            $opData->message     =   "Bill Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_date'] =  date("Y-m-d", strtotime($billdate));
        }


        $bill_name = $dbc->real_escape_string(trim($data['challandescription']));

        if($bill_name == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Bill description is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_name'] =  $bill_name;
        }


        $MAXIMUM_FILESIZE = 4 * 1024 * 1024;

        $rEFileTypes =  "/^\.(jpg|jpeg|gif|png|pdf|zip|rar|mp4|avi|mpeg){1}$/i";

        $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tenderid ";
        $result1 = $dbc->get_result($sql1);
        $path = $path ."/". $result1[0]['WorkOrderNo']."/Labor";

        if (!is_dir($path)) {

            if(@mkdir($path,0755, true)){
                file_put_contents($logFile, "Labor MKDIR Called :: $path \n", FILE_APPEND | LOCK_EX);
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not create upload directory Labor!!!";    
                print json_encode($opData);
                file_put_contents($logFile, "MKDIR Failed :: $path \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }


        $isFile = is_uploaded_file($_FILES["photo"]['tmp_name']);
        
        if(isset($data['photo_str']) &&  $data['photo_str'] != '') {

            $safe_filename = $path."/".struuid(true).".png";

            $base64Img = trim($data['photo_str']);

            $status = file_put_contents($safe_filename,base64_decode($base64Img));

            if($status) {
                $db['upload_bill'] = $safe_filename;
                $dbc->insert_query($db,"tender_labour_bills");


                $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_labour_bills` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor=vendors.id order by tc.created_date desc";

        
		        $result = $dbc->get_result($sql);

                if(count($result)) {
                    $opData->labor = $result;
                }

                $opData->status     =   "success";
                $opData->message     =   "Labor Bill Uploaded Successfully !!!";
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not save base6 Image !!!";    
                file_put_contents($logFile, "Can not save base6 Image !!! :: $safe_filename \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }
     
        if(!$isFile &&  $db['upload_bill'] == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Both images are empty !!!";    
            file_put_contents($logFile, "Both images are empty !!!\n", FILE_APPEND | LOCK_EX);
            print json_encode($opData);
            exit;    
        }
        
        if($isFile){

            $safe_filename = uniqid().preg_replace( array("/\s+/", "/[^-\.\w]+/"), array("_", ""), trim($_FILES["photo"]['name']));

            //if ($_FILES["photo"]['size'] <= $MAXIMUM_FILESIZE &&  preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
            if (preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
                $isMove = move_uploaded_file ($_FILES["photo"]['tmp_name'],$path."/".$safe_filename);
                
                $db['upload_bill'] = $path.'/'.$safe_filename;

                $message = "Image file uploaded successfully !!\n$path/$safe_filename\n";
                file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);                        

                $dbc->insert_query($db,"tender_labour_bills");
                $opData->status     =   "success";
                $opData->message     =   "Labor Bill Uploadeds Successfully !!!";

		
                $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_labour_bills` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor=vendors.id order by tc.created_date desc";
                $result = $dbc->get_result($sql);

                if(count($result)) {
                    $opData->labor = $result;
                }

                file_put_contents($logFile, json_encode($opData), FILE_APPEND | LOCK_EX);                        

            }
            else{          
                $opData->status     =   "fail";
                $opData->message     =   "Uploaded file not supported or too large to handle !!!";
            }
        }

    }

    //Upload Expenses
    if($action == "uploadExpenses") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challandesc =  '';

        $userid = $dbc->real_escape_string(trim($data['userid']));
        $userid = (is_numeric($userid) ? (int)$userid : 0);

      
        if($userid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $userid;
            $db['site_person'] =  $userid;
        }
        

        $tenderid = $dbc->real_escape_string(trim($data['tenderid']));
        $tenderid = (is_numeric($tenderid) ? (int)$tenderid : 0);

        if($tenderid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tenderid;
        }


        $expenseid = $dbc->real_escape_string(trim($data['expensetype']));
        $expenseid = (is_numeric($expenseid) ? (int)$expenseid : 0);

        if($expenseid <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Expense Type is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['expense_type'] =  $expenseid;
        }

        
        $amount = $dbc->real_escape_string(trim($data['amount']));
        $amount = (is_numeric($amount) ? (float)$amount : 0);

        if($amount <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Amount is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['amount'] =  $amount;
        }

        
        $expensedate = $dbc->real_escape_string(trim($data['challandate']));

        if (!isDate($expensedate)) {
            $opData->status     =   "fail";
            $opData->message     =   "Expense Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['report_date'] =  date("Y-m-d", strtotime($expensedate));
        }


        $summary = $dbc->real_escape_string(trim($data['challandescription']));

        if($summary == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Expense description is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['summary'] =  $summary;
        }


        $MAXIMUM_FILESIZE = 4 * 1024 * 1024;

        $rEFileTypes =  "/^\.(jpg|jpeg|gif|png|pdf|zip|rar|mp4|avi|mpeg){1}$/i";

        $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tenderid ";
        $result1 = $dbc->get_result($sql1);
        $path = $path ."/". $result1[0]['WorkOrderNo']."/Expense";
                
        if (!is_dir($path)) {

            if(@mkdir($path,0755, true)){
                file_put_contents($logFile, "Expense MKDIR Called :: $path \n", FILE_APPEND | LOCK_EX);
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not create upload directory Expense!!!";    
                print json_encode($opData);
                file_put_contents($logFile, "MKDIR Failed :: $path \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }


        $isFile = is_uploaded_file($_FILES["photo"]['tmp_name']);
        
        if(isset($data['photo_str']) &&  $data['photo_str'] != '') {

            $safe_filename = $path."/".struuid(true).".png";

            $base64Img = trim($data['photo_str']);

            $status = file_put_contents($safe_filename,base64_decode($base64Img));

            if($status) {
                $db['image'] = $safe_filename;
                $dbc->insert_query($db,"expenses");
        
                $sql = "SELECT tc.*, tenders.TenderName, expense_type.type FROM `expenses` as tc, tenders, expense_type where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.expense_type=expense_type.id order by tc.created_date desc limit 5";
		        $result = $dbc->get_result($sql);

                if(count($result)) {
                    $opData->expense = $result;
                }

                $opData->status     =   "success";
                $opData->message     =   "Expense Details Uploaded Successfully  !!!";
            }
            else{
                $opData->status     =   "fail";
                $opData->message     =   "Can not save base6 Image !!!";    
                file_put_contents($logFile, "Can not save base6 Image !!! :: $safe_filename \n", FILE_APPEND | LOCK_EX);
                exit;
            }
        }
     
        if(!$isFile &&  $db['image'] == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Both images are empty !!!";    
            file_put_contents($logFile, "Both images are empty !!!\n", FILE_APPEND | LOCK_EX);
            print json_encode($opData);
            exit;    
        }
        
        if($isFile){

            $safe_filename = uniqid().preg_replace( array("/\s+/", "/[^-\.\w]+/"), array("_", ""), trim($_FILES["photo"]['name']));

            //if ($_FILES["photo"]['size'] <= $MAXIMUM_FILESIZE &&  preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
            if (preg_match($rEFileTypes, strrchr($safe_filename, '.')))      {
                $isMove = move_uploaded_file ($_FILES["photo"]['tmp_name'],$path."/".$safe_filename);
                
                $db['image'] = $path.'/'.$safe_filename;

                $message = "Image file uploaded successfully !!\n$path/$safe_filename\n";
                file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);                        

                $dbc->insert_query($db,"expenses");
                $opData->status     =   "success";
                $opData->message     =   "Expense Details Uploaded Successfully !!!";

               
                $sql = "SELECT tc.*, tenders.TenderName, expense_type.type FROM `expenses` as tc, tenders, expense_type where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.expense_type=expense_type.id order by tc.created_date desc limit 5";
                $result = $dbc->get_result($sql);

                if(count($result)) {
                    $opData->expense = $result;
                }

                file_put_contents($logFile, json_encode($opData), FILE_APPEND | LOCK_EX);                        

            }
            else{          
                $opData->status     =   "fail";
                $opData->message     =   "Uploaded file not supported or too large to handle !!!";
            }
        }

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
