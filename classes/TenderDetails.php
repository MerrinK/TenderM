<?php
class TenderDetails {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";

		
	}

	public function CheckAccounts(){
		if(!isset($_SESSION['ROLE_ID']) || ($_SESSION['ROLE_ID'] == "") ){
			ajaxResponse("0", 'ROLE_ID is null');
		}
		$Role_id=$_SESSION['ROLE_ID'];
		$Accounts=0;
		$admin=0;
		if($Role_id==1)$admin=1;
		if($Role_id==5)$Accounts=1;
		



		$data=array("Accounts"=>$Accounts,"admin"=>$admin,"id"=>$_REQUEST['id'],"TenderName"=>$_REQUEST['TenderName'],"location"=>$_REQUEST['location']);
		ajaxResponse("1", $data);


	}

	public function BOQ_List(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_SESSION['ROLE_ID']) || ($_SESSION['ROLE_ID'] == "") ){
			ajaxResponse("0", 'ROLE_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}


		$id=$_REQUEST['id'];
		$BOQ=$_REQUEST['id'];
		$eschar='"';
		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];

		// -- CONCAT( SUBSTRING(a.description, 1, 25) , ' ...')AS DescShort,
		// -- CONCAT(a.description,'', '') )AS DescShort,

		$sql= "SELECT a.id, a.item,a.total_qty,a.rate,a.unit,

			((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS RequestedQuantity,
			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ConfirmedQuantity,
			((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ReceivedQuantity,

			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id))) AS PendingQuantity

			FROM tender_boq_excel a
				INNER JOIN tenders b ON b.id= a.tender_id 
				WHERE a.deleted=0 AND a.tender_id=" . $id;

				// echo $sql;
				// die();

		$result = $this->dbc->get_result($sql);

		$BOQ=[];
		$ind=-1;
		foreach ($result as $row) {
			$ind++;
			$boq_id= $row['id'];
			$sql2_0= "SELECT total_qty,CONCAT( SUBSTRING(description, 1, 25) , ' ...') AS DescShort, description
			FROM tender_boq_excel  WHERE id=".$boq_id;
		

			
			$result2_0 = $this->dbc->get_result($sql2_0);

			$total_qty=$result2_0[0]['total_qty'];
			
			// $row['remaining_qty']=$result2_0[0]['total_qty']-$row['ConfirmedQuantity'];
			$row['remaining_qty']=$result2_0[0]['total_qty']-$row['ReceivedQuantity'];
			$row['pending_qty']="0";

			$desc = $result2_0[0]['description'];
			$row['description']= $desc;

			$shortdesc =  $result2_0[0]['DescShort'];
			$row['DescShort']= $shortdesc;

			//check for zero remaining quantity
			if($row['remaining_qty']<=0){
				$ind--;
				continue;
			}
			$BOQ[$ind]=$row;
			// print_r($row);
			// echo "<br><br>";
		}





		$sql1= "SELECT count(a.id) AS Count FROM users a INNER JOIN role b ON a.role=b.id WHERE  a.id= '$User_id' AND (b.id= 1 OR b.id= 2 )";
		$result1 = $this->dbc->get_result($sql1);

		
		$sql2= "SELECT TenderName FROM tenders WHERE id='$id'";
		$result2 = $this->dbc->get_result($sql2);


		
		// $User_id=$_SESSION['USER_ID'];
		// $AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		// $AdminData = $this->dbc->get_result($AdminQry);
		// $admin=0;
		// if($AdminData[0]['is_admin']==1){
		// 	$admin=1;
		// }



		// $sql001= "SELECT role FROM users WHERE id='$User_id' ";
		// $result001 = $this->dbc->get_result($sql001);
		// $role=$result001[0]['role'];


		$role=$_SESSION['ROLE_ID'];

		if($role==1){
			$admin=1;
			$SiteIncharge=0;
		}else if($role==2 || $role==3){
			$admin=0;
			$SiteIncharge=1;
		}else{
			$SiteIncharge=0;
			$admin=0;
		}


		


		$data=array("BOQ"=>$BOQ, "Access"=>$result1[0]['Count'], "TenderName"=>$result2[0]['TenderName'],"TenderId"=>$id,"admin"=>$admin,"SiteIncharge"=>$SiteIncharge);

		ajaxResponse("1", $data);
	}







	public function AllRequested_POs(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$User_id=$_SESSION['USER_ID'];
		$ROLE_ID=$_SESSION['ROLE_ID'];
		$admin=0;
		$SiteIncharge=0;

		// $AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		// $AdminData = $this->dbc->get_result($AdminQry);

		// if($AdminData[0]['is_admin']==1){
		// 	$admin=1;
		// }

		if($ROLE_ID==1){
			$admin=1;
		}else if($ROLE_ID==2 || $ROLE_ID==4){
			$SiteIncharge=1;
		}

		$tender_id=$_REQUEST['tender_id'];	

		$sql0= "SELECT id, TenderName FROM tenders WHERE id='$tender_id'";
		$result0 = $this->dbc->get_result($sql0);

		$sql01= "SELECT GROUP_CONCAT(id) AS BOQ_Ids FROM tender_boq_excel WHERE deleted=0 AND  tender_id='$tender_id'";
		$result01 = $this->dbc->get_result($sql01);
		$BOQ_Ids=$result01[0]['BOQ_Ids'];
	
		
		$sql="SELECT a.*, CAST(a.created_date AS date) AS created_date, CAST(a.required_by AS date) AS required_by, b.item,b.total_qty ,b.unit,
		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalRequestedQty, 

		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalConfirmedQty,  

		((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalReceivedQty,

		((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE po_request_id=a.id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE po_request_id=a.id)))AS remaining_qty,

		 -- b.description,
		  CONCAT( SUBSTRING(b.description, 1, 250) , ' ...')AS description,
		   CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort 
			FROM po_request a 
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			WHERE b.deleted=0 AND a.status=0 AND a.tender_id=".$tender_id ." ORDER BY a.id  DESC";
			// echo$sql ;

		$result = $this->dbc->get_result($sql);
		// print_r($result);
		$ind=-1;
	

		$BOQ= $result;//Delete this
		
		$data=array("BOQ"=>$BOQ, "TenderId"=>$result0[0]['id'],"TenderName"=>$result0[0]['TenderName'], "BOQ_Id"=>0,"admin"=>$admin,"SiteIncharge"=>$SiteIncharge  );

		ajaxResponse("1", $data);


	}

	public function View_RequstedPO(){
		if(!isset($_REQUEST['PORequest_id']) || ($_REQUEST['PORequest_id'] == "") ){
			ajaxResponse("0", 'PORequest id is null');
		}

		$PORequest_id=$_REQUEST['PORequest_id'];

		$sql= "SELECT a.quantity_requested,a.material_description, b.name AS material_type, a.unit_name AS unit, c.name AS material_sub_type
				FROM po_request_materials a
				INNER JOIN material_type b ON b.id=a.material_type_id
				LEFT OUTER JOIN material_sub_type c ON c.id=a.material_sub_type_id
				WHERE a.po_request_id=".$PORequest_id;
				// echo $sql;
		$PO_Data = $this->dbc->get_result($sql);

		$sql= "SELECT a.* FROM po_request_materials_additional a WHERE a.po_request_id=".$PORequest_id;
				// echo $sql;
		$PO_Data_Add = $this->dbc->get_result($sql);


		$sql2= "SELECT  b.item,c.TenderName,a.request_note
				FROM po_request a
				INNER JOIN tender_boq_excel b ON b.id=a.boq_id
				INNER JOIN tenders c ON b.tender_id=c.id
				WHERE a.id =".$PORequest_id;
		$BOQ_Data = $this->dbc->get_result($sql2);

		$data=array("PO_Data"=>$PO_Data,"PO_Data_Add"=>$PO_Data_Add,"TenderName"=>$BOQ_Data[0]['TenderName'],"item"=>$BOQ_Data[0]['item'],"request_note"=>$BOQ_Data[0]['request_note'] );
		ajaxResponse("1", $data);

	}

	public function SelectACtive_POs(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$id=$_REQUEST['tender_id'];


		$User_id=$_SESSION['USER_ID'];

		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}


		$sql= "SELECT a.*, CAST(a.created_date AS date) AS created_date, CAST(a.required_by AS date) AS required_by, b.item,b.id AS boq_id,b.total_qty,b.unit,
		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))AS TotalRequestedQty,

		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))AS TotalConfirmedQty,

		((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=po_request_id ) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=po_request_id ) )AS TotalReceivedQty,

		(SELECT IFNULL(SUM(difference),0) FROM po_request_received_qty WHERE po_request_id=a.id) AS difference,


		(((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=po_request_id ) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=po_request_id )))AS pending_qty,

		 
		 CONCAT( SUBSTRING(b.description, 1, 250) , ' ...')AS description,
		 CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort ,
		 CONCAT(u.first_name, ' ', u.last_name) AS OrderedBy
			FROM po_request a
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			INNER JOIN users u ON  a.created_by=u.id
			WHERE  b.deleted=0 AND  a.tender_id=".$id." AND a.status=1 ORDER BY a.id DESC";

			    // echo $sql;
		$result = $this->dbc->get_result($sql);

		$ind=-1;
		$BOQ=$result;

		if(isset($result[0])){
			$data=array("BOQ"=>$BOQ, "TenderId"=>$id, "BOQ_Id"=>$id, "admin"=>$admin );
		}else{
			$data=array( "TenderId"=>$id ,"BOQ_Id"=>$id, "admin"=>$admin );
		}
		ajaxResponse("1", $data);

	}


	public function VendorCompanyList(){
		$sql2= "SELECT id, company_name AS name FROM vendors WHERE deleted=0 ORDER BY company_name ASC";
		$result2 = $this->dbc->get_result($sql2);
		$data=array("Vendor"=>$result2);
		ajaxResponse("1", $data);
	}




	public function challanBills(){
		$tender_id=$_REQUEST['tender_id'];
		
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "")){
			ajaxResponse("0", "Tender id null");
		}

		// $vendor_id=$_REQUEST['vendor_id'];

		$sql0= "SELECT GROUP_CONCAT(challan_ids) AS challan_ids FROM  tender_challan_bills  WHERE tender_id='$tender_id' AND deleted=0";
		$result0 = $this->dbc->get_result($sql0);

		// if(isset($result0[0]['challan_ids'])){
		// 	ajaxResponse("0", "Challan id null");
		// }

		$qry="";
		if(isset($result0[0]['challan_ids'])){
			$qry=" AND a.id NOT IN (".$result0[0]['challan_ids'].") ";
		}

		$sql= "SELECT a.*,b.TenderName, v.name AS VendorName,v.company_name AS VendorCompanyName FROM tender_challan a INNER JOIN tenders b ON a.tender_id=b.id INNER JOIN vendors v ON a.vendor_id=v.id WHERE a.tender_id='$tender_id' AND a.deleted=0 " .$qry ." ORDER by id DESC ";

		// echo $sql;
		$result = $this->dbc->get_result($sql);

		$User_id=$_SESSION['USER_ID'];
		$ROLE_ID=$_SESSION['ROLE_ID'];
		$admin=0;
		$Accounts=0;


		// $AdminQry= "SELECT u.is_admin, r.name FROM users u INNER JOIN role r ON u.role=r.id WHERE u.id=".$User_id;
		// $AdminData = $this->dbc->get_result($AdminQry);

		// if($AdminData[0]['is_admin']==1){
		// 	$admin=1;
		// }else if($AdminData[0]['name']=='Accounts'){
		// 	$Accounts=1;
		// }

		if($ROLE_ID==1){
			$admin=1;
		}else if($ROLE_ID==5){
			$Accounts=1;
		}

		// if($AdminData[0]['is_admin']==1){
		// 	$admin=1;
		// }else if($AdminData[0]['name']=='Accounts'){
		// 	$Accounts=1;
		// }


		$sql2= "SELECT id, company_name AS name FROM vendors WHERE deleted = 0
				AND id IN ( SELECT vendor_id
					  FROM tender_challan
					  WHERE tender_id = '$tender_id' AND deleted = 0
					  GROUP BY vendor_id
				) ORDER BY company_name ASC;";
		$result2 = $this->dbc->get_result($sql2);

		$ind=-1;
		$ChallanDetails=[];
		foreach ($result as $row ) {
			$ind++;
			$row['description']=str_replace("\\n", "nl2br", $row['challan_description']);
			$ChallanDetails[$ind]=$row;
		}


		$data=array("ChallanDetails"=>$ChallanDetails,"admin"=>$admin,"Accounts"=>$Accounts, "VendorList"=>$result2);
		ajaxResponse("1", $data);

		

	}

	public function AddNewChallan(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER ID null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id null');
		}

		if(!isset($_REQUEST['Challan_vendor']) || ($_REQUEST['Challan_vendor'] == "") ){
			ajaxResponse("0", 'Challan vendor null');
		}
		if(!isset($_REQUEST['challan_number']) || ($_REQUEST['challan_number'] == "") ){
			ajaxResponse("0", 'Challan number null');
		}
		if(!isset($_REQUEST['challan_date']) || ($_REQUEST['challan_date'] == "") ){
			ajaxResponse("0", 'Challan date null');
		}
		if(!isset($_REQUEST['challan_description']) || ($_REQUEST['challan_description'] == "") ){
			ajaxResponse("0", 'Challan description null');
		}

		$sql= "SELECT id FROM tender_challan ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		if(isset($result[0]['id'])){
			$id=$result[0]['id'];
			$id++;
		}else{
			ajaxResponse("0", 'Unable to insert the record');
		}

		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];

		// $Today=date('Y-m-d');
		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["vendor_id"]=$_REQUEST['Challan_vendor'];
		$data["challan_no"]=$_REQUEST['challan_number'];
		$data["challan_date"]=$_REQUEST['challan_date'];
		$data["challan_amount"]=$_REQUEST['challan_amount'];
		$data["challan_description"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['challan_description'])));
		$data["created_by"]=$_SESSION['USER_ID'];
		$data["created_date"]=date('Y-m-d');
		

		// $folder="UploadDoc/";
		$folder="UploadDoc/".$WorkOrderNo."/Challans/";

		if(isset($_FILES['UploadChallan']['name']) && $_FILES['UploadChallan']['name']!=''){
			$info_1 = pathinfo($_FILES['UploadChallan']['name']);
			$ext_1 = $info_1['extension']; 
			$target_1 =  $folder. 'UploadChallan_'.$id.'.'.$ext_1;
			// move_uploaded_file( $_FILES['UploadChallan']['tmp_name'], $target_1);
			$data['challan_image']=$target_1;
		}
		$result= $this->dbc->insert_query($data,"tender_challan");


		$getTenderQry = "SELECT TenderName FROM tenders WHERE id=".$_REQUEST['tender_id'];
 		$getTenderData = $this->dbc->get_result($getTenderQry);

 		$VendorQry = "SELECT name FROM vendors WHERE id=".$_REQUEST['Challan_vendor'];
 		$VendorData = $this->dbc->get_result($VendorQry);

		$is_admin_qry="SELECT id, first_name, last_name, email FROM users WHERE is_admin=1 LIMIT 1" ;
 		$IsAdminData = $this->dbc->get_result($is_admin_qry);

		$to=$IsAdminData[0]['email'];
		$name=$IsAdminData[0]['first_name'].' ' .$IsAdminData[0]['last_name'];

		$subject="New Challan   ";
		$body="<h4> Dear ".$name ."</h4>";
		$body.='A Challan  is generated against : <br/><br/>';
		$body.='Tender Name : '.$getTenderData[0]['TenderName'].' <br/>';
		$body.='Vendor Name : '.$VendorData[0]['name'].' <br/>';
		$body.='Challan Number : '.$_REQUEST['challan_number'].' <br/>';
		$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/><br/>';
		$body.='Thank you. <br/>';
		$body.='--<br/>';
		$body.='Via Web ERP <br/>';
		$host = SITE_NAME;
		$from = SITE_USER;
		$password = SITE_PASS;
		$port = SITE_PORT;

		$Common=new CommonFunction($this->dbc);
		$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$target_1);



		if($result>=1){
			if(isset($_FILES['UploadChallan']['name']) && $_FILES['UploadChallan']['name']!=''){
				move_uploaded_file( $_FILES['UploadChallan']['tmp_name'], $target_1);
			}
			ajaxResponse("1", '');
		}else{
			ajaxResponse("0", 'Unable to insert the record');

		}
	}

	public function DeleteChellan(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}


		$dataDelete=array();
		$dataDelete["deleted"]=1;
		$dataDelete["deleted_by"]=$_SESSION['USER_ID'];
		$dataDelete["deleted_date"]=date("Y-m-d");

		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];
		$res = $this->dbc->update_query($dataDelete, 'tender_challan', $dataDelete_id);
		$this->unlinkImage('tender_challan',$_REQUEST['id'], 'challan_image');
		ajaxResponse("1", '');

	}


	public function SelectChellanOfVendor(){

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$vendor_id=$_REQUEST['vendor_id'];
		$tender_id=$_REQUEST['tender_id'];
		$startDate=$_REQUEST['startDate'];
		$endDate=$_REQUEST['endDate'];
		$AddQry1="";
		$AddQry2="";
		$AddQry3="";

		if($vendor_id!=""){
			$AddQry1=" AND FIND_IN_SET(a.vendor_id,'". $vendor_id."' ) ";
		}
		if($startDate!="" && $startDate!='Invalid date'){
			$AddQry2=" AND a.challan_date>='". $startDate."' ";
		}
		if($endDate!="" && $endDate!='Invalid date'){
			$AddQry3=" AND a.challan_date<='". $endDate."' ";
		}



		$sql0= "SELECT GROUP_CONCAT(challan_ids) AS challan_ids FROM  tender_challan_bills  WHERE tender_id='$tender_id' AND deleted =0";
		// echo $sql0;
		$result0 = $this->dbc->get_result($sql0);

		$qry="";
		if(isset($result0[0]['challan_ids'])){
			$qry=" AND a.id NOT IN (".$result0[0]['challan_ids'].") ";
		}

		$sql= "SELECT a.*,b.TenderName, v.name AS VendorName,v.company_name AS VendorCompanyName  FROM tender_challan a INNER JOIN tenders b ON a.tender_id=b.id INNER JOIN vendors v ON a.vendor_id=v.id WHERE a.tender_id='$tender_id' AND a.deleted=0 " .$qry . $AddQry1 . $AddQry2. $AddQry3 ." ORDER by id DESC ";
		// echo $sql;
		$result = $this->dbc->get_result($sql);




		// $sql= "SELECT a.*, v.name AS VendorName FROM tender_challan a  INNER JOIN vendors v ON a.vendor_id=v.id WHERE a.tender_id='$tender_id'  AND a.deleted=0  ".$qry;
		// $result = $this->dbc->get_result($sql);

		

		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}
		

		$ind=-1;
		$ChallanDetails=[];
		foreach ($result as $row ) {
			$ind++;
			$row['challan_description']=str_replace("\\n", "nl2br", $row['challan_description']);
			$ChallanDetails[$ind]=$row;
		}


		$sql2= "SELECT id, name FROM vendors WHERE deleted=0";
		$result2 = $this->dbc->get_result($sql2);

	

		$data=array("ChallanDetails"=>$ChallanDetails, "admin"=>$admin, "VendorList"=>$result2);
		ajaxResponse("1", $data);



	}

	public function SelectChellanForBills(){
		if(!isset($_REQUEST['ids']) || ($_REQUEST['ids'] == "") ){
			ajaxResponse("0", 'Ids is null');
		}

		$ids=implode(",", $_REQUEST['ids']);

		$sql="SELECT COUNT( DISTINCT vendor_id) AS Count FROM tender_challan WHERE id IN  (".$ids.")";
		$result = $this->dbc->get_result($sql);
		if($result[0]['Count']>1){
			ajaxResponse("2", "Please select the same vendor for billing");
		}else{
			$sql2= "SELECT * FROM tender_challan WHERE id IN (".$ids.")";
			$result2 = $this->dbc->get_result($sql2);

			$sql2_1= "SELECT SUM(challan_amount) AS TotalAmount FROM tender_challan WHERE id IN (".$ids.")";
			// echo $sql2_1;
			$result2_1 = $this->dbc->get_result($sql2_1);

			$sql0= "SELECT a.id,b.WorkOrderNo FROM tender_challan_bills a INNER JOIN tenders b ON b.id=a.tender_id ORDER BY id DESC LIMIT 1 ";
			$result0 = $this->dbc->get_result($sql0);
			$id=$result0[0]['id'];
			$TenderCode=$result0[0]['WorkOrderNo'];
			$id++;


			$Today= date('Ymd');
			$BillNo=$TenderCode.'/'.str_pad($id, 4, '0', STR_PAD_LEFT).'/'.$Today;

			$sql3= "SELECT * FROM bill_type";
			$result3 = $this->dbc->get_result($sql3);

			$data=array("ChallanDetails"=>$result2, "vendor_id"=>$result2[0]['vendor_id'], "Challan_ids"=>$ids, "BillNo"=>$BillNo,"TotalAmount"=>$result2_1[0]['TotalAmount'],"BillType"=>$result3);
			ajaxResponse("1", $data);
		}
	}


	

	public function AddNewBills(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		if(!isset($_REQUEST['vendor_id']) || ($_REQUEST['vendor_id'] == "") ){
			ajaxResponse("0", 'Vendor id is null');
		}
		if(!isset($_REQUEST['challan_ids']) || ($_REQUEST['challan_ids'] == "") ){
			ajaxResponse("0", 'Challan id is null');
		}
		if(!isset($_REQUEST['Challan_BillType']) || ($_REQUEST['Challan_BillType'] == "") ){
			ajaxResponse("0", 'Challan bill type is null');
		}
		if(!isset($_REQUEST['challan_bill_number']) || ($_REQUEST['challan_bill_number'] == "") ){
			ajaxResponse("0", 'Challan bill number is null');
		}
		if(!isset($_REQUEST['challan_billdate']) || ($_REQUEST['challan_billdate'] == "") ){
			ajaxResponse("0", 'Challan bill date is null');
		}
		// if(!isset($_REQUEST['challan_billAmount']) || ($_REQUEST['challan_billAmount'] == "") ){
		// 	ajaxResponse("0", 'Challan bill amount is null');
		// }

		if(!isset($_REQUEST['challan_billdescription']) || ($_REQUEST['challan_billdescription'] == "") ){
			ajaxResponse("0", 'Challan bill description is null');
		}

		$sql= "SELECT id FROM tender_challan_bills ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		if(isset($result[0]['id']))$id=$result[0]['id'];
		else $id=0;
		$id++;


		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];

		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["vendor_id"]=$_REQUEST['vendor_id'];
		$data["challan_ids"]=$_REQUEST['challan_ids'];
		$data["bill_type"]=$_REQUEST['Challan_BillType'];
		$data["bill_number"]=$_REQUEST['challan_bill_number'];
		$data["bill_date"]=$_REQUEST['challan_billdate'];
		// $data["bill_amount"]=$_REQUEST['challan_billAmount'];
		// $data["bill_description"]=$_REQUEST['challan_billdescription'];
		$data["bill_description"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['challan_billdescription'])));

		$data["created_by"]=$_SESSION['USER_ID'];
		$data["created_date"]=date("Y-m-d");

		// $folder="UploadDoc/";
		$folder="UploadDoc/".$WorkOrderNo."/Expense/";

		if(isset($_FILES['UploadBills']['name']) && $_FILES['UploadBills']['name']!=''){
			$info_1 = pathinfo($_FILES['UploadBills']['name']);
			$ext_1 = $info_1['extension']; 
			$target_1 =  $folder. 'UploadBills_'.$id.'.'.$ext_1;
			// move_uploaded_file( $_FILES['UploadBills']['tmp_name'], $target_1);
			$data['upload_bills']=$target_1;
		}
			
		$result=$this->dbc->insert_query($data,"tender_challan_bills");

		$getTenderQry = "SELECT TenderName FROM tenders WHERE id=".$_REQUEST['tender_id'];
 		$getTenderData = $this->dbc->get_result($getTenderQry);

 		$VendorQry = "SELECT name FROM vendors WHERE id=".$_REQUEST['vendor_id'];
 		$VendorData = $this->dbc->get_result($VendorQry);

		$is_admin_qry="SELECT id, first_name, last_name, email FROM users WHERE is_admin=1 LIMIT 1" ;
 		$IsAdminData = $this->dbc->get_result($is_admin_qry);

		$to=$IsAdminData[0]['email'];
		$name=$IsAdminData[0]['first_name'].' ' .$IsAdminData[0]['last_name'];

		$subject="New Bill  ";
		$body="<h4> Dear ".$name ."</h4>";
		$body.='New Bill is created : <br/><br/>';
		$body.='Tender Name : '.$getTenderData[0]['TenderName'].' <br/>';
		$body.='Vendor Name : '.$VendorData[0]['name'].' <br/>';
		$body.='Bill Number : '.$_REQUEST['challan_bill_number'].' <br/>';
		$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/><br/>';
		$body.='Thank you. <br/>';
		$body.='--<br/>';
		$body.='Via Web ERP <br/>';

		$host = SITE_NAME;
		$from = SITE_USER;
		$password = SITE_PASS;
		$port = SITE_PORT;

		$Common=new CommonFunction($this->dbc);
		$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$target_1);



		if($result>=1){
			if(isset($_FILES['UploadBills']['name']) && $_FILES['UploadBills']['name']!=''){
				move_uploaded_file( $_FILES['UploadBills']['tmp_name'], $target_1);
			}
			ajaxResponse("1", '');
		}else{
			ajaxResponse("0", 'Unable to insert the record');

		}
	}

	public function SelectCreatedBills(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$tender_id=$_REQUEST['tender_id'];

		$sql= "SELECT cb.*,t.TenderName,v.company_name AS VendorName, bt.type AS bill_type FROM tender_challan_bills cb INNER JOIN tenders t ON t.id=cb.tender_id INNER JOIN vendors v ON v.id=cb.vendor_id  INNER JOIN bill_type bt ON bt.id=cb.bill_type WHERE cb.tender_id='$tender_id' AND cb.deleted=0 ORDER BY id  DESC  ";
		$result = $this->dbc->get_result($sql);

		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}

		$data=array("CreatedBills"=>$result, "admin"=>$admin);
		ajaxResponse("1", $data);

	}

	public function viewBillsChellans(){
		if(!isset($_REQUEST['challan_ids']) || ($_REQUEST['challan_ids'] == "") ){
			ajaxResponse("0", 'Challan id is null');
		}

		$challan_ids=$_REQUEST['challan_ids'];
		$sql= "SELECT c.*, v.name AS VendorName FROM tender_challan c INNER JOIN vendors v ON v.id=c.vendor_id  WHERE c.id IN (".$challan_ids.")";
		// echo $sql; die;
		$result = $this->dbc->get_result($sql);


		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}

		$sql2= "SELECT id, name FROM vendors WHERE deleted=0";
		$result2 = $this->dbc->get_result($sql2);

		$data=array("ChallanDetails"=>$result, "admin"=>$admin);
		ajaxResponse("1", $data);
	}
	public function DeleteChellanBills(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$dataDelete=array();
		$dataDelete["deleted"]=1;
		$dataDelete["deleted_by"]=$_SESSION['USER_ID'];
		$dataDelete["deleted_date"]=date("Y-m-d");
		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($dataDelete, 'tender_challan_bills', $dataDelete_id);
		$this->unlinkImage('tender_challan_bills',$_REQUEST['id'], 'upload_bills');	
		ajaxResponse("1", '');

	}

	
	
	public function GetDailyProgress(){
		$tender_id=$_REQUEST['tender_id'];

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$tender_id=$_REQUEST['tender_id'];
		
		$sql= "SELECT * FROM progress WHERE deleted=0 AND tender_id=".$tender_id ." ORDER BY id DESC " ;
		$result = $this->dbc->get_result($sql);



		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT count(u.id) AS Count FROM users u INNER JOIN role r ON u.role=r.id WHERE (r.name='Site Incharge' OR r.name='Admin') AND u.id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		// admin and site incharge
		if($AdminData[0]['Count']==1){
			$admin=1;
		}

		$data=array("DailyProgress"=>$result,"admin"=>$admin);
		ajaxResponse("1", $data);
	}





	public function AddNewProgress(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		if(!isset($_REQUEST['progress_date']) || ($_REQUEST['progress_date'] == "") ){
			ajaxResponse("0", 'Progress date is null');
		}

		if(!isset($_REQUEST['progress_description']) || ($_REQUEST['progress_description'] == "") ){
			ajaxResponse("0", 'Progress description is null');
		}

		$TotalFiles=count(array_filter($_FILES['UploadProgress']['name']));
		$Err_find = 0;
		// echo $TotalFiles;
		if($TotalFiles <= 0){
			ajaxResponse("0", 'Unable to insert the record');
		}else{

			$sql= "SELECT id FROM progress_image ORDER BY id DESC LIMIT 1";
			$result = $this->dbc->get_result($sql);
			if(isset($result[0]['id']))$id=$result[0]['id'];
			else $id=0;
			// $id++;


			$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
			$result2 = $this->dbc->get_result($sql2);
			if(isset($result2[0]['WorkOrderNo'])){
				$WorkOrderNo=$result2[0]['WorkOrderNo'];
			}else{
				ajaxResponse("0", 'Work Order No null');
			}

			$data=array();
			$data["tender_id"]=$_REQUEST['tender_id'];
			$data["date"]=$_REQUEST['progress_date'];
			// $data["description"]=$_REQUEST['progress_description'];
			$data["description"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['progress_description'])));

			$data["created_by"]=$_SESSION['USER_ID'];
			$data["created_date"]=date("Y-m-d");

			$this->dbc->insert_query($data,"progress");
			$Insert_id=$this->dbc->get_insert_id();
			// $folder="UploadDoc/";

			$ImageArray=array();
			
			$dataImage=array();
			$dataImage["tender_id"]=$_REQUEST['tender_id'];
			$dataImage["progress_id"]=$Insert_id;
			$dataImage["created_by"]=$_SESSION['USER_ID'];
			$dataImage["created_date"]=date("Y-m-d");

			$TotalFiles=count(array_filter($_FILES['UploadProgress']['name']));
			$folder="UploadDoc/".$WorkOrderNo."/Progress/";
			$last_int_id = "";
				
			// if($result>=1){
				for ($i=0; $i < $TotalFiles; $i++) { 
					if(isset($_FILES['UploadProgress']['name'][$i]) && $_FILES['UploadProgress']['name'][$i]!=''){
						
						$info_1 = pathinfo($_FILES['UploadProgress']['name'][$i]);
						$ext_1 = $info_1['extension']; 
						$target_1 =  $folder. 'UploadProgress_'.$id.'.'.$ext_1;
						move_uploaded_file( $_FILES['UploadProgress']['tmp_name'][$i], $target_1);
						$dataImage['image']=$target_1;
						array_push($ImageArray,$target_1);
						$res = $this->dbc->insert_query($dataImage,"progress_image");
						if($res){
							$Err_find++;
						}
					}
					$id++;
				}


				$getTenderQry = "SELECT TenderName FROM tenders WHERE id=".$_REQUEST['tender_id'];
		 		$getTenderData = $this->dbc->get_result($getTenderQry);

		 		// $VendorQry = "SELECT name FROM vendors WHERE id=".$_REQUEST['BillVendor'];
		 		// $VendorData = $this->dbc->get_result($VendorQry);

				$is_admin_qry="SELECT id, first_name, last_name, email FROM users WHERE is_admin=1 LIMIT 1" ;
		 		$IsAdminData = $this->dbc->get_result($is_admin_qry);

				$to=$IsAdminData[0]['email'];
				$name=$IsAdminData[0]['first_name'].' ' .$IsAdminData[0]['last_name'];

				$subject="New Progress  ";
				$body="<h4> Dear ".$name ."</h4>";
				$body.='New Progress is created against : <br/><br/>';
				$body.='Tender Name : '.$getTenderData[0]['TenderName'].' <br/>';
				$body.='Description : '.$_REQUEST['progress_description'].' <br/>';
				$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/><br/>';
				$body.='Thank you. <br/>';
				$body.='--<br/>';
				$body.='Via Web ERP <br/>';

				$host = SITE_NAME;
				$from = SITE_USER;
				$password = SITE_PASS;
				$port = SITE_PORT;

				$Common=new CommonFunction($this->dbc);
				$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$ImageArray);




				// echo 'Err_find'.$Err_find;
				if($TotalFiles == $Err_find){



					ajaxResponse("1", '');
				}else{
					ajaxResponse("0", 'Unable to insert the record');
				}
				
			// }else{
			// 	

			// }
		}

	}

	public function DeleteProgress(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$dataDelete=array();
		$dataDelete["deleted"]=1;
		$data["deleted_by"]=$_SESSION['USER_ID'];
		$data["deleted_date"]=date("Y-m-d");
		
		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];

		$this->dbc->update_query($dataDelete, 'progress', $dataDelete_id);

		$this->unlinkProgressImage($_REQUEST['id']);

		ajaxResponse("1", '');

	}

	public function SelectProgressImages(){

		if(!isset($_REQUEST['progress_id']) || ($_REQUEST['progress_id'] == "") ){
			ajaxResponse("0", 'Progress id is null');
		}

		$sql= "SELECT id, image FROM progress_image WHERE deleted=0 AND progress_id =".$_REQUEST['progress_id'];

		$result = $this->dbc->get_result($sql);
		$data=array("DailyProgressImage"=>$result);
		ajaxResponse("1", $data);

	}

	public function DeleteProgressimage(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$dataDelete=array();
		$dataDelete["deleted"]=1;
		$data["deleted_by"]=$_SESSION['USER_ID'];
		$data["deleted_date"]=date("Y-m-d");
		
		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];

		$this->dbc->update_query($dataDelete, 'progress_image', $dataDelete_id);
		ajaxResponse("1", '');

	}


	public function FetchExpenses(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$tender_id=$_REQUEST['tender_id'];



		$sql0= "SELECT GROUP_CONCAT(expense_ids) AS expense_ids FROM  expenses_voucher  WHERE tender_id='$tender_id' AND deleted=0";
		// echo $sql0;
		$result0 = $this->dbc->get_result($sql0);
		$closed_ids=explode(',',$result0[0]['expense_ids']);

		
		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT u.is_admin, r.name FROM users u INNER JOIN role r ON u.role=r.id WHERE u.id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		$Accounts=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}else if($AdminData[0]['name']=='Accounts'){
			$Accounts=1;
		}


		$sql= "SELECT e.site_person AS site_person_id , CONCAT(u.first_name, ' ', u.last_name)AS site_person, e.id,e.summary,e.report_date,e.image,e.amount,et.type AS expense_type, et.id AS expense_type_id FROM expenses e INNER JOIN expense_type et ON et.id=e.expense_type LEFT OUTER JOIN users u ON e.site_person =u.id WHERE e.deleted=0 AND e.tender_id =".$_REQUEST['tender_id']  ." ORDER BY e.id DESC";

		// echo $sql;


		$result = $this->dbc->get_result($sql);
		$ind=-1;
		$Expenses=[];
		foreach($result AS $row){
			$ind++;
			if(in_array($row['id'],$closed_ids)){
				$row['closed']=1;
			}else{
				$row['closed']=0;
			}
			$row['summary']=str_replace("\\n", ",", $row['summary']);

			$Expenses[$ind]=$row;
// die( $row['summary']);
		}
		$sql2= "SELECT * FROM expense_type ORDER BY type ASC ";
		$result2 = $this->dbc->get_result($sql2);


		$role=$_SESSION['ROLE_ID'];
		$adminSiteincharge=0;
		$SitePerson=[];
		if($role==1 || $role==2|| $role==5){
			$adminSiteincharge=1;

			$sql3= "SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name)AS SitePerson FROM expenses e INNER JOIN expense_type et ON et.id=e.expense_type LEFT OUTER JOIN users u ON e.site_person =u.id WHERE e.deleted=0 AND e.tender_id =".$_REQUEST['tender_id']  ." ORDER BY u.first_name ASC";
			$SitePerson = $this->dbc->get_result($sql3);

		}


		$data=array("SitePerson"=>$SitePerson,"Expenses"=>$Expenses,"ExpenseType"=>$result2, "admin"=>$admin, "Accounts"=>$Accounts,"adminSiteincharge"=>$adminSiteincharge);
		ajaxResponse("1", $data);

	}

//cc here 
	public function FilterExpenses(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		// if(!isset($_REQUEST['expense_type']) || ($_REQUEST['expense_type'] == "") ){
		// 	ajaxResponse("0", 'Expense type is null');
		// }

		// if(!isset($_REQUEST['expense_date_range']) || ($_REQUEST['expense_date_range'] == "") ){
		// 	ajaxResponse("0", 'Expense date range is null');
		// }

		$tender_id=$_REQUEST['tender_id'];

		$SitePerson=$_REQUEST['SitePerson'];
		$expense_type=$_REQUEST['expense_type'];
		// $expense_date_range=$_REQUEST['expense_date_range'];
		$startDate=$_REQUEST['startDate'];
		$endDate=$_REQUEST['endDate'];
		$AddQry0="";
		$AddQry1="";
		$AddQry2="";
		$AddQry3="";

		if($SitePerson!=""){
			$AddQry0=" AND e.site_person=". $SitePerson . " ";
		}
		if($expense_type!=""){
			$AddQry1=" AND e.expense_type=". $expense_type . " ";
		}
		if($startDate!=""){
			$AddQry2=" AND e.report_date>='". $startDate."' ";
		}
		if($endDate!=""){
			$AddQry3=" AND e.report_date<='". $endDate."' ";
		}


		$sql0= "SELECT GROUP_CONCAT(expense_ids) AS expense_ids FROM  expenses_voucher  WHERE tender_id='$tender_id' AND deleted=0";
		// echo $sql0;
		$result0 = $this->dbc->get_result($sql0);
		$closed_ids=explode(',',$result0[0]['expense_ids']);

		
		$User_id=$_SESSION['USER_ID'];
		$ROLE_ID=$_SESSION['ROLE_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($ROLE_ID==1 ){
			$admin=1;
		}

		$sql= "SELECT e.id,e.summary,e.report_date,e.image,e.amount,et.type AS expense_type, et.id AS expense_type_id, CONCAT(u.first_name, ' ', u.last_name)AS site_person FROM expenses e INNER JOIN expense_type et ON et.id=e.expense_type LEFT OUTER JOIN users u ON e.site_person =u.id  WHERE e.deleted=0 AND e.tender_id =".$_REQUEST['tender_id']. " ".  $AddQry0.  $AddQry1 .$AddQry2 .$AddQry3. " ORDER BY e.id DESC";
		
		// echo $sql;
		$result = $this->dbc->get_result($sql);
		$ind=-1;
		$Expenses=[];
		foreach($result AS $row){
			$ind++;
			if(in_array($row['id'],$closed_ids)){
				$row['closed']=1;
			}else{
				$row['closed']=0;
			}
			$row['summary']=str_replace("\\n", "nl2br,", $row['summary']);

			$Expenses[$ind]=$row;
		}

		$role=$_SESSION['ROLE_ID'];
		$adminSiteincharge=0;
		if($role==1 || $role==2|| $role==5){
			$adminSiteincharge=1;
		}



		

		$data=array("Expenses"=>$Expenses, "admin"=>$admin, "adminSiteincharge"=>$adminSiteincharge);
		ajaxResponse("1", $data);

	}

	
	public function getExpense_Id(){

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$sql= "SELECT * FROM expenses WHERE id= =".$_REQUEST['id'];
		$result = $this->dbc->get_result($sql);

		$url=url();
		$data=array("Expenses"=>$result,"url"=>$url);
		ajaxResponse("1", $data);


	}

	public function getExpenseType(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
				
		$sql= "SELECT * FROM expense_type ORDER BY type";
		$result = $this->dbc->get_result($sql);

		$sql1= "SELECT SiteIncharge, SiteSupervisor FROM tenders WHERE id=".$_REQUEST['tender_id'];
		$result1 = $this->dbc->get_result($sql1);
		$SiteSupervisor=$result1[0]['SiteSupervisor'];
		$SiteIncharge=$result1[0]['SiteIncharge'];

		$sql2= "SELECT id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE id IN ($SiteSupervisor)";
		$result2 = $this->dbc->get_result($sql2);

		$sql22= "SELECT id, CONCAT(first_name,' ',last_name) AS name FROM users WHERE id IN ($SiteIncharge)";
		$result22 = $this->dbc->get_result($sql22);


		$role=$_SESSION['ROLE_ID'];
		$adminSiteincharge=0;
		if($role==1 || $role==2 ){
			$adminSiteincharge=1;
		}



		$data=array("ExpenseType"=>$result,"SiteSupervisor"=>$result2,"SiteIncharge"=>$result22,"adminSiteincharge"=>$adminSiteincharge);

		ajaxResponse("1", $data);
	}


	public function AddNewExpenses(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		if(!isset($_REQUEST['expense_date']) || ($_REQUEST['expense_date'] == "") ){
			ajaxResponse("0", 'Expense date is null');
		}
		if(!isset($_REQUEST['expense_type']) || ($_REQUEST['expense_type'] == "") ){
			ajaxResponse("0", 'Expense type is null');
		}
		if(!isset($_REQUEST['expense_amount']) || ($_REQUEST['expense_amount'] == "") ){
			ajaxResponse("0", 'Expense amount is null');
		}
		if(!isset($_REQUEST['expense_summary']) || ($_REQUEST['expense_summary'] == "") ){
			ajaxResponse("0", 'Expense summary is null');
		}
		// if(!isset($_REQUEST['expense_SiteSupervisor']) || ($_REQUEST['expense_SiteSupervisor'] == "") ){
		// 	ajaxResponse("0", ' SiteSupervisor is null');
		// }
	
		$sql= "SELECT id FROM expenses ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		if(isset($result[0]['id']))$id=$result[0]['id'];
		else $id=0;
		$id++;


		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];

		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["report_date"]=$_REQUEST['expense_date'];
		$data["expense_type"]=$_REQUEST['expense_type'];
		$data["amount"]=$_REQUEST['expense_amount'];
		
		if(isset($_REQUEST['expense_site_person']) && $_REQUEST['expense_site_person']!='' &&  $_REQUEST['expense_site_person']!=0){
			$data["site_person"]=$_REQUEST['expense_site_person'];
		}else{
			$data["site_person"]=$_SESSION['USER_ID'];
		}

		$data["summary"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['expense_summary']));
		
		// $folder="UploadDoc/";
		$folder="UploadDoc/".$WorkOrderNo."/Expense/";

		if(isset($_FILES['UploadExpenses']['name']) && $_FILES['UploadExpenses']['name']!=''){
			$info_1 = pathinfo($_FILES['UploadExpenses']['name']);
			$ext_1 = $info_1['extension']; 
			$target_1 =  $folder. 'UploadExpensesBills_'.$id.'.'.$ext_1;
			// move_uploaded_file( $_FILES['UploadExpenses']['tmp_name'], $target_1);
			$data['image']=$target_1;
		}



		if(!isset($_REQUEST['save']) || ($_REQUEST['save'] == "") ){
			ajaxResponse("0", 'Action is null');
		}

		if($_REQUEST['save']=='add'){
			$data["created_by"]=$_SESSION['USER_ID'];
			$data["created_date_time"]=date('Y-m-d H:i:s');

			$result=$this->dbc->insert_query($data,"expenses");


			$getTenderQry = "SELECT TenderName FROM tenders WHERE id=".$_REQUEST['tender_id'];
	 		$getTenderData = $this->dbc->get_result($getTenderQry);

	 		$ExpenseTypeQry = "SELECT type FROM expense_type WHERE id=".$_REQUEST['expense_type'];
	 		$ExpenseTypeData = $this->dbc->get_result($ExpenseTypeQry);

			$is_admin_qry="SELECT id, first_name, last_name, email FROM users WHERE is_admin=1 LIMIT 1" ;
	 		$IsAdminData = $this->dbc->get_result($is_admin_qry);

			$to=$IsAdminData[0]['email'];
			$name=$IsAdminData[0]['first_name'].' ' .$IsAdminData[0]['last_name'];

			$subject="New Expense   ";
			$body="<h4> Dear ".$name ."</h4>";
			$body.='New Expense is generated against : <br/><br/>';
			$body.='Tender Name : '.$getTenderData[0]['TenderName'].' <br/>';
			$body.='Expense Type  : '.$ExpenseTypeData[0]['type'].' <br/>';
			$body.='Expense Amount : '.$_REQUEST['expense_amount'].' <br/>';
			$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/><br/>';
			$body.='Thank you. <br/>';
			$body.='--<br/>';
			$body.='Via Web ERP <br/>';

			$host = SITE_NAME;
			$from = SITE_USER;
			$password = SITE_PASS;
			$port = SITE_PORT;

			$Common=new CommonFunction($this->dbc);
			$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$target_1);


		}else if($_REQUEST['save']=='update'){

			if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
				ajaxResponse("0", 'id is null');
			}

			$dataDelete_id=array();
			$dataDelete_id["id"]=$_REQUEST['id'];
			$result=1;
			$this->dbc->update_query($data,'expenses', $dataDelete_id);
		}
		if($result>=1){
			if(isset($_FILES['UploadExpenses']['name']) && $_FILES['UploadExpenses']['name']!=''){
				move_uploaded_file( $_FILES['UploadExpenses']['tmp_name'], $target_1);
			}
			ajaxResponse("1", 'Record Updated Successfully');
		}else{
			ajaxResponse("0", 'Unable to insert the record');
		}
	}


	public function DeleteExpense(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		// die();
		$dataDelete=array();
		$dataDelete["deleted"]=1;
		$dataDelete["deleted_by"]=$_SESSION['USER_ID'];
		$dataDelete["deleted_date"]=date("Y-m-d");
		$dataDelete["deleted_date_time"]=date('Y-m-d H:i:s');

// print_r($dataDelete);die();
		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($dataDelete,'expenses', $dataDelete_id);
		$this->unlinkImage('expenses',$_REQUEST['id']);
		ajaxResponse("1", '');

	}


	public function SelectVoucherForBills(){
		if(!isset($_REQUEST['ids']) || ($_REQUEST['ids'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$ids=implode(",", $_REQUEST['ids']);

		$tender_id=$_REQUEST['tender_id'];


		$sql="SELECT COUNT( DISTINCT site_person) AS Count FROM expenses WHERE id IN  (".$ids.")";
		// echo $sql;
		$result = $this->dbc->get_result($sql);
		if($result[0]['Count']>1){
			ajaxResponse("2", "Please select the same person to create voucher");
		}else{

			$sql2_1= "SELECT SUM(amount) AS TotalAmount FROM expenses WHERE id IN (".$ids.")";
			$result2_1 = $this->dbc->get_result($sql2_1);

			$sql0= "SELECT WorkOrderNo, TenderName FROM  tenders WHERE id='$tender_id'";
			$result0 = $this->dbc->get_result($sql0);
			$TenderCode=$result0[0]['WorkOrderNo'];
			$TenderName=$result0[0]['TenderName'];

			$sql1= "SELECT id FROM expenses_voucher ORDER BY id DESC LIMIT 1";
			$result1 = $this->dbc->get_result($sql1);
			$id=$result1[0]['id'];
			$id++;


			$Today= date('Ymd');
			$BillNo=$TenderCode.'/'.str_pad($id, 4, '0', STR_PAD_LEFT).'/'.$Today;

			$data=array( "voucher_ids"=>$ids, "BillNo"=>$BillNo,"TotalAmount"=>$result2_1[0]['TotalAmount'], "TenderName"=>$TenderName);
			ajaxResponse("1", $data);
		}
	}

	public function AddNewVoucherBills(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		if(!isset($_REQUEST['voucher_ids']) || ($_REQUEST['voucher_ids'] == "") ){
			ajaxResponse("0", 'Voucher ids is null');
		}
		if(!isset($_REQUEST['voucher_bill_number']) || ($_REQUEST['voucher_bill_number'] == "") ){
			ajaxResponse("0", 'Voucher bill number is null');
		}
		if(!isset($_REQUEST['voucher_billdate']) || ($_REQUEST['voucher_billdate'] == "") ){
			ajaxResponse("0", 'Voucher bill date is null');
		}
		if(!isset($_REQUEST['voucher_billAmount']) || ($_REQUEST['voucher_billAmount'] == "") ){
			ajaxResponse("0", 'Voucher bill amount is null');
		}
		if(!isset($_REQUEST['voucher_billdescription']) || ($_REQUEST['voucher_billdescription'] == "") ){
			ajaxResponse("0", 'Voucher bill description is null');
		}

		$sql="SELECT COUNT( DISTINCT site_supervisor) AS Count FROM expenses WHERE id IN  (".$_REQUEST['voucher_ids'].")";
		$result = $this->dbc->get_result($sql);
		if($result[0]['Count']>1){
			ajaxResponse("2", "Please select the same person to create voucher");
		}else{
			$sql0="SELECT site_supervisor AS paid_to FROM expenses WHERE id IN  (".$_REQUEST['voucher_ids'].") LIMIT 1";
			$result0 = $this->dbc->get_result($sql0);
			
		}





		$sql= "SELECT id FROM expenses_voucher ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		if(isset($result[0]['id']))$id=$result[0]['id'];
		else $id=0;
		$id++;

		// print_r($_FILES); die();
		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];

		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["paid_to"]=$result0[0]['paid_to'];
		$data["expense_ids"]=$_REQUEST['voucher_ids'];
		$data["expenses_number"]=$_REQUEST['voucher_bill_number'];
		$data["expenses_date"]=$_REQUEST['voucher_billdate'];
		$data["expenses_amount"]=$_REQUEST['voucher_billAmount'];
		// $data["expenses_description"]=$_REQUEST['voucher_billdescription'];
		$data["expenses_description"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['voucher_billdescription'])));

		$data["created_by"]=$_SESSION['USER_ID'];
		$data["created_date"]=date("Y-m-d");
		// $data["created_date_time"]=date("Y-m-d H:i:s");

		// $folder="UploadDoc/";
		// $folder="UploadDoc/".$WorkOrderNo."/Expense/";

		// if(isset($_FILES['UploadVoucher_Bills']['name']) && $_FILES['UploadVoucher_Bills']['name']!=''){
		// 	$info_1 = pathinfo($_FILES['UploadVoucher_Bills']['name']);
		// 	$ext_1 = $info_1['extension']; 
		// 	$target_1 =  $folder. 'UploadVoucher_Bills_'.$id.'.'.$ext_1;
		// 	// move_uploaded_file( $_FILES['UploadVoucher_Bills']['tmp_name'], $target_1);
		// 	$data['image']=$target_1;
		// }

			
		$result=$this->dbc->insert_query($data,"expenses_voucher");
		if($result>=1){
			if(isset($_FILES['UploadVoucher_Bills']['name']) && $_FILES['UploadVoucher_Bills']['name']!=''){
				move_uploaded_file( $_FILES['UploadVoucher_Bills']['tmp_name'], $target_1);
			}
			ajaxResponse("1", '');
		}else{
			ajaxResponse("0", 'Unable to insert the record');

		}



	}

	public function SelectCreatedVouchers(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$tender_id=$_REQUEST['tender_id'];
		$sql= "SELECT ev.* FROM expenses_voucher ev  WHERE ev.tender_id='$tender_id' AND ev.deleted=0 ORDER BY id  DESC  ";
		$result = $this->dbc->get_result($sql);

		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}

		$data=array("CreatedVoucher"=>$result, "admin"=>$admin);
		ajaxResponse("1", $data);

	}

	


	public function DeleteVoucher(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}
		// die();
		$dataDelete=array();
		$dataDelete["deleted"]=1;
		$dataDelete["deleted_by"]=$_SESSION['USER_ID'];
		$dataDelete["deleted_date"]=date("Y-m-d");
		$dataDelete["deleted_date_time"]=date('Y-m-d H:i:s');

// print_r($dataDelete);die();
		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($dataDelete,'expenses_voucher', $dataDelete_id);
		ajaxResponse("1", '');

	}

	public function viewVouchers_Id(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['voucher_ids']) || ($_REQUEST['voucher_ids'] == "") ){
			ajaxResponse("0", 'Voucher id is null');
		}

		$voucher_ids=$_REQUEST['voucher_ids'];
		$sql= "SELECT e.id,e.summary,e.report_date,e.image,e.amount,et.type AS expense_type, et.id AS expense_type_id FROM expenses e INNER JOIN expense_type et ON et.id=e.expense_type WHERE e.deleted=0 AND e.id IN (".$voucher_ids.")";
		$result = $this->dbc->get_result($sql);

		$sql1= "SELECT  SUM(e.amount) AS TotalAmount FROM expenses e INNER JOIN expense_type et ON et.id=e.expense_type WHERE e.deleted=0 AND e.id IN (".$voucher_ids.") ";
		$result1 = $this->dbc->get_result($sql1);
		$TotalAmount=$result1[0]['TotalAmount'];

		$CF= new CommonFunction();
		$InWords=$CF->getIndianCurrency($TotalAmount);

		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}

		// $sql2= "SELECT id, name FROM vendors WHERE deleted=0";
		// $result2 = $this->dbc->get_result($sql2);

		// $tender_id=$_REQUEST['tender_id'];
		// $sql0= "SELECT  TenderName FROM  tenders WHERE id='$tender_id'";
		// $result0 = $this->dbc->get_result($sql0);
		// $TenderName=$result0[0]['TenderName'];
		
		$id=$_REQUEST['id'];
		$sql0= "SELECT  t.TenderName, v.expenses_date,v.expenses_number, CONCAT(u.first_name, ' ', u.last_name) AS SiteIncharge, CONCAT(u2.first_name, ' ', u2.last_name) AS PaidTo FROM expenses_voucher v INNER JOIN  tenders t ON v.tender_id=t.id INNER JOIN users u ON  t.SiteIncharge=u.id LEFT OUTER JOIN users u2 ON  v.paid_to=u2.id WHERE v.id='$id'";
		// echo $sql0;
		$result0 = $this->dbc->get_result($sql0);
		$TenderName=$result0[0]['TenderName'];
		$VoucherDate=$result0[0]['expenses_date'];
		$VoucherNumber=$result0[0]['expenses_number'];
		$SiteIncharge=$result0[0]['SiteIncharge'];
		$PaidTo=$result0[0]['PaidTo'];

		$data=array("Expenses"=>$result, "admin"=>$admin, "TotalAmount"=>$TotalAmount,"TenderName"=>$TenderName,"VoucherDate"=>$VoucherDate, "VoucherNumber"=>$VoucherNumber, "SiteIncharge"=>$SiteIncharge, "PaidTo"=>$PaidTo, "InWords"=>$InWords);
		ajaxResponse("1", $data);
	}


	public function addBOQItem(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}


		if(!isset($_REQUEST['boq_item']) || ($_REQUEST['boq_item'] == "") ){
			ajaxResponse("0", 'Item is null');
		}
		if(!isset($_REQUEST['boq_description']) || ($_REQUEST['boq_description'] == "") ){
			ajaxResponse("0", 'Description is null');
		}
		if(!isset($_REQUEST['boq_total_qty']) || ($_REQUEST['boq_total_qty'] == "") ){
			ajaxResponse("0", 'Total quantity is null');
		}
		if(!isset($_REQUEST['boq_unit']) || ($_REQUEST['boq_unit'] == "") ){
			ajaxResponse("0", 'Unit is null');
		}
		if(!isset($_REQUEST['boq_rate']) || ($_REQUEST['boq_rate'] == "") ){
			ajaxResponse("0", 'Amount is null');
		}


		// print_r($_FILES); die();
		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];

		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["item"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['boq_item']));
		// $data["description"]=$_REQUEST['boq_description'];
		$data["description"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['boq_description'])));

		$data["total_qty"]=$_REQUEST['boq_total_qty'];
		// $data["unit"]=$_REQUEST['boq_unit'];
		$data["unit"]=$_REQUEST['boq_unit_name'];
		$data["rate"]=$_REQUEST['boq_rate'];
		$data["amount"]=$_REQUEST['boq_rate']*$_REQUEST['boq_total_qty'];
		$data["created_by"]=$_SESSION['USER_ID'];
		$data["created_date"]=date('Y-m-d H:i:s');
	
			
		$result=$this->dbc->insert_query($data,"tender_boq_excel");
		if($result>=1){
			ajaxResponse("1", 'Record Inserted Successfully');
		}else{
			ajaxResponse("0", 'Unable to insert the record');

		}


	}

	public function addBOQExcel(){


		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		
		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];
		
		$folder='UploadDoc/'.$WorkOrderNo."/tenderDocs/";
		$User_id=$_SESSION['USER_ID'];
		$Today=date('Y-m-d H:i:s');
		$Tender_id=$_REQUEST['tender_id'];
		if(isset($_FILES['UploadBOQ']['name']) && $_FILES['UploadBOQ']['name']!=''){

			$info_4 = pathinfo($_FILES['UploadBOQ']['name']);

			$ext_4 = $info_4['extension']; 
			$target_4 =  $folder. 'UploadBOQ_'.$Tender_id.'.'.$ext_4;
			$CmFn= new CommonFunction();

			$ExcelFile=$CmFn->readExcelSheet($_FILES['UploadBOQ']['tmp_name']);
			// die('here');

			$sheetData = $ExcelFile->getActiveSheet()->toArray(null,true,true,true);

			// die('here');

			// print_r($sheetData);
			// die('here5');

			$start=0;// Ignore First row
			if($sheetData[1]['B']=='ITEM' && $sheetData[1]['C']=='DESCRITPTION' && $sheetData[1]['D']=='TOTAL QTY' && $sheetData[1]['E']=='RATE (Rs.)' && $sheetData[1]['F']=='UNIT' && $sheetData[1]['G']=='AMOUNT (Rs.)'){
				
				
				move_uploaded_file($_FILES['UploadBOQ']['tmp_name'], $target_4);
				$dataUpdate=array();
				$dataUpdate["UploadBOQ"]=$target_4;
				$dataUpdateId=array();
				$dataUpdateId["id"]=$Tender_id;
				$this->dbc->update_query($dataUpdate,"tender_boq_excel",$dataUpdateId);



				$dataDelete=array();
				$dataDelete["deleted"]=1;
				$dataDelete["deleted_by"]=$User_id;
				$dataDelete["deleted_date"]=$Today;
				$dataDeleteId=array();
				$dataDeleteId["tender_id"]=$Tender_id;
				$this->dbc->update_query($dataDelete,"tender_boq_excel",$dataDeleteId);
				foreach ($sheetData as $row) {
					if ($start++ == 0) continue;
					if($row['B']!='' && $row['C']!='' && $row['D']!='' && $row['E']!='' && $row['F']!='' && $row['G']!=''){

						$data=array();
						$data["tender_id"]=$Tender_id;
						$data["item"]=$row['B'];
						// $data["description"]= str_replace( "'", '', $row['C']);
						$desc = preg_replace('/[^[:print:]]/', '', $row['C']);
						$desc_new = str_replace( "'", '', $desc);

						$data["description"]= $desc_new;
						$rounded=round((float)$row['D']);
						// die($rounded);
						$data["total_qty"]=$rounded;
						$data["rate"]=$row['E'];
						$data["unit"]=$row['F'];
						$data["amount"]= str_replace( ',', '', $row['G']) ;
						$data["created_by"]=$User_id;
						$data["created_date"]=$Today;

						$this->dbc->insert_query($data,"tender_boq_excel");


					}
				}
				
			}else{
				ajaxResponse("2", "Excel is not in the required format");
			}


		}
		ajaxResponse("1", "Excel uploaded Successfully");


	}


	public function fetchTenderID(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$id=$_REQUEST['id'];
		$sql="SELECT t.*, CONCAT(u.first_name, ' ',u.last_name )AS SiteIncharge ,  d.name AS Department, IFNULL(t.ContractDepositAmount,0) AS ContractDeposit FROM `tenders` t INNER JOIN users u ON t.SiteIncharge=u.id INNER JOIN users uss ON t.SiteSupervisor=uss.id INNER JOIN department d ON d.id=t.BMCDepartment WHERE t.id='$id' ";

		$result=$this->dbc->get_array($sql);
		$SiteSupervisor=$result['SiteSupervisor'];

		$sql2="SELECT GROUP_CONCAT(' ',first_name, ' ' ,last_name) AS user_name  FROM users WHERE id IN($SiteSupervisor) ";
		// echo $sql2;
		$result2=$this->dbc->get_array($sql2);
		$result['SiteSupervisor']=$result2['user_name'];

		$SiteEngineer=$result['SiteEngineer'];
		$sql2_1="SELECT GROUP_CONCAT(' ',first_name, ' ' ,last_name) AS user_name  FROM users WHERE id IN($SiteEngineer) ";
		$result2_1=$this->dbc->get_array($sql2_1);
		if(isset($result2_1['user_name'])) $result['SiteEngineer']=$result2_1['user_name'];
		else $result['SiteEngineer']='-';
		

		$sql6="SELECT a.*,b.item,b.total_qty ,b.unit, 
			((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalRequestedQty, 

			((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalConfirmedQty,  

			((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalReceivedQty,

			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE po_request_id=a.id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE po_request_id=a.id)))AS remaining_qty,

			CONCAT( SUBSTRING(b.description, 1, 50) , ' ...')AS description, 
			CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort 
			FROM po_request a 
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			WHERE a.status=0 AND a.tender_id=".$id;


			
		$result6 = $this->dbc->get_result($sql6);
		// print_r($result6);
		// // $result='';
		// $result6='';
		// $data=array("Tender"=>$result,"BOQ"=>$result5,"RequestedMaterial"=>$result6);
		$data=array("Tender"=>$result,"RequestedMaterial"=>$result6);
		ajaxResponse("1", $data);
	}

	public function DocumentsNCategory(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$tender_id=$_REQUEST['tender_id'];
		$sql="SELECT * FROM document_category";
		$result = $this->dbc->get_result($sql);

		$sql2="SELECT d.*, CONCAT(u.first_name,' ',u.last_name)AS userName, c.name AS category_type,d.id AS id
				FROM document d 
				INNER JOIN users u ON d.created_by=u.id
				INNER JOIN document_category c ON c.id=d.category
				WHERE d.deleted=0 AND d.tender_id=".$tender_id ." ORDER BY d.id DESC";

		$result2 = $this->dbc->get_result($sql2);

		$url=url();
		$data=array("Category"=>$result,"Documents"=>$result2,"url"=>$url);
		ajaxResponse("1", $data);

	}

	public function DocumentsCategory_Id(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$tender_id=$_REQUEST['tender_id'];

		$qry='';
		if($_REQUEST['category']!=''){
			$qry=' AND d.category='.$_REQUEST['category'];
		}

		$sql2="SELECT d.*, CONCAT(u.first_name,' ',u.last_name)AS userName, c.name AS category_type,d.id AS id
				FROM document d 
				INNER JOIN users u ON d.created_by=u.id
				INNER JOIN document_category c ON c.id=d.category
				WHERE d.deleted=0 AND d.tender_id=".$tender_id . $qry ." ORDER BY d.id DESC";

		$result2 = $this->dbc->get_result($sql2);

		$data=array("Documents"=>$result2);
		ajaxResponse("1", $data);

	}

	public function AddNewDocument(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}




		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["category"]=$_REQUEST['document_category'];
		$data["tag1"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['document_tag1']));
		$data["tag2"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['document_tag2']));
		
		$data["created_by"]=$_SESSION['USER_ID'];
		$data["created_date"]=date('Y-m-d');
		$data["created_date_time"]=date('Y-m-d H:i:s');
		

		// $folder="UploadDoc/";

		$sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		$result2 = $this->dbc->get_result($sql2);
		$WorkOrderNo=$result2[0]['WorkOrderNo'];
		$folder="UploadDoc/".$WorkOrderNo."/tenderDocs/";

		$sql= "SELECT id FROM document ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		if(isset($result[0]['id']))$id=$result[0]['id'];
		else $id=0;
		$id++;

		// print_r($_FILES['UploadDocument']['type']);

		if(isset($_FILES['UploadDocument']['name']) && $_FILES['UploadDocument']['name']!=''){
			$info_1 = pathinfo($_FILES['UploadDocument']['name']);
			$ext_1 = $info_1['extension']; 
			$target_1 =  $folder. 'Document_'.$_REQUEST['tender_id'].'_'.$_REQUEST['document_category'].'_'.$id.'.'.$ext_1;
			// move_uploaded_file( $_FILES['UploadDocument']['tmp_name'], $target_1);
			$data['image']=$target_1;

			$FileSize=round(($_FILES['UploadDocument']['size']/1000),2);
			$data['size']=$FileSize .'KB';

			$file_type2=explode("/",$_FILES['UploadDocument']['type']);
			$file_type=explode(".",$file_type2[1]);
			$FileType=end($file_type);
			$data['file_type']=$FileType;
		}
		// print_r($_FILES['UploadDocument']);

		$result= $this->dbc->insert_query($data,"document");
		if($result>=1){
			if(isset($_FILES['UploadDocument']['name']) && $_FILES['UploadDocument']['name']!=''){
				move_uploaded_file( $_FILES['UploadDocument']['tmp_name'], $target_1);
			}
			ajaxResponse("1", '');
		}else{
			ajaxResponse("0", 'Unable to insert the record');

		}

	}


	public function Documents_Id(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'Document id is null');
		}
		$id=$_REQUEST['id'];
		$sql= "SELECT id,tender_id,category,created_date, image FROM  document  WHERE id=".$id;
		$result = $this->dbc->get_result($sql);
		$url=url();
		$file=$url.$result[0]['image'];
		$fileName='Doc_'. $result[0]['tender_id'].'_'.$result[0]['category'].$result[0]['id'];
		$data=array("file"=>$file,"fileName"=>$fileName);
		ajaxResponse("1", $data);
	}

	public function RotateImage(){
		if(!isset($_REQUEST['degree']) || ($_REQUEST['degree'] == "") ){
			ajaxResponse("0", 'Degree is null');
		}
		if(!isset($_REQUEST['Image_Id']) || ($_REQUEST['Image_Id'] == "") ){
			ajaxResponse("0", 'Image_Id is null');
		}
		if(!isset($_REQUEST['table']) || ($_REQUEST['table'] == "") ){
			ajaxResponse("0", 'Table is null');
		}

		$degrees=$_REQUEST['degree'];
		// $src=$_REQUEST['src'];
		
		$Image_Id=$_REQUEST['Image_Id'];
		$table=$_REQUEST['table'];
		$src='';
		if($table=='Chellan'){
			$sql= "SELECT id,challan_image FROM tender_challan  WHERE id=".$Image_Id;
			// echo $sql;
			$result = $this->dbc->get_result($sql);
			$src=$result[0]['challan_image'];
		}else if($table=='Expense'){
			$sql= "SELECT id,image FROM expenses  WHERE id=".$Image_Id;
			$result = $this->dbc->get_result($sql);
			$src=$result[0]['image'];
		}
			

		$filename = $src ;
		// $filename = '../img/uploads/' . $post->getimagepath();

		// Content type
		// header('Content-type: image/jpeg');

		// Load

		// die($src);
		$source = imagecreatefromjpeg($filename);

		// Rotate
		$rotate = imagerotate($source, $degrees, 0);

		imagejpeg($rotate, realpath($src));
		// Free the memory
		imagedestroy($source);
		imagedestroy($rotate);

		ajaxResponse("1", $src);


	}

	public function RotateImagePOPover(){
		if(!isset($_REQUEST['degree']) || ($_REQUEST['degree'] == "") ){
			ajaxResponse("0", 'Degree is null');
		}
		if(!isset($_REQUEST['src']) || ($_REQUEST['src'] == "") ){
			ajaxResponse("0", 'Table is null');
		}

		$degrees=$_REQUEST['degree'];
		$src=$_REQUEST['src'];
		
		$filename = $src ;
		$source = imagecreatefromjpeg($filename);
		$rotate = imagerotate($source, $degrees, 0);
		imagejpeg($rotate, realpath($src));
		imagedestroy($source);
		imagedestroy($rotate);
		// die($src);
		ajaxResponse("1", $src);

	}

	public function RotateImage_dummy(){
		if(!isset($_REQUEST['degree']) || ($_REQUEST['degree'] == "") ){
			ajaxResponse("0", 'Degree is null');
		}
		if(!isset($_REQUEST['Image_Id']) || ($_REQUEST['Image_Id'] == "") ){
			ajaxResponse("0", 'Image_Id is null');
		}
		if(!isset($_REQUEST['table']) || ($_REQUEST['table'] == "") ){
			ajaxResponse("0", 'Table is null');
		}

		$degrees=$_REQUEST['degree'];
		// $src=$_REQUEST['src'];
		
		$Image_Id=$_REQUEST['Image_Id'];
		$table=$_REQUEST['table'];
		$src='';
		if($table='Chellan'){
			$sql= "SELECT id,challan_image FROM tender_challan  WHERE id=".$Image_Id;
			// echo $sql;
			$result = $this->dbc->get_result($sql);
			$src=$result[0]['challan_image'];
		}
			

		$filename = $src;
		// $filename = '../img/uploads/' . $post->getimagepath();

		// Content type
		// header('Content-type: image/jpeg');

		// Load
		// die($filename);
		$fileLocation='/web/tenderm/UploadDoc/3456/Challans/UploadChallan_198.jpeg';

		// die();
		// $source = imagecreatefromjpeg($fileLocation);
		// print_r($source);
		// die();

		// Rotate
		$rotate = imagerotate($filename, $degrees, 0);

		imagejpeg($rotate, realpath($src));
		// Free the memory
		imagedestroy($filename);
		imagedestroy($rotate);

		ajaxResponse("1", $src);


	}

	// public function RotateImage(){
	// 	if(!isset($_REQUEST['degree']) || ($_REQUEST['degree'] == "") ){
	// 		ajaxResponse("0", 'Degree is null');
	// 	}
	// 	if(!isset($_REQUEST['Image_Id']) || ($_REQUEST['Image_Id'] == "") ){
	// 		ajaxResponse("0", 'Image_Id is null');
	// 	}
	// 	if(!isset($_REQUEST['table']) || ($_REQUEST['table'] == "") ){
	// 		ajaxResponse("0", 'Table is null');
	// 	}

	// 	$degrees=$_REQUEST['degree'];
	// 	// $src=$_REQUEST['src'];
		
	// 	$Image_Id=$_REQUEST['Image_Id'];
	// 	$table=$_REQUEST['table'];
	// 	$src='';
	// 	if($table='Chellan'){
	// 		$sql= "SELECT id,challan_image FROM tender_challan  WHERE id=".$Image_Id;
	// 		// echo $sql;
	// 		$result = $this->dbc->get_result($sql);
	// 		$src=$result[0]['challan_image'];
	// 	}
			

	// 	// $filename = $src;
	// 	$source = imagecreatefromjpeg($src);
	// 	$rotate = imagerotate($source, $degrees, 0);
	// 	imagejpeg($rotate, $src);
	// 	ajaxResponse("1", $src);


	// }





	public function DeleteConfirmedRequest(){
		// if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
		// 	ajaxResponse("0", 'Tender challan is null');
		// }

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'Id is null');
		}
		if(!isset($_REQUEST['fileLoc']) || ($_REQUEST['fileLoc'] == "") ){
			ajaxResponse("0", 'File Not Found');
		}

		

		$PoSQL="SELECT r.po_number,r.vendor,v.company_name,v.email,v.name,v.address FROM po_request r INNER JOIN vendors v ON v.id=r.vendor WHERE  r.id=".$_REQUEST['id'];
		$POData = $this->dbc->get_result($PoSQL);

		$to=$POData[0]['email'];
		$name=$POData[0]['name'];
		$PoNumber=$POData[0]['po_number'];

		$subject="PO cancelled -- ".$PoNumber;
		$body="<h4> Dear ".$name ."</h4>";
		$body.='Please note that following purchase order is cancelled by Dev Engineers.<br/><br/>';
		$body.='PO NO : '.$PoNumber.' <br/><br/>';
		$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/><br/>';
		$body.='Thank you. <br/>';
		$body.='--<br/>';
		$body.='Via Web ERP <br/>';

		$host = SITE_NAME;
		$from = SITE_USER;
		$password = SITE_PASS;
		$port = SITE_PORT;

		$Common=new CommonFunction($this->dbc);
		$Mail=$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);

		



		$file=$_REQUEST['fileLoc'];
		if(is_file($file)) {
			unlink($file);
		}

		$dataDelete=array();
		$dataDelete["status"]=0;
		$dataDelete["purchase"]=0;
		$dataDelete["reject"]=0;
		$dataDelete["vendor"]=0;
		$dataDelete["pdf_location"]='';
		$dataDelete["total_quantity"]=0;
		$dataDelete["total_amount"]=0;
	
		$dataDelete["updated_by"]=$_SESSION['USER_ID'];
		$dataDelete["updated_date"]=date("Y-m-d H:i:s");

		$dataDelete_id=array();
		$dataDelete_id["id"]=$_REQUEST['id'];



		$DeleteData2=array();
		$DeleteData2["quantity_confirmed"]=0;
		$DeleteData2_id=array();
		$DeleteData2_id["po_request_id"]=$_REQUEST['id'];

		


		$this->dbc->update_query($dataDelete, 'po_request', $dataDelete_id);
		$this->dbc->update_query($DeleteData2, 'po_request_materials', $DeleteData2_id);
		$this->dbc->update_query($DeleteData2, 'po_request_materials_additional', $DeleteData2_id);


		

		if($Mail==1){
			ajaxResponse("1", 'Updated Successfully');
		}else{
			ajaxResponse("0", 'Error in send mail');
		}

	}


	public function Approve_BOQsPO(){
		if(!isset($_REQUEST['Po_id']) || ($_REQUEST['Po_id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}

		$dataUpdate=array();
		$dataUpdate["approved"]=1;
		$dataUpdate["updated_by"]=$_SESSION['USER_ID'];
		$dataUpdate["updated_date"]=date("Y-m-d");

		$dataUpdate_id=array();
		$dataUpdate_id["id"]=$_REQUEST['Po_id'];
		$res = $this->dbc->update_query($dataUpdate, 'po_request', $dataUpdate_id);
		ajaxResponse("1", 'Record Approved Successfully');

	}

	public function Reject_BOQsPO(){
		if(!isset($_REQUEST['Po_id']) || ($_REQUEST['Po_id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}

		$dataUpdate=array();
		$dataUpdate["status"]=1;
		$dataUpdate["reject"]=1;
		$dataUpdate["updated_by"]=$_SESSION['USER_ID'];
		$dataUpdate["updated_date"]=date("Y-m-d");

		$dataUpdate_id=array();
		$dataUpdate_id["id"]=$_REQUEST['Po_id'];
		$res = $this->dbc->update_query($dataUpdate, 'po_request', $dataUpdate_id);
		ajaxResponse("1", 'Record Rejected Successfully');

	}


	public function unconfirmed_POs(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$id=$_REQUEST['tender_id'];


		$User_id=$_SESSION['USER_ID'];

		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}


		$sql= "SELECT a.*, CAST(a.created_date AS date) AS created_date, CAST(a.required_by AS date) AS required_by, b.item,b.id AS boq_id,b.total_qty,b.unit,
		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))AS TotalRequestedQty,

		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))AS TotalConfirmedQty,

		((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=po_request_id ) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=po_request_id ))AS TotalReceivedQty,

		(((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=po_request_id ) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=po_request_id )))AS pending_qty,

		 
		 CONCAT( SUBSTRING(b.description, 1, 250) , ' ...')AS description,
		 CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort ,
		 CONCAT(u.first_name, ' ', u.last_name) AS OrderedBy
			FROM po_request a
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			INNER JOIN users u ON  a.created_by=u.id
			WHERE  b.deleted=0 AND  a.tender_id=".$id." AND  a.approved=0 AND a.status=0 AND a.reject=0 ORDER BY a.id DESC";

			    // echo $sql;
		$result = $this->dbc->get_result($sql);

		$ind=-1;
		$BOQ=$result;

		if(isset($result[0])){
			$data=array("BOQ"=>$BOQ, "TenderId"=>$id, "BOQ_Id"=>$id, "admin"=>$admin );
		}else{
			$data=array( "TenderId"=>$id ,"BOQ_Id"=>$id, "admin"=>$admin );
		}
		ajaxResponse("1", $data);
	}

	public function updateReceivedQuantity(){
		
		$dataUpdate=array();
		$dataUpdate_id=array();
		$dataUpdate["received_quantity"]=$_REQUEST['newQuantity'];
		$dataUpdate_id["id"]=$_REQUEST['id'];

		$result = $this->dbc->update_query($dataUpdate, 'po_request', $dataUpdate_id);

		$data=array();
		$data["po_request_id"]=$_REQUEST['id'];
		$data["received_quantity"]=$_REQUEST['quantity'];
		$data["new_received_quantity"]=$_REQUEST['newQuantity'];
		$data["difference"]=$_REQUEST['newQuantity']-$_REQUEST['quantity'];
		$data["created_by"]=$_SESSION['USER_ID'];
		$data["created_date"]=date('Y-m-d');
		$data["created_date_time"]=date('Y-m-d H:i:s');
		
		$result= $this->dbc->insert_query($data,"po_request_received_qty");
		ajaxResponse("1", 'Record Modified Successfully');
	}



	public function ArunS1(){ //SelectACtive_POs
		$sql = "SELECT mt.id AS mt_id ,mt.name AS mt_name, mst.id AS mst_id, mst.name AS mst_name FROM material_type mt LEFT JOIN material_sub_type mst ON mst.material_type_id=mt.id ORDER BY mt.name ASC , mst.name ASC";
                
        $result = $this->dbc->get_result($sql);

		$materialType = [];
		foreach ($result as $row) {
		    if (!isset($materialType[$row['mt_id']])) {
		        $materialType[$row['mt_id']] = [
		            'id' => $row['mt_id'],
		            'name' => $row['mt_name'],
		            'sub' => []
		        ];
		    }

		    $materialType[$row['mt_id']]['sub'][] = [
		        'mst_id' => $row['mst_id'],
		        'mst_name' => $row['mst_name']
		    ];
		}

		// Re-index the array to start from 0
		$materialType = array_values($materialType);

		$data=array("materialType"=>$materialType);
		ajaxResponse("1", $data);


	}

	public function ArunS2(){
		$sql = "SELECT mt.id AS mt_id ,mt.name AS mt_name, mst.id AS mst_id, mst.name AS mst_name FROM material_type mt LEFT JOIN material_sub_type mst ON mst.material_type_id=mt.id ORDER BY mt.name ASC , mst.name ASC";
                
        $result = $this->dbc->get_result($sql);

        $ind=-1;
        $material_type=[];
        $materialType=[];
        foreach ($result as $row) {
            if(!in_array($row['mt_name'], $material_type )){
                $ind++;
                $materialType[$ind]['id'] =$row['mt_id'];
                $materialType[$ind]['name']=$row['mt_name'];
                $materialType[$ind]['sub']=[];
                array_push($material_type, $row['mt_name']);
            }
            $key = array_search($row['mt_name'], $material_type);
            array_push($materialType[$key]['sub'], [
              'mst_id' => $row['mst_id'],
              'mst_name' => $row['mst_name']
            ]);
            // array_push($materialType[$key]['sub'], $row);

        }

		$data=array("materialType"=>$materialType,"material_type"=>$material_type);
		ajaxResponse("1", $data);


	}

	public function unlinkImage($tbl_name,$id, $image='image'){
		$sql = "SELECT '$image' AS image FROM  $tbl_name WHERE id=$id";
		// die($sql);
        $result = $this->dbc->get_result($sql);
		$file =$result[0]['image'];
		if(is_file($file)) {
			unlink($file);
		}
	}

	public function unlinkProgressImage($id){

		$sql = "SELECT image FROM progress_image WHERE progress_id=$id";
        $result = $this->dbc->get_result($sql);
		foreach ($result as $row) {
			$file =$row['image'];
			if(is_file($file)) {
				unlink($file);
			}
		}


	}





	public function GalleryView(){
		$qry='';
		if(!$_SESSION ['ROLE']=='Admin '){
			$qry=' AND p.created_by ='.$_SESSION ['USER_ID'];
		}
		$tender_id=$_REQUEST['tender_id'];
		$Limit=25;
		// $sql= "SELECT p.id, DATE_FORMAT(p.date, '%d-%m-%Y') as date,pi.id as Image_Id, image FROM progress p RIGHT JOIN progress_image pi ON pi.progress_id=p.id WHERE  p.tender_id=".$tender_id .$qry ." ORDER BY p.date DESC" ;
		// echo $sql;

		$sql="SELECT p.id, 
		       	CASE
		         	WHEN YEAR(p.date) <> YEAR(CURRENT_DATE) THEN DATE_FORMAT(p.date, '%d-%b-%y')
		         	ELSE DATE_FORMAT(p.date, '%d-%b')
		       	END AS date, pi.id AS Image_Id, image FROM progress p
				RIGHT JOIN progress_image pi ON pi.progress_id = p.id
				WHERE p.deleted=0 AND pi.deleted=0 AND p.tender_id =  ".$tender_id .$qry ." 
				ORDER BY p.date  DESC ";
		// echo $sql;

		$result = $this->dbc->get_result($sql);

		$DateArray = [];
		foreach ($result as $row) {
		    if (!isset($DateArray[$row['date']])) {
		        $DateArray[$row['date']] = [
		            'date' => $row['date'],
		            'img' => []
		        ];
		    }

		    $DateArray[$row['date']]['img'][] = [
		        'src' => $row['image'],
		    ];
		}
		$GalleryView=array_values($DateArray);
		$trimmedGalleryView = array_slice($GalleryView, 0, $Limit);
		$data=array("GalleryView"=>$trimmedGalleryView);
		ajaxResponse("1", $data);

	}

	public function EditProgressDate(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}
		$table_name='progress';

		$UpdateData=array();
		$UpdateData["date"]=$_REQUEST['EditDateProgress'];
		$UpdateData_id=array();
		$UpdateData_id["id"]=$_REQUEST['id'];
		$res = $this->dbc->update_query($UpdateData, $table_name, $UpdateData_id);


		$data=array();
		$data["modified_table"]=$table_name;
		$data["modified_id"]=$_REQUEST['id'];
		$data["modified_feild"]='date';
		$data["old_value"]=$_REQUEST['old_date'];
		$data["new_value"]=$_REQUEST['EditDateProgress'];
		$data["modified_by"]=$_SESSION['USER_ID'];
		$data["modified_date"]=date('Y-m-d H:i:s');
		$res = $this->dbc->insert_query($data, 'user_log');

		ajaxResponse("1", '');
	}

	public function EditChallanDate(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}
		$table_name='tender_challan';

		$UpdateData=array();
		$UpdateData["challan_date"]=$_REQUEST['EditDateChallan'];
		$UpdateData_id=array();
		$UpdateData_id["id"]=$_REQUEST['id'];
		$res = $this->dbc->update_query($UpdateData, $table_name, $UpdateData_id);


		$data=array();
		$data["modified_table"]=$table_name;
		$data["modified_id"]=$_REQUEST['id'];
		$data["modified_feild"]='challan_date';
		$data["old_value"]=$_REQUEST['old_date'];
		$data["new_value"]=$_REQUEST['EditDateChallan'];
		$data["modified_by"]=$_SESSION['USER_ID'];
		$data["modified_date"]=date('Y-m-d H:i:s');
		$res = $this->dbc->insert_query($data, 'user_log');

		ajaxResponse("1", '');
	}

	public function EditExpensesDate(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}
		$table_name='expenses';

		$UpdateData=array();
		$UpdateData["report_date"]=$_REQUEST['EditDateExpenses'];
		$UpdateData_id=array();
		$UpdateData_id["id"]=$_REQUEST['id'];
		$res = $this->dbc->update_query($UpdateData, $table_name, $UpdateData_id);


		$data=array();
		$data["modified_table"]=$table_name;
		$data["modified_id"]=$_REQUEST['id'];
		$data["modified_feild"]='report_date';
		$data["old_value"]=$_REQUEST['old_date'];
		$data["new_value"]=$_REQUEST['EditDateExpenses'];
		$data["modified_by"]=$_SESSION['USER_ID'];
		$data["modified_date"]=date('Y-m-d H:i:s');
		$res = $this->dbc->insert_query($data, 'user_log');

		ajaxResponse("1", '');
	}

	
	public function EditLabourBillDate(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'challan is null');
		}
		$table_name='tender_labour_bills';

		$UpdateData=array();
		$UpdateData["bill_date"]=$_REQUEST['EditDateLabourBill'];
		$UpdateData_id=array();
		$UpdateData_id["id"]=$_REQUEST['id'];
		$res = $this->dbc->update_query($UpdateData, $table_name, $UpdateData_id);


		$data=array();
		$data["modified_table"]=$table_name;
		$data["modified_id"]=$_REQUEST['id'];
		$data["modified_feild"]='bill_date';
		$data["old_value"]=$_REQUEST['old_date'];
		$data["new_value"]=$_REQUEST['EditDateLabourBill'];
		$data["modified_by"]=$_SESSION['USER_ID'];
		$data["modified_date"]=date('Y-m-d H:i:s');
		$res = $this->dbc->insert_query($data, 'user_log');

		ajaxResponse("1", '');
	}

	
	public function ByPass_GetBOQ(){
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender_id is null');
		}


		$sql= "SELECT a.id, a.item,a.total_qty,a.rate,a.unit,

			((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS RequestedQuantity,
			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ConfirmedQuantity,
			((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ReceivedQuantity,

			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id))) AS PendingQuantity

			FROM tender_boq_excel a
				INNER JOIN tenders b ON b.id= a.tender_id 
				WHERE a.deleted=0 AND a.tender_id=" . $_REQUEST['tender_id'];

				// echo $sql;
				// die();

		$result = $this->dbc->get_result($sql);

		$BOQ=[];
		$ind=-1;
		foreach ($result as $row) {
			$ind++;
			$boq_id= $row['id'];
			$sql2_0= "SELECT total_qty,CONCAT( SUBSTRING(description, 1, 25) , ' ...') AS DescShort, description
			FROM tender_boq_excel  WHERE id=".$boq_id;
			$result2_0 = $this->dbc->get_result($sql2_0);

			$total_qty=$result2_0[0]['total_qty'];
			
			// $row['remaining_qty']=$result2_0[0]['total_qty']-$row['ConfirmedQuantity'];
			$row['remaining_qty']=$result2_0[0]['total_qty']-$row['ReceivedQuantity'];
			$row['pending_qty']="0";

			$desc = $result2_0[0]['description'];
			$row['description']= $desc;

			$shortdesc =  $result2_0[0]['DescShort'];
			$row['DescShort']= $shortdesc;

			//check for zero remaining quantity
			if($row['remaining_qty']<=0){
				$ind--;
				continue;
			}
			$BOQ[$ind]=$row;
			// print_r($row);
			// echo "<br><br>";
		}

		$data=array("BOQ"=>$BOQ,"tender_id"=>$_REQUEST['tender_id']);
		ajaxResponse("1", $data);
	}

	
	public function DeletePORequest(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}
		
		$sql="DELETE FROM po_request WHERE id=".$_REQUEST['id'];
		$res = $this->dbc->_query($sql);

		$sql="DELETE FROM po_request_materials WHERE po_request_id=".$_REQUEST['id'];
		$res = $this->dbc->_query($sql);

		$sql="DELETE FROM po_request_materials_additional WHERE po_request_id=".$_REQUEST['id'];
		$res = $this->dbc->_query($sql);

		$data=array();
		$data["modified_table"]='po_request';
		$data["modified_id"]=$_REQUEST['id'];
		$data["modified_feild"]='Deleted Record';
		$data["modified_by"]=$_SESSION['USER_ID'];
		$data["modified_date"]=date('Y-m-d H:i:s');
		$res = $this->dbc->insert_query($data, 'user_log');

		ajaxResponse("1", '');
	}

	public function getUnits(){
		$sql= "SELECT * FROM units  ORDER BY unit";
		$result = $this->dbc->get_result($sql);
		$data=array("Units"=>$result);
		ajaxResponse("1", $data);
	}




}

?>



