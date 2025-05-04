<?php
include "config.php";


/*
$req_dump = print_r($_FILES, true);
$fp = file_put_contents('./request.log', $req_dump, FILE_APPEND);

$req_dump = print_r($_REQUEST, true);
$fp = file_put_contents('./request.log', $req_dump, FILE_APPEND);
*/

$data = json_decode(file_get_contents('php://input'), true);


$req_dump = print_r($data, true);
$fp = file_put_contents('./request.log', $req_dump, FILE_APPEND);


if(isset($data['action']) &&  $data['action'] != '') {

    $action = $dbc->real_escape_string(trim($data['action']));

    if($action == "userLogin") {

        $user_name=$dbc->real_escape_string(trim($data['username']));
        $password=md5($dbc->real_escape_string(trim($data['password'])));

        $data = Login($user_name, $password);
	    $fp = file_put_contents('./request.log', print_r($data), FILE_APPEND);

        // print_r($data);

    }else if($action == "userRegister"){
        $first_name=$dbc->real_escape_string(trim($data['first_name']));
        $last_name=$dbc->real_escape_string(trim($data['last_name']));
        $mobile=$dbc->real_escape_string(trim($data['mobile']));
        $email=$dbc->real_escape_string(trim($data['email']));
        $user_name=$dbc->real_escape_string(trim($data['user_name']));
        // $password=$dbc->real_escape_string(trim($data['password']));
        $password=md5($dbc->real_escape_string(trim($data['password'])));
        

        Register($user_name, $password,$first_name,$last_name,$mobile,$email);
    }

}
function Register($user_name, $password, $first_name, $last_name, $mobile, $email){
    global $dbc; 
    $opData = new stdClass(); // fix: define opData

    // Secure inputs
    // $user_name = mysqli_real_escape_string($dbc->conn, $user_name);
    $sql = "SELECT id,user_name FROM users WHERE user_name='$user_name'";
    $result = $dbc->get_result($sql);

    if (isset($result[0]['id']) && $result[0]['user_name']== $user_name) {
        $opData->status = "fail";
        $opData->message = "Username already exists";
        echo json_encode($opData);
        exit;

    }else{

        $data = array(
            'user_name'     => $user_name,
            'password'      => $password, 
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'mobile'        => $mobile,
            'is_admin'      => 0,
            'role'          => 4,
            'verify'        => 1,
            'enable'        => 1,
            'created_by'    => 39,
            'created_date'  => date('Y-m-d H:i:s')
        );

        if ($dbc->insert_query($data, "users")) {
            // Login($user_name,$password);
            $opData->status = "success";
            $opData->message = "Registered Successfully";

        } else {
            $opData->status = "fail";
            $opData->message = "Database error: " . $dbc->error;
            
        }
        echo json_encode($opData);
        exit;
    }
}


function Login($user_name,$password){

    global $dbc; 
   
    $opData = new stdClass();
    
    $sql="SELECT a.*, b.name AS roleName FROM users a INNER JOIN role b ON b.id=a.role WHERE a.user_name='$user_name' AND a.password='$password' AND deleted=0";

	$fp = file_put_contents('./request.log', $sql, FILE_APPEND);

    $result = $dbc->get_result($sql);

    if(!isset($result[0]['user_name'])){
        
        $opData->status = "fail";

    }else{

        $opData->userid = $userid = $result[0]['id'];
        $opData->status     =   "success";
        $opData->session     =   struuid(true);
        $opData->user_name = $result[0]['user_name'];
        $opData->name = $result[0]['first_name'] . ' ' . $result[0]['last_name'];
        $opData->email  =   $result[0]['email'];
        $opData->first_name = $result[0]['first_name'];
        $opData->last_name = $result[0]['last_name'];
        $opData->mobile = $result[0]['mobile'];
        $opData->ROLE   =   $result[0]['roleName'];
        $opData->ROLE_ID    =   $result[0]['role'];
        $opData->IS_ADMIN   =  $result[0]['is_admin'];

        
        if($result[0]['role']==1 || $result[0]['role']=='1'){
            $sql = "SELECT t.id,t.TenderName, t.TenderStartDate, u.mobile AS site_incharge_mobile 
                FROM `tenders` t INNER JOIN users u ON u.id=t.SiteIncharge WHERE u.deleted=0";
        }else{

            $sql = "SELECT t.id,t.TenderName, t.TenderStartDate, u.mobile AS site_incharge_mobile 
                FROM `tenders` t  
                INNER JOIN users u ON u.id=t.SiteIncharge WHERE t.SiteIncharge = $userid or find_in_set($userid, t.SiteSupervisor) <> 0 or find_in_set($userid, t.SiteEngineer) <> 0 ";
        }

	    $fp = file_put_contents('./request.log', $sql, FILE_APPEND);

        $result = $dbc->get_result($sql);
   
        if(count($result)) {
            $opData->tenders = $result;
        }

        $sql = "SELECT id,company_name FROM `vendors` order by company_name asc"; // where SiteIncharge = $userid or SiteSupervisor = $userid";
	   $fp = file_put_contents('./request.log', $sql, FILE_APPEND);
        $result = $dbc->get_result($sql);

        if(count($result)) {
            $opData->vendors = $result;
        }

        $sql = "SELECT * FROM `expense_type` order by type asc";
        $result = $dbc->get_result($sql);

        if(count($result)) {
            $opData->expenseTypes = $result;
        }
           
     }
  
     return json_encode($opData);
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
