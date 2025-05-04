<?php
// require_once("TCPDF/tcpdf_include.php");

class BOQ {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";

		
	}

	public function BOQList(){

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

		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];

		if($Role_id==1){
			$AddQry="";
		}else if($Role_id==2){
			$AddQry=" AND b.SiteIncharge=".$User_id." " ;
		}else if($Role_id==3){
			$AddQry=" AND b.SiteSupervisor=".$User_id." " ;
		}

		$sql= "SELECT a.*, CONCAT( SUBSTRING(a.description, 1, 25) , ' ...')AS DescShort,
			(SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=boq_id) AS RequestedQuantity,
			(SELECT SUM(quantity_confirmed) FROM po_request_materials WHERE a.id=boq_id) AS ConfirmedQuantity, 
			(SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=boq_id) AS ReceivedQuantity,
			((SELECT SUM(quantity_confirmed) FROM po_request_materials WHERE a.id=boq_id)-(SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=boq_id)) AS PendingQuantity
			FROM tender_boq_excel a
				INNER JOIN tenders b ON b.id= a.tender_id 
				WHERE a.tender_id='$id' AND a.deleted=0" . $AddQry;

		$result = $this->dbc->get_result($sql);
		$BOQ=[];
		$ind=-1;
		foreach ($result as $row) {
			$ind++;
			$boq_id= $row['id'];
			$sql2_0= "SELECT total_qty  FROM tender_boq_excel WHERE id='$boq_id'";
			$result2_0 = $this->dbc->get_result($sql2_0);

			$total_qty=$result2_0[0]['total_qty'];
			
			// $row['remaining_qty']=$result2_0[0]['total_qty']-$row['ConfirmedQuantity'];
			$row['remaining_qty']=$result2_0[0]['total_qty']-$row['ReceivedQuantity'];
			$row['pending_qty']="0";
			$desc = preg_replace('/[^[:print:]]/', '', $row['description']);
			$row['description']= $desc;

			$shortdesc = preg_replace('/[^[:print:]]/', '', $row['DescShort']);
			$row['DescShort']= $shortdesc;

			if($row['remaining_qty']>0){
				$BOQ[$ind]=$row;
			}

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



		$sql001= "SELECT role FROM users WHERE id='$User_id' ";
		$result001 = $this->dbc->get_result($sql001);
		$role=$result001[0]['role'];

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

	public function DeleteBOQ_ID(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$data=array();
		$data['deleted']=1;
		$data['deleted_by']=$_SESSION['USER_ID'];
		$data['deleted_date']=date('Y-m-d H:i:s');

		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($data, 'tender_boq_excel', $data_id);
		ajaxResponse("1", '');




	}

	public function Reject_BOQ_Id(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data=array();
		// $data['rejected_by']=$User_id;
		// $data['rejected_date']=$Today;
		$data['reject']=1;
		$data['purchase']=0;
		$data['status']=1;


		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($data, 'po_request', $data_id);

		$getBoqQry = "SELECT d.first_name,d.last_name,d.email,c.TenderName, a.boq_id AS boq_id, c.id AS tender_id ,b.item FROM po_request a
						INNER JOIN tender_boq_excel b ON a.boq_id=b.id
						INNER JOIN tenders c ON b.tender_id=c.id
						INNER JOIN users d ON a.created_by=d.id
						WHERE a.id=".$_REQUEST['id'];
		$boqData = $this->dbc->get_result($getBoqQry);



		$StdMaterials_Qry="SELECT mt.name AS material_type,mst.name as material_sub_type, pom.unit_name,pom.quantity_requested FROM po_request_materials pom
			INNER JOIN material_type mt ON pom.material_type_id=mt.id
			INNER JOIN material_sub_type mst ON pom.material_sub_type_id=mst.id
			WHERE po_request_id=". $_REQUEST['id'];
		$StdMaterials = $this->dbc->get_result($StdMaterials_Qry);

		$AddMaterials_Qry="SELECT material_type,material_sub_type, unit_name,quantity_requested FROM po_request_materials_additional WHERE po_request_id=". $_REQUEST['id'];
		$AddMaterials = $this->dbc->get_result($AddMaterials_Qry);

		$table='<table style="border-collapse: collapse;">
					<tr>
						<td style="background-color: black;border: 1px solid #000000; ">
							<span style="color: #fff; text-align: center; font-size:9px;">
								S.No
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px; height: 20px">
							<span style="color: #fff; text-align: center">
								Material Type
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Sub Type
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Unit
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Quantity
							</span>
						</td>
					</tr>';


		$i=0;
		foreach($StdMaterials as $row1){
			$i++;
			$table.='<tr><td  style="border: 1px solid #000000;">'.$i.'</td>
				<td  style="border: 1px solid #000000;">'.$row1['material_type'].'</td>
				<td  style="border: 1px solid #000000;">'.$row1['material_sub_type'].'</td>
				<td  style="border: 1px solid #000000;">'.$row1['unit_name'].'</td>
				<td  style="border: 1px solid #000000;">'.$row1['quantity_requested'].'</td></tr>';
		}
		foreach($AddMaterials as $row2){
			$i++;
			$table.='<tr><td  style="border: 1px solid #000000;">'.$i.'</td>
				<td  style="border: 1px solid #000000;">'.$row2['material_type'].'</td>
				<td  style="border: 1px solid #000000;">'.$row2['material_sub_type'].'</td>
				<td  style="border: 1px solid #000000;">'.$row2['unit_name'].'</td>
				<td  style="border: 1px solid #000000;">'.$row2['quantity_requested'].'</td></tr>';
		}

		$table.="</table>";

		// echo $table;
		// die();

// print_r($boqData[0]['first_name']);die;
		$to=$boqData[0]['email'];
		$name=$boqData[0]['first_name'].' ' .$boqData[0]['last_name'];

		$subject="Request regected. ";
		$body="<h4> Dear ".$name ."</h4>";
		$body.='Your reuest was rejected by dev Engineers. <br/><br/>';
		$body.='Tender Name : '.$boqData[0]['TenderName'].' <br/>';
		$body.='BOQ Item  : '.$boqData[0]['item'].' <br/>';
		$body.='Materials Requested  :  <br/>';
		$body.=$table;
		$body.='<br/>';
		$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/><br/>';
		$body.='Thank you. <br/>';
		$body.='Bhavesh Purohit <br/>';
		$body.='Dev Engineers <br/>';

		$host = SITE_NAME;
		$from = SITE_USER;
		$password = SITE_PASS;
		$port = SITE_PORT;

		$Common=new CommonFunction($this->dbc);
		$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);

		$data=array("BOQ_id"=>$boqData[0]['boq_id'],"tender_id"=>$boqData[0]['tender_id']);
		ajaxResponse("1", $data);

	}


 
	public function SelectBOQ_Id(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'boq_id is null');
		}
		if(!isset($_REQUEST['TenderId']) || ($_REQUEST['TenderId'] == "") ){
			ajaxResponse("0", 'Tender Id is null');
		}

		$boq_id=$_REQUEST['boq_id'];
		$TenderId=$_REQUEST['TenderId'];
		$sql= "SELECT * FROM tender_boq_excel WHERE id='$boq_id'";
		$result = $this->dbc->get_result($sql);

		// $result[0]['description'] = '';

		$sql1= "SELECT * FROM material_type ORDER BY name ASC";
		$result1 = $this->dbc->get_result($sql1);

		


		//	 print_r($result);

		$total_qty=$result[0]['total_qty'];
		// $Tender_id=$result[0]['tender_id'];
		$boq_id=$result[0]['id'];

		$sql3= "SELECT a.id, a.quantity AS before_order_qty, b.quantity AS after_order_qty FROM boq_order a 
			LEFT OUTER JOIN purchase_boq_order b ON b.boq_order_id=a.id WHERE a.boq_id='$boq_id' AND a.rejected=0 ";
		$result3 = $this->dbc->get_result($sql3);
		
		$Used_Qty=0;
		foreach ($result3 as $row3) {
			if(isset($row3['after_order_qty'])){
				$Used_Qty+=$row3['after_order_qty'];
			}else{
				$Used_Qty+=$row3['before_order_qty'];
			}
		}
		$Remaining_Qty=$total_qty-$Used_Qty;

		$User_id=$_SESSION['USER_ID'];

		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}

		$unit_qry= "SELECT * FROM units ";
		$UnitsData = $this->dbc->get_result($unit_qry);

		$data=array("BOQ"=>$result[0],"material_type"=>$result1,"used_qty"=>$Used_Qty,"remaining_qty"=>$Remaining_Qty,"TenderId"=>$TenderId,"admin"=>$admin,"Units"=>$UnitsData);
		ajaxResponse("1", $data);
	}


	public function Select_sub_type(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$id=$_REQUEST['id'];
		$sql= "SELECT id,name FROM material_sub_type WHERE material_type_id='$id'";
		$result = $this->dbc->get_result($sql);

		$data=array("SubType"=>$result);
		ajaxResponse("1", $data);
	}


	public function Select_PO(){
		if(!isset($_REQUEST['Order_id']) || ($_REQUEST['Order_id'] == "") ){
			ajaxResponse("0", 'Order id is null');
		}

		$id=$_REQUEST['Order_id'];
		$sql= "SELECT * FROM tender_boq_excel WHERE id='$id'";
		$result = $this->dbc->get_result($sql);

		$sql2= "SELECT id, name FROM vendors WHERE deleted=0";
		$result2 = $this->dbc->get_result($sql2);

		$data=array("BOQ"=>$result[0],"VendorList"=>$result2);
		ajaxResponse("1", $data);

	}
	
	// public function GetVendorList(){

	// 	$sql2= "SELECT id, name FROM vendors WHERE deleted=0";
	// 	$result2 = $this->dbc->get_result($sql2);

	// 	$data=array("VendorList"=>$result2);
	// 	ajaxResponse("1", $data);

	// }

	

	public function All_Requested_POs(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$User_id=$_SESSION['USER_ID'];

		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}

		$tender_id=$_REQUEST['tender_id'];	

		$sql0= "SELECT id, TenderName FROM tenders WHERE id='$tender_id'";
		$result0 = $this->dbc->get_result($sql0);
		
		
		$sql="SELECT a.*,b.item,b.total_qty ,b.unit,
		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalRequestedQty, 

		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalConfirmedQty,  

		((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalReceivedQty,

		((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE po_request_id=a.id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE po_request_id=a.id)))AS remaining_qty,

		 -- b.description,
		 CONCAT( SUBSTRING(b.description, 1, 250) , ' ...')AS description, 
		 CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort 
			FROM po_request a 
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			WHERE a.status=0 AND b.deleted=0 AND a.tender_id=".$tender_id;
			// echo$sql ;

		$result = $this->dbc->get_result($sql);
		

		$ind=-1;
	

		$BOQ= $result;//Delete this
		// $Tender_Id=$result[0][''];
		
		$data=array("BOQ"=>$BOQ, "TenderId"=>$result0[0]['id'],"TenderName"=>$result0[0]['TenderName'], "BOQ_Id"=>0,"admin"=>$admin  );

		ajaxResponse("1", $data);


	}



	
	public function Select_PO_BOQ_Id(){

		if(!isset($_REQUEST['BOQ_id']) || ($_REQUEST['BOQ_id'] == "") ){
			ajaxResponse("0", 'BOQ id is null');
		}

		if(!isset($_REQUEST['Tender_id']) || ($_REQUEST['Tender_id'] == "") ){
			ajaxResponse("0", 'Tender id id is null');
		}

		$id=$_REQUEST['BOQ_id'];
		$tender_id=$_REQUEST['BOQ_id'];
		$sql= "SELECT b.*, CONCAT( SUBSTRING(c.description, 1, 5) , ' ...')AS DescShort,  c.item, c.description,c.total_qty, a.required_by FROM po_request a RIGHT JOIN po_request_materials b ON a.id=b.po_request_id INNER JOIN tender_boq_excel  c ON b.boq_id=c.id WHERE c.id=".$id; 
 			// INNER JOIN tenders d ON c.tender_id=d.id
 				
 		// echo $sql;
 				// print_r($result);
		// (SELECT SUM(quantity_requested) FROM po_request_materials WHERE po_request_id=a.id) AS TotalOrderedQty
 		$result = $this->dbc->get_result($sql);


 		$TenderQry="SELECT b.TenderName FROM tender_boq_excel a INNER JOIN tenders b ON b.id=a.tender_id WHERE a.id=".$id;
 		$TenderData = $this->dbc->get_result($TenderQry);

		
		$data=array("BOQ_Id"=>$id,"TenderId"=>$_REQUEST['Tender_id'], "Requests"=>$result, "TenderName"=>$TenderData[0]['TenderName']);

		ajaxResponse("1", $data);

	}

	public function Select_PurchaseOrder(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'BOQ id is null');
		}
		if(!isset($_REQUEST['Tender_id']) || ($_REQUEST['Tender_id'] == "") ){
			ajaxResponse("0", 'Tender id id is null');
		}

		$boq_id=$_REQUEST['boq_id'];
		$Tender_id=$_REQUEST['Tender_id'];

		$PO_Qry="SELECT a.*,b.item,b.total_qty,b.unit,(SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)AS TotalRequestedQty, (SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=po_request_id)AS TotalReceivedQty,((SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)-(SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=po_request_id))AS pending_qty, b.description, CONCAT( SUBSTRING(b.description, 1, 5) , ' ...') AS DescShort FROM po_request a INNER JOIN tender_boq_excel b ON b.id=a.boq_id WHERE b.id=".$boq_id . " ORDER BY a.id DESC" ;

 		$PO_Data = $this->dbc->get_result($PO_Qry);

		$TenderQry="SELECT b.TenderName FROM tender_boq_excel a INNER JOIN tenders b ON b.id=a.tender_id WHERE a.id=".$boq_id;

 		$TenderData = $this->dbc->get_result($TenderQry);


 		$User_id=$_SESSION['USER_ID'];

		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;

		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}
		// print_r($PO_Qry);

		// die;



		$data=array("BOQ_Id"=>$boq_id,"TenderId"=>$Tender_id, "Requests"=>$PO_Data, "TenderName"=>$TenderData[0]['TenderName'],"admin"=>$admin);

		ajaxResponse("1", $data);

	}





	public function Purchase_OrderedRequest_old(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		} 

		$id=$_REQUEST['id'];

		$sql= "SELECT a.*,b.total_qty FROM boq_order a 
			    INNER JOIN tender_boq_excel b ON a.boq_id=b.id
			    INNER JOIN tenders c ON a.tender_id=c.id
			    INNER JOIN users d ON a.created_by=d.id
			    WHERE a.id='$id'";
		$result = $this->dbc->get_result($sql);

		$sql2= "SELECT id, name FROM vendors WHERE deleted=0";
		$result2 = $this->dbc->get_result($sql2);

		$sql5= "SELECT * FROM material_type";
		$result5 = $this->dbc->get_result($sql5);

		$sql4= "SELECT * FROM opc_ppc ";
		$result4 = $this->dbc->get_result($sql4);

		
		

		$total_qty=$result[0]['total_qty'];
		// $Tender_id=$result[0]['tender_id'];
		$boq_id=$result[0]['boq_id'];

		$sql3= "SELECT a.id, a.quantity AS before_order_qty, b.quantity AS after_order_qty FROM boq_order a 
			LEFT OUTER JOIN purchase_boq_order b ON b.boq_order_id=a.id WHERE a.boq_id='$boq_id' AND a.rejected=0 ";
		$result3 = $this->dbc->get_result($sql3);
		
		$Used_Qty=0;
		foreach ($result3 as $row3) {
			if(isset($row3['after_order_qty'])){
				$Used_Qty+=$row3['after_order_qty'];
			}else{
				$Used_Qty+=$row3['before_order_qty'];
			}
		}
		$Remaining_Qty=$total_qty-$Used_Qty;



		// $data=array("BOQ"=>$result[0],"VendorList"=>$result2,"material_type"=>$result3,"opc_ppc"=>$result4,"total_qty"=>$total_qty);
		$data=array("BOQ"=>$result[0],"VendorList"=>$result2,"material_type"=>$result5,"opc_ppc"=>$result4,"total_qty"=>$total_qty,"used_qty"=>$Used_Qty,"remaining_qty"=>$Remaining_Qty);

		ajaxResponse("1", $data);
	}

	public function Confirm_BOQs_PO(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['boq_order_id']) || ($_REQUEST['boq_order_id'] == "") ){
			ajaxResponse("0", 'boq order id is null');
		}
		if(!isset($_REQUEST['Quantity']) || ($_REQUEST['Quantity'] == "") ){
			ajaxResponse("0", 'Quantity is null');
		}
		if(!isset($_REQUEST['received_quantity']) || ($_REQUEST['received_quantity'] == "") ){
			ajaxResponse("0", 'Received quantity is null');
		}
		if(!isset($_REQUEST['description_received']) || ($_REQUEST['description_received'] == "") ){
			ajaxResponse("0", 'Description received is null');
		}
		if(!isset($_REQUEST['received_date']) || ($_REQUEST['received_date'] == "") ){
			ajaxResponse("0", 'Received date is null');
		}

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data=array();

		// print_r($_REQUEST);
		$data['purchase_boq_order_id']=$_REQUEST['boq_order_id'];
		
		$data['quantity']=$_REQUEST['Quantity'];
		$data['received_quantity']=$_REQUEST['received_quantity'];
		$data['description']=$_REQUEST['description_received'];
		$data['receiced_date']=$_REQUEST['received_date'];
		$data['received_by']=$User_id;
		$data['created_date']=$Today;
		
		$this->dbc->insert_query($data,"receipt");
		ajaxResponse("1", $_REQUEST['boq_order_id']);

	}


	public function get_purchase_boq_order(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$id=$_REQUEST['id'];

		$sql="SELECT (SELECT SUM(quantity_confirmed) FROM po_request_materials WHERE boq_id=a.boq_id ) as confirmed_quantity, bx.item, bx.total_qty, tdr.TenderName, a.required_by ,(bx.total_qty-(SELECT SUM(quantity_confirmed) FROM po_request_materials WHERE boq_id=a.boq_id )) AS remaining_qty, a.boq_id, usr.first_name, usr.last_name, bx.unit ,a.request_note FROM po_request a INNER JOIN tender_boq_excel bx ON bx.id=a.boq_id INNER JOIN tenders tdr ON tdr.id=bx.tender_id LEFT JOIN users usr ON usr.id=a.created_by WHERE a.id=".$id;

		$result = $this->dbc->get_result($sql);


		$poQry="SELECT id,required_by, description FROM  po_request WHERE id=".$id;
		$PoData = $this->dbc->get_result($poQry);


		
		$sql2= "SELECT id, name FROM vendors WHERE deleted=0";
		$result2 = $this->dbc->get_result($sql2);

		$sql5= "SELECT * FROM material_type";
		$result5 = $this->dbc->get_result($sql5);

		$poData_sql= "SELECT a.id, a.quantity_requested, b.name AS material_type, c.name AS material_sub_type, b.id AS material_type_id, c.id AS material_sub_type_id, a.	quantity_confirmed, a.unit_price, a.unit_name, a.material_description FROM po_request_materials a INNER JOIN material_type b ON b.id=a.material_type_id INNER JOIN material_sub_type c ON c.id=a.material_sub_type_id WHERE a.po_request_id=".$id;
		$porequstMeterials = $this->dbc->get_result($poData_sql);


		$sqlAditional= "SELECT a.id, a.material_type, a.material_sub_type, a.quantity_requested, a.quantity_confirmed, a.unit_price, a.unit_name, a.material_description FROM po_request_materials_additional a  WHERE a.po_request_id=".$id;
				// echo $sql;
		$PO_DataAdd = $this->dbc->get_result($sqlAditional);

		// print_r($porequstMeterials);die;
		

		$total_qty=$result[0]['total_qty'];
		// $Tender_id=$result[0]['tender_id'];
		$boq_id=$result[0]['boq_id'];

		$sql3= "SELECT a.id, a.quantity AS before_order_qty, b.quantity AS after_order_qty FROM boq_order a 
			LEFT OUTER JOIN purchase_boq_order b ON b.boq_order_id=a.id WHERE a.boq_id='$boq_id' AND a.rejected=0 ";
		$result3 = $this->dbc->get_result($sql3);
		
		$Used_Qty=0;



		// $data=array("BOQ"=>$result[0],"VendorList"=>$result2,"material_type"=>$result3,"opc_ppc"=>$result4,"total_qty"=>$total_qty);
		$data=array("BOQ"=>$result[0],"VendorList"=>$result2,"material_type"=>$result5,"porequstMeterials"=> $porequstMeterials,"PO_DataAdd"=> $PO_DataAdd,"PoData"=>$PoData[0] );

		ajaxResponse("1", $data);
	}

	public function generatePDF($data){

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


		// set document information
		$pdf->setCreator(PDF_CREATOR);
		// $pdf->setAuthor('Nicola Asuni');
		// $pdf->setTitle('TCPDF Example 001');
		// $pdf->setSubject('TCPDF Tutorial');
		// $pdf->setKeywords('TCPDF, PDF, example, test, guide');

		 // die( PDF_HEADER_LOGO);
		// set default header data
		$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH);
		// $pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, SITE_HEADER_TITLE);
		// $pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));

		// $pdf->setFooterData(array(0,64,0), array(0,64,128));

		// set header and footer fonts
		// $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		// $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		// $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		// $pdf->setFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		// $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		// if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		// 	require_once(dirname(__FILE__).'/lang/eng.php');
		// 	$pdf->setLanguageArray($l);
		// }

		// ---------------------------------------------------------

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->setFont('dejavusans', '', 12, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// set text shadow effect
		// $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

		// Set some content to print

		// $data['tender_id']=$_REQUEST['tender_id'];
		// $data['boq_id']=$_REQUEST['boq_id'];
		// $data['boq_order_id']=$_REQUEST['boq_order_id'];
		// $data['material_type']=$_REQUEST['material_type'];
		// $data['opc_ppc']=$_REQUEST['opc_ppc'];
		// $data['Quantity']=$_REQUEST['Quantity'];
		// $data['Required_by']=$_REQUEST['Required_by'];
		// $data['Vendor']=$_REQUEST['Vendor'];
		// $data['Rate']=$_REQUEST['Rate'];
		// $data['boq_order_id']=$_REQUEST['boq_order_id'];
		// $data['description']=$_REQUEST['description'];

		


		$html ='
		<table style="width:100%" cellpadding="0" cellspacing="0">
			<tr>
				<td style="width: 8%"></td>
				<td style="width: 37%"></td>
				<td style="width: 5%"></td>
				<td style="width: 13%"></td>
				<td style="width: 8%"></td>
				<td style="width: 16%"></td>
				<td style="width: 12%"></td>
				<td style="width: 10%"></td>
				<td style="width: 14%"></td>
				<td style="width: 12%"></td>
			</tr>
			<tr>
				<td colspan="2">M/S. DEV ENGINEERS,</td>
				<td></td>
				<td></td>
				<td colspan="3" style=" text-align:right">PURCHASE ORDER</td>
			</tr>
			<tr>
				<td colspan="2">
					<br/>
					<br/>
					<span style="font-size: 10px;">307,Jalaram Business Centre,Ganjawala Lane, Borivali (West), Mumbai - 400092.PO NO: PO/3215/kl Maharashtra, India
					</span>
				</td>
				<td></td>
				<td></td>
				<td colspan="3">
					<br/>
					<span style="font-size: 10px; text-align:right">
					<br/>PO NO:  '.$data['boq_order_id'].'
					<br/>PO Date :'.$data['Required_by'].'
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="2" style="background-color: black;border: 1px solid #000000;  height: 20px">
					<span style="color: #fff; text-align: center; display: block; padding: 10px;">
						Vendor
					</span>
				</td>
				<td></td>
				<td colspan="4" style="background-color: black;border: 1px solid #000000; ">
					<span style="color: #fff; text-align: center">
						Deliver To
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-size: 10px">
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%">
								<br/><br/>
								'.$data["vendorAddress"].'
								<br/><br/>Phone: - +91-'.$data["vendorMobile"].'
								<br/>Email: '.$data["vendorEmail"].'
								<br/>
							</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
				<td></td>
				<td colspan="4" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-size: 10px">
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%">
								<br/><br/>Mr. 
								'.$data["SiteIncahare"].'<br/>
								M/S. DEV ENGINEERS,<br/>
								<br/>
								'.$data["SiteAddress"].'<br/>
								<br/>
								Phone: - +91-'.$data["SiteIncahareMobile"].'
								<br/>
								Email: '.$data["SiteIncahareEmail"].'
								<br/>
							</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="2" style="background-color: black;border: 1px solid #000000; ">
					<span style="color: #fff; text-align: center; display: block; padding: 10px;">
						Site
					</span>
				</td>
				<td></td>
				<td colspan="4" style="background-color: black;border: 1px solid #000000; height: 20px">
					<div style="display: block; padding: 10px;">
						<span style="color: #fff; text-align: center;">
							Delivery Terms
						</span>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-size: 10px; text-align: center">
				<br/><br/>
				'.$data["SiteAddress"].'
				<br/>
				</td>
				<td></td>
				<td colspan="4" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-size: 10px; text-align: center">
				<br/><br/>Immediate<br/>
				</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td style="background-color: black;border: 1px solid #000000; ">
					<span style="color: #fff; text-align: center; font-size:9px;">
						#ITEM
					</span>
				</td>
				<td colspan="2" style="background-color: black;border: 1px solid #000000;  font-size:9px; height: 20px">
					<span style="color: #fff; text-align: center">
						Item Description
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Total Qty
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Per
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Unit Price(Rs)
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Total(Rs)
					</span>
				</td>
			</tr>
			<tr>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >1</td>
				<td  style="border: 1px solid #000000; text-align: center; font-size: 10px;"  colspan="2">'.$data["description"].'</td>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >'.$data["Quantity"].'</td>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >'.$data["units"].'</td>
				<td style="border: 1px solid #000000; text-align: right; font-size: 10px;" >
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">'.$data["Rate"].'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
				<td style="border: 1px solid #000000; text-align: right" >
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">'.$data["TotalRate"].'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000; font-size: 10px; text-align: center;">TOTAL</td>
				<td  style="border: 1px solid #000000; font-size: 10px; text-align: center;">'.$data["Quantity"].'</td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000; font-size: 10px; text-align: right;">'.$data["TotalRate"].'</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="7" style="font-size: 10px;">'.$data["InWords"].'</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="3" style="background-color: black;border-left: 1px solid #000000; border-right: 1px solid #000000;  height: 20px">
					<span style="color: #fff; line-height: 5px; text-align: center">Other Comments or Special Instructions
					</span>
				</td>
				<td></td>
				<td></td>
				<td colspan="2">
					<span style="font-size: 10px; text-align: right">
					For <span style="font-weight: bold;">DEV ENGINEERS</span>
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="border-left: 1px solid #000000; border-right: 1px solid #000000;">
					<span style="font-size: 10px"><br/>&nbsp; 1. Prices are exclusive of GST and other taxes.
					</span>
				</td>
				<td></td>
				<td colspan="2"></td>
			</tr>
			<tr>
				<td colspan="3" style="border-left: 1px solid #000000; border-right: 1px solid #000000;">
					<span style="font-size: 10px">&nbsp; 2. Transportation Including at our Mumbai Site.
					</span>
				</td>
				<td></td>
				<td colspan="2"></td>
			</tr>
			<tr>
				<td colspan="3" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000;">
					<span style="font-size: 10px">&nbsp; 3. Delivery immediately.</span><br/>
				</td>
				<td></td>
				<td></td>
				<td colspan="2">
					<span style="font-size: 8px; text-align: right; vertical-align: text-bottom;">
						Authorized Signatory
					</span>
				</td>
			</tr>
		</table>';

		// Print text using writeHTMLCell()
		// $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		$pdf->writeHTML($html, true, false, true, false, '');

		// ---------------------------------------------------------
		ob_end_clean();
		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.

		$sql="SELECT id FROM purchase_boq_order ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		$LastId=$result[0]['id'];
		$LastId++;

		$filename = 'Tender/UploadDoc/PurchaseOrder_'.$LastId.'.pdf';
		$pdf->Output($_SERVER['DOCUMENT_ROOT'] . $filename, 'F');
		return $filename;


		
		// $Serverfilename ='tenderm/UploadDoc/example_'.$data['created_by'].'.pdf';
		// $pdf->Output($_SERVER['DOCUMENT_ROOT'] . $Serverfilename, 'F');
		// return $Serverfilename;


		
	}
// 	public function AddTenderPage(){
// 		$sql1= "SELECT id,user_name AS name FROM users WHERE role=3 AND  deleted=0";
// 		$result1 = $this->dbc->get_result($sql1);
// 		$sql2= "SELECT id,user_name AS name FROM users WHERE role=2 AND  deleted=0";
// 		$result2 = $this->dbc->get_result($sql2);

// // BMCDepartment
// 		$sql3= "SELECT id,user_name AS name FROM users WHERE role=3 AND  deleted=0";
// 		$result3 = $this->dbc->get_result($sql3);


// 		$data=array("SiteIncharge"=>$result1,"SiteSupervisor"=>$result2, "BMCDepartment"=>$result3);



// 		ajaxResponse("1", $data);

// 	}



	public function OrderRequest(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'boq id is null');
		}

		if(!isset($_REQUEST['material_type']) || ($_REQUEST['material_type'] == "") ){
			ajaxResponse("0", 'Material type is null');
		}
		if(!isset($_REQUEST['opc_ppc']) || ($_REQUEST['opc_ppc'] == "") ){
			ajaxResponse("0", 'opc ppc is null');
		}
		if(!isset($_REQUEST['Quantity']) || ($_REQUEST['Quantity'] == "") ){
			ajaxResponse("0", 'Quantity is null');
		}
		if(!isset($_REQUEST['Required_by']) || ($_REQUEST['Required_by'] == "") ){
			ajaxResponse("0", 'Required by is null');
		}

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

 
		$data=array();
		$data['tender_id']=$_REQUEST['tender_id'];
		$data['boq_id']=$_REQUEST['boq_id'];
		$data['material_type']=$_REQUEST['material_type'];
		$data['opc_ppc']=$_REQUEST['opc_ppc'];
		$data['quantity']=$_REQUEST['Quantity'];
		$data['required_by']=$_REQUEST['Required_by'];

		
		// $save=$_REQUEST['save'];
		// if($save=='add'){
			$data['created_by']=$User_id;
			$data['created_date']=$Today;
			$this->dbc->insert_query($data,"boq_order");
		// }else if($save=='update'){
			// $data['updated_by']=$User_id;
			// $data['updated_date']=$Today;

			// $data_id=array();
			// $data_id["id"]=$_REQUEST['id'];
			// $this->dbc->update_query($data, 'tenders', $data_id);
		// }

		$AdminQry= "SELECT * FROM users  WHERE is_admin=1";
		// echo $User_qry;
		$AdminData = $this->dbc->get_result($AdminQry);
		$AdminName=$AdminData[0]['first_name'].' '.$AdminData[0]['last_name'];
		$AdminEmail=$AdminData[0]['email'];



		$User_qry= "SELECT * FROM users  WHERE id ='$User_id' ";
		$UserData = $this->dbc->get_result($User_qry);
		$created_by=$UserData[0]['first_name'].' '.$UserData[0]['last_name'];


		$boq_sql= "SELECT a.item,b.TenderName FROM tender_boq_excel a 
				INNER JOIN tenders b ON a.tender_id=b.id 
				WHERE a.id=1";

				// echo $boq_sql;
		$boqData = $this->dbc->get_result($boq_sql);

		// $data['tender_id']
		// $data['created_by']

		// $created_by=
		// if(isset($UserData[1]['first_name'])){
		// 	$created_by=$UserData[1]['first_name'].' '.$UserData[1]['last_name'];
		// }else{
		// 	$created_by=$UserData[0]['first_name'].' '.$UserData[0]['last_name'];
		// }



		$to=$AdminEmail;
		$subject="New Purchase Order Request.";
		$body="<h4> Dear ".$AdminName .".</h4>";
		$body.='A request was made in by'.$created_by .'. <br/><br/>';
		$body.='Tender Name : '.$boqData[0]['TenderName'].' <br/>';
		$body.='BOQ Item : '.$boqData[0]['item'].' <br/> <br/>';
		// $body.='<br/><br/>';
		$body.='Thank you. <br/>';

		$host = SITE_NAME;
		$from = SITE_USER;
		$password = SITE_PASS;
		$port = SITE_PORT;

		$Common=new CommonFunction($this->dbc);
		$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);

		ajaxResponse("1", $_REQUEST['tender_id']);

	}



	

	public function PurchaseOrderedRequest(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['boq_order_id']) || ($_REQUEST['boq_order_id'] == "") ){
			ajaxResponse("0", 'boq order id is null');
		}

		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'boq id is null');
		}

		if(!isset($_REQUEST['material_type']) || ($_REQUEST['material_type'] == "") ){
			ajaxResponse("0", 'Material type is null');
		}

		if(!isset($_REQUEST['opc_ppc']) || ($_REQUEST['opc_ppc'] == "") ){
			ajaxResponse("0", 'opc ppc is null');
		}

		if(!isset($_REQUEST['Quantity']) || ($_REQUEST['Quantity'] == "") ){
			ajaxResponse("0", 'Quantity is null');
		}

		if(!isset($_REQUEST['Required_by']) || ($_REQUEST['Required_by'] == "") ){
			ajaxResponse("0", 'Required by is null');
		}

		if(!isset($_REQUEST['Vendor']) || ($_REQUEST['Vendor'] == "") ){
			ajaxResponse("0", 'Vendor is null');
		}

		if(!isset($_REQUEST['Rate']) || ($_REQUEST['Rate'] == "") ){
			ajaxResponse("0", 'Rate is null');
		}

		if(!isset($_REQUEST['description']) || ($_REQUEST['description'] == "") ){
			ajaxResponse("0", 'Description is null');
		}

		$CF=new CommonFunction($this->dbc);

		$boq_order_id=$_REQUEST['boq_order_id'];

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data=array();
		$data['tender_id']=$_REQUEST['tender_id'];
		$data['boq_id']=$_REQUEST['boq_id'];
		$data['boq_order_id']=$_REQUEST['boq_order_id'];
		$data['material_type']=$_REQUEST['material_type'];
		$data['opc_ppc']=$_REQUEST['opc_ppc'];
		$data['quantity']=$_REQUEST['Quantity'];
		$data['required_by']=$_REQUEST['Required_by'];
		$data['vendor']=$_REQUEST['Vendor'];
		$data['rate']=$_REQUEST['Rate'];
		$data['description']=$_REQUEST['description'];

		// echo $data['Rate'];


		$data['created_by']=$User_id;
		$data['created_date']=$Today;
		$this->dbc->insert_query($data,"purchase_boq_order");
		$PORequest_id=$this->dbc->insert_id;
		$Insert_Id['id']=$this->dbc->insert_id;
		$data_id=array();
		$data_up=array();
		$data_id["id"]=$boq_order_id;
		$data_up["confirm"]=1;

		$this->dbc->update_query($data_up, 'boq_order', $data_id);

		// $sql= "SELECT * , CONCAT( SUBSTRING(description, 1, 5) , ' ...')AS DescShort FROM tender_boq_excel WHERE tender_id='$id' AND deleted=0";
		// $result = $this->dbc->get_result($sql);
		$sql= "SELECT * FROM vendors  WHERE id=".$_REQUEST['Vendor'];
		$result = $this->dbc->get_result($sql);


		$data["vendorCompany_name"]=$result[0]['company_name'];
		$data["vendorAddress"]=$result[0]['address'];
		$data["vendorEmail"]=$result[0]['email'];
		$data["vendorMobile"]=$result[0]['mobile'];
		$vendorName=$result[0]['name'];


		$sql1= "SELECT a.address,b.user_name,b.email,b.mobile FROM tenders a
				INNER JOIN users b ON a.SiteIncharge=b.id
				  WHERE a.id=".$_REQUEST['tender_id'];
		$result1 = $this->dbc->get_result($sql1);

		$sql2= "SELECT unit  FROM tender_boq_excel  WHERE id=".$_REQUEST['boq_order_id'];
		$result2 = $this->dbc->get_result($sql);


		$data["SiteAddress"]=$result1[0]['address'];
		$data["SiteIncahare"]=$result1[0]['user_name'];
		$data["SiteIncahareEmail"]=$result1[0]['email'];
		$data["SiteIncahareMobile"]=$result1[0]['mobile'];
		// $data['TotalRate']=round($_REQUEST['Quantity']*$_REQUEST['Rate'],2);
		$data['TotalRate']=number_format((float)($_REQUEST['Quantity']*$_REQUEST['Rate']), 2, '.', '');
		$data['Rate']=number_format((float)$_REQUEST['Rate'], 2, '.', '');

		$sql2= "SELECT unit  FROM tender_boq_excel  WHERE id=".$_REQUEST['boq_order_id'];
		// die($sql2);
		$result2 = $this->dbc->get_result($sql2);
		$data['units']=$result2[0]['unit'];

		$data['InWords']=$CF->getIndianCurrency($data['TotalRate']);

	
		$filePath= $this->generatePDF($data);
		$Insert_Data['pdf_location']=$filePath;
		$this->dbc->update_query($Insert_Data, 'purchase_boq_order', $Insert_Id);

		$sql3= "SELECT * FROM tenders WHERE id=".$_REQUEST['tender_id'];
		$result3 = $this->dbc->get_result($sql3);

		$sql4="SELECT * FROM users WHERE is_admin =1";
		$result4 = $this->dbc->get_result($sql4);

		$sql4_1="SELECT * FROM users WHERE id =".$result3[0]['SiteIncharge'];
		// echo $sql4_1;
		$result4_1 = $this->dbc->get_result($sql4_1);

		// die($result4_1[0]['email']);

		$sendMailTo = array(["email" => $result[0]['email'], "first_name"=> $result[0]['name'], "last_name"=> ''],["email" =>$result4[0]['email'], "first_name"=> $result4[0]['first_name'], "last_name"=> $result4[0]['last_name']],["email" =>$result4_1[0]['email'], "first_name"=> $result4_1[0]['first_name'], "last_name"=> $result4_1[0]['last_name']]);

		// $sql5="SELECT * FROM material_type WHERE id=".$_REQUEST['material_type'];
		// $result5 = $this->dbc->get_result($sql5);


		$sql5="SELECT group_concat(b.name, ',') AS materialName   FROM po_request_materials a
				INNER JOIN material_type b ON a.material_type_id=b.id WHERE a.po_request_id=".$PORequest_id;
		$result5 = $this->dbc->get_result($sql5);
		$sql5_1="SELECT group_concat(a.material_type, ',') AS materialName   FROM po_request_materials_additional a
				WHERE a.po_request_id=".$PORequest_id;
		$result5_1 = $this->dbc->get_result($sql5_1);

		$materialName= $result5[0]["materialName"].', '. $result5_1[0]["materialName"];



		$materialName = $result5[0]["name"];

		
		foreach ($sendMailTo as $row) {

			$to=$row['email'];
			$name=$row['first_name'].' ' .$row['last_name'];

			$subject="New Purchase Order Details ";
			$body="<h4> Dear ".$name ."</h4>";
			$body.='Please see the attached purchase order for following material. <br/><br/>';
			$body.='Material : '.$materialName.' <br/>';
			$body.='Vendor : '.$vendorName.' <br/> <br/>';
			$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/>';
			$body.='Thank you. <br/><br/>';
			$body.='Bhavesh Purohit <br/>';
			$body.='Dev Engineers <br/>';

			$host = SITE_NAME;
			$from = SITE_USER;
			$password = SITE_PASS;
			$port = SITE_PORT;

			$Common=new CommonFunction($this->dbc);
			$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$filePath);

		}

		ajaxResponse("1", $_REQUEST['boq_id']);


	}

	public function UpdateTotalQty(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}
		if(!isset($_REQUEST['NewTotalQty']) || ($_REQUEST['NewTotalQty'] == "") ){
			ajaxResponse("0", 'New Total Qty is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'tender_id is null');
		}

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data_id=array();
		$data_up=array();
		$data_id["id"]=$_REQUEST['id'];
		$data_up['total_qty']=$_REQUEST['NewTotalQty'];
		$data_up['updated_by']=$User_id;
		$data_up['updated_date']=date('Y-m-d H:i:s');

		$this->dbc->update_query($data_up, 'tender_boq_excel', $data_id);
		ajaxResponse("1",$_REQUEST['tender_id']);

	}


	public function GetMaterialType(){
		$MaterialTypeQry= "SELECT *  FROM material_type  ORDER BY name ASC";
		$MaterialTypeData = $this->dbc->get_result($MaterialTypeQry);

		$UnitQry= "SELECT *  FROM units ";
		$UnitData = $this->dbc->get_result($UnitQry);

		$data=array("MaterialType"=>$MaterialTypeData,"Units"=>$UnitData);
		ajaxResponse("1", $data);
	}

	public function Units(){
		$UnitQry= "SELECT *  FROM units ";
		$UnitData = $this->dbc->get_result($UnitQry);
		$data=array("Units"=>$UnitData);
		ajaxResponse("1", $data);
	}

	public function GetMaterial_SubType(){
		if(!isset($_REQUEST['Material_Id']) || ($_REQUEST['Material_Id'] == "") ){
			ajaxResponse("0", 'Material Id is null');
		}

		$SubTypeQry= "SELECT *  FROM material_sub_type WHERE material_type_id =".$_REQUEST['Material_Id'];
		$SubTypeData = $this->dbc->get_result($SubTypeQry);
		$data=array("SubType"=>$SubTypeData);
		ajaxResponse("1", $data);

	}


	public function SavePORequest(){
		// die('wait');
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
	
		if(!isset($_REQUEST['Required_by']) || ($_REQUEST['Required_by'] == "") ){
			ajaxResponse("0", 'Required by is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'boq id is null');
		}
	

		$Required_by=$_REQUEST['Required_by'];
		$tender_id=$_REQUEST['tender_id'];
		$boq_id=$_REQUEST['boq_id'];

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");
		$DateTime=date("Y-m-d H:i:s");

		$data=array();
		$data['boq_id']=$_REQUEST['boq_id'];
		$data['tender_id']=$_REQUEST['tender_id'];
		$data['required_by']=$_REQUEST['Required_by'];
		$data['request_note']=preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['request_note']);
		$data['created_by']=$User_id;
		$data['created_date']=$DateTime;
			
		$sql="SELECT * FROM po_request";
		$result1=$this->dbc->get_array($sql);
		// print_r($_REQUEST);die;
		try {
		$this->dbc->insert_query($data,"po_request");
		
		$Insert_Id=$this->dbc->insert_id;
		
		if(isset($_REQUEST['MaterialsOrdered'])){
			$MaterialsOrdered=$_REQUEST['MaterialsOrdered'];
			$c=count($MaterialsOrdered);
			for ($i=1; $i<$c; $i++) { 
				// print_r("D");
				$material_type=$MaterialsOrdered[$i]['material_type'];
				$sub_type=$MaterialsOrdered[$i]['sub_type'];
				$Quantity=$MaterialsOrdered[$i]['Quantity'];
				$unit_id=$MaterialsOrdered[$i]['unit_id'];
				$unit_name=$MaterialsOrdered[$i]['unit_name'];

				$met_data=array();
				$met_data['po_request_id']=$Insert_Id;
				$met_data['boq_id']=$boq_id;
				$met_data['tender_id']=$tender_id;
				$met_data['material_type_id']=$material_type;
				$met_data['material_sub_type_id']=$sub_type;	
				$met_data['quantity_requested']=$Quantity;	
				$met_data['unit_id']=$unit_id;	
				$met_data['unit_name']=$unit_name;	

				$this->dbc->insert_query($met_data,"po_request_materials");
			}
		} 

		if(isset($_REQUEST['AdditionalMaterialsOrdered'])){
			$AdditionalMaterialsOrdered=$_REQUEST['AdditionalMaterialsOrdered'];

			$c1=count($AdditionalMaterialsOrdered);
			for ($j=1; $j<$c1; $j++) { 
				// echo "asdfa";
				$material_type=$AdditionalMaterialsOrdered[$j]['additional_material_type'];
				$sub_type=$AdditionalMaterialsOrdered[$j]['additional_sub_type'];
				$Quantity=$AdditionalMaterialsOrdered[$j]['additional_Quantity'];
				// echo ("material_type : ".$Quantity ."<br>");
				$unit_id=$AdditionalMaterialsOrdered[$j]['additional_unit_id'];
				$unit_name=$AdditionalMaterialsOrdered[$j]['additional_unit_name'];

				$add_met_data=array();
				$add_met_data['po_request_id']=$Insert_Id;
				$add_met_data['boq_id']=$boq_id;
				$add_met_data['tender_id']=$tender_id;
				$add_met_data['material_type']=$material_type;
				$add_met_data['material_sub_type']=$sub_type;	
				$add_met_data['quantity_requested']=$Quantity;	
				$add_met_data['unit_id']=$unit_id;	
				$add_met_data['unit_name']=$unit_name;	

				$this->dbc->insert_query($add_met_data,"po_request_materials_additional");
			}

		}

		$data=array("boq_id"=>$boq_id,"tender_id"=>$tender_id );



		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];

		$AdminQry= "SELECT * FROM users  WHERE is_admin=1";
		$AdminData = $this->dbc->get_result($AdminQry);
		$AdminName=$AdminData[0]['first_name'].' '.$AdminData[0]['last_name'];
		$AdminEmail=$AdminData[0]['email'];


		$User_qry= "SELECT * FROM users  WHERE id ='$User_id' ";
		$UserData = $this->dbc->get_result($User_qry);
		$created_by=$UserData[0]['first_name'].' '.$UserData[0]['last_name'];

		$boq_sql= "SELECT a.TenderName, b.item FROM tenders a INNER JOIN tender_boq_excel b ON a.id=b.tender_id  WHERE b.id=".$boq_id;
		$boqData = $this->dbc->get_result($boq_sql);



		$StdMaterials_Qry="SELECT mt.name AS material_type,mst.name as material_sub_type, pom.unit_name,pom.quantity_requested FROM po_request_materials pom
			INNER JOIN material_type mt ON pom.material_type_id=mt.id
			INNER JOIN material_sub_type mst ON pom.material_sub_type_id=mst.id
			WHERE po_request_id=". $Insert_Id;
		$StdMaterials = $this->dbc->get_result($StdMaterials_Qry);

		$AddMaterials_Qry="SELECT material_type,material_sub_type, unit_name,quantity_requested FROM po_request_materials_additional WHERE po_request_id=". $Insert_Id;
		$AddMaterials = $this->dbc->get_result($AddMaterials_Qry);

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

		$Common=new CommonFunction($this->dbc);
		$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);
		// $Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,'','','','','',$user );

		} catch(Exception $e) {
		    var_dump($e);
		}

		ajaxResponse("1", $data);
	}





	public function ViewRequstedPO(){
		if(!isset($_REQUEST['PORequest_id']) || ($_REQUEST['PORequest_id'] == "") ){
			ajaxResponse("0", 'PORequest id is null');
		}

		$PORequest_id=$_REQUEST['PORequest_id'];

		$sql= "SELECT a.quantity_requested,a.material_description, b.name AS material_type, c.name AS material_sub_type
				FROM po_request_materials a
				INNER JOIN material_type b ON b.id=a.material_type_id
				LEFT OUTER JOIN material_sub_type c ON c.id=a.material_sub_type_id
				WHERE a.po_request_id=".$PORequest_id;
				// echo $sql;
		$PO_Data = $this->dbc->get_result($sql);

		$sql= "SELECT a.* FROM po_request_materials_additional a WHERE a.po_request_id=".$PORequest_id;
				// echo $sql;
		$PO_Data_Add = $this->dbc->get_result($sql);


		$sql2= "SELECT  b.item,c.TenderName
				FROM po_request a
				INNER JOIN tender_boq_excel b ON b.id=a.boq_id
				INNER JOIN tenders c ON b.tender_id=c.id
				WHERE a.id =".$PORequest_id;
		$BOQ_Data = $this->dbc->get_result($sql2);

		$data=array("PO_Data"=>$PO_Data,"PO_Data_Add"=>$PO_Data_Add,"TenderName"=>$BOQ_Data[0]['TenderName'],"item"=>$BOQ_Data[0]['item']);
		ajaxResponse("1", $data);

	}


	public function Select_BOQPurchaseOrder(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
	
		if(!isset($_REQUEST['Tender_id']) || ($_REQUEST['Tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'boq id is null');
		}


		$User_id=$_SESSION['USER_ID'];

		$boq_id=$_REQUEST['boq_id'];
		$Tender_id=$_REQUEST['Tender_id'];


		$PO_Qry="SELECT a.*,b.item,b.total_qty ,b.unit,
		((SELECT IFNULL(SUM(quantity_requested), 0) FROM po_request_materials WHERE po_request_id=a.id)+(SELECT IFNULL(SUM(quantity_requested), 0) FROM po_request_materials_additional WHERE po_request_id=a.id)) AS TotalRequestedQty, 
		((SELECT IFNULL(SUM(quantity_requested), 0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_requested), 0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalConfirmedQty,  
		((SELECT IFNULL(SUM(quantity_received), 0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received), 0) FROM po_request_materials_additional WHERE po_request_id=a.id))AS TotalReceivedQty,

		(((SELECT IFNULL(SUM(quantity_confirmed), 0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_confirmed), 0) FROM po_request_materials_additional WHERE po_request_id=a.id)) - ((SELECT IFNULL(SUM(quantity_received), 0) FROM po_request_materials WHERE po_request_id=a.id) + (SELECT IFNULL(SUM(quantity_received), 0) FROM po_request_materials_additional WHERE po_request_id=a.id)))AS remaining_qty, b.description, CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort 
			FROM po_request a 
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			WHERE a.status=0 AND a.boq_id=".$boq_id;

		// $PO_Qry="SELECT a.*,b.item,b.total_qty,b.unit,(SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)AS TotalRequestedQty, b.description, CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort ,a.status
		// 	FROM po_request a 
		// 	INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
		// 	WHERE a.boq_id=".$boq_id;
// echo $PO_Qry;die;
 		$PO_Data = $this->dbc->get_result($PO_Qry);

		$TenderQry="SELECT b.TenderName FROM tender_boq_excel a INNER JOIN tenders b ON b.id=a.tender_id WHERE a.id=".$boq_id;

 		$TenderData = $this->dbc->get_result($TenderQry);
		// echo $TenderQry;

 		$is_admin_qry="SELECT is_admin FROM users WHERE id=".$User_id;
 		$IsAdminData = $this->dbc->get_result($is_admin_qry);
		// "admin"=>$IsAdminData[0]['is_admin']

		$data=array("BOQ_Id"=>$boq_id,"TenderId"=>$Tender_id, "Requests"=>$PO_Data, "TenderName"=>$TenderData[0]['TenderName'],"admin"=>$IsAdminData[0]['is_admin']);

		ajaxResponse("1", $data);
	}



	public function Purchase_OrderedRequest(){
		if(!isset($_REQUEST['PORequest_id']) || ($_REQUEST['PORequest_id'] == "") ){
			ajaxResponse("0", 'PORequest id is null');
		}

		$PORequest_id=$_REQUEST['PORequest_id'];

		$sql= "SELECT a.id AS row_id,a.unit_name, a.quantity_requested, b.name AS material_type, IFNULL(c.name, '') AS material_sub_type, b.id AS material_type_id, c.id AS material_sub_type_id FROM po_request_materials a INNER JOIN material_type b ON b.id=a.material_type_id LEFT OUTER JOIN material_sub_type c ON c.id=a.material_sub_type_id WHERE a.po_request_id=".$PORequest_id;
				// echo $sql;
		$PO_Data = $this->dbc->get_result($sql);

		$sqlAditional= "SELECT id AS row_id , material_type, material_sub_type,unit_name, quantity_requested FROM po_request_materials_additional a  WHERE a.po_request_id=".$PORequest_id ;
				// echo $sql;
		$PO_DataAdd = $this->dbc->get_result($sqlAditional);

		$sql2= "SELECT bx.id AS boq_id, tdr.id AS tender_id, a.created_by, CONCAT(u.first_name, ' ', u.last_name) AS created_user,  (SELECT SUM(quantity_confirmed) FROM po_request_materials WHERE boq_id=a.boq_id ) as confirmed_quantity, bx.item, bx.total_qty, tdr.TenderName, a.required_by ,(bx.total_qty-(SELECT SUM(quantity_confirmed) FROM po_request_materials WHERE boq_id=a.boq_id )) AS remaining_qty, bx.unit,a.request_note FROM po_request a INNER JOIN tender_boq_excel bx ON bx.id=a.boq_id INNER JOIN tenders tdr ON tdr.id=bx.tender_id INNER JOIN users u ON a.created_by=u.id WHERE a.id=".$PORequest_id;
				// echo $sql2;

		$BOQ_Data = $this->dbc->get_result($sql2);

		$vendorQry= "SELECT id, company_name AS name FROM vendors WHERE  deleted=0 ORDER BY company_name ASC ";
		$vendorData = $this->dbc->get_result($vendorQry);

		$data=array("BOQ"=>$BOQ_Data[0],"PO_Data"=>$PO_Data,"PO_DataAdd"=>$PO_DataAdd,"TenderName"=>$BOQ_Data[0]['TenderName'],"item"=>$BOQ_Data[0]['item'],"required_by"=>$BOQ_Data[0]['required_by'],"vendor"=>$vendorData);
		ajaxResponse("1", $data);
	}

	public function SelectVendor(){

		$sql2= "SELECT id, company_name AS name FROM vendors WHERE deleted=0 ORDER BY company_name ASC ";
		$vendorData = $this->dbc->get_result($sql2);
		$data=array("vendor"=>$vendorData);
		ajaxResponse("1", $data);

	}

	public function Save_ConfirmRequestedPO(){


		if(!isset($_REQUEST['Required_by']) || ($_REQUEST['Required_by'] == "") ){
			ajaxResponse("0", 'Required by is null');
		}
		if(!isset($_REQUEST['PORequest_id']) || ($_REQUEST['PORequest_id'] == "") ){
			ajaxResponse("0", 'PORequest id is null');
		}
		if(!isset($_REQUEST['vendor']) || ($_REQUEST['vendor'] == "") ){
			ajaxResponse("0", 'vendor is null');
		}
		if(!isset($_REQUEST['TotalQuantity']) || ($_REQUEST['TotalQuantity'] == "") ){
			ajaxResponse("0", 'Total Quantity is null');
		}
		if(!isset($_REQUEST['TotalAmount']) || ($_REQUEST['TotalAmount'] == "") ){
			ajaxResponse("0", 'Total Amount is null');
		}
		if(!isset($_REQUEST['description']) || ($_REQUEST['description'] == "") ){
			ajaxResponse("0", 'description is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		if(!isset($_REQUEST['boq_id']) || ($_REQUEST['boq_id'] == "") ){
			ajaxResponse("0", 'boq id is null');
		}

		// echo $_REQUEST['MaterialsOrdered_add'][1]['row_id']; die;
		// print_r($_REQUEST['MaterialsOrdered_add']); die;

		$Required_by=$_REQUEST['Required_by'];
		$PORequest_id=$_REQUEST['PORequest_id'];



		$data_PORequest=array();
		// $data_PORequest["rate"]=$_REQUEST['Rate'];
	
		$data_PORequest["vendor"]=$_REQUEST['vendor'];
		$data_PORequest["total_quantity"]=$_REQUEST['TotalQuantity'];
		$data_PORequest["total_amount"]=$_REQUEST['TotalAmount'];
		// $data_PORequest["description"]=$_REQUEST['description'];
		$data["description"]=preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['description']);

		$data_PORequest["purchase"]=1;
		$data_PORequest["status"]=1;

		$data_PORequest_id=array();
		$data_PORequest_id["id"]=$PORequest_id;


		$this->dbc->update_query($data_PORequest, 'po_request', $data_PORequest_id);

		if(isset($_REQUEST['MaterialsOrdered'])){
			$MaterialsOrdered=$_REQUEST['MaterialsOrdered'];

			$c=count($MaterialsOrdered);
			for ($i=1; $i<$c; $i++) { 
				// print_r("D");
				$dataQty=array();
				$dataQty['quantity_confirmed']=$MaterialsOrdered[$i]['Quantity'];
				$dataQty['unit_price']=$MaterialsOrdered[$i]['unit_price'];
				$dataQty['material_description']=$MaterialsOrdered[$i]['material_description'];
				// $dataQty['unit_name']=$MaterialsOrdered[$i]['unit_name'];
				// echo $MaterialsOrdered[$i]['material_description'];
				$data_id=array();
				$data_id["id"]=$MaterialsOrdered[$i]['row_id'];
				$this->dbc->update_query($dataQty, 'po_request_materials', $data_id);
			}
		}

		if(isset($_REQUEST['MaterialsOrdered_add'])){
			$MaterialsOrdered_add=$_REQUEST['MaterialsOrdered_add'];

			$c2=count($MaterialsOrdered_add);
			for ($i=1; $i<$c2; $i++) { 
				// print_r("D");
				$dataQty_add=array();
				$dataQty_add['quantity_confirmed']=$MaterialsOrdered_add[$i]['Quantity'];
				$dataQty_add['unit_price']=$MaterialsOrdered_add[$i]['unit_price'];
				$dataQty_add['material_description']=$MaterialsOrdered_add[$i]['material_description'];
				// $dataQty_add['unit_name']=$MaterialsOrdered_add[$i]['unit_name'];
				// echo $MaterialsOrdered_add[$i]['material_description'] ;
				$data_id_add=array();
				$data_id_add["id"]=$MaterialsOrdered_add[$i]['row_id'];
				$this->dbc->update_query($dataQty_add, 'po_request_materials_additional', $data_id_add);
			}
		}



		// die();	
		$data=array();
		$data["tender_id"]=$_REQUEST['tender_id'];
		$data["boq_id"]=$_REQUEST['boq_id'];
		$vendor_id=$_REQUEST['vendor'];
		$sql= "SELECT * FROM vendors  WHERE id=".$vendor_id;
		$result = $this->dbc->get_result($sql);
		$data["vendorAddress"]=$result[0]['address'];
		$data["vendorEmail"]=$result[0]['email'];
		$data["vendorMobile"]=$result[0]['mobile'];
		$data["vendorName"]=$result[0]['name'];
		$data["vendorCompanyName"]=$result[0]['company_name'];
		$vendorName=$result[0]['name'];

		$data["description"]=$_REQUEST['description'];
		# $data["description"]=preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['description']);


		$data['TotalRate']=$_REQUEST['TotalAmount'];

		// echo $data['TotalRate'];die();
		$data['TotalQuantity']=$_REQUEST['TotalQuantity'];
		
		$CF=new CommonFunction($this->dbc);
		$data['InWords']=$CF->getIndianCurrency($data['TotalRate']);

		// $data['boq_order_id']=$PORequest_id;
		$data['Required_by']=$Required_by;


		$sql1= "SELECT a.TenderName,a.address,a.WorkOrderNo,b.user_name,b.first_name,b.last_name, b.email,b.mobile,b2.mobile AS SiteSupervisorMobile,boq.unit,a.SiteIncharge, a.id AS Tender_id,boq.id AS boq_id FROM tenders a
				INNER JOIN users b ON a.SiteIncharge=b.id
				INNER JOIN users b2 ON a.SiteSupervisor=b2.id
				INNER JOIN po_request po ON po.tender_id=a.id
				INNER JOIN tender_boq_excel boq ON boq.id=po.boq_id
				  WHERE po.id=".$PORequest_id;
		$result1 = $this->dbc->get_result($sql1);

		$TenderName=$result1[0]['TenderName'];

		$data["WorkOrderNo"]=$result1[0]['WorkOrderNo'];
		$data["SiteAddress"]=$result1[0]['address'];
		$data["SiteIncahare"]=$result1[0]['first_name'] .' ' . $result1[0]['last_name'];

		$data["SiteIncahareEmail"]=$result1[0]['email'];
		$data["SiteIncahareMobile"]=$result1[0]['mobile'];
		$data["SiteSupervisorMobile"]=$result1[0]['SiteSupervisorMobile'];
		$SiteIncharge=$result1[0]['SiteIncharge'];
		// $unit=$result1[0]['unit'];



		$po_number=$result1[0]['WorkOrderNo'].'/'.str_pad($PORequest_id, 4, '0', STR_PAD_LEFT).'/'.date("Y");
		$data['boq_order_id']=$po_number;

		// echo data['boq_order_id'];
		$ResultData= $this->generatePOConfirmPDF($data, $MaterialsOrdered,$MaterialsOrdered_add,$PORequest_id);

		$filePath=$ResultData['filename'];

		// echo $filePath; die();

		$Insert_Data=array();
		$Insert_Data['pdf_location']=$filePath;
		$Insert_Data['po_number']=$po_number;
		$Insert_Id=array();
		$Insert_Id['id']=$PORequest_id;



		$this->dbc->update_query($Insert_Data, 'po_request', $Insert_Id);


		$sql4="SELECT * FROM users WHERE is_admin =1";
		$result4 = $this->dbc->get_result($sql4);

		$sql4_1="SELECT * FROM users WHERE id =".$SiteIncharge;
		// echo $sql4_1;
		$result4_1 = $this->dbc->get_result($sql4_1);

		// die($result4_1[0]['email']);

		// $sendMailTo = array(["email" => $result[0]['email'], "first_name"=> $result[0]['name'], "last_name"=> '', "address"=> $result[0]['address']]
		// 	,["email" =>$result4[0]['email'], "first_name"=> $result4[0]['first_name'], "last_name"=> $result4[0]['last_name'], "address"=>'M/S. DEV ENGINEERS,'],
		// 	["email" =>$result4_1[0]['email'], "first_name"=> $result4_1[0]['first_name'], "last_name"=> $result4_1[0]['last_name'], "address"=>'M/S. DEV ENGINEERS,']);

		// // $sql5="SELECT * FROM material_type WHERE id=".$_REQUEST['material_type'];
		// $sql5="SELECT group_concat(b.name) AS materialName   FROM po_request_materials a
		// 		INNER JOIN material_type b ON a.material_type_id=b.id WHERE a.po_request_id=".$PORequest_id;
		// $result5 = $this->dbc->get_result($sql5);

		// $sql5_1="SELECT group_concat(a.material_type) AS materialName   FROM po_request_materials_additional a
		// 		WHERE a.po_request_id=".$PORequest_id;
		// $result5_1 = $this->dbc->get_result($sql5_1);



		// $materialName= $result5[0]["materialName"].', '. $result5_1[0]["materialName"];
		//Server location
		$fileLocation= $_SERVER['DOCUMENT_ROOT'].'tenderm/'. $filePath;

		//local path
		// $fileLocation= 'C:/xampp7.4/htdocs/Tender/'. $filePath;
		
		// foreach ($sendMailTo as $row) {

			$name=$result[0]['name'];
			$user=$name ;

			$to = $result[0]['email'];
			
			$address=$result[0]['address'];

			$subject="New PO Details --  ".$TenderName;

			$body.="<h4> Dear ".$name ."</h4>";

			$body.='Please see the following site details and materials details as per attached PO.<br/><br/><br/>';

			$body .="<b>Site Address:</b> <br>".$data["SiteAddress"];

			$body .="<br/><Br/><b>Site In Charge:</b> <br>".$data["SiteIncahare"]." / ".$data["SiteIncahareMobile"];;		
			$body .="<br/> <br/> <b>Site Supervisor Mobile:</b> <br>".$data["SiteSupervisorMobile"];
			

			$body.='<br/><br/><h4> Material Details: </h4><table style="border-collapse: collapse;">
					<tr>
						<td style="background-color: black;border: 1px solid #000000; ">
							<span style="color: #fff; text-align: center; font-size:9px;">
								#Item
							</span>
						</td>
						<td colspan="2" style="background-color: black;border: 1px solid #000000;  font-size:9px; height: 20px">
							<span style="color: #fff; text-align: center">
								Item Description
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Total Qty
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Unit
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Unit Price(Rs)
							</span>
						</td>
						<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
							<span style="color: #fff; text-align: center">
								Total(Rs)
							</span>
						</td>
					</tr>
					'.$ResultData['MaterialsOrdered'].'
				</table><br/><br/>';

			$body.='Please contact Mr. Bhavesh Purohit for any clarifications. <br/>';
			$body.='Thank you. <br/><br/>';
			$body.='Bhavesh Purohit <br/>';
			$body.='Dev Engineers <br/>';

			$host = SITE_NAME;
			$from = SITE_USER;
			$password = SITE_PASS;
			$port = SITE_PORT;

			$cc=$result4[0]['email'];
			$ccName=$result4[0]['first_name'].' '. $result4[0]['last_name'];
			$bcc=$result4_1[0]['email'];
			$bccName=$result4_1[0]['first_name'].' '.$result4_1[0]['last_name'];
			// $fileLocation=$_SERVER['DOCUMENT_ROOT']. $filePath;

			$Common=new CommonFunction($this->dbc);
			$Mail=$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$fileLocation,$cc,$ccName,$bcc,$bccName,$user );
		// }

		if($Mail==1){
			$data=array("boq_id"=>$result1[0]['boq_id'],"Tender_id"=>$result1[0]['Tender_id']);
			ajaxResponse("1", $data);
		}else{
			ajaxResponse("0", 'Error in send mail');
		}




	}


	public function generatePOConfirmPDF($data, $MaterialsOrdered,$MaterialsOrdered_add,$PORequest_id){
		
		$date = date('m/d/Y h:i:s a', time());
		$c=count($MaterialsOrdered);
		$meterialRow = "";
		$SumAmount=0;
		for ($i=1; $i<$c; $i++) { 
			// print_r($MaterialsOrdered[$i]);

			$TotalRate = $MaterialsOrdered[$i]["Quantity"]*$MaterialsOrdered[$i]["unit_price"];
			$SumAmount+=$TotalRate;
			$meterialRow.='<tr><td  style="border: 1px solid #000000;text-align: center;font-size: 10px;">'.$i.'</td>
				<td  style="border: 1px solid #000000; text-align: left; font-size: 10px; padding-left:1px;"  colspan="2">
				<table>
						<tr>
							<td style="width: 2%"></td>
							<td style="width: 96%; font-size: 10px;">'.$MaterialsOrdered[$i]["material_type_text"].', '.$MaterialsOrdered[$i]["sub_type_text"] . ' - ' . $MaterialsOrdered[$i]["material_description"].'</td>
							<td style="width: 2%"></td>
						</tr>
					</table>
				</td>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >'.$MaterialsOrdered[$i]["Quantity"].'</td>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >'.$MaterialsOrdered[$i]["unit_name"].'</td>
				<td style="border: 1px solid #000000; text-align: right; font-size: 10px;" >
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">₹'.$this->moneyFormatIndia($MaterialsOrdered[$i]["unit_price"]).'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
				<td style="border: 1px solid #000000; text-align: right" >
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">₹'.$this->moneyFormatIndia($TotalRate).'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
			</tr>';


		}
		$c2=count($MaterialsOrdered_add);
		$meterialRow_add = "";
		for ($i2=1; $i2<$c2; $i2++) { 
			// print_r($MaterialsOrdered[$i]);

			$TotalRate = $MaterialsOrdered_add[$i2]["Quantity"]*$MaterialsOrdered_add[$i2]["unit_price"];
			$SumAmount+=$TotalRate;
			$meterialRow.='<tr><td  style="border: 1px solid #000000;text-align: center;font-size: 10px;">'.$i++.'</td>
				<td  style="border: 1px solid #000000; text-align: left; font-size: 10px;  padding-left:1px"  colspan="2">
					<table>
						<tr>
							<td style="width: 2%"></td>
							<td style="width: 96%; font-size: 10px;">'.$MaterialsOrdered_add[$i2]["material_type_text"].', '.$MaterialsOrdered_add[$i2]["sub_type_text"] . ' - ' . $MaterialsOrdered_add[$i2]["material_description"].'</td>
							<td style="width: 2%"></td>
						</tr>
					</table>
				</td>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >'.$MaterialsOrdered_add[$i2]["Quantity"].'</td>
				<td style="border: 1px solid #000000; text-align: center; font-size: 10px;" >'.$MaterialsOrdered_add[$i2]["unit_name"].'</td>
				<td style="border: 1px solid #000000; text-align: right; font-size: 10px;" >
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">₹'.$this->moneyFormatIndia($MaterialsOrdered_add[$i2]["unit_price"]).'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
				<td style="border: 1px solid #000000; text-align: right" >
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">₹'.$this->moneyFormatIndia($TotalRate).'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
			</tr>';


		}
		// die();


		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->setCreator(PDF_CREATOR);
		$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH);
		$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->setFontSubsetting(true);
		$pdf->setFont('dejavusans', '', 12, '', true);

		// $pdf->setFont('calibri/calibri', '', 12, '', true);

		// $fontname=$pdf->addTTFfont('path/myfont.ttf', '', '', 32);
		// $html='<span style="font-family:'.$fontname'.;font-weight:bold">my text in bold</span>: my normal text';
		// $pdf->writeHTMLCell($w=0,$h=0,$x=11,$y=201,$html,$border=0,$ln=0,$fill=false,$reseth=true,$align='L',$autopadding=false);



		$pdf->AddPage();

		$html ='
		<table style="width:100% " cellpadding="0" cellspacing="0">
			<tr>
				<td style="width: 8%"></td>
				<td style="width: 37%"></td>
				<td style="width: 5%"></td>
				<td style="width: 13%"></td>
				<td style="width: 8%"></td>
				<td style="width: 16%"></td>
				<td style="width: 12%"></td>
				<td style="width: 10%"></td>
				<td style="width: 14%"></td>
				<td style="width: 12%"></td>
			</tr>
			<tr>
				<td colspan="2">307, Jalaram Business Center,</td>
				<td></td>
				<td></td>
				<td colspan="3" style=" text-align:right">PURCHASE ORDER</td>
			</tr>
			<tr>
				<td colspan="2">
					<br/> Ganjawala Lane,
					<span>
						<br/>Nr. Chamunda Circle,
						<br/>Borivali (W), Mumbai - 92
						<br/>
						<br/>Tel: 022 28945556
						<br/>GST:  27AAKFD8486D1Z3
					</span>
				</td>
				<td></td>
				<td></td>
				<td colspan="3">
					<br/>
					<span style="font-size: 10px; text-align:right">
					<br/>PO NO:  '.$data['boq_order_id'].'
					<br/>PO Date : '.$data['Required_by'].'
					<br/>Req# : '.$data["tender_id"].'_'.$data["boq_id"].'_'.$PORequest_id.'_'.$date.'
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="2" style="background-color: black;border: 1px solid #000000;  height: 20px;" >
					<span style="color: #fff; text-align: center; display: block; padding: 10px;">
						Vendor
					</span>
				</td>
				<td></td>
				<td colspan="4" style="background-color: black;border: 1px solid #000000; ">
					<span style="color: #fff; text-align: center">
						Deliver To
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-size: 10px">
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%"><br/>
								<br/>Mr. '.$data["vendorName"].'
								<br/>M/S. '.$data["vendorCompanyName"].'
								<br/><br/>'.$data["vendorAddress"].'
								<br/><br/>Phone: - +91-'.$data["vendorMobile"].'
								<br/>Email: '.$data["vendorEmail"].'
								<br/>
							</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
				<td></td>
				<td colspan="4" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000; font-size: 10px">
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%">
								<br/><br/>Mr. 
								'.$data["SiteIncahare"].'<br/>
								M/S. DEV ENGINEERS,<br/>
								<br/>
								'.$data["SiteAddress"].'<br/>
								<br/>
								Phone: - +91-'.$data["SiteIncahareMobile"].'
								<br/>
								Email: '.$data["SiteIncahareEmail"].'
								<br/>
								Supervisor mobile: - '.$data["SiteSupervisorMobile"].'
								<br/>
							</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td style="background-color: black;border: 1px solid #000000; ">
					<span style="color: #fff; text-align: center; font-size:9px;">
						#Item
					</span>
				</td>
				<td colspan="2" style="background-color: black;border: 1px solid #000000;  font-size:9px; height: 20px">
					<span style="color: #fff; text-align: center">
						Item Description
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Total Qty
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Per
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Unit Price(Rs)
					</span>
				</td>
				<td style="background-color: black;border: 1px solid #000000;  font-size:9px;">
					<span style="color: #fff; text-align: center">
						Total(Rs)
					</span>
				</td>
			</tr>'.
			$meterialRow.$meterialRow_add
			.'<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000; font-size:9px;">
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%"></td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000;"></td>
				<td colspan="2" style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
				<td  style="border: 1px solid #000000;"></td>
			</tr>
			<tr>
				<td  style="border: 1px solid #000000; font-size: 10px;text-align: center;">TOTAL</td>
				<td colspan="5" style="border: 1px solid #000000; font-size: 10px; ">
					<table>
						<tr>
							<td style="width: 1%"></td>
							<td style="width: 98%; font-size: 10px;">'.$data["InWords"].'</td>
							<td style="width: 1%"></td>
						</tr>
					</table>
				</td>
				<td  style="border: 1px solid #000000; font-size: 10px; text-align: right;">
					<table>
						<tr>
							<td style="width: 5%"></td>
							<td style="width: 90%; font-size: 10px;">₹'.$this->moneyFormatIndia($SumAmount).'</td>
							<td style="width: 5%"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="7" style="height: 20px"></td>
			</tr>
			<tr>
				<td colspan="3" style="background-color: black;border-left: 1px solid #000000; border-right: 1px solid #000000;  height: 20px">
					<span style="color: #fff; line-height: 5px; text-align: center">Note
					</span>
				</td>
				<td></td>
				<td></td>
				<td colspan="2">
					<span style="font-size: 10px; text-align: right">
					For <span style="font-weight: bold;">DEV ENGINEERS</span>
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="3" style="border-left: 1px solid #000000; border-right: 1px solid #000000;"><br/><br/>
					<table>
						<tr>
							<td style="width: 2%"></td>
							<td style="width: 96%; font-size: 10px;">'.nl2br($data["description"],false).'</td>
							<td style="width: 2%"></td>
						</tr>
					</table>
				</td>
				<td></td>
				<td colspan="2"></td>
			</tr>
			
			<tr>
				<td colspan="3" style="border-left: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px solid #000000;"></td>
				<td></td>
				<td></td>
				<td colspan="2">
					<span style="font-size: 8px; text-align: right; vertical-align: text-bottom;">
						Authorized Signatory
					</span>
				</td>
			</tr>
		</table>';

		// Print text using writeHTMLCell()
		// $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		$pdf->writeHTML($html, true, false, true, false, '');

		// ---------------------------------------------------------
		ob_end_clean();
		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.

	
		//Only for Testing localhost
		// $ProjectFolder="Tender/";
		// $filename = 'UploadDoc/'.$data["WorkOrderNo"].'/tenderDocs/Dev-PurOrd_'.$data["tender_id"].'_'.$data["boq_id"].'_'.$PORequest_id.'.pdf';

	
		//For Server
		$ProjectFolder="tenderm/";
		$filename = 'UploadDoc/'.$data["WorkOrderNo"].'/tenderDocs/Dev-PurOrd_'.$data["tender_id"].'_'.$data["boq_id"].'_'.$PORequest_id.'.pdf';
		ob_clean();


		$pdf->Output($_SERVER['DOCUMENT_ROOT'] .$ProjectFolder. $filename, 'F');
		// echo $filename;
		// die();
		$data=array("filename"=>$filename, "MaterialsOrdered"=>$meterialRow);
		return $data;


	//work	
		// $Serverfilename ='tenderm/UploadDoc/example_'.$data['created_by'].'.pdf';
		// $pdf->Output($_SERVER['DOCUMENT_ROOT'] . $Serverfilename, 'F');
		// return $Serverfilename;


		
	}

	function moneyFormatIndia($num) {
	    $explrestunits = "" ;
	    if(strlen($num)>3) {
	        $lastthree = substr($num, strlen($num)-3, strlen($num));
	        $restunits = substr($num, 0, strlen($num)-3); // extracts the last three digits
	        $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
	        $expunit = str_split($restunits, 2);
	        for($i=0; $i<sizeof($expunit); $i++) {
	            // creates each of the 2's group and adds a comma to the end
	            if($i==0) {
	                $explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer
	            } else {
	                $explrestunits .= $expunit[$i].",";
	            }
	        }
	        $thecash = $explrestunits.$lastthree;
	    } else {
	        $thecash = $num;
	    }
	    return $thecash; // writes the final format where $currency is the currency symbol.


	}



	public function Save_ReceivedQuantity(){
		if(!isset($_REQUEST['received_date']) || ($_REQUEST['received_date'] == "") ){
			ajaxResponse("0", 'Received date is null');
		}
		if(!isset($_REQUEST['received_quantity']) || ($_REQUEST['received_quantity'] == "") ){
			ajaxResponse("0", 'Received quantity is null');
		}
		if(!isset($_REQUEST['description_received']) || ($_REQUEST['description_received'] == "") ){
			ajaxResponse("0", 'Description received is null');
		}
		if(!isset($_REQUEST['po_request_id']) || ($_REQUEST['po_request_id'] == "") ){
			ajaxResponse("0", 'po request id is null');
		}
		
		// $MaterialsReceived_add=$_REQUEST['MaterialsReceived_add'];
		// print_r($_REQUEST['MaterialsReceived_add']); die;
		$received_date=$_REQUEST['received_date'];
		$received_quantity=$_REQUEST['received_quantity'];
		$description_received=$_REQUEST['description_received'];
		$po_request_id=$_REQUEST['po_request_id'];




		$data_POReceived=array();
		$data_POReceived["received_quantity"]=$_REQUEST['received_quantity'];
		$data_POReceived["description_received"]=$_REQUEST['description_received'];
		$data_POReceived["received_date"]=$_REQUEST['received_date'];
		$data_POReceived["received"]=1;
		$data_POReceived["status"]=1;

		$data_POReceived_id=array();
		$data_POReceived_id["id"]=$po_request_id;


		$this->dbc->update_query($data_POReceived, 'po_request', $data_POReceived_id);

		if(isset($_REQUEST['MaterialsReceived'])){
			$MaterialsReceived=$_REQUEST['MaterialsReceived'];

			$c=count($MaterialsReceived);
			for ($i=0; $i<$c; $i++) { 
				// print_r("D");

				$dataQty=array();
				$dataQty['quantity_received']=$MaterialsReceived[$i]['quantity_received'];

				$data_id=array();
				$data_id["id"]=$MaterialsReceived[$i]['row_id'];
				$this->dbc->update_query($dataQty, 'po_request_materials', $data_id);

			}
		}
		if(isset($_REQUEST['MaterialsReceived_add'])){
			$MaterialsReceived_add=$_REQUEST['MaterialsReceived_add'];
			// print_r($MaterialsReceived_add);
			$c2=count($MaterialsReceived_add);
			for ($i=0; $i<$c2; $i++) { 
				// print_r($MaterialsReceived_add[$i]['row_id']);

				$dataQty_add=array();
				$dataQty_add['quantity_received']=$MaterialsReceived_add[$i]['quantity_received'];

				$data_add_id=array();
				$data_add_id["id"]=$MaterialsReceived_add[$i]['row_id'];
				$this->dbc->update_query($dataQty_add, 'po_request_materials_additional', $data_add_id);

			}
		}
		// die();
		$sql = "SELECT * FROM po_request WHERE id=".$po_request_id;
		$po_request = $this->dbc->get_result($sql);


		$data=array("boq_id"=>$po_request[0]['boq_id'],"Tender_id"=>$po_request[0]['tender_id']);
		ajaxResponse("1", $data);




	}

	public function DeletePDF(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$data=array();
		$data["pdf_location"]="";
		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($data, 'po_request', $data_id);
	}







	public function InsertDbTables($sql){
		return $this->dbc->_query($sql);
	}





}
?>



