<?php
class Employees {

	private $dbc;
  	private $error_message;

  	function __construct($dbc){
	    $this->dbc = $dbc;
	    $this->error_message="";
	}

	public function Role(){
		$sql= "SELECT * FROM role";
		$result = $this->dbc->get_result($sql);
		$data=array("Role"=>$result);
		ajaxResponse("1", $data);
	}

	public function dashboard(){
		if(!isset($_SESSION['USER_ID']) || ($_SESSION['USER_ID'] == "") ){
			ajaxResponse("0", 'USER_ID is null');
		}
		if(!isset($_SESSION['ROLE_ID']) || ($_SESSION['ROLE_ID'] == "") ){
			ajaxResponse("0", 'ROLE_ID is null');
		}

		$sql1= "SELECT count(id) As Count FROM users WHERE role!=1 AND deleted=0";
		$sql2= "SELECT count(id) As Count FROM vendors WHERE deleted=0";

		$result1 = $this->dbc->get_result($sql1);
		$result2 = $this->dbc->get_result($sql2);

		$sql5= "SELECT SUM(EMDAmount) AS EMDAmount,SUM(ASDAmount) AS ASDAmount, SUM(BankAmount) AS BankAmount FROM tenders WHERE deleted=0";
		$result5 = $this->dbc->get_result($sql5);

		$CheckZeroEMDAmount=0;
		$CheckZeroASDAmount=0;
		$CheckZeroBankAmount=0;


		if($result5[0]['EMDAmount']<=0)$CheckZeroEMDAmount=1;
		if($result5[0]['ASDAmount']<=0)$CheckZeroASDAmount=1;
		if($result5[0]['BankAmount']<=0)$CheckZeroBankAmount=1;



		$User_id=$_SESSION['USER_ID'];
		$Role_id=$_SESSION['ROLE_ID'];
		$admin=0;
		$Accounts=0;

		if($Role_id==1){
			$AddQry="";
			$admin=1;
		}else if($Role_id==2 || $Role_id==4){
			$AddQry=" AND (a.SiteIncharge = ".$User_id." OR FIND_IN_SET(".$User_id.", a.SiteEngineer) )";
		}else if($Role_id==3){
			$AddQry=" AND FIND_IN_SET(".$User_id.",a.SiteSupervisor ) " ;
		}else if($Role_id==5){
			$Accounts=1;
			$AddQry="";
		}

		$sql3= "SELECT count(a.id) As Count FROM tenders a WHERE a.deleted=0 ".$AddQry;
		// echo $sql3;
		$result3 = $this->dbc->get_result($sql3);

		// $sql01= "SELECT GROUP_CONCAT(id) AS BOQ_Ids FROM tender_boq_excel WHERE deleted =0 ";
		// $result01 = $this->dbc->get_result($sql01);
		// $BOQ_Ids=$result01[0]['BOQ_Ids'];

		$requestCount= "SELECT count(b.id) As Count FROM po_request b 
						INNER JOIN tender_boq_excel c ON c.id=b.boq_id 
						INNER JOIN tenders a ON a.id=c.tender_id 
						WHERE c.deleted=0 AND b.approved=1 AND b.status=0 AND b.purchase=0 AND b.reject=0 ".$AddQry;
		$requestCountData = $this->dbc->get_result($requestCount);

		$requestCountUnAproved= "SELECT count(b.id) As Count FROM po_request b 
						INNER JOIN tender_boq_excel c ON c.id=b.boq_id 
						INNER JOIN tenders a ON a.id=c.tender_id 
						WHERE c.deleted=0 AND b.approved=0 AND b.status=0 AND b.purchase=0 AND b.reject=0 ".$AddQry;
		$requestCountDataUnAproved = $this->dbc->get_result($requestCountUnAproved);


		$sql4= "SELECT a.id, a.TenderName,a.WorkOrderNo, b.user_name AS SiteSupervisor,
					( SELECT count(f.id) As Count FROM po_request f 
							INNER JOIN tender_boq_excel g ON g.id=f.boq_id 
							INNER JOIN tenders h ON h.id=g.tender_id 
							WHERE f.status=0  AND f.approved=1  AND f.purchase=0 AND f.reject=0 AND h.id=a.id AND g.deleted=0) AS orderRequest,

					( SELECT count(f.id) As Count FROM po_request f 
							INNER JOIN tender_boq_excel g ON g.id=f.boq_id 
							INNER JOIN tenders h ON h.id=g.tender_id 
							WHERE f.status=0  AND f.approved=0  AND f.purchase=0 AND f.reject=0 AND h.id=a.id AND g.deleted=0) AS UnaprovedOrderRequest, 
					
					(a.EMDAmount + a.ASDAmount +a.BankAmount) AS TotalAmout,a.TenderEndDate, IF(a.TenderEndDate>=CURDATE(), 1, 0) AS DateCheck,
					(SELECT SUM(po.total_amount) FROM po_request po WHERE po.tender_id=a.id )AS PO_total,tlc.above_below,tlc.percentage,

					(SELECT SUM(bill_amount) FROM tender_labour_bills WHERE tender_id=a.id AND deleted=0) AS labour_amount_spend,
					tlc.tender_amount, tlc.percentage,  tlc.above_below,tlc.labour_amount,(tlc.labour_amount * 0.02) AS misc_amount
					

					FROM tenders a 
					INNER JOIN users b on a.SiteSupervisor=b.id
					LEFT OUTER JOIN tender_labour_cost tlc ON tlc.tender_id=a.id WHERE a.deleted=0 ".$AddQry ." ORDER BY a.id DESC LIMIT 10";

		// $sql4= "SELECT a.id, a.TenderName,a.WorkOrderNo, b.user_name AS SiteSupervisor,
		// 			( SELECT count(f.id) As Count FROM po_request f 
		// 					INNER JOIN tender_boq_excel g ON g.id=f.boq_id 
		// 					INNER JOIN tenders h ON h.id=g.tender_id 
		// 					WHERE f.status=0 AND f.purchase=0 AND f.reject=0 AND h.id=a.id AND g.deleted=0) AS orderRequest, 
		// 			(a.EMDAmount + a.ASDAmount +a.BankAmount) AS TotalAmout,a.TenderEndDate, IF(a.TenderEndDate>=CURDATE(), 1, 0) AS DateCheck,
		// 			(SELECT SUM(po.total_amount) FROM po_request po WHERE po.tender_id=a.id )AS PO_total,tlc.above_below,tlc.percentage,
		// 			tlc.labour_amount,(tlc.labour_amount * 0.02) AS misc_amount ,
		// 			FROM tenders a 
		// 			INNER JOIN users b on a.SiteSupervisor=b.id
		// 			LEFT OUTER JOIN tender_labour_cost tlc ON tlc.tender_id=a.id WHERE a.deleted=0 ".$AddQry ." ORDER BY a.id DESC LIMIT 6";
		// echo $sql4;die;
		$result4 = $this->dbc->get_result($sql4);


		$data=array("Users"=>$result1[0]['Count'],"Vendors"=>$result2[0]['Count'],"TOTALEMD"=>$result5[0]['EMDAmount'],"TOTALASD"=>$result5[0]['ASDAmount'],"TOTALBG"=>$result5[0]['BankAmount'],"Tenders"=>$result3[0]['Count'],"TenderList"=>$result4,"admin"=>$admin, "Accounts"=>$Accounts,"TOTALREQUEST"=>$requestCountData[0]['Count'],"CheckZeroEMDAmount"=>$CheckZeroEMDAmount,"CheckZeroASDAmount"=>$CheckZeroASDAmount,"CheckZeroBankAmount"=>$CheckZeroBankAmount, "TOTALREQUEST_UnAproved"=>$requestCountDataUnAproved[0]['Count']);
		ajaxResponse("1", $data);
	}

	public function Register(){
		$Today=date("Y-m-d");
		
		if(!isset($_REQUEST['first_name']) || ($_REQUEST['first_name'] == "") ){
			ajaxResponse("0", 'First name is null');
		}
		if(!isset($_REQUEST['last_name']) || ($_REQUEST['last_name'] == "") ){
			ajaxResponse("0", 'Last name is null');
		}
		if(!isset($_REQUEST['user_name']) || ($_REQUEST['user_name'] == "") ){
			ajaxResponse("0", 'User name is null');
		}
		if(!isset($_REQUEST['email']) || ($_REQUEST['email'] == "") ){
			ajaxResponse("0", 'Email is null');
		}
		if(!isset($_REQUEST['password']) || ($_REQUEST['password'] == "") ){
			ajaxResponse("0", 'Password is null');
		}
		if(!isset($_REQUEST['mobile']) || ($_REQUEST['mobile'] == "") ){
			ajaxResponse("0", 'Mobile is null');
		}
		if(!isset($_REQUEST['role']) || ($_REQUEST['role'] == "") ){
			ajaxResponse("0", 'Role is null');
		}

		$id=isset($_REQUEST['id']) ? $_REQUEST['id'] : 0 ;

		$user_name=$_REQUEST['user_name'];
		$sql0= "SELECT count(id) AS count FROM users WHERE user_name='$user_name' AND deleted=0 AND id !='$id'";
		$result0 = $this->dbc->get_result($sql0);

		if($result0[0]['count']>0){
			ajaxResponse("0", 'Please Choose a different user name');
		}else{
			$data=array();
			$data["first_name"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['first_name']));
			$data["last_name"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['last_name']));
			$data["user_name"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['user_name']));
			$data["email"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['email']));
			$data["password"]=md5($_REQUEST['password']);
			$data["mobile"]=$_REQUEST['mobile'];
			$data["verify"]=1;
			$data["enable"]=1;
			$data["role"]=$_REQUEST['role'];
			if($_REQUEST['role']==1) $data["is_admin"]=1;
			else $data["is_admin"]=0;

			// $data["is_admin"]=$_REQUEST['is_admin'];

			if(isset($_REQUEST['SendEmail'])){
				//Work to be done
				// $Common=new CommonFunction($dbc);
				// $Common->sendEmail($subject,$body,$to,$from,$host,$password,$port );
			}
			
			$save=$_REQUEST['save'];
			if($save=='add'){
				$data["created_by"]=$_SESSION['USER_ID'];
				$data["created_date"]=date('Y-m-d H:i:s');
				$this->dbc->insert_query($data,"users");
				// $this->dbc->Create_Insert_Query("users",$data);

				ajaxResponse("1", '');
			}else if($save=='update'){
				$data["updated_by"]=$_SESSION['USER_ID'];
				$data["updated_date"]=date('Y-m-d H:i:s');

				$data_id=array();
				$data_id["id"]=$_REQUEST['id'];
				$this->dbc->update_query($data, 'users', $data_id);
				// $this->dbc->Create_Update_Query("users",$data,$data_id);
				ajaxResponse("1", '');
			}
			// ajaxResponse("1", '');
		}

	}

	public function EmployeeList(){
		$sql= "SELECT a.*, b.name AS roleName FROM users a INNER JOIN role b ON b.id=a.role WHERE a.deleted=0 ORDER BY a.user_name";
		$result = $this->dbc->get_result($sql);
		// $result = $this->dbc->get_rows($sql);
		$data=array("Employees"=>$result);
		ajaxResponse("1", $data);
	}

	public function DeleteUser(){
		$data=array();
		$data["deleted"]=1;
		$data["deleted_by"]=$_SESSION['USER_ID'];
		$data["deleted_date"]=date('Y-m-d H:i:s');
		$data_id=array();
		$data_id["id"]=$_REQUEST['id'];
		$this->dbc->update_query($data, 'users', $data_id);
		ajaxResponse("1", 'Deleted Successfully');

	}

	public function CheckUserName(){
		$user_name=$_REQUEST['user_name'];
		$sql= "SELECT count(id) AS count FROM users WHERE user_name='$user_name' AND deleted=0";
		$result = $this->dbc->get_result($sql);
		ajaxResponse("1", $result[0]['count']);
	}

	


  	public function Login(){
    	if(isset($_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'] )&&  $_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'] !='' ){
			$user_name=$_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'];
			$password=$_COOKIE['KEEP_ME_LOGGED_IN_PASSWORD'];
    	}else if(isset($_REQUEST['user_name'] ) && isset($_REQUEST['password'] )){
			$user_name=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['user_name']));
			$password=md5($_REQUEST['password']);
    	}
		
		$sql="SELECT a.*, b.name AS roleName FROM users a INNER JOIN role b ON b.id=a.role WHERE a.user_name='$user_name' AND a.password='$password' AND deleted=0";
		$result = $this->dbc->get_result($sql);

		if(!isset($result[0]['user_name'])){
			$sql1="SELECT count(id) AS count FROM users  WHERE user_name='$user_name' AND deleted=0";
			$result1 = $this->dbc->get_result($sql1);
			if($result1[0]['count']==0){
				// $Message='Incorrect User Name...';
				$data=2;
			}else{
				$sql2="SELECT count(id) AS count FROM users  WHERE user_name='$user_name' AND  password='$password' AND deleted=0";
				$result2 = $this->dbc->get_result($sql2);
				if($result2[0]['count']==0){
					// $Message='Incorrect Password...';
					$data=3;
				}

			}
			ajaxResponse("0", $data);
		}else{
			
		 	$_SESSION['SITE']='NEWPROJECT';
		 	$_SESSION['USER_ID']=$result[0]['id'];
			$_SESSION['USER_FIRST_NAME']=$result[0]['first_name'];
			$_SESSION['USER_LAST_NAME']=$result[0]['last_name'];
			$_SESSION['USER_NAME']=$result[0]['user_name'];
		    $_SESSION['USER_EMAIL']=$result[0]['email'];
		 	$_SESSION['ROLE']=$result[0]['roleName'];
		 	$_SESSION['ROLE_ID']=$result[0]['role'];
		 	$_SESSION['IS_ADMIN']=$result[0]['is_admin'];
	   		
	 	}

    	if(isset($_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'] ) &&  $_COOKIE['KEEP_ME_LOGGED_IN_USER_NAME'] !='' ){
			header("Location: dashboard.php");
    	}else{

		 	if(isset($_REQUEST['RememberMe'])){
		 		setcookie('KEEP_ME_LOGGED_IN_USER_NAME',$user_name, time()+2592000,'/');
				setcookie('KEEP_ME_LOGGED_IN_PASSWORD',$password, time()+2592000,'/');
			}else{
				setcookie('KEEP_ME_LOGGED_IN_USER_NAME',"", time()-1,'/');
				setcookie('KEEP_ME_LOGGED_IN_PASSWORD',"", time()-1,'/');
			}

			$data=array("role"=>$result[0]['role']); 
			ajaxResponse("1", $data);
    	}

	}	

	public function ForgotPassword(){
		if(!isset($_REQUEST['email']) || ($_REQUEST['email'] == "") ){
			ajaxResponse("0", 'Email Not  Found');
		}

		$email=$_REQUEST['email'];


		$sql="SELECT id, CONCAT(first_name ,' ', last_name) AS name, user_name, passwsord_updated FROM users WHERE email='$email' ";
		$result = $this->dbc->get_result($sql);

		if(!isset($result[0]['id'])){
			ajaxResponse("0", 'Email Not  Found');
		}else{
			$token = md5($email).rand(10,9999);

			$expFormat = mktime( date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y") );
		    $expDate = date("Y-m-d H:i:s",$expFormat);

	    	$link = "<a href='https://devengineers.com/tenderm/ResetPassword.php?key=".$email."&token=".$token."'>Click To Reset password</a>";
	    	// $link = "<a href='http://localhost/tender/ResetPassword.php?key=".$email."&token=".$token."'>Click this link to reset your password</a>";

	    	$data=array();
			$data["reset_token"]=$token;
			$data["password"]='xxxxx';
			$data["exp_date"]=$expDate;
			$data_id=array();
			$data_id["email"]=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['email']));
			$this->dbc->update_query($data, 'users', $data_id);

			$to=$email;
			$name=$result[0]['name'];
			$UserName=$result[0]['user_name'];

			$subject="Reset Password for site Devengineers";
			$body="<h4> Dear ".$name ."</h4>";
			$body.='User Name : '.$UserName.' <br/><br/>';
			$body.= $link .' <br/><br/>';
			$body.='Thank you. <br/>';
			$body.='--<br/>';
			$body.='Via Web ERP <br/>';

			$host = SITE_NAME;
			$from = SITE_USER;
			$password = SITE_PASS;
			$port = SITE_PORT;

			$Common=new CommonFunction($this->dbc);
			$Mail=$Common->sendEmail($subject, $body, $to, $from, $host, $password, $port);

			if($Mail==1){
				ajaxResponse("1", 'Check Your Mail');
			}else{
				ajaxResponse("0", 'Error in send mail');
			}
		}


		
	}


	public function ResetPassword(){
		if(!isset($_REQUEST['email']) || ($_REQUEST['email'] == "") ){
			ajaxResponse("0", 'Email Not  Found');
		}
		if(!isset($_REQUEST['token']) || ($_REQUEST['token'] == "") ){
			ajaxResponse("0", 'Tocken not  Found');
		}
		$email=mysqli_real_escape_string($this->dbc, stripcslashes($_REQUEST['email']));
		$token=$_REQUEST['token'];
		$expFormat = mktime( date("H"), date("i"), date("s"), date("m"), date("d"), date("Y") );
		$expDate = date("Y-m-d H:i:s", $expFormat);

		$sql="SELECT id, CONCAT(first_name ,' ', last_name) AS name, user_name  FROM users WHERE email='$email' AND reset_token= '$token' AND exp_date>'$expDate'";
		// echo $sql;
		$result = $this->dbc->get_result($sql);

		if(!isset($result[0]['id'])){
			ajaxResponse("0", 'link expired contact administrator');
		}else{
	    	$data=array();
			$data["password"]=md5($_REQUEST['password']);
			$data["reset_token"]='';
			$data["exp_date"]='';
			$data_id=array();
			$data_id["email"]=$_REQUEST['email'];
			$this->dbc->update_query($data, 'users', $data_id);
			ajaxResponse("1", 'Password Updated Successfully');
		}


		
	}











}
?>



