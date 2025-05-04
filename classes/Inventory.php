<?php
class Inventory {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";

		
	}


	public function Documents(){

		$sql1= "SELECT i.id,i.doc_id,i.name,i.description ,i.count ,i.value ,t.TenderName AS tender_name ,it.name AS type  
				FROM inventory i  
				INNER JOIN inventory_type it ON i.type=it.id
				INNER JOIN tenders t ON i.tender_id=t.id
				WHERE i.deleted=0";
		$result1 = $this->dbc->get_result($sql1);

		$User_id=$_SESSION['USER_ID'];
		$AdminQry= "SELECT is_admin FROM users  WHERE id=".$User_id;
		$AdminData = $this->dbc->get_result($AdminQry);
		$admin=0;
		if($AdminData[0]['is_admin']==1){
			$admin=1;
		}


		$data=array("Doc"=>$result1, "admin"=>$admin );
		ajaxResponse("1", $data);
	}
	public function SelectInvDoc(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'Id is null');
		}
		$id=$_REQUEST['id'];
		$sql1= "SELECT * FROM inventory WHERE id=".$id;
		$result1 = $this->dbc->get_result($sql1);
		$data=array("Inventory"=>$result1[0]);
		ajaxResponse("1", $data);
	}

	public function AddNew_TypeLoc(){
		$sql1= "SELECT id, name FROM inventory_type WHERE deleted=0";
		$sql2= "SELECT id, TenderName AS name FROM tenders WHERE deleted=0 ORDER BY TenderName ASC";
		$result1 = $this->dbc->get_result($sql1);
		$result2 = $this->dbc->get_result($sql2);
		$data=array("InventoryType"=>$result1,"Tender"=>$result2);
		ajaxResponse("1", $data);
	}

	public function RegisterInventoryDocument(){
		
		if(!isset($_REQUEST['doc_id']) || ($_REQUEST['doc_id'] == "") ){
			ajaxResponse("0", 'Id is null');
		}
		if(!isset($_REQUEST['doc_type']) || ($_REQUEST['doc_type'] == "") ){
			ajaxResponse("0", 'Type is null');
		}
		if(!isset($_REQUEST['doc_name']) || ($_REQUEST['doc_name'] == "") ){
			ajaxResponse("0", 'Name is null');
		}
		if(!isset($_REQUEST['doc_tender_id']) || ($_REQUEST['doc_tender_id'] == "") ){
			ajaxResponse("0", 'Tender id is null');
		}
		if(!isset($_REQUEST['doc_description']) || ($_REQUEST['doc_description'] == "") ){
			ajaxResponse("0", 'Description is null');
		}
		if(!isset($_REQUEST['doc_count']) || ($_REQUEST['doc_count'] == "") ){
			ajaxResponse("0", 'count is null');
		}
		if(!isset($_REQUEST['doc_value']) || ($_REQUEST['doc_value'] == "") ){
			ajaxResponse("0", 'value is null');
		}
		


		$data=array();
		$data["doc_id"]=$_REQUEST['doc_id'];
		$data["type"]=$_REQUEST['doc_type'];
		$data["name"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['doc_name']));
		$data["tender_id"]=$_REQUEST['doc_tender_id'];
		$data["description"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['doc_description'])));
		$data["count"]=$_REQUEST['doc_count'];
		$data["value"]=$_REQUEST['doc_value'];
		$data["insurance_expiry_date"]=$_REQUEST['doc_insurance_expiry_date'];
		$data["fitness_expiry_date"]=$_REQUEST['doc_fitness_expiry_date'];
		
		$save=$_REQUEST['save'];
		$result = "";
		if($save=='add'){
			$data["created_by"]=$_SESSION['USER_ID'];
			$data["created_date"]=date('Y-m-d H:i:s');

			$result = $this->dbc->insert_query($data,"inventory");
		}else if($save=='update'){
			$data["updated_by"]=$_SESSION['USER_ID'];
			$data["updated_date"]=date('Y-m-d H:i:s');
			$data_id=array();
			$data_id["id"]=$_REQUEST['id'];
			$result = $this->dbc->update_query($data, 'inventory', $data_id);
			
		}
		if($result){
			ajaxResponse("1", 'Record Updated');
		}else{
			ajaxResponse("0", 'Failed');
		}



	}


	public function DeleteInvDoc(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'Id is null');
		}
		$data=array();
		$data["deleted"]=1;
		$data["deleted_by"]=$_SESSION['USER_ID'];
		$data["deleted_date"]=date('Y-m-d H:i:s');
		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$result = $this->dbc->update_query($data, 'inventory', $data_id);

		// die('here');
		if($result){
			ajaxResponse("1", 'Record Deleted Successfully');
		}else{
			ajaxResponse("0", 'Failed');
		}



	}



}

?>



