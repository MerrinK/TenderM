<?php
class Vendors {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";

		
	}

	public function VendorList(){
		$sql= "SELECT * FROM vendors WHERE deleted=0 ORDER BY company_name ASC";
		$result = $this->dbc->get_result($sql);
		
		$User_id=$_SESSION['USER_ID'];

		$ind=-1;
		$vendor=[];
		foreach ($result as $row){
			$ind++;
			if($row['created_by']==$User_id){
				$row['EditPermit']=1;

			}else{
				$row['EditPermit']=0;
			}
			// echo $row['EditPermit'] .' ';
			$vendor[$ind]=$row;
		}


		$is_admin_qry="SELECT is_admin FROM users WHERE id=".$User_id;
		// echo $is_admin_qry;
 		$IsAdminData = $this->dbc->get_result($is_admin_qry);



		$data=array("Vendor"=>$vendor,"admin"=>$IsAdminData[0]['is_admin']);
		ajaxResponse("1", $data);
	}

	public function SelectVendorOfId(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$id=$_REQUEST['id'];
		$sql= "SELECT address FROM vendors WHERE id='$id'";
		$result = $this->dbc->get_result($sql);
		$data=array("Vendor"=>$result[0]);
		ajaxResponse("1", $data);
	}
	


	public function RegisterVendor(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}

		if(!isset($_REQUEST['company_name']) || ($_REQUEST['company_name'] == "") ){
			ajaxResponse("0", 'Company name is null');
		}
		if(!isset($_REQUEST['email']) || ($_REQUEST['email'] == "") ){
			ajaxResponse("0", 'Email is null');
		}
		if(!isset($_REQUEST['name']) || ($_REQUEST['name'] == "") ){
			ajaxResponse("0", 'Name is null');
		}
		// if(!isset($_REQUEST['gst_no']) || ($_REQUEST['gst_no'] == "") ){
		// 	ajaxResponse("0", 'gst no is null');
		// }

		if(!isset($_REQUEST['mobile']) || ($_REQUEST['mobile'] == "") ){
			ajaxResponse("0", 'Mobile is null');
		}
		if(!isset($_REQUEST['address']) || ($_REQUEST['address'] == "") ){
			ajaxResponse("0", 'Address is null');
		}
		

		$data=array();
		$data["company_name"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['company_name']));
		$data["email"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['email']));
		$data["name"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['name']));
		$data["gst_no"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['gst_no']));
		$data["mobile"]=$_REQUEST['mobile'];
		$data["address"]=mysqli_real_escape_string($this->dbc, stripcslashes(preg_replace('/[\x00-\x1F\x7F]/u', '', $_REQUEST['address'])));
		$data["pan_card"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['pan_card']));
		$data["aadhaar_card"]=$_REQUEST['aadhaar_card'];
		$data["location"]=$_REQUEST['location_Multiple'];
		$save=$_REQUEST['save'];

		$result = "";
			
		if($save=='add'){
			$data["created_by"]=$_SESSION['USER_ID'];
			$data["created_date"]=date('Y-m-d H:i:s');

			$result = $this->dbc->insert_query($data,"vendors");
		}else if($save=='update'){

			$data["updated_by"]=$_SESSION['USER_ID'];
			$data["updated_date"]=date('Y-m-d H:i:s');
			$data_id=array();
			$data_id["id"]=$_REQUEST['id'];
			$result = $this->dbc->update_query($data, 'vendors', $data_id);
			
		}
		if($result){
			ajaxResponse("1", '');
		}else{
			ajaxResponse("0", '');
		}
		

	}



	public function DeleteVendor(){
		if(!isset($_REQUEST['id']) || ($_REQUEST['id'] == "") ){
			ajaxResponse("0", 'id is null');
		}

		$data=array();
		$data["deleted"]=1;
		$data["deleted_by"]=$_SESSION['USER_ID'];
		$data["deleted_date"]=date('Y-m-d H:i:s');

		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($data, 'vendors', $data_id);
		ajaxResponse("1", '');

	}

	public function GetLocations(){
		$sql= "SELECT id, name FROM location";
		$result = $this->dbc->get_result($sql);
		$data=array("Locations"=>$result);
		ajaxResponse("1", $data);
	}
	


}
?>



