<?php
include "wassenger.php";
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

$req_dump = print_r($_REQUEST, true);
$fp = file_put_contents($logFile, $req_dump, FILE_APPEND);

$db = $data = array();

$data = $_REQUEST;

// $path = "./UploadDoc";
$path = "./UpDoc";

if(isset($data['action']) &&  $data['action'] != ''){

    $action = $dbc->real_escape_string(trim($data['action']));

    // Upload Challans
    if($action == "uploadTesting") {


    }
    if($action == "uploadChallan") {

        $userid = $tenderid = $vendorid = $challannumber = $challandate = $challan_amount= $challandesc =  '';

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
        $db['challan_no'] =  $challannumber;

        $challannumber = (is_numeric($challannumber) ? (int)$challannumber : 0);

        if($challannumber <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Challan Number is missing or not numeric !!!" ;
            print json_encode($opData);
            exit;

        }
        else{
            $db['challan_no'] =  $challannumber;
        }

        $challanamount = $dbc->real_escape_string(trim($data['challanamount']));

        if($challanamount=='' || $challanamount==null || $challanamount<0)$challanamount=0;
        $challanamount = (is_numeric($challanamount) ? (float)$challanamount : 0);


        if($challanamount < 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Challan Amount is missing or not numeric !!!" ;
            print json_encode($opData);
            exit;

        }
        else{
            $db['challan_amount'] =  $data['challanamount'];
        }


        
        $challandate = $dbc->real_escape_string(trim($data['challandate']));
        // $challandate = $dbc->real_escape_string(trim('02.02.2019'));
        // $challandate = '02\02\2019';

        if (!isDate2($challandate)) {
            $opData->status     =   "fail";
            $opData->message     =   "Challan Date is missing or not in DD-MM-YYYY format !!!" .$challandate;
            print json_encode($opData);
            exit;

        }
        else{
            $db['challan_date'] =  date("Y-m-d", strtotime($challandate));
        }


        $challandesc = $dbc->real_escape_string(trim($data['challandescription']));
        $db['challan_description'] =  $challandesc;

        // if($challandesc == '') {
        //     $opData->status     =   "fail";
        //     $opData->message     =   "Challan description is missing !!!";
        //     print json_encode($opData);
        //     exit;

        // }
        // else{
        //     $db['challan_description'] =  $challandesc;
        // }


        $MAXIMUM_FILESIZE = 4 * 1024 * 1024;

        $rEFileTypes =  "/^\.(jpg|jpeg|gif|png|pdf|zip|rar|mp4|avi|mpeg){1}$/i";

        // $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tenderid ";
        // $result1 = $dbc->get_result($sql1);
        // $path = $path ."/". $result1[0]['WorkOrderNo']."/Challans";
        $path = $path ."/". $tenderid."/Challans";

                
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



              



                // $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_challan` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor_id=vendors.id order by tc.created_date desc";

        		// $result = $dbc->get_result($sql);

        		// if(count($result)) {
        		//     $opData->challans = $result;
        		// }

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


                $sql1 = "SELECT CONCAT(first_name,' ', last_name) AS name FROM `users` where id=$userid";
                $result1 = $dbc->get_result($sql1);
                $sql2 = "SELECT t.TenderName, CONCAT(u.first_name,' ', u.last_name) AS SiteIncharge, u.mobile  FROM `tenders` t INNER JOIN users u ON u.id=t.SiteIncharge where t.id=$tenderid";
                $result2 = $dbc->get_result($sql2);
                $sql3 = "SELECT company_name FROM `vendors` where id=$vendorid";
                $result3 = $dbc->get_result($sql3);
                

                $updatedPath = str_replace('./', '/', $db['challan_image']);
                $Img='https://www.devengineers.com/tenderm'. $updatedPath;
                $mobile='+91'.$result2[0]['mobile'];


                $message = "🔔 *Challan Added*\n\n" .
                   "*By:* ".$result1[0]['name']."\n" .
                   "*Tender:* ".$result2[0]['TenderName']."\n" .
                   "*Vendor:* ".$result3[0]['company_name']."\n" .
                   "*Amount:* ₹".$data['challanamount']."\n" .
                   "*Challan No:* ".$data['challannumber']."\n" .
                   "*Date:* ".$data['challandate']."\n" .
                   "*Description:* \n".$data['challandescription']."\n" .
                   "*Image:* \n".$Img;



                $WhMSg1= sendWhatsappMessage($mobile, $message);



		// $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_challan` as tc, tenders, vendors where tc.tender_id=$tenderid and tc.created_by=$userid and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor_id=vendors.id order by tc.created_date desc";

        // $result = $dbc->get_result($sql);

		// if(count($result)) {
		//     $opData->challans = $result;
		// }

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

        $userid = $tender_id =   $date = $description =  '';
        
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
            $db['created_date'] =  date('Y-m-d H:i:s');
        }
        
        
        $tender_id = $dbc->real_escape_string(trim($data['tender_id']));
        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);

        
        if($tender_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "New Tendor ID is missing !!!";
            print json_encode($opData);
            exit;
        
        }
        else{
            $db['tender_id'] =  $tender_id;
        }
        
        
        
        $date = $dbc->real_escape_string(trim($data['date']));
        
        if (!isDate($date)) {
            $opData->status     =   "fail";
            $opData->message     =   "Progress Date is missing or not in DD-MM-YYYY format !!! ".$date;
            print json_encode($opData);
            exit;
        
        }
        else{
            $db['date'] =  date("Y-m-d", strtotime($date));
        }
        
        
        $description = $dbc->real_escape_string(trim($data['description']));
        
        if($description == '') {
            $opData->status     =   "fail";
            $opData->message     =   "Progress description is missing !!!";
            print json_encode($opData);
            exit;
        
        }
        else{
            $db['description'] =  $description;
        }
        
        
        $MAXIMUM_FILESIZE = 4 * 1024 * 1024;
        
        $rEFileTypes =  "/^\.(jpg|jpeg|gif|png|pdf|zip|rar|mp4|avi|mpeg){1}$/i";
        
        // $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tender_id ";
        // $result1 = $dbc->get_result($sql1);
        // $path = $path ."/". $result1[0]['WorkOrderNo']."/Progress";
        $path = $path ."/". $tender_id ."/Progress";
        
                
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
                $pdb['created_date'] = $userid;
                
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


    if ($action == "uploadProgressNew") {
        $userid = $dbc->real_escape_string(trim($_POST['userid']));
        $tender_id = $dbc->real_escape_string(trim($_POST['tender_id']));
        $date = $dbc->real_escape_string(trim($_POST['date']));
        $description = $dbc->real_escape_string(trim($_POST['description']));
        
        // Validate inputs
        if ($userid <= 0) {
            $opData->status = "fail";
            $opData->message = "User ID is missing";
            echo json_encode($opData);
            exit;
        }
        
        if ($tender_id <= 0) {
            $opData->status = "fail";
            $opData->message = "Tender ID is missing";
            echo json_encode($opData);
            exit;
        }
        
        if (!isDate2($date)) {
            $opData->status = "fail";
            $opData->message = "Invalid date format";
            echo json_encode($opData);
            exit;
        }
        
        if (empty($description)) {
            $opData->status = "fail";
            $opData->message = "Description is missing";
            echo json_encode($opData);
            exit;
        }
        
        // Prepare database record
        $db = [
            'tender_id' => $tender_id,
            'date' => date("Y-m-d", strtotime($date)),
            'description' => $description,
            'created_by' => $userid,
            'created_date' => date('Y-m-d H:i:s')
        ];
        
        // Create progress directory if not exists
        $uploadPath = $path . "/" . $tender_id . "/Progress";
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                $opData->status = "fail";
                $opData->message = "Cannot create upload directory";
                echo json_encode($opData);
                exit;
            }
        }
        
        // Insert progress record
        if (!$dbc->insert_query($db, "progress")) {
            $opData->status = "fail";
            $opData->message = "Database error: " . $dbc->error;
            echo json_encode($opData);
            exit;
        }
        
        $progress_id = $dbc->get_insert_id();
        $uploadedFiles = [];
        
        // Process uploaded files
        if (!empty($_FILES['photos']['name'][0])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                // Validate file
                $fileName = $_FILES['photos']['name'][$key];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (!in_array($fileExt, $allowedExtensions)) {
                    continue;
                }
                
                // Generate unique filename
                $newFileName = uniqid() . '.' . $fileExt;
                $destination = $uploadPath . '/' . $newFileName;
                
                if (move_uploaded_file($tmpName, $destination)) {
                    // Insert image record
                    $imageData = [
                        'progress_id' => $progress_id,
                        'image' => $destination,
                        'tender_id' => $tender_id,
                        'created_by' => $userid,
                        'created_date' => date('Y-m-d H:i:s')
                    ];
                    
                    if ($dbc->insert_query($imageData, "progress_image")) {
                        $uploadedFiles[] = $destination;
                    }
                }
            }
        }
        
        
        
        $opData->status = "success";
        $opData->message = "Progress uploaded successfully";
        
    }


    //Upload Labor Bills
    if($action == "uploadLabor") {

        $user_id = $tender_id = $vendor_id =  $bill_number = $bill_date = $bill_name = $bill_amount =  '';

	   file_put_contents($logFile, "print inside labor bills", FILE_APPEND | LOCK_EX);


        $user_id = $dbc->real_escape_string(trim($data['userid']));
        $user_id = (is_numeric($user_id) ? (int)$user_id : 0);

      
        if($user_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $user_id;
            $db['created_date'] =  date('Y-m-d H:i:s');
        }
        

        $tender_id = $dbc->real_escape_string(trim($data['tenderid']));
        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);

        if($tender_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tender_id;
        }


        $vendor_id = $dbc->real_escape_string(trim($data['vendorid']));
        $vendor_id = (is_numeric($vendor_id) ? (int)$vendor_id : 0);

        if($vendor_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Vendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['vendor'] =  $vendor_id;
        }

        
        $bill_amount = $dbc->real_escape_string(trim($data['amount']));
        $bill_amount = (is_numeric($bill_amount) ? (float)$bill_amount : 0);

        if($bill_amount <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Amount is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_amount'] =  $bill_amount;
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
        
        
        $bill_date = $dbc->real_escape_string(trim($data['challandate']));

        if (!isDate($bill_date)) {
            $opData->status     =   "fail";
            $opData->message     =   "Bill Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_date'] =  date("Y-m-d", strtotime($bill_date));
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

        // $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tender_id ";
        // $result1 = $dbc->get_result($sql1);
        // $path = $path ."/". $result1[0]['WorkOrderNo']."/Labor";
        $path = $path ."/". $tender_id ."/Labor";

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


                $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_labour_bills` as tc, tenders, vendors where tc.tender_id=$tender_id and tc.created_by=$user_id and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor=vendors.id order by tc.created_date desc";

        
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
    if($action == "uploadLaborNew") {

        $user_id = $tender_id = $vendor_id =  $bill_number = $bill_date = $bill_name = $bill_amount =  '';

       file_put_contents($logFile, "print inside labor bills", FILE_APPEND | LOCK_EX);


        $user_id = $dbc->real_escape_string(trim($data['userid']));
        $user_id = (is_numeric($user_id) ? (int)$user_id : 0);

      
        if($user_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $user_id;
            $db['created_date'] =  date('Y-m-d H:i:s');
        }
        

        $tender_id = $dbc->real_escape_string(trim($data['tenderid']));
        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);

        if($tender_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tender_id;
        }


        $vendor_id = $dbc->real_escape_string(trim($data['vendorid']));
        $vendor_id = (is_numeric($vendor_id) ? (int)$vendor_id : 0);

        if($vendor_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Vendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['vendor'] =  $vendor_id;
        }

        
        $bill_amount = $dbc->real_escape_string(trim($data['bill_amount']));
        $bill_amount = (is_numeric($bill_amount) ? (float)$bill_amount : 0);

        if($bill_amount <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Amount is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_amount'] =  $bill_amount;
        }


        
        $bill_number = $dbc->real_escape_string(trim($data['bill_number']));
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
        
        
        $bill_date = $dbc->real_escape_string(trim($data['bill_date']));

        if (!isDate2($bill_date)) {
            $opData->status     =   "fail";
            $opData->message     =   "Bill Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['bill_date'] =  date("Y-m-d", strtotime($bill_date));
        }


        $bill_name = $dbc->real_escape_string(trim($data['bill_name']));

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

        // $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tender_id ";
        // $result1 = $dbc->get_result($sql1);
        // $path = $path ."/". $result1[0]['WorkOrderNo']."/Labor";
        $path = $path ."/". $tender_id ."/Labor";

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


                $sql = "SELECT tc.*, tenders.TenderName, vendors.company_name FROM `tender_labour_bills` as tc, tenders, vendors where tc.tender_id=$tender_id and tc.created_by=$user_id and tc.deleted=0 and tc.tender_id=tenders.id and tc.vendor=vendors.id order by tc.created_date desc";

        
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



                $sql1 = "SELECT CONCAT(first_name,' ', last_name) AS name FROM `users` where id=$user_id";
                $result1 = $dbc->get_result($sql1);
                $sql2 = "SELECT t.TenderName, CONCAT(u.first_name,' ', u.last_name) AS SiteIncharge, u.mobile  FROM `tenders` t INNER JOIN users u ON u.id=t.SiteIncharge where t.id=$tender_id";
                $result2 = $dbc->get_result($sql2);
                $sql3 = "SELECT company_name FROM `vendors` where id=$vendor_id";
                $result3 = $dbc->get_result($sql3);
                

                $updatedPath = str_replace('./', '/', $db['upload_bill']);
                $Img='https://www.devengineers.com/tenderm'. $updatedPath;
                $mobile='+91'.$result2[0]['mobile'];


                $message = "🔔 *Labor Bill Added*\n\n" .
                   "*By:* ".$result1[0]['name']."\n" .
                   "*mobile:* ".$result2[0]['mobile']."\n" .
                   "*Tender:* ".$result2[0]['TenderName']."\n" .
                   "*Vendor:* ".$result3[0]['company_name']."\n" .
                   "*Amount:* ₹".$bill_amount."\n" .
                   "*Bill No:* ".$bill_number."\n" .
                   "*Date:* ".$bill_date."\n" .
                   "*Description:* \n".$bill_name."\n" .
                   "*Image:* \n".$Img;


                $WhMSg1= sendWhatsappMessage($mobile, $message);


        
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


        $user_id = $tender_id = $expense_type =  $date = $summary = $amount =  '';

        $user_id = $dbc->real_escape_string(trim($data['user_id']));
        $user_id = (is_numeric($user_id) ? (int)$user_id : 0);

      
        if($user_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $user_id;
            $db['site_person'] =  $user_id;
        }
        

        $tender_id = $dbc->real_escape_string(trim($data['tender_id']));
        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);

        if($tender_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tender_id;
        }


        $expense_type = $dbc->real_escape_string(trim($data['expense_type']));
        $expense_type = (is_numeric($expense_type) ? (int)$expense_type : 0);

        if($expense_type <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Expense Type is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['expense_type'] =  $expense_type;
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

        
        $date = $dbc->real_escape_string(trim($data['date']));

        if (!isDate($date)) {
            $opData->status     =   "fail";
            $opData->message     =   "Expense Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['report_date'] =  date("Y-m-d", strtotime($date));
        }


        $summary = $dbc->real_escape_string(trim($data['summary']));

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

        // $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tender_id ";
        // $result1 = $dbc->get_result($sql1);
        // $path = $path ."/". $result1[0]['WorkOrderNo']."/Expense";
        $path = $path ."/". $tender_id."/Expense";
                
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

               
                $sql = "SELECT tc.*, tenders.TenderName, expense_type.type FROM `expenses` as tc, tenders, expense_type where tc.tender_id=$tender_id and tc.created_by=$user_id and tc.deleted=0 and tc.tender_id=tenders.id and tc.expense_type=expense_type.id order by tc.created_date desc limit 5";
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
     if($action == "uploadExpensesNew") {


        $user_id = $tender_id = $expense_type =  $date = $summary = $amount =  '';

        $user_id = $dbc->real_escape_string(trim($data['user_id']));
        $user_id = (is_numeric($user_id) ? (int)$user_id : 0);

      
        if($user_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "User ID is missing!!!";
            print json_encode($opData);
            exit;
        }
        else{
            $db['created_by'] =  $user_id;
            $db['site_person'] =  $user_id;
        }
        

        $tender_id = $dbc->real_escape_string(trim($data['tender_id']));
        $tender_id = (is_numeric($tender_id) ? (int)$tender_id : 0);

        if($tender_id <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Tendor ID is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['tender_id'] =  $tender_id;
        }


        $expense_type = $dbc->real_escape_string(trim($data['expense_type']));
        $expense_type = (is_numeric($expense_type) ? (int)$expense_type : 0);

        if($expense_type <= 0) {
            $opData->status     =   "fail";
            $opData->message     =   "Expense Type is missing !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['expense_type'] =  $expense_type;
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

        
        $date = $dbc->real_escape_string(trim($data['date']));

        if (!isDate2($date)) {
            $opData->status     =   "fail";
            $opData->message     =   "Expense Date is missing or not in DD-MM-YYYY format !!!";
            print json_encode($opData);
            exit;

        }
        else{
            $db['report_date'] =  date("Y-m-d", strtotime($date));
        }


        $summary = $dbc->real_escape_string(trim($data['summary']));

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

        // $sql1 = "SELECT WorkOrderNo FROM tenders WHERE id=$tender_id ";
        // $result1 = $dbc->get_result($sql1);
        // $path = $path ."/". $result1[0]['WorkOrderNo']."/Expense";
        $path = $path ."/". $tender_id."/Expense";
                
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



                $sql1 = "SELECT CONCAT(first_name,' ', last_name) AS name FROM `users` where id=$user_id";
                $result1 = $dbc->get_result($sql1);
                $sql2 = "SELECT t.TenderName, CONCAT(u.first_name,' ', u.last_name) AS SiteIncharge, u.mobile  FROM `tenders` t INNER JOIN users u ON u.id=t.SiteIncharge where t.id=$tender_id";
                $result2 = $dbc->get_result($sql2);
                $sql3 = "SELECT type FROM `expense_type` where id=$expense_type";
                $result3 = $dbc->get_result($sql3);
                

                $updatedPath = str_replace('./', '/', $db['image']);
                $Img='https://www.devengineers.com/tenderm'. $updatedPath;
                $mobile='+91'.$result2[0]['mobile'];


                $message = "🔔 *Expense Added*\n\n" .
                   "*By:* ".$result1[0]['name']."\n" .
                   "*mobile:* ".$result2[0]['mobile']."\n" .
                   "*Tender:* ".$result2[0]['TenderName']."\n" .
                   "*Expense Type:* ".$result3[0]['type']."\n" .
                   "*Amount:* ₹".$amount."\n" .
                   "*Date:* ".$date."\n" .
                   "*Summary:* \n".$summary."\n" .
                   "*Image:* \n".$Img;


                $WhMSg1= sendWhatsappMessage($mobile, $message);






               
                $sql = "SELECT tc.*, tenders.TenderName, expense_type.type FROM `expenses` as tc, tenders, expense_type where tc.tender_id=$tender_id and tc.created_by=$user_id and tc.deleted=0 and tc.tender_id=tenders.id and tc.expense_type=expense_type.id order by tc.created_date desc limit 5";
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
function isDate2($string) {
    $matches = array();
    $pattern = '/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/';
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
