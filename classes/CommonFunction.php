<?php 
include_once("config.php");
// require_once('excel/Spreadsheet/Excel/Reader/OLERead.php');
require_once('phpMailer/PHPMailerAutoload.php');
require_once("excel/PHPExcel.php");
require_once("classes/256.php");
// include_once(APL."MCrypt.php");

class CommonFunction
{
		
    # Constructor Method 
    function __constructor(){
    }
    
    public static function getCommandList(){
	  $commands = array(ON_BUTTON=>"Light ON", CANCEL_BUTTON=>"Light OFF",LIGHT_DIM=>"Light Dim",OVERRIDE_1=>"Scene 1",OVERRIDE_2=>"Scene 2",OVERRIDE_3=>"Scene 3");
      return $commands;
	}
	
	public static function safeExecution($fnHandle)
	{
		$result="";
		ob_start();
		try {
			if (!isset($fnHandle)) throw new Exception("Process function is not defined");
			$fnHandle();
		}
		catch (Throwable $t)
		{
			$result.=$t->getMessage();
		}
		catch(Exception $e)
		{
			$result.=$t->getMessage();
		}
		$result .= ob_get_contents();
		ob_end_clean();
		$result=str_ireplace("'","",$result);
		return $result;
	}
	
	public static function updateProcessLog($pname,$processId,$statusMsg)
	{
		$dbc= new DBC();
		//echo "insert into pmi_bprocess_log(Id,ProcessName,LastRunTime,Status) values ($processId,'$pname',Now(),'$statusMsg') on duplicate key update LastRunTime=Now(),Status='$statusMsg'";
		return $dbc->query("insert into pmi_bprocess_log(Id,ProcessName,LastRunTime,Status) values ($processId,'$pname',Now(),'$statusMsg') on duplicate key update LastRunTime=Now(),Status='$statusMsg'");
	}
	
	function invokeWebMethod($url,$to = 30)
	{
		try
		{
			//  Initiate curl
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Uncomment this line if you get no gateway response.
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch,CURLOPT_TIMEOUT,$to);

			//echo $url;
			// Set the url
			curl_setopt($ch, CURLOPT_URL,$url);
			// Execute
			$result =curl_exec($ch);

			//die("result===".$result);
			// Closing
			curl_close($ch);
			
			return $result;
		} 
		catch (Exception $ex) {
			echo "Exception is:" . $ex->getMessage() . "\n";
		}
	}
        
	function getWebURL($IPAddress,$data){
		
		$mcrypt = new MCrypt();
		$k = uniqid('SP');
		$hk = $mcrypt->encrypt($k);
		$data["k"] = $k;
		$data["hk"] = $hk;
		
		$url = str_replace("[DEVICE_IP]",$IPAddress,LOOKUP_URL);
		$postURL = $url.http_build_query($data);
		$postURL =  preg_replace( "/[\n\r]/", "", $postURL );
		//echo $postURL;
		return $postURL;
	}
	
    public static function userHasRole($role)
	{
		$loggedInUserRoleIds  = $_SESSION['LOGGED_USER_ROLE_IDS'];
		foreach($loggedInUserRoleIds as $urole)
		{
			if ( $role == $urole ) return true;
		}
		return false;
	}
	
	public static function stringToArr($str)
	{
		$str=str_ireplace("[","",$str);
		$str=str_ireplace("]","",$str);
		$str=str_ireplace("\\","",$str);
		$str=str_ireplace("\"","",$str);
		return explode(",",$str);
	}
	
	public static function userHasCluster($clustersCanRead,$clusterVal)
	{
		echo "############# userHasCluster";
		print_r($clustersCanRead);
		foreach($clustersCanRead as $cluster)
		{
			if ( $cluster === $clusterVal ) return true;
		}
		return false;
	}
				
    //Generate Random Password
    public function random_password( $length = 8 ) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if($length == 4)$chars = "0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
    
    //Send Mail
    public function sendEmail($subject,$body,$to,$from,$host,$password,$port,$filePath='',$cc='',$ccName='',$bcc='richard@florix.net',$bccName='Richard Victor Correia',$user=''){
        //date_default_timezone_set('Etc/UTC');
        $result = 0;
        try{
            $mail = new PHPMailer;
            $mail->isSMTP();
		$mail->Mailer = "smtp";
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            // $mail->SMTPDebug = 0;
            $mail->SMTPDebug = 0;
            //Ask for HTML-friendly debug output
            //$mail->Debugoutput = 'html';
            //Set the hostname of the mail server
            $mail->Host = $host;
            $mail->SMTPAuth   = true;
            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = $port;//587; //$port;
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Username to use for SMTP authentication
            $mail->Username = $from;
            //Password to use for SMTP authentication
            $mail->Password = $password;
            //Set who the message is to be sent from
            $mail->setFrom($from, 'Dev Engineers');

		$mail->SMTPAutoTLS = false;
		$mail->SMTPSecure = 'none';
			
            //Set an alternative reply-to address
            // $mail->addReplyTo($from, 'Support');
            //Set who the message is to be sent to
            
            // $mail->addAddress($to, 'User');
            $mail->addAddress($to, $user);
            if($cc !=""){
	    		$mail->addCC($cc, $ccName);
            }
            if($bcc !=""){
	    		$mail->addBCC($bcc, $bccName);
            }

            
            //Set the subject line
            $mail->Subject = $subject;


            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
			$mail->IsHTML(true);
            $mail->MsgHTML($body, dirname(__FILE__));
            if($filePath !=""){
                $mail->addAttachment($filePath);
            }
            //send the message, check for errors
            if (!$mail->send()) {
				// echo "Error while sending Email.";
				var_dump($mail);
               	$result = 0;
            } else {
				// echo "Email sent successfully";
                $result = 1;
            }
        }
        catch(Exception $e)
        {
              // echo $errorMessage =  "Error in send the email to `".$to."` : ".$mail->ErrorInfo;
        }
        return $result;
    } 

    /************************************************
    * Send response as datatable format            *
    ************************************************/
    function setDataTableContent($dataArray)
    {
        $sOutput = "";
        $data = $dataArray['Results'];
        $totalRecords = $dataArray['TotalRecords'];
        if( sizeof($data) > 0 )	
        {
            $iTotal = sizeof($data);
            $iTotalDisplayRecord = ( $totalRecords != '' ) ? $totalRecords : $iTotal ;

            $sOutput = '{'; 
            $sOutput .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
            $sOutput .= '"iTotalRecords": '.$iTotal.', ';
            $sOutput .= '"iTotalDisplayRecords": '.$iTotalDisplayRecord.', ';
            $sOutput .= '"aaData":  ';
            $sOutput .= json_encode($data);
            $sOutput .= '}';			

        }
        else
        {
            $sOutput = '{';
            $sOutput .= '"sEcho": '.intval($_REQUEST['sEcho']).', ';
            $sOutput .= '"iTotalRecords": 0, ';
            $sOutput .= '"iTotalDisplayRecords": 0, ';
            $sOutput .= '"aaData":  []}';
        }

        echo $sOutput;
    }
    
    function resizeImage($image,$width,$height,$scale) 
    {
        //echo $image."-".$width."-".$height."-".$scale;
        $image_data = getimagesize($image);
        $imageType = image_type_to_mime_type($image_data[2]);
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        echo $newImageWidth."<br>";
        echo $newImageHeight;
        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
        
        switch($imageType) {
                case "image/gif":
                        $source=imagecreatefromgif($image); 
                        break;
                case "image/pjpeg":
                case "image/jpeg":
                case "image/jpg":
                        $source=imagecreatefromjpeg($image); 
                        break;
                case "image/png":
                case "image/x-png":
                        $source=imagecreatefrompng($image); 
                        break;
        }
        imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);

        switch($imageType) {
                case "image/gif":
                        imagegif($newImage,$image); 
                        break;
                case "image/pjpeg":
                case "image/jpeg":
                case "image/jpg":
                        imagejpeg($newImage,$image,90); 
                        break;
                case "image/png":
                case "image/x-png":
                        imagepng($newImage,$image);  
                        break;
        }

        chmod($image, 0777);
        return $image;
    }
    
    /****************************************
	 * Crop the image resized here and Return thumb image name
     ***************************************/
    function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale,$location)
    {
        $image_data = getimagesize($image);
        $imageType = image_type_to_mime_type($image_data[2]);		
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);

        //echo $width . " X " .$height . "X" . $scale;

        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight) or die('Cannot Initialize new GD image stream');

        //imagejpeg($newImage,NULL,90);

        switch($imageType) {
                case "image/gif":
                        $source=imagecreatefromgif($image); 
                        break;
                case "image/pjpeg":
                case "image/jpeg":
                case "image/jpg":
                        $source=imagecreatefromjpeg($image); 
                        break;
                case "image/png":
                case "image/x-png":
                        $source=imagecreatefrompng($image); 
                        break;
        }
        imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
        switch($imageType) {
                case "image/gif":
                        imagegif($newImage,$thumb_image_name); 
                        break;
                case "image/pjpeg":
                case "image/jpeg":
                case "image/jpg":
                        imagejpeg($newImage,$thumb_image_name,90); 
                        break;
                case "image/png":
                case "image/x-png":
                        imagepng($newImage,$thumb_image_name);  
                        break;
        }
        chmod($thumb_image_name, 0777);
        //echo $location ."==". THUMBNAILS_PATH;
        if($location == THUMBNAILS_PATH)
        {
                unlink($image);
        }
        
        imagepng(imagecreatefromstring(file_get_contents($thumb_image_name)), $image);
        return $thumb_image_name;
    }
    
    public function readExcelSheet($fileName){

        // echo $fileName;
        $excelReader = PHPExcel_IOFactory::createReaderForFile($fileName);
        $excelObj = $excelReader->load($fileName);
         //die($fileName."11111111");
        return $excelObj;
        /*$data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP1251');
        $data->read($fileName);
        return $data;*/
    }
    
    
    public function getUDPPacketID($dao){
        try{
            $sql="SELECT PacketID FROM pmi_packet_settings;";
            //echo $sql;
            $rtnVal = $dao->get_single_result($sql);
            $rtnVal = !empty($rtnVal) ? $rtnVal : 5;
			//$rtnVal = ( $rtnVal < 50 ) ? 50 : $rtnVal;
            //echo "\n\nPACKET ID === $rtnVal \n\n";
            $updateVal = $rtnVal == 255 ? 5 : $rtnVal+1;
            $dao->update("UPDATE pmi_packet_settings SET PacketID = $updateVal");
            
            //echo "UPDATE pmi_packet_settings SET PacketID = $rtnVal \n\n";
            return $rtnVal;//$rtnVal["PacketID"];
        } catch (Exception $ex) {
            throw $ex;
        }
    }
	public function  generateErrorLog($dest, $title)
	{
		$db = new DBC();
		$sql = "INSERT INTO pmi_device_alerts (DeviceID, AlertDefID, Status, CratedOn,Value,Description,Severity,Title) VALUES (1 , 1 , 0 , Now(),1,'$dest',0,'$title') ";
		echo $sql;
		$rtnVal = $db->query($sql);
	}
	public function  htd($ahb, $alb)
	{
		$hb=ord($ahb);
		$lb=ord($alb);		
		$res = 255;
		if (($hb > 47) && ($hb < 58)) $res = ($hb - 48);
		
		else if (($hb > 64) && ($hb < 71)) $res = (10 + ($hb - 65));
		$res *= 16; 
		if (($lb > 47) && ($lb < 58)) $res += ($lb - 48);
		else if (($lb > 64) && ($lb < 71)) $res += (10 + ($lb - 65));

		return $res;
	}
	
    public function insertErrorLog($param=array(),$db=null) {
        
        $insertArr = array();
        $status = 0;
        if(sizeof($param) > 0){
            
            if(isset($param["DeviceID"])) $insertArr["DeviceID"] = $param["DeviceID"];
            if(isset($param["AlertDefID"])) $insertArr["AlertDefID"] = $param["AlertDefID"];
            if(isset($param["Severity"])) $insertArr["Severity"] = $param["Severity"];
            if(isset($param["Title"])) $insertArr["Title"] = $param["Title"];
            if(isset($param["Description"])) $insertArr["Description"] = $param["Description"];
            if(isset($param["Value"])) $insertArr["Value"] = $param["Value"];
            if(isset($param["Status"])) $insertArr["Status"] = $param["Status"];
            if(isset($param["AlertType"])) $insertArr["AlertType"] = $param["AlertType"];
            if(isset($param["TypeDBID"])) $insertArr["TypeDBID"] = $param["TypeDBID"];
            if(isset($param["TypeAction"])) $insertArr["TypeAction"] = $param["TypeAction"];
            if(isset($param["PolicyType"])) $insertArr["PolicyType"] = $param["PolicyType"];
            
            if(sizeof($insertArr) > 0) $status = $db->insert_query($insertArr, "pmi_device_alerts");
            
        }
        
    }

	public function encryptSalt($instanceId, $privateKey = PRIVATE_KEY) {
        return encrypt( $instanceId, $privateKey );
    }
	
	function uniqidReal($lenght = 13) {
		// uniqid gives 13 chars, but you could adjust it to your needs.
		if (function_exists("random_bytes")) {
			$bytes = random_bytes(ceil($lenght / 2));
		} elseif (function_exists("openssl_random_pseudo_bytes")) {
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
		} else {
			throw new Exception("no cryptographically secure random function available");
		}
		return substr(bin2hex($bytes), 0, $lenght);
	}
	
	public static function downloadFileFromAPI($url,$targetFile)
	{
		set_time_limit(0);
		//This is the file where we save the    information
		$fp = fopen ($targetFile, 'w+');
		//Here is the file we are downloading, replace spaces with %20
		$ch = curl_init(str_replace(" ","%20",$url));
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		// write curl response to file
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// get curl response
		curl_exec($ch); 
		curl_close($ch);
		fclose($fp);
	}
	
    // API Call
    public static function callAPI($data,$url) {
		//print_r($data);
		$query = '';
        
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 	// if no connection in 30 seconds, error out
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);	// every  minute, timeout if nothing happens in this channel
		
		if( sizeof($data) > 0 ) {
			// Data json 
			$query = json_encode($data);
			
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
				'Content-Type: application/json', 
				'Content-Length: ' . strlen($query)
				) 
			);  
		}
		
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		$err = curl_error($ch);
		curl_close($ch);
		return array($response,$info,$err);
    }
	
	//Get max id from particular table
	public function getTableMaxID($tableName="",$dao){
		$param = "ID";
		$rtnVal = 0;
		if($tableName != ""){
			if(
				$tableName == "pmi_device" ||
				$tableName == "pmi_location_image" ||
				$tableName == "pmi_map_nodes" ||
				$tableName == "pmi_parameters" ||
				$tableName == "pmi_smtp_config" ||
				$tableName == "pmi_user" ||
				$tableName == "pmi_user_roles" ||
				$tableName == "pmi_user_roles"
				
			){
				$param = "id";
			}
			$sql = "SELECT $param FROM $tableName ORDER BY $param DESC LIMIT 1";
			$rtnVal = $dao->get_single_result($sql);
			
		}
		return $rtnVal;
	}
	
	//Update synchronize status tables rows
	public function setSynchronized($tableName="",$IDs,$dao){
		$param = "ID";
		$rtnVal = false;
		if($tableName != ""){
			if(
				$tableName == "pmi_device" ||
				$tableName == "pmi_location_image" ||
				$tableName == "pmi_map_nodes" ||
				$tableName == "pmi_parameters" ||
				$tableName == "pmi_smtp_config" ||
				$tableName == "pmi_user" ||
				$tableName == "pmi_user_roles" ||
				$tableName == "pmi_user_roles"
				
			){
				$param = "id";
			}
			$sql = "UPDATE $tableName SET Synchronize=1  WHERE $param <= $IDs";
			$rtnVal = $dao->update($sql);
		}
		return $rtnVal;
	}
    
	
	public function serverQueue($result,$db){
		$existRec = array();
		//echo "..........................";
		$existRec = $db->get_result_array("SELECT * FROM pmi_inx_cloud where status=1");
		//print_r($existRec);die;
		if(sizeof($existRec) > 0){
			//$CM = new CommonFunction();
			//file_put_contents('response.txt',print_r($result,true),FILE_APPEND);
			$name = $existRec[0]["Name"];
			$password = stripslashes($existRec[0]["Password"]);
			
			$apiEndPoint = $existRec[0]["CloudUrl"]."/".API_CONNECTED_PAGE;
			$obLogId = ( isset( $result['obLogId'] ) ) ? $result['obLogId'] : 0;
			if( $obLogId >  0 ) {
				$apiEndPoint = $existRec[0]["CloudUrl"]."/terminalResponse.php";
			}
			
			//file_put_contents('response.txt',print_r($apiEndPoint,true)."\n",FILE_APPEND);
			$encryptKey = $this->encryptSalt($password);
			$resultData = ($result["result"] == '')?'': $result["result"];
			$resultMsg = (isset($result["msg"]) &&  isset($result["msg"]) != '')?$result["msg"]: $result["message"];
			//file_put_contents('response.txt',print_r($result["status"],true)."\n",FILE_APPEND);
			$resultStatus = ($result["status"] != '')?$result["status"]: $result["Status"];
			$resultStatus = ($resultStatus == '')?0:$resultStatus;
			$resultQuery = ($result["query"] == '')?'': $result["query"];
			$resultFilename = ($result["filename"] == '')?'': $result["filename"];
			
			$params = array(
			  'i'=> $name,
			  'p'=> $encryptKey,
			  'a'=> 'alertMessage',
			  'msg' => $resultMsg,
			  'status' => $resultStatus,
			  'result' => json_encode($resultData),
			  'query' => $resultQuery,
			  'type' => $result["type"],
			  'filename' => $resultFilename,
			  'obLogId' => $obLogId
			);
			//echo $apiEndPoint; 
			//print_r($params);
			
			//file_put_contents('response.txt',print_r($params,true)."\n",FILE_APPEND);
			//echo "Calling $apiEndPoint \n";
			$response = $this->callAPI($apiEndPoint, $params);//send  data to cloud
			//ssvar_dump($response);
			//file_put_contents('response.txt',print_r($response,true)." <<<<<<< \n",FILE_APPEND);
			return $response;
		}
	}
    
	static function currentTimestamp()
	{
		$t=time();
		
		return date("Y-m-d  h:i:s",$t);
	}

	static function getperipheralTypeName($type,$no)
	{
			switch(intval($type))
			{
					case 1:
						return "Actuator$no";
					case 2:
						return "WallSwitch$no";
					case 3:
						return "SensorOS";
					default:
						return "N/A";
			}
			return "";
	}
	
	public static function addAccessLog($dbc,$function,$action,$table,$changedData="")
	{
		$userId= (isset($_SESSION['LOGGED_USER_ID'])) ? $_SESSION['LOGGED_USER_ID'] : 0;
		$dbrec = array();
		$dbrec["UserId"] = $userId;
		$dbrec["Function"] = $function;
		$dbrec["Action"] = $action;
		$dbrec["TableName"] = $table;
        $dbrec["ChangedData"]  = mysqli_real_escape_string($dbc,$changedData);
        $sql=$dbc->_createInsertQuery($dbrec,"pmi_access_log");
		return $dbc->_query($sql);
	}
	
	private static function build_data_files($boundary, $fields, $files){
		$data = '';
		$eol = "\r\n";

		$delimiter = '-------------' . $boundary;

		foreach ($fields as $name => $content) {
			$data .= "--" . $delimiter . $eol
				. 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
				. $content . $eol;
		}


		foreach ($files as $name => $content) {
			$data .= "--" . $delimiter . $eol
				. 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
				//. 'Content-Type: image/png'.$eol
				. 'Content-Transfer-Encoding: binary'.$eol
				;

			$data .= $eol;
			$data .= $content . $eol;
		}
		$data .= "--" . $delimiter . "--".$eol;


		return $data;
	}
	
	public static function getContractID()
	{
		if ( PATH_SEPARATOR  != ";" )	{	// Linux
			//$output=null;
			//$retval=null;
			//exec("cat /sys/class/dmi/id/board_serial", $output, $retval);
			return str_replace("\n","",file_get_contents(ROOTPATH."/files/serialno.txt"));
		}
		return "ZM203S005969";
	}
	
	public static function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}

	public static function uploadFileToCloud($instanceId,$instancePass,$dataModel,$filePath,$apiEndpoint)
	{
		$fields = array(
			'c' => 'Sync',
			'a'=> 'createBackup',
			'file' => basename($filePath),
			'instanceId' => $instanceId,
			'instancePass' => $instancePass,
			'dataModel' => $dataModel
		);
		$files = array();
		$files[0] = file_get_contents($filePath);
		$curl = curl_init();

		$boundary = uniqid();
		$delimiter = '-------------' . $boundary;

		$post_data = self::build_data_files($boundary, $fields, $files);
		
		//die($post_data);
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $apiEndpoint,
		  CURLOPT_RETURNTRANSFER => 1,
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POST => 1,
		  CURLOPT_POSTFIELDS => $post_data,
		  CURLOPT_HTTPHEADER => array(
			//"Authorization: Bearer $TOKEN",
			"Content-Type: multipart/form-data; boundary=" . $delimiter,
			"Content-Length: " . strlen($post_data)
		  ),
		));
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		$err = curl_error($curl);
		curl_close($curl);
		
		return array($response,$info,$err);
	}
	
	public static function exportDatabase($host,$user,$pass,$dbname, $tables=false,$skipTables=false)
    {
        $mysqli = new mysqli($host,$user,$pass,$dbname); 
        $mysqli->select_db($dbname); 
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0]; 
        }   
        if($tables !== false) 
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        if ( $skipTables !== false ) {
			$target_tables = array_diff($target_tables, $skipTables);
		}
		
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);  
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            $TableMLine     =   $res->fetch_row();
            $TableDLine		= "Drop table if exists $table";
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableDLine.";\n\n";
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
        /*$backup_name = $backup_name ? $backup_name : $name.".sql";
        header('Content-Type: application/octet-stream');   
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");
        */
        return $content;
    }
    
	public static function getRootPath()
	{
		return ROOTPATH;
	}
	
	public static function goExecuteCmd ($clusterID, $cmd, $param1, $param2, $param3,$param4='')
	{
		echo "command:".$cmd."cluster: ".$clusterID." p1: ".$param1." p2: ".$param2." p3: ".$param3."\n";
		$tagArr = array();
		$minstatvalue = $GLOBALS["LastStatUpdateTime"];		
		$db = new DBC();
		
		///ip look up 
		
		$sql2 = <<<EOF
		SELECT T.ID,T.Name,T.IPAddress FROM pmi_cluster_mapping CM 
		LEFT JOIN pmi_tag T ON (T.NodeID = CM.NodeID AND T.TagTypeID=1)
		WHERE 
		CM.Deleted=0 AND 
		T.Deleted=0 AND 
		T.Acknowledged=1 AND
		T.`Status`=1 AND T.StatusOn > NOW() - INTERVAL $minstatvalue MINUTE AND
		CM.ClusterID=$clusterID ;
EOF;
		//echo $sql2;
		$tagArr = $db->get_result($sql2);
		
		$notQue = new BGPQueueClient(NOTIF_QUEUE_SERVER,SYSTEMLOG_QUEUE_NAME);
		for ($i = 0; $i < sizeof($tagArr); $i++) {
			$data = array(
				"IP" => $tagArr[$i]["IPAddress"],
				"event" =>  "".$cmd, 
				"param1" => "".$param1,
				"param2" => "".$clusterID,
				"param3" => "".$param3,
				"param4" => "".$param4,
			);
			print_r($data);
			$notQue->queueNotificationRequest(HARDWARE_POLICY_PROCESS, $data);
		}
	}
	
	public static function sendCoapRequest($ipAddress,$resource,$context,$dtype,$data)
	{
		$query="";
		$fname= "/tmp/".uniqid("cs-args");
		$afile = fopen($fname,"w") or die("unable to open file");
		fwrite($afile,$ipAddress."\n");
		fwrite($afile,"2\n");
		fwrite($afile,$resource."\n");
		fwrite($afile,$query."\n");
		fwrite($afile,"1"."\n");	// arg count
		fwrite($afile,$context."\n");
		fwrite($afile,$dtype."\n");
		fwrite($afile,$data."\n");
		fclose($afile);
		
		exec("/var/www/coap-send.sh ".$fname,$output,$returnVar);
		$out=implode($output,"<br/>");
		return $out;
	}

	public static function sendCoapRequest2($ipAddress,$resource,$query, $context,$dtype,$data)
	{
		// OLD
		$fname= "/tmp/".uniqid("cs-args");
		$afile = fopen($fname,"w") or die("unable to open file");
		fwrite($afile,$ipAddress."\n");
		fwrite($afile,"2\n");//1= get or 2 = put 
		fwrite($afile,$resource."\n");
		fwrite($afile,""."\n");	// query
		fwrite($afile,"1"."\n");	// arg count
		fwrite($afile,$context."\n");
		fwrite($afile,$dtype."\n");
		if($context == "fl")fwrite($afile,"$query,3,$data,65000"."\n");
		else fwrite($afile,"$query,$data"."\n");
		
		fclose($afile);
		
		print_r(file_get_contents($fname));
		exec("/var/www/coap-send.sh ".$fname,$output,$returnVar);
		$out=implode($output,"<br/>");
		return $out;
	}
	
	// Get High Byte
	public static function getHiByte($dec){
	   $intVal =  intval($dec);
	   $places = 8;
	   return $intVal >> $places;
	}

	// Get Low Byte
	public static function getLoByte($dec){
	   $intVal =  intval($dec);
	   $places = 0xFF;
	   return $intVal & $places;
	}
	
	public static function startsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		return substr( $haystack, 0, $length ) === $needle;
	}

	public static function endsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		if( !$length ) {
			return true;
		}
		return substr( $haystack, -$length ) === $needle;
	}



	public function getIndianCurrency(float $number){
	    $decimal = round($number - ($no = floor($number)), 2) * 100;
	    $hundred = null;
	    $digits_length = strlen($no);
	    $i = 0;
	    $str = array();
	    $words = array(0 => '', 1 => 'One', 2 => 'Two',
	        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
	        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
	        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
	        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
	        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
	        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
	        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
	        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
	    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
	    while( $i < $digits_length ) {
	        $divider = ($i == 2) ? 10 : 100;
	        $number = floor($no % $divider);
	        $no = floor($no / $divider);
	        $i += $divider == 10 ? 1 : 2;
	        if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : null;
	            // $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
	            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
	            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
	        } else $str[] = null;
	    }
	    $Rupees = implode('', array_reverse($str));
	    $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
	    return ($Rupees ? 'Rupees: '. $Rupees   : '') . $paise . 'only.';
	}
}
