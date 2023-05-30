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
	//$fp = file_put_contents('./request.log', print_r($data), FILE_APPEND);

        print_r($data);
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
        $opData->email  =   $result[0]['email'];
        $opData->ROLE   =   $result[0]['roleName'];
        $opData->ROLE_ID    =   $result[0]['role'];
        $opData->IS_ADMIN   =  $result[0]['is_admin'];
        
        $sql = "SELECT id,TenderName FROM `tenders` where SiteIncharge = $userid or find_in_set($userid, SiteSupervisor) <> 0";
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
