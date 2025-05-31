<?php
class Tender {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";

		
	}

	public function TenderList(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_SESSION['ROLE_ID']) || ($_SESSION['ROLE_ID'] == "") ){
			ajaxResponse("0", 'ROLE_ID is null');
		}

		
		$reqQry="";
		if($_REQUEST['request']==1){
			$reqQry=" AND ( SELECT count(f.id) As Count FROM po_request f INNER JOIN tender_boq_excel g ON g.id=f.boq_id WHERE f.status=0  AND f.approved=1  AND  f.purchase=0 AND f.reject=0 AND a.id=f.tender_id AND g.deleted=0)>0 ";
		}

		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];
		$Accounts=0;


		if($Role_id==1){
			$admin=1;
			$SiteIncharge=0;
			$AddQry="";

		}else if($Role_id==2 || $Role_id==4){
			$admin=0;
			$SiteIncharge=1;
			$AddQry=" AND (a.SiteIncharge=".$User_id." OR FIND_IN_SET(".$User_id.", a.SiteEngineer) ) " ;

		}else if($Role_id==3){
			$admin=0;
			$SiteIncharge=1;
			// $AddQry=" AND a.SiteSupervisor=".$User_id." " ;
			// $AddQry=" AND ( a.SiteSupervisor  = '$User_id' OR a.SiteSupervisor  LIKE '$User_id,%' OR a.SiteSupervisor  LIKE '%,$User_id,%' OR a.SiteSupervisor  LIKE '%,$User_id' ) ";
			$AddQry=" AND  FIND_IN_SET(".$User_id.", a.SiteSupervisor)   ";

		}else if($Role_id==5){
			$admin=0;
			$SiteIncharge=0;
			$Accounts=1;
			$AddQry="" ;
		}else{
			$SiteIncharge=0;
			$admin=0;
		}

		// $sql= "SELECT a.*, ( SELECT count(f.id) As Count FROM po_request f INNER JOIN tender_boq_excel g ON g.id=f.boq_id WHERE f.status=0 AND f.purchase=0 AND f.reject=0 AND a.id=f.tender_id AND g.deleted=0) AS orderRequest FROM tenders a WHERE a.deleted=0  ORDER BY a.id DESC";
		$sql= "SELECT a.*, ( SELECT count(f.id) As Count FROM po_request f INNER JOIN tender_boq_excel g ON g.id=f.boq_id WHERE f.status=0 AND f.approved=1  AND f.purchase=0 AND f.reject=0 AND a.id=f.tender_id AND g.deleted=0) AS orderRequest, ( SELECT count(f.id) As Count FROM po_request f INNER JOIN tender_boq_excel g ON g.id=f.boq_id WHERE f.status=0 AND f.approved=0  AND f.purchase=0 AND f.reject=0 AND a.id=f.tender_id AND g.deleted=0) AS orderRequestUnconfirmed, ( SELECT count(f.id) As Count FROM po_request f INNER JOIN tender_boq_excel g ON g.id=f.boq_id WHERE   a.id=f.tender_id AND g.deleted=0 ) AS TotalRequest FROM tenders a WHERE a.deleted=0 ". $AddQry . $reqQry ."  ORDER BY a.id DESC";

		// $sql= "SELECT a.*, ( SELECT count(f.id) As Count FROM po_request f INNER JOIN tender_boq_excel g ON g.id=f.boq_id  WHERE f.status=0 AND f.purchase=0 AND f.reject=0 AND a.id=f.tender_id AND g.deleted=0) AS orderRequest, FROM tenders a WHERE a.deleted=0 ".$AddQry." ORDER BY a.id DESC";
		// echo $sql;

		

		$result = $this->dbc->get_result($sql);



		$data=array("Tender"=>$result,"admin"=>$admin,"SiteIncharge"=>$SiteIncharge,"Accounts"=>$Accounts);
		ajaxResponse("1", $data);
	}


	public function SelectTender_Id(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$id=$_REQUEST['id'];
		$sql= "SELECT * FROM tenders WHERE id='$id'";
		$result = $this->dbc->get_result($sql);

		// print_r($result);die();
		$data=array("Tender"=>$result[0],"url"=>url());

		ajaxResponse("1", $data);


	}
	public function AddTenderPage(){
		// $sql1= "SELECT id,first_name,last_name FROM users WHERE role=2 AND  deleted=0 ORDER BY first_name";
		// $SiteIncharge = $this->dbc->get_result($sql1);
		// $sql2= "SELECT id,first_name,last_name  FROM users WHERE role=3 AND  deleted=0 ORDER BY first_name";
		// $SiteSupervisor = $this->dbc->get_result($sql2);
		// $sql2= "SELECT id,first_name,last_name  FROM users WHERE role=4 AND  deleted=0 ORDER BY first_name";
		// $SiteEngineer = $this->dbc->get_result($sql2);

		// BMCDepartment role IN (2, 3, 4)
		$sql3= "SELECT *  FROM department  ORDER BY name";
		$BMCDepartment = $this->dbc->get_result($sql3);

		$sql4= "SELECT id,first_name,last_name  FROM users WHERE  role IN (2, 3, 4) AND  deleted=0 ORDER BY first_name";
		$All = $this->dbc->get_result($sql4);


		// $data=array("SiteIncharge"=>$SiteIncharge,"SiteEngineer"=>$SiteEngineer,"SiteSupervisor"=>$SiteSupervisor, "BMCDepartment"=>$BMCDepartment);
		$data=array("SiteIncharge"=>$All,"SiteEngineer"=>$All,"SiteSupervisor"=>$All, "BMCDepartment"=>$BMCDepartment);



		ajaxResponse("1", $data);

	}



	// public function RegisterTenderxxx(){
	// 	if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){
	// 		$id=$_REQUEST['id'];
	// 	}else{
	// 		$sql= "SELECT id FROM users ORDER BY id DESC LIMIT 1";
	// 		$result = $this->dbc->get_result($sql);
	// 		$id=$result[0]['id'];
	// 		$id++;
	// 	}
		
	// 	$info_4 = pathinfo($_FILES['UploadBOQ']['name']);
	// 	$ext_4 = $info_4['extension']; 
	// 	$target_4 = 'UploadDoc/UploadBOQ_'.$id.'.'.$ext_4;
	// 	move_uploaded_file( $_FILES['UploadBOQ']['tmp_name'], $target_4);
	// 	// $data['UploadBOQ']=$target_4;

	// 	$CmFn= new CommonFunction();
	// 	$ExcelFile=$CmFn->readExcelSheet($target_4);
	// 	$sheetData = $ExcelFile->getActiveSheet()->toArray(null,true,true,true);

	// 	$start=0;// Ignore First row
	// 	if($sheetData[1]['B']=='ITEM' && $sheetData[1]['C']=='DESCRITPTION' && $sheetData[1]['D']=='TOTAL QTY' && $sheetData[1]['E']=='RATE (Rs.)' && $sheetData[1]['F']=='UNIT' && $sheetData[1]['G']=='AMOUNT (Rs.)'){
	// 		foreach ($sheetData as $row) {
	// 			if ($start++ == 0) continue;
	// 			if($row['B']!='' && $row['C']!='' && $row['D']!='' && $row['E']!='' && $row['F']!='' && $row['G']!=''){
	// 				$str="('".$row['B']."','".$row['C']."','".$row['D']."','".$row['E']."','".$row['F']."','".$row['G']."')";
	// 			}
				
	// 		}
	// 	}


	// 	// if(isset($_REQUEST['ExcludeMoonsoon'])){
	// 	// 	$data['ExcludeMoonsoon']=1;
	// 	// }else{
	// 	// 	$data['ExcludeMoonsoon']=0;
	// 	// }
		
	// 	// $save=$_REQUEST['save'];
	// 	// if($save=='add'){
	// 	// 	$this->dbc->insert_query($data,"tenders");
	// 	// }else if($save=='update'){
	// 	// 	$data_id=array();
	// 	// 	$data_id["id"]=$_REQUEST['id'];
	// 	// 	$this->dbc->update_query($data, 'tenders', $data_id);
	// 	// }
	// 	// ajaxResponse("1", '');

	// }




	public function RegisterTender(){


		if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){
			$id=$_REQUEST['id'];
		}else{
			$id = $this->dbc->get_next_insert_id('tenders');

			// $sql= "SELECT id FROM tenders ORDER BY id DESC LIMIT 1";
			// $result = $this->dbc->get_result($sql);
			// $id=$result[0]['id'];
			// $id++;

		}

		// print_r($_REQUEST);
		// die();
		
		$data=array();
		$data['TenderName']=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['TenderName']));
		$data['WorkOrderNo']=$_REQUEST['WorkOrderNo'];
		$data['SiteIncharge']=$_REQUEST['SiteIncharge'];
		// $data['SiteSupervisor']=$_REQUEST['SiteSupervisor'];
		$data['SiteSupervisor']=$_REQUEST['SiteSupervisorMultiple'];
		$data['SiteEngineer']=$_REQUEST['SiteEngineerMultiple'];
		$data['BMCDepartment']=$_REQUEST['BMCDepartment'];
		$data['EMDStartDate']=$_REQUEST['EMDStartDate'];
		// $data['EMDEndDate']=$_REQUEST['EMDEndDate'];
		$data['EMDAmount']=$_REQUEST['EMDAmount'];
		$data['ASDStartDate']=$_REQUEST['ASDStartDate'];
		$data['ASDEndDate']=$_REQUEST['ASDEndDate'];
		$data['ASDAmount']=$_REQUEST['ASDAmount'];
		// $data['BankAccNo']=$_REQUEST['BankAccNo'];
		// $data['BankName']=$_REQUEST['BankName'];
		$data['BankAmount']=$_REQUEST['BankAmount'];
		// $data['RetentionEndDate']=$_REQUEST['RetentionEndDate'];
		// $data['RetentionAmount']=$_REQUEST['RetentionAmount'];
		$data['TenderStartDate']=$_REQUEST['TenderStartDate'];
		$data['TenderEndDate']=$_REQUEST['TenderEndDate'];
		$data['address']=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['address']));

		$data['InsurancePolicy']=$_REQUEST['InsurancePolicy'];
		$data['InsurancePolicyExpiryDate']=$_REQUEST['InsurancePolicyExpiryDate'];
		$data['Miscell']=$_REQUEST['Miscell'];


		$data['WorkOrderText']=$_REQUEST['WorkOrderText'];
		$data['WorkOrderDate']=$_REQUEST['WorkOrderDate'];
		$data['tender_description']=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['tender_description']));
		$data['InsuranceCAR']=$_REQUEST['InsuranceCAR'];
		$data['InsuranceCARExpiryDate']=$_REQUEST['InsuranceCARExpiryDate'];
		$data['ASDReceipt']=$_REQUEST['ASDReceipt'];
		$data['PerformanceBG']=$_REQUEST['PerformanceBG'];
		$data['BGIssueDate']=$_REQUEST['BGIssueDate'];
		$data['BGExpiryDate']=$_REQUEST['BGExpiryDate'];
		$data['ContractDeposit']=$_REQUEST['ContractDeposit'];
		$data['ContractDepositIssueDate']=$_REQUEST['ContractDepositIssueDate'];
		$data['ContractDepositExpiryDate']=$_REQUEST['ContractDepositExpiryDate'];
		$data['ContractDepositAmount']=$_REQUEST['ContractDepositAmount'];
		$data['DefectLiabilityPeriod']=$_REQUEST['DefectLiabilityPeriod'];
		



		if(isset($_REQUEST['ExcludeMoonsoon'])){
			$data['ExcludeMoonsoon']=1;
		}else{
			$data['ExcludeMoonsoon']=0;
		}


		// $folder='UploadDoc/'.$_REQUEST['WorkOrderNo']."/tenderDocs/";
		$folder='UpDoc/'.$id."/tenderDocs/";


		if(isset($_FILES['EMDUpload']['name']) && $_FILES['EMDUpload']['name']!=''){
			$info_1 = pathinfo($_FILES['EMDUpload']['name']);
			$ext_1 = $info_1['extension']; 
			$target_1 = $folder. 'EMDUpload_'.$id.'.'.$ext_1;
			// move_uploaded_file( $_FILES['EMDUpload']['tmp_name'], $target_1);
			$data['EMDUpload']=$target_1;
		}
		if(isset($_FILES['InsurancePolicyUpload']['name']) && $_FILES['InsurancePolicyUpload']['name']!=''){
			$info_12 = pathinfo($_FILES['InsurancePolicyUpload']['name']);
			$ext_12 = $info_1['extension']; 
			$target_12 = $folder. 'InsurancePolicyUpload_'.$id.'.'.$ext_12;
			// move_uploaded_file( $_FILES['InsurancePolicyUpload']['tmp_name'], $target_1);
			$data['InsurancePolicyUpload']=$target_12;
		}
		if(isset($_FILES['InsuranceCARUpload']['name']) && $_FILES['InsuranceCARUpload']['name']!=''){
			$info_13 = pathinfo($_FILES['InsuranceCARUpload']['name']);
			$ext_13 = $info_13['extension']; 
			$target_13 = $folder. 'InsuranceCARUpload_'.$id.'.'.$ext_13;
			// move_uploaded_file( $_FILES['InsuranceCARUpload']['tmp_name'], $target_13);
			$data['InsuranceCARUpload']=$target_13;
		}

		if(isset($_FILES['ASDUpload']['name']) && $_FILES['ASDUpload']['name']!=''){
			$info_2 = pathinfo($_FILES['ASDUpload']['name']);
			$ext_2 = $info_2['extension']; 
			$target_2 =  $folder. 'ASDUpload_'.$id.'.'.$ext_2;
			// move_uploaded_file( $_FILES['ASDUpload']['tmp_name'], $target_2);
			$data['ASDUpload']=$target_2;
		}

		if(isset($_FILES['RetentionUpload']['name']) && $_FILES['RetentionUpload']['name']!=''){
			$info_3 = pathinfo($_FILES['RetentionUpload']['name']);
			$ext_3 = $info_3['extension']; 
			$target_3 =  $folder. 'RetentionUpload_'.$id.'.'.$ext_3;
			// move_uploaded_file( $_FILES['RetentionUpload']['tmp_name'], $target_3);
			$data['RetentionUpload']=$target_1;
		}

		// if(isset($_FILES['UploadBOQ']['name']) && $_FILES['UploadBOQ']['name']!=''){
		// 	$info_4 = pathinfo($_FILES['UploadBOQ']['name']);
		// 	$ext_4 = $info_4['extension']; 
		// 	$target_4 =  $folder. 'UploadBOQ_'.$id.'.'.$ext_4;
		// 	// move_uploaded_file( $_FILES['UploadBOQ']['tmp_name'], $target_4);
		// 	$data['UploadBOQ']=$target_4;
		// }
		if(isset($_FILES['MiscellUpload']['name']) && $_FILES['MiscellUpload']['name']!=''){
			$info_5 = pathinfo($_FILES['MiscellUpload']['name']);
			$ext_5 = $info_5['extension']; 
			$target_5 =  $folder. 'MiscellUpload_'.$id.'.'.$ext_5;
			// move_uploaded_file( $_FILES['MiscellUpload']['tmp_name'], $target_5);
			$data['MiscellUpload']=$target_5;
		}

		if(isset($_FILES['PerformanceBGUpload']['name']) && $_FILES['PerformanceBGUpload']['name']!=''){
			$info_6 = pathinfo($_FILES['PerformanceBGUpload']['name']);
			$ext_6 = $info_6['extension']; 
			$target_6 =  $folder. 'PerformanceBGUpload_'.$id.'.'.$ext_6;
			// move_uploaded_file( $_FILES['PerformanceBGUpload']['tmp_name'], $target_6);
			$data['PerformanceBGUpload']=$target_6;
		}
		if(isset($_FILES['BGContractDepositUpload']['name']) && $_FILES['BGContractDepositUpload']['name']!=''){
			$info_7 = pathinfo($_FILES['BGContractDepositUpload']['name']);
			$ext_7 = $info_7['extension']; 
			$target_7 =  $folder. 'BGContractDepositUpload_'.$id.'.'.$ext_7;
			// move_uploaded_file( $_FILES['BGContractDepositUpload']['tmp_name'], $target_7);
			$data['BGContractDepositUpload']=$target_7;
		}




		$User_id=$_SESSION['USER_ID'];
		$Today=date('Y-m-d H:i:s');

		$save=$_REQUEST['save'];

		$this->dbc->autoCommit(false);
		try {
			if($save=='add'){
				$data['created_by']=$User_id;
				$data['created_date']=date('Y-m-d H:i:s');

				$result=$this->dbc->insert_query($data,"tenders");
				
				$Tender_id = $this->dbc->get_insert_id();
				if($Tender_id>=1){

					$oldmask = umask(0);
					mkdir("UpDoc/".$Tender_id, 0777);
					mkdir("UpDoc/".$Tender_id.'/tenderDocs', 0777);
					mkdir("UpDoc/".$Tender_id.'/Labor', 0777);
					mkdir("UpDoc/".$Tender_id.'/Labour', 0777);
					mkdir("UpDoc/".$Tender_id.'/Challans', 0777);
					mkdir("UpDoc/".$Tender_id.'/Progress', 0777);
					mkdir("UpDoc/".$Tender_id.'/Expense', 0777);
					mkdir("UpDoc/".$Tender_id.'/Miscell', 0777);
					// mkdir("UploadDoc/".$_REQUEST['WorkOrderNo'].'/', 0777);
					umask($oldmask);
					// sleep(3);


					if(isset($_FILES['EMDUpload']['name']) && $_FILES['EMDUpload']['name']!=''){
						move_uploaded_file( $_FILES['EMDUpload']['tmp_name'], $target_1);
					}
					if(isset($_FILES['InsurancePolicyUpload']['name']) && $_FILES['InsurancePolicyUpload']['name']!=''){
						move_uploaded_file( $_FILES['InsurancePolicyUpload']['tmp_name'], $target_12);
					}
					if(isset($_FILES['InsuranceCARUpload']['name']) && $_FILES['InsuranceCARUpload']['name']!=''){
						move_uploaded_file( $_FILES['InsuranceCARUpload']['tmp_name'], $target_13);
					}
					if(isset($_FILES['ASDUpload']['name']) && $_FILES['ASDUpload']['name']!=''){
						move_uploaded_file( $_FILES['ASDUpload']['tmp_name'], $target_2);
					}
					if(isset($_FILES['RetentionUpload']['name']) && $_FILES['RetentionUpload']['name']!=''){
						move_uploaded_file( $_FILES['RetentionUpload']['tmp_name'], $target_3);
					}
					if(isset($_FILES['UploadBOQ']['name']) && $_FILES['UploadBOQ']['name']!=''){
						move_uploaded_file( $_FILES['UploadBOQ']['tmp_name'], $target_4);
					}
					if(isset($_FILES['MiscellUpload']['name']) && $_FILES['MiscellUpload']['name']!=''){
						move_uploaded_file( $_FILES['MiscellUpload']['tmp_name'], $target_5);
					}
					if(isset($_FILES['PerformanceBGUpload']['name']) && $_FILES['PerformanceBGUpload']['name']!=''){
						move_uploaded_file( $_FILES['PerformanceBGUpload']['tmp_name'], $target_6);
					}
					if(isset($_FILES['BGContractDepositUpload']['name']) && $_FILES['BGContractDepositUpload']['name']!=''){
						move_uploaded_file( $_FILES['BGContractDepositUpload']['tmp_name'], $target_7);
					}

				}
				
			}else if($save=='update'){
				$data['updated_by']=$User_id;
				$data['updated_date']=date('Y-m-d H:i:s');
				$data_id=array();
				$data_id["id"]=$_REQUEST['id'];
				$Tender_id=$_REQUEST['id'];
				$this->dbc->update_query($data, 'tenders', $data_id);
				if(isset($_FILES['EMDUpload']['name']) && $_FILES['EMDUpload']['name']!=''){
					move_uploaded_file( $_FILES['EMDUpload']['tmp_name'], $target_1);
				}
				if(isset($_FILES['InsurancePolicyUpload']['name']) && $_FILES['InsurancePolicyUpload']['name']!=''){
					move_uploaded_file( $_FILES['InsurancePolicyUpload']['tmp_name'], $target_12);
				}
				if(isset($_FILES['InsuranceCARUpload']['name']) && $_FILES['InsuranceCARUpload']['name']!=''){
					move_uploaded_file( $_FILES['InsuranceCARUpload']['tmp_name'], $target_13);
				}
				if(isset($_FILES['ASDUpload']['name']) && $_FILES['ASDUpload']['name']!=''){
					move_uploaded_file( $_FILES['ASDUpload']['tmp_name'], $target_2);
				}

				if(isset($_FILES['RetentionUpload']['name']) && $_FILES['RetentionUpload']['name']!=''){
					move_uploaded_file( $_FILES['RetentionUpload']['tmp_name'], $target_3);
				}
				if(isset($_FILES['UploadBOQ']['name']) && $_FILES['UploadBOQ']['name']!=''){
					move_uploaded_file( $_FILES['UploadBOQ']['tmp_name'], $target_4);
				}
				if(isset($_FILES['MiscellUpload']['name']) && $_FILES['MiscellUpload']['name']!=''){
					move_uploaded_file( $_FILES['MiscellUpload']['tmp_name'], $target_5);
				}
				if(isset($_FILES['PerformanceBGUpload']['name']) && $_FILES['PerformanceBGUpload']['name']!=''){
					move_uploaded_file( $_FILES['PerformanceBGUpload']['tmp_name'], $target_6);
				}
				if(isset($_FILES['BGContractDepositUpload']['name']) && $_FILES['BGContractDepositUpload']['name']!=''){
					move_uploaded_file( $_FILES['BGContractDepositUpload']['tmp_name'], $target_7);
				}

				if(isset($_FILES['UploadBOQ']['name']) && $_FILES['UploadBOQ']['name']!=''){
					
					$dataDelete=array();
					$dataDelete["deleted"]=1;
					$dataDelete["deleted_by"]=$User_id;
					$dataDelete["deleted_date"]=date('Y-m-d H:i:s');
					
					$dataDelete_id=array();
					$dataDelete_id["tender_id"]=$_REQUEST['id'];

					$this->dbc->update_query($dataDelete, 'tender_boq_excel', $dataDelete_id);
				}



			}
			// die('b4f');
			if(isset($_FILES['UploadBOQ']['name']) && $_FILES['UploadBOQ']['name']!=''){
				
				$CmFn= new CommonFunction();
				$ExcelFile=$CmFn->readExcelSheet($target_4);
				
				$sheetData = $ExcelFile->getActiveSheet()->toArray(null,true,true,true);

				$start=0;// Ignore First row
				if($sheetData[1]['B']=='ITEM' && $sheetData[1]['C']=='DESCRITPTION' && $sheetData[1]['D']=='TOTAL QTY' && $sheetData[1]['E']=='RATE (Rs.)' && $sheetData[1]['F']=='UNIT' && $sheetData[1]['G']=='AMOUNT (Rs.)'){
					// $str="INSERT INTO tender_boq_excel(tender_id, item, description, total_qty, rate, unit, amount,created_by,created_date) VALUES ";
					foreach ($sheetData as $row) {
						if ($start++ == 0) continue;
						if($row['B']!='' && $row['C']!='' && $row['D']!='' && $row['E']!='' && $row['F']!='' && $row['G']!=''){
							// $str.="('".$Tender_id."','".."','".$row['C']."','".$row['D']."','".$row['E']."','".$row['F']."','".$row['G']."','".$User_id."','".$Today."'),";

							$data=array();
							$data["tender_id"]=$Tender_id;
							$data["item"]=$row['B'];
							// $data["description"]= str_replace( "'", '', $row['C']);
							$desc = preg_replace('/[^[:print:]]/', '', $row['C']);
							$desc_new = str_replace( "'", '', $desc);

							$data["description"]= $desc_new;

							$data["total_qty"]=$row['D'];
							$data["rate"]=$row['E'];
							$data["unit"]=$row['F'];
							$data["amount"]= str_replace( ',', '', $row['G']) ;
							$data["created_by"]=$User_id;
							$data["created_date"]=$Today;

							$this->dbc->insert_query($data,"tender_boq_excel");


						}
					}
					// $qry=substr($str, 0, -1);
					// echo $qry;

					// die();
					// $result = $this->dbc->query($qry);
					
				}else{
					$this->dbc->rollback();
					ajaxResponse("2", "Excel is not in the required format");
				}


			}


			if ($Tender_id>=0 ){
			// if ($Tender_id>0 && $result>0 ){
				$this->dbc->commit();
				ajaxResponse("1", "Commit");
			}else throw new Exception("query failed");
	    }catch (Exception $e) {
			$this->dbc->rollback();
			ajaxResponse("2", "RolledBack");
		}
	}



	public function DeleteTender(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$data=array();
		$data["deleted"]=1;
		$data["deleted_by"]=$_SESSION['USER_ID'];
		$data["deleted_date"]=date('Y-m-d H:i:s');

		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($data, 'tenders', $data_id);
		ajaxResponse("1", '');

	}

	public function fetchTenderID(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$id=$_REQUEST['id'];
		$sql="SELECT * FROM tenders WHERE id='$id' ";

		$result=$this->dbc->get_array($sql);

		// print_r($result);
		$SiteIncharge=$result['SiteIncharge'];
		$SiteSupervisor=$result['SiteSupervisor'];
		$Department=$result['BMCDepartment'];

		$sql1="SELECT user_name FROM users WHERE id='$SiteIncharge' ";
		$result1=$this->dbc->get_array($sql1);
		$result['SiteIncharge']=$result1['user_name'];

		$sql2="SELECT user_name FROM users WHERE id='$SiteSupervisor' ";
		$result2=$this->dbc->get_array($sql2);
		$result['SiteSupervisor']=$result2['user_name'];

		$sql3="SELECT name FROM department WHERE id='$Department' ";
		$result3=$this->dbc->get_array($sql3);
		$result['Department']=$result3['name'];


		

		// $sql5="SELECT a.*,b.name As Material,c.name AS OPC_PPC FROM boq_order a 
		// 		INNER JOIN material_type b ON a.material_type=b.id
		// 		INNER JOIN material_sub_type c ON a.opc_ppc=c.id
		// 		WHERE tender_id='$id' ";
		// // echo $sql5;
		// $result5=$this->dbc->get_assoc_array($sql5);

		$sql5="SELECT pom.quantity_confirmed, mt.name AS Material, mts.name AS sub_type, po.required_by FROM  po_request_materials pom INNER JOIN material_type mt ON pom.material_type_id=mt.id INNER JOIN material_sub_type mts ON pom.material_sub_type_id=mts.id INNER JOIN po_request po ON po_request_id=po.id WHERE po.status=1 AND po.purchase=1 AND  pom.tender_id ='$id' ";
		// echo $sql5;
		$result5=$this->dbc->get_assoc_array($sql5);

		$sql6="SELECT a.*,b.item,b.total_qty ,b.unit,(SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)AS TotalRequestedQty, (SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)AS TotalConfirmedQty,  (SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=po_request_id)AS TotalReceivedQty,((SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)-(SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=po_request_id))AS remaining_qty, b.description, CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort 
			FROM po_request a 
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			WHERE a.status=0 AND a.tender_id=".$id;
			// echo$sql ;

		$result6 = $this->dbc->get_result($sql6);




		$data=array("Tender"=>$result,"BOQ"=>$result5,"RequestedMaterial"=>$result6);

		// $data=$this->dbc->get_array($sql);

		ajaxResponse("1", $data);
		

	}


	

	public function Select_ACtive_PO(){
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
		// $sql= "SELECT a.*,b.item,b.id AS boq_id,b.total_qty,b.unit,(SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)AS TotalRequestedQty,(SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)AS TotalConfirmedQty,(SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=po_request_id)AS TotalReceivedQty,((SELECT SUM(quantity_requested) FROM po_request_materials WHERE a.id=po_request_id)-(SELECT SUM(quantity_received) FROM po_request_materials WHERE a.id=po_request_id))AS pending_qty, b.description, CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort  FROM po_request a INNER JOIN tender_boq_excel b ON b.id=a.boq_id  WHERE  a.tender_id=".$id." AND a.status=1";


		$sql= "SELECT a.*,b.item,b.id AS boq_id,b.total_qty,b.unit,
		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))AS TotalRequestedQty,

		((SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_requested),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))AS TotalConfirmedQty,

		((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=po_request_id ) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=po_request_id ))AS TotalReceivedQty,

		(((SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials WHERE po_request_id=a.id ) + (SELECT IFNULL(SUM(quantity_confirmed),0) FROM po_request_materials_additional WHERE po_request_id=a.id ))-((SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials WHERE a.id=po_request_id ) + (SELECT IFNULL(SUM(quantity_received),0) FROM po_request_materials_additional WHERE a.id=po_request_id )))AS pending_qty,

		 b.description, CONCAT( SUBSTRING(b.description, 1, 5) , ' ...')AS DescShort 
			FROM po_request a
			INNER JOIN tender_boq_excel b ON b.id=a.boq_id 
			WHERE b.deleted=0 AND a.tender_id=".$id." AND a.status=1";

			    // echo $sql;
		$result = $this->dbc->get_result($sql);

		$ind=-1;
		$BOQ=$result;
		// $BOQ=[];
		// foreach ($result as $row) {
		// 	$ind++;

		// 	$boq_id= $row['boq_id'];

		// 	$sql2_0= "SELECT total_qty  FROM tender_boq_excel WHERE id='$boq_id'";
		// 	$result2_0 = $this->dbc->get_result($sql2_0);
		// 	$total_qty=$result2_0[0]['total_qty'];

		// 	$sql2= "SELECT SUM(a.quantity) AS request_qty  FROM boq_order a 
		// 		WHERE a.boq_id='$boq_id' AND (a.confirm=1  OR a.rejected=1)";
		// 	$result2 = $this->dbc->get_result($sql2);


		// 	$ReceivedQry= "SELECT SUM(received_quantity) AS received_quantity FROM receipt  WHERE purchase_boq_order_id= ".$row['id'];
		// 	// echo $ReceivedQry;
		// 	$ReceivedData = $this->dbc->get_result($ReceivedQry);
		// 	$row['received_qty']=$ReceivedData[0]['received_quantity'];
		// 	$row['pending_qty']=$row['quantity']-$row['received_qty'];

		// 	$row['remaining_qty']=$total_qty-$result2[0]['request_qty'];
		// 	$BOQ[$ind]=$row;
		// }
		// $Tender_Id=$result[0][''];

		if(isset($result[0])){
			$data=array("BOQ"=>$BOQ, "TenderId"=>$id, "BOQ_Id"=>$id, "admin"=>$admin );
		}else{
			$data=array( "TenderId"=>$id ,"BOQ_Id"=>$id, "admin"=>$admin );
		}

		ajaxResponse("1", $data);
	}

	public function AddLabourCosts(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['Tender_id']) || ($_REQUEST['Tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		$User_id=$_SESSION['USER_ID'];

		$id= $_REQUEST['Tender_id'];

		$admin=0;
		// $User_id=$_SESSION['USER_ID'];
		// $sql001= "SELECT role FROM users WHERE id='$User_id' ";
		// $result001 = $this->dbc->get_result($sql001);
		// if($result001[0]['role']==1){
		// 	$admin=1;
		// }

		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}



		$sql0= "SELECT id,TenderName FROM tenders WHERE id='$id'";
		$result0 = $this->dbc->get_result($sql0);

		$sql= "SELECT * FROM tender_labour_cost WHERE tender_id='$id' LIMIT 1 ";
		$result = $this->dbc->get_result($sql);
		$balanceLabour = 0.00;
		$tenderLaborAmt = 0.00;

		$Below='';
		if(isset($result[0]['id'])){


			$TenderCosting=$result;
			$TenderTag=1;

			if($result[0]['above_below']=='Below'){
				$Below=1;
			}else{
				$Below=0;
			}

			$tenderLaborAmt = $result[0]['labour_amount'];
		}else{
			$TenderCosting='';
			$TenderTag=0;
		}

		$sql111= "SELECT count(id) AS count FROM tender_labour_bills  WHERE tender_id='$id' AND deleted=0 ORDER BY id DESC";
		$result111 = $this->dbc->get_result($sql111);

		

		$sql11= "SELECT SUM(bill_amount) AS TotalAmout FROM tender_labour_bills WHERE tender_id='$id' AND deleted=0";
		$result11 = $this->dbc->get_result($sql11);

		$sql1= "SELECT a.*, v.name AS VendorName FROM tender_labour_bills a INNER JOIN vendors v ON v.id=a.vendor WHERE a.tender_id='$id' AND a.deleted=0  ORDER BY a.id DESC";
		// echo $sql1;
		$result1 = $this->dbc->get_result($sql1);

		if(isset($result[0]['id'])){
			$BillTag=1;
		}else{
			$BillTag=0;
		}

		$labourSpendAmt = $result11[0]['TotalAmout'];
		$balanceLabour = $tenderLaborAmt - $labourSpendAmt;

		$isOver = ($balanceLabour < 0);
		
		$url=url();
		$data=array("tender_id"=>$id,"TenderName"=>$result0[0]['TenderName'],"Bills"=>$result1,"BillTag"=>$BillTag,"TenderCosting"=>$TenderCosting,"TenderTag"=>$TenderTag,"url"=>$url,"total_labour_amount"=>number_format($labourSpendAmt, 2, '.', ''),"balance_labour_amount"=>number_format($balanceLabour, 2, '.', ''), 'isOver'=>$isOver,"labourAvailable"=>$result111[0]['count'],"Below"=>$Below,"admin"=>$admin);

		ajaxResponse("1", $data);
	}

	public function RegisterTenderCosting(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		if(!isset($_REQUEST['TenderAmount']) || ($_REQUEST['TenderAmount'] == "") ){
			ajaxResponse("0", 'Tender Amount is null');
		}
		if(!isset($_REQUEST['aboveBelow']) || ($_REQUEST['aboveBelow'] == "") ){
			ajaxResponse("0", 'Above Below is null');
		}
		if(!isset($_REQUEST['TenderPrecentage']) || ($_REQUEST['TenderPrecentage'] == "") ){
			ajaxResponse("0", 'Tender Precentage is null');
		}
		if(!isset($_REQUEST['LabourWorkPre']) || ($_REQUEST['LabourWorkPre'] == "") ){
			ajaxResponse("0", 'Labour Work Pre is null');
		}
		if(!isset($_REQUEST['LabourAount']) || ($_REQUEST['LabourAount'] == "") ){
			ajaxResponse("0", 'Labour Aount is null');
		}

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data=array();
		$data['tender_id']=$_REQUEST['tender_id'];
		$data['tender_amount']=$_REQUEST['TenderAmount'];
		$data['above_below']=$_REQUEST['aboveBelow'];
		$data['percentage']=$_REQUEST['TenderPrecentage'];
		$data['labour_work_percentage']=$_REQUEST['LabourWorkPre'];
		$data['labour_amount']=$_REQUEST['LabourAount'];
		$data['quoted_amount']=$_REQUEST['quotedAmount'];
		
		if(isset($_REQUEST['id']) && $_REQUEST['id']!=''){
			$data['updated_by']=$User_id;
			$data['updated_date']=$Today;

			$data_id=array();
			$data_id["id"]=$_REQUEST['id'];

			$this->dbc->update_query($data, 'tender_labour_cost', $data_id);

		}else{
			$data['created_by']=$User_id;
			$data['created_date']=$Today;

			$this->dbc->insert_query($data,"tender_labour_cost");
			
		}
		ajaxResponse("1", '');

	}

public function DeleteBill(){

	if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
		ajaxResponse("0", 'USER_ID is null');
	}

	if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
		ajaxResponse("0", 'id is null');
	}


		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data=array();
		$data['deleted']=1;
		$data['deleted_by']=$User_id;
		$data['deleted_date']=$Today;

		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];

		$this->dbc->update_query($data, 'tender_labour_bills', $data_id);
		$this->unlinkImage('tender_labour_bills',$_REQUEST['id'], 'upload_bill');

		ajaxResponse("1", '');

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

	public function AddLabourBill(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
	
		if(!isset($_REQUEST['tender_id']) || ($_REQUEST['tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}

		if(!isset($_REQUEST['BillNumber']) || ($_REQUEST['BillNumber'] == "") ){
			ajaxResponse("0", 'Bill Number is null');
		}
		if(!isset($_REQUEST['BillName']) || ($_REQUEST['BillName'] == "") ){
			ajaxResponse("0", 'Bill Name is null');
		}
		if(!isset($_REQUEST['BillAmount']) || ($_REQUEST['BillAmount'] == "") ){
			ajaxResponse("0", 'Bill Amount is null');
		}
		if(!isset($_REQUEST['BillVendor']) || ($_REQUEST['BillVendor'] == "") ){
			ajaxResponse("0", 'Bill Vendor is null');
		}
		if(!isset($_REQUEST['BillDate']) || ($_REQUEST['BillDate'] == "") ){
			ajaxResponse("0", 'Bill Date is null');
		}

		$sql= "SELECT id FROM tender_labour_bills ORDER BY id DESC LIMIT 1";
		$result = $this->dbc->get_result($sql);
		$id=$result[0]['id'];
		$id++;

		// $sql2= "SELECT WorkOrderNo FROM tenders WHERE  id =".$_REQUEST['tender_id'];
		// $result2 = $this->dbc->get_result($sql2);
		// $WorkOrderNo=$result2[0]['WorkOrderNo'];

		$User_id=$_SESSION['USER_ID'];
		$Today=date("Y-m-d");

		$data=array();
		$data['tender_id']=$_REQUEST['tender_id'];
		$data['bill_number']=$_REQUEST['BillNumber'];
		$data['bill_name']=$_REQUEST['BillName'];
		$data['bill_amount']=$_REQUEST['BillAmount'];
		$data['vendor']=$_REQUEST['BillVendor'];
		$data['bill_date']=$_REQUEST['BillDate'];
		
		// $folder="UploadDoc/".$WorkOrderNo."/Labor/";
		$folder="UpDoc/".$_REQUEST['tender_id']."/Labor/";

		if(isset($_FILES['AddCostBills']['name']) && $_FILES['AddCostBills']['name']!=''){
			$info_1 = pathinfo($_FILES['AddCostBills']['name']);
			$ext_1 = $info_1['extension']; 
			$target_1 = $folder. 'AddCostBills_'.$id.'.'.$ext_1;
			move_uploaded_file( $_FILES['AddCostBills']['tmp_name'], $target_1);
			$data['upload_bill']=$target_1;
		}
		
		$data['created_by']=$User_id;
		$data['created_date']=$Today;

		$this->dbc->insert_query($data,"tender_labour_bills");
		




		$getTenderQry = "SELECT TenderName FROM tenders WHERE id=".$_REQUEST['tender_id'];
 		$getTenderData = $this->dbc->get_result($getTenderQry);

 		$VendorQry = "SELECT name FROM vendors WHERE id=".$_REQUEST['BillVendor'];
 		$VendorData = $this->dbc->get_result($VendorQry);

		$is_admin_qry="SELECT id, first_name, last_name, email FROM users WHERE is_admin=1 LIMIT 1" ;
 		$IsAdminData = $this->dbc->get_result($is_admin_qry);

		$to=$IsAdminData[0]['email'];
		$name=$IsAdminData[0]['first_name'].' ' .$IsAdminData[0]['last_name'];

		$subject="New Labour Bill  ";
		$body="<h4> Dear ".$name ."</h4>";
		$body.='Labour bill is generated against : <br/><br/>';
		$body.='Tender Name : '.$getTenderData[0]['TenderName'].' <br/>';
		$body.='Vendor Name : '.$VendorData[0]['name'].' <br/>';
		$body.='Bill Number : '.$_REQUEST['BillNumber'].' <br/>';
		$body.='<br/>';
		$body.='Thank you. <br/>';
		$body.='Via Web ERP <br/>';
		$body.='Dev Engineers <br/>';

		$host = SITE_NAME;
		$from = SITE_USER;
		$password = SITE_PASS;
		$port = SITE_PORT;

		$Common=new CommonFunction($this->dbc);
		$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port,$target_1);

		ajaxResponse("1", '');

	}

	public function FetchAllRequestedPO(){

		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_SESSION['ROLE_ID']) || ($_SESSION['ROLE_ID'] == "") ){
			ajaxResponse("0", 'ROLE_ID is null');
		}
		
		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];
		$isAdmin=0;
		if($Role_id==1){
			$AddQry="";
			$isAdmin=1;
		}else if($Role_id==2 || $Role_id==4){
			$AddQry=" AND ( a.SiteIncharge=".$User_id." OR FIND_IN_SET(".$User_id.", a.SiteEngineer) )" ;
		}else if($Role_id==3){
			$AddQry=" AND a.SiteSupervisor=".$User_id." " ;
		}



		$sql= "SELECT b.*,a.TenderName,c.description,CONCAT( SUBSTRING(c.description, 1, 5) , ' ...')AS DescShort,c.item,c.total_qty,b.quantity FROM boq_order b 
				INNER JOIN tenders a ON b.tender_id=a.id 
				INNER JOIN tender_boq_excel c ON b.boq_id=c.id 
				WHERE b.confirm=0 AND b.rejected=0".$AddQry;
		$result = $this->dbc->get_result($sql);
		$data=array("POs"=>$result,"isAdmin"=>$isAdmin);

		ajaxResponse("1", $data);

	}


	function RequestedPOs(){


		$userid = $roleid = $tenderid = 0;

        $userid=$_SESSION['USER_ID'];
		$roleid=$_SESSION['ROLE_ID'];

        $Whr_role = '';
        if ($roleid > 1) {
            $Whr_role = ' AND r.created_by = ' . $userid;
        }

        $Whr_tender = '';
		$tenderid=$_REQUEST['tender_id'];

        if ($tenderid !='') {
            $Whr_tender = ' AND r.tender_id = ' . $tenderid;
        }

        $sql = "SELECT r.id, r.tender_id, r.boq_id, r.required_by, t.TenderName, b.item AS boq, CONCAT(u.first_name, ' ', u.last_name ) AS requested_by, r.approved,

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
                INNER JOIN users u ON u.id = r.created_by

                WHERE r.received = 0 " . $Whr_role . $Whr_tender . " 
                ORDER BY r.id DESC ";


                // echo $sql;
		$result = $this->dbc->get_result($sql);
		$data=array("RequestedPO"=>$result);
		ajaxResponse("1", $data);
	}


	public function AllTenders(){

		$sql0= "SELECT id,TenderName FROM tenders WHERE deleted=0 ORDER BY TenderName ASC ";
		$result0 = $this->dbc->get_result($sql0);
		$data=array("Tender"=>$result0);
		ajaxResponse("1", $data);
	}

	public function sendMessage(){

        $data=array();
		$data['sender_id']=$_SESSION['USER_ID'];
		$data['message']=$_REQUEST['message'];
		$data['chatbox_id']=$_REQUEST['po_id'];
		$data['created_at']=date('Y-m-d H:i:s');
		// $data['receiver_id']=0;
		// print_r($data);

		$result=$this->dbc->insert_query($data,"chats");

		$chats = $this->dbc->get_insert_id();

		if($chats>=1){
			$data=array("Message"=>'chat added successfully');
			ajaxResponse("1", $data);

		}
        


	}

	public function getMessages(){
		$po_id=$_REQUEST['po_id'];

		$sql= "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) AS username FROM chats c INNER JOIN users u ON u.id= c.sender_id WHERE c.deleted=0  AND  c.chatbox_id = '$po_id' ";
		$result = $this->dbc->get_result($sql);

		$data=array("po_id"=>$po_id,"Messages"=>$result,"user_id"=>$_SESSION['USER_ID']);
		ajaxResponse("1", $data);

	}


}
?>



