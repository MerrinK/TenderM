<?php
require_once('config.php');


		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			// ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_SESSION['ROLE_ID']) || ($_SESSION['ROLE_ID'] == "") ){
			// ajaxResponse("0", 'ROLE_ID is null');
		}

		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			// ajaxResponse("0", 'id is null');
		}


		$id=21;
		// $BOQ=$_REQUEST['id'];

		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];

		$sql= "SELECT a.*, 
		CONCAT( SUBSTRING(a.description, 1, 250) , ' ...')AS description,
		CONCAT( SUBSTRING(a.description, 1, 25) , ' ...')AS DescShort,

			((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS RequestedQuantity,
			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ConfirmedQuantity,
			((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id)) AS ReceivedQuantity,

			((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE a.id=boq_id)-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=boq_id)+(SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=boq_id))) AS PendingQuantity

			FROM tender_boq_excel a
				INNER JOIN tenders b ON b.id= a.tender_id 
				WHERE a.deleted=0 AND a.tender_id='$id'";

				// echo $sql;
				// die();
			$dbc= new DBC();	
		$result = $dbc->get_result($sql);
		$BOQ=[];
		$ind=-1;
		foreach ($result as $row) {
			$ind++;
			$boq_id= $row['id'];
			$sql2_0= "SELECT total_qty, description,CONCAT( SUBSTRING(description, 1, 25) , ' ...')AS DescShort  FROM tender_boq_excel WHERE id='$boq_id'";
			$result2_0 = $dbc->get_result($sql2_0);

			$total_qty=$result2_0[0]['total_qty'];
			
			// $row['remaining_qty']=$result2_0[0]['total_qty']-$row['ConfirmedQuantity'];
			$row['remaining_qty']=$result2_0[0]['total_qty']-$row['ReceivedQuantity'];
			$row['pending_qty']="0";
			$desc = preg_replace('/[^[:print:]]/', '', $row['description']);
			$row['description']= $desc;


			$shortdesc =  $row['DescShort'];
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
		$result1 = $dbc->get_result($sql1);

		
		$sql2= "SELECT TenderName FROM tenders WHERE id='$id'";
		$result2 = $dbc->get_result($sql2);



		$sql001= "SELECT role FROM users WHERE id='$User_id' ";
		$result001 = $dbc->get_result($sql001);
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
		print_r($data)

		// ajaxResponse("1", $data);
		
?>
