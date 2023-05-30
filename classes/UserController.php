<?php	
    include_once("config.php");
    
    class UserController extends ControllerBase 
    {	
		# Constructor Method 
		function __constructor(){
		}

		//Check Logged in User Exists
		function authenticate()
		{
			$result=array("status" => 0);
			$db = new DBC();
			
			$username = $_REQUEST['username'];
			$password = $_REQUEST['password'];
			
			$sql = "select * from user where uname ='$username' and upass='$password' and active = 'Y'";
			$userInfo = $db->get_result($sql);
			
			if( isset($userInfo[0]) )
			{
				$_SESSION['LOGGED_IN_USERNAME'] = $userInfo[0]['uname'];
				$_SESSION['LOGGED_USER_ID'] = $userInfo[0]['id'];
				$_SESSION['LOGGED_USER_ROLE_ID'] = $userInfo[0]['role'];
				$result["status"]=1;
			}
			else {
				$result['status'] = '0';
			}
			return $result;
		}

		//Register Controller function
		function register()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$school_name = $_REQUEST['school_name'];
       		$email     = $_REQUEST['email'];

			$sql = "select * from user where uname ='$email' and active = 'Y'";
			$userInfo = $db->get_result($sql);
			
			if( isset($userInfo[0]) )
			{
				$result["status"]='0';
				return $result;
			}
			else {

				$user=array();
				$user["uname"]=$email;
				$user["upass"]=$_REQUEST['password'];
				$user["active"]='Y';
				$user["role"]=1;
				$db->insert_query($user,"user");

				$sql = "select id from user where uname ='$email'";
				$userInfo = $db->get_result($sql);

				$data=array();
				$data["user"]=$userInfo[0]['id'];
				$data["first_name"]=$_REQUEST['first_name'];
				$data["last_name"]=$_REQUEST['sur_name'];
				$data["mobile"]=$_REQUEST['mobile'];
				$data["user_type"]='parent';

				$db->insert_query($data,"UserProfile");

				$result["status"] = '1';
			}
			return $result;
		}

		//add address
		function add_address()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parent = $_REQUEST['parent'];
			$defaultV = $_REQUEST['defaultV'];

			$sqlFindParent = "select * from UserProfile where id=$parent";
       		$parentInfo = $db->get_result($sqlFindParent);

       		if( isset($parentInfo[0]))
       		{
       			if($defaultV == 0){

       				$sql = "select * from UserAddress where parent=$parent and is_default=1";
					$userDetails=$db->get_result($sql);
					if( isset($userDetails[0]) )
					{
						$result["status"]='2';
					}else{
						$data=array();
						$data["parent"]=$parent;
						$data["address"]=$_REQUEST['address'];
						$data["postal_code"]=$_REQUEST['postal_code'];
						$data["is_default"]=1;
						
						$db->insert_query($data,"UserAddress");
						$result['status'] = '1';

					}
       			}else{
       				$data=array();
					$data["parent"]=$parent;
					$data["address"]=$_REQUEST['address'];
					$data["postal_code"]=$_REQUEST['postal_code'];
					$data["is_default"]=0;
					
					$db->insert_query($data,"UserAddress");
					$result['status'] = '1';
       			}

       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;		
		}

		//User Add Student function
		function add_student()
		{
			$result=array("status" => 0);
			$db = new DBC();

       		$username     = $_REQUEST['username'];
       		$parentId     = $_REQUEST['parent'];

       		$sqlFindParent = "select * from UserProfile where id=$parentId";
       		$parentInfo = $db->get_result($sqlFindParent);

       		if( isset($parentInfo[0]))
       		{
       			$sql = "select * from user where uname ='$username' and active = 'Y'";
				$userInfo = $db->get_result($sql);
				
				if( isset($userInfo[0]) )
				{
					$result["status"]='2';
				}
				else {

					$user=array();
					$user["uname"]=$username;
					$user["upass"]=$_REQUEST['password'];
					$user["active"]='Y';
					$user["role"]=2;
					$db->insert_query($user,"user");

					$sql = "select id from user where uname ='$username'";
					$userInfo = $db->get_result($sql);

					$data=array();
					$data["user"]=$userInfo[0]['id'];
					$data["name"]=$_REQUEST['name'];
					$data["username"]=$_REQUEST['username'];
					$data["mobile"]=$_REQUEST['mobile'];
					$data["student_class"]=$_REQUEST['student_class'];
					$data["division"]=$_REQUEST['division'];
					$data["age"]=$_REQUEST['age'];
					$data["parent"]=$parentId;
					$data["address"]="";
					$data["user_type"]='student';

					$db->insert_query($data,"StudentRegister");

					$updatesql ="update UserProfile set is_riderparent=1 where id=$parentId";
					$db->update($updatesql);
					
					$result["status"] = '1';
				}

       		}

			
			return $result;
		}

		//add_account_details
		function add_account_details()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parent = $_REQUEST['parent'];

			$sqlFindParent = "select * from UserProfile where id=$parent";
       		$parentInfo = $db->get_result($sqlFindParent);

       		if( isset($parentInfo[0]))
       		{
       			$sql = "select * from UserAccountDetails where parent=$parent";

				$userDetails=$db->get_result($sql);

				if( isset($userDetails[0]) )
				{
					$result["status"]='2';
				}
				else {
					$data=array();
					$data["parent"]=$parent;
					$data["account_number"]=$_REQUEST['account_number'];
					$data["card_type"]=$_REQUEST['card_type'];
					$data["account_holder_name"]=$_REQUEST['account_holder_name'];
					$data["valid_through"]=$_REQUEST['valid_through'];
					$data["cvv"]=$_REQUEST['cvv'];
					
					$db->insert_query($data,"UserAccountDetails");
					$result['status'] = '1';
				}
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;	
		}

		//upload profile pic
		function add_profilepic()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parent = $_REQUEST['parent'];
			$sqlFindParent = "select * from UserProfile where id=$parent";
       		$parentInfo = $db->get_result($sqlFindParent);

       		if( isset($parentInfo[0]))
       		{
       			$filename = $_FILES['file']['name'];
       			$extension =end((explode(".", $filename)));
       			$uniqueid = uniqid($parent);
       			$uniquefilename=$uniqueid.".".$extension;

				if(0 < $_FILES['file']['error'])
				{
				    $result["status"]='2';

				}else{
	                move_uploaded_file($_FILES['file']['tmp_name'], 'img/'.$uniquefilename);
	                $updatesql ="update UserProfile set profile_image_name='$uniquefilename' where id=$parent";
					$db->update($updatesql);

					$result["status"]='1';
				}

       		}else{
       			$result["status"]='0';
       		}

			return $result;	
		}

		//add_VehicleDetails
		function add_VehicleDetails()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parent = $_REQUEST['parent'];

			$sqlFindParent = "select * from UserProfile where id=$parent";
       		$parentInfo = $db->get_result($sqlFindParent);

       		if( isset($parentInfo[0]))
       		{
       			$sql = "select * from UserVehicleDetails where parent=$parent";

				$userDetails=$db->get_result($sql);

				if( isset($userDetails[0]) )
				{
					$result["status"]='2';
				}
				else {
					$data=array();
					$data["parent"]=$parent;
					$data["make"]=$_REQUEST['make'];
					$data["registration_number"]=$_REQUEST['registration_number'];
					$data["MOT_details"]=$_REQUEST['MOT_details'];
					$data["policy_number"]=$_REQUEST['address'];
					$data["policy_provider"]=$_REQUEST['postal_code'];
					
					$db->insert_query($data,"UserVehicleDetails");

					$dataSR =array();
					$dataSR["no_of_seats"]=$_REQUEST['no_of_seats'];
					$dataSR["counter"]=12;
					$dataSR["parent"]=$parent;

					$db->insert_query($dataSR,"RideRegistrationDetail");

					$updatesql ="update UserProfile set is_driverparent=1 where id=$parent";
					$db->update($updatesql);
					

					$result['status'] = '1';
				}
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;	
		}

		//Ride Registration
		function ride_registration()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$Home_to_school_time=$GLOBALS['Home_to_school_time'];
			$School_to_home_time=$GLOBALS['School_to_home_time'];

			$parent = $_REQUEST['parent'];
			$advance_ride=$_REQUEST['advance_ride'];
			$instant_ride=$_REQUEST['instant_ride'];
			$both_ride=$_REQUEST['both_ride'];
			$availability=$_REQUEST['availability'];
			$start_date=$_REQUEST['start_date'];
			$end_date=$_REQUEST['end_date'];
			$date_range=$_REQUEST['date_range'];


			$sqlFindParent = "select * from UserProfile where id=$parent and is_driverparent=1";
       		$parentInfo = $db->get_result($sqlFindParent);

       		if( isset($parentInfo[0]))
       		{
    //    			$sql = "select * from RideScheduleDates where parent=$parent";

				// $userDDetails=$db->get_result($sql);

				// if( isset($userDDetails[0]) )
				// {
				// 	$result["status"]='2';
				// }
				// else {

					$sqlR="select * from RideRegistrationDetail where parent=$parent";
					$userRDetails=$db->get_result($sqlR);

					if($availability == 1){

						$today_date=date("Y-m-d");

						if($today_date == $start_date){
							date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
							if(date('H:i:s') < $Home_to_school_time){
								
								$data=array();
								$data["advance_ride"]=$advance_ride;
								$data["instant_ride"]=$instant_ride;
								$data["both_ride"]=$both_ride;
								$data["availability"]=$availability;
								$last_day=date('Y-m-t',strtotime($today_date));

								$data["last_date"]=$last_day;
								$data["counter"]=11;

								$where="parent=$parent";
								
								$db->update_query($data,"RideRegistrationDetail",$where);


								$dataD=array();
								$dataD["parent"]=$parent;
								$dataD["start_date"]=$today_date;
								$dataD["end_date"]=$last_day;
								$dataD["date_range"]=$date_range;
								
								$db->insert_query($dataD,"RideScheduleDates");

								$format='Y-m-d';
								$current = strtotime($today_date);
								$end = strtotime($last_day);
								$stepVal = '+1 day';
								while($current <= $end){
									if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){

										$dt=date($format,$current);
										$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
										$CheckDate=	$db->get_result($sqlCheckDate);

										if(! isset($CheckDate[0]))
										{
											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=1;
											$ride["school_to_home"]=0;
											$db->insert_query($ride,"Ride");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=0;
											$ride["school_to_home"]=1;
											$db->insert_query($ride,"Ride");
										}
										
									}

									$current=strtotime($stepVal,$current);
								}
								$result['status'] = '1';
							}else if(date('H:i:s') < $School_to_home_time ){
								
								$data=array();
								$data["advance_ride"]=$advance_ride;
								$data["instant_ride"]=$instant_ride;
								$data["both_ride"]=$both_ride;
								$data["availability"]=$availability;
								$last_day=date('Y-m-t',strtotime($today_date));

								$data["last_date"]=$last_day;
								$data["counter"]=11;

								$where="parent=$parent";
								
								$db->update_query($data,"RideRegistrationDetail",$where);


								$dataD=array();
								$dataD["parent"]=$parent;
								$dataD["start_date"]=$today_date;
								$dataD["end_date"]=$last_day;
								$dataD["date_range"]=$date_range;
								
								$db->insert_query($dataD,"RideScheduleDates");

								if(date("l",strtotime($today_date)) != "Saturday" && date("l",strtotime($today_date)) != "Sunday"){

									$dt=$today_date;
									$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
									$CheckDate=	$db->get_result($sqlCheckDate);

									if(! isset($CheckDate[0]))
									{
										$ride=array();
										$ride["parent"]=$parent;
										$ride["both_ride"]=$both_ride;
										$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
										$ride["created_at"]=date("Y-m-d");
										$ride["instant_ride"]=$instant_ride;
										$ride["advance_ride"]=$advance_ride;
										$ride["date"]=$today_date;
										$ride["home_to_school"]=0;
										$ride["school_to_home"]=1;
										$db->insert_query($ride,"Ride");
									}

									
								}


								$format='Y-m-d';
								$tomorrow = date('Y-m-d',strtotime($today_date.'+1 day'));
								$current = strtotime($tomorrow);
								$end = strtotime($last_day);
								$stepVal = '+1 day';
								while($current <= $end){
									if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){

										$dt=date($format,$current);
										$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
										$CheckDate=	$db->get_result($sqlCheckDate);

										if(! isset($CheckDate[0]))
										{
											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=1;
											$ride["school_to_home"]=0;
											$db->insert_query($ride,"Ride");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=0;
											$ride["school_to_home"]=1;
											$db->insert_query($ride,"Ride");
										}
										
									}

									$current=strtotime($stepVal,$current);
								}
								$result['status'] = '1';
							}else{
								$data=array();
								$data["advance_ride"]=$advance_ride;
								$data["instant_ride"]=$instant_ride;
								$data["both_ride"]=$both_ride;
								$data["availability"]=$availability;
								$last_day=date('Y-m-t',strtotime($today_date));

								$data["last_date"]=$last_day;
								$data["counter"]=11;

								$where="parent=$parent";
								
								$db->update_query($data,"RideRegistrationDetail",$where);


								$dataD=array();
								$dataD["parent"]=$parent;
								$dataD["start_date"]=$today_date;
								$dataD["end_date"]=$last_day;
								$dataD["date_range"]=$date_range;
								
								$db->insert_query($dataD,"RideScheduleDates");

								$format='Y-m-d';
								$tomorrow = date('Y-m-d',strtotime($today_date.'+1 day'));
								$current = strtotime($tomorrow);
								$end = strtotime($last_day);
								$stepVal = '+1 day';
								while($current <= $end){
									if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){

										$dt=date($format,$current);
										$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
										$CheckDate=	$db->get_result($sqlCheckDate);

										if(! isset($CheckDate[0]))
										{
											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=1;
											$ride["school_to_home"]=0;
											$db->insert_query($ride,"Ride");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=0;
											$ride["school_to_home"]=1;
											$db->insert_query($ride,"Ride");
										}
										
									}

									$current=strtotime($stepVal,$current);
								}
								$result['status'] = '1';
							}
						}else{
							

							$data=array();
							$data["advance_ride"]=$advance_ride;
							$data["instant_ride"]=$instant_ride;
							$data["both_ride"]=$both_ride;
							$data["availability"]=$availability;
							$last_day=date('Y-m-t',strtotime($today_date));

							$data["last_date"]=$last_day;
							$data["counter"]=11;

							$where="parent=$parent";
							
							$db->update_query($data,"RideRegistrationDetail",$where);


							$dataD=array();
							$dataD["parent"]=$parent;
							$dataD["start_date"]=$today_date;
							$dataD["end_date"]=$last_day;
							$dataD["date_range"]=$date_range;
							
							$db->insert_query($dataD,"RideScheduleDates");

							$format='Y-m-d';
							$current = strtotime($today_date);
							$end = strtotime($last_day);
							$stepVal = '+1 day';
							while($current <= $end){
								if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){

									$dt=date($format,$current);
									$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
									$CheckDate=	$db->get_result($sqlCheckDate);

									if(! isset($CheckDate[0]))
									{
										$ride=array();
										$ride["parent"]=$parent;
										$ride["both_ride"]=$both_ride;
										$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
										$ride["created_at"]=date("Y-m-d");
										$ride["instant_ride"]=$instant_ride;
										$ride["advance_ride"]=$advance_ride;
										$ride["date"]=date($format,$current);
										$ride["home_to_school"]=1;
										$ride["school_to_home"]=0;
										$db->insert_query($ride,"Ride");

										$ride=array();
										$ride["parent"]=$parent;
										$ride["both_ride"]=$both_ride;
										$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
										$ride["created_at"]=date("Y-m-d");
										$ride["instant_ride"]=$instant_ride;
										$ride["advance_ride"]=$advance_ride;
										$ride["date"]=date($format,$current);
										$ride["home_to_school"]=0;
										$ride["school_to_home"]=1;
										$db->insert_query($ride,"Ride");
									}
									
								}

								$current=strtotime($stepVal,$current);
							}
							$result['status'] = '1';
						}
							

							// if($userRDetails[0]['last_date'] == $today_date){

							// 	$new_start_date =date('Y-m-d',strtotime($userRDetails[0]['last_date'].'+1 day'));
							// 	$new_last_day=date('Y-m-t',strtotime($new_start_date));

							// 	if($userRDetails[0]['counter'] != 0){

							// 		$data=array();

							// 		$data["last_date"]=$new_last_day;
							// 		$data["counter"]=$userRDetails[0]['counter'] - 1;

							// 		$where="parent=$parent";
									
							// 		$db->update_query($data,"RideRegistrationDetail",$where);


							// 		$dataD=array();
							// 		$dataD["start_date"]=$new_start_date;
							// 		$dataD["end_date"]=$new_last_day;
							// 		$whereD="parent=$parent";
									
							// 		$db->update_query($dataD,"RideScheduleDates",$whereD);

							// 		$format='Y-m-d';
							// 		$current = strtotime($new_start_date);
							// 		$end = strtotime($new_last_day);
							// 		$stepVal = '+1 day';
							// 		while($current <= $end){
							// 			if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){
							// 				$ride=array();
							// 				$ride["parent"]=$parent;
							// 				$ride["both_ride"]=$both_ride;
							// 				$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
							// 				$ride["created_at"]=date("Y-m-d");
							// 				$ride["instant_ride"]=$instant_ride;
							// 				$ride["advance_ride"]=$advance_ride;
							// 				$ride["date"]=date($format,$current);
							// 				$ride["home_to_school"]=1;
							// 				$ride["school_to_home"]=0;
							// 				$db->insert_query($ride,"Ride");

							// 				$ride=array();
							// 				$ride["parent"]=$parent;
							// 				$ride["both_ride"]=$both_ride;
							// 				$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
							// 				$ride["created_at"]=date("Y-m-d");
							// 				$ride["instant_ride"]=$instant_ride;
							// 				$ride["advance_ride"]=$advance_ride;
							// 				$ride["date"]=date($format,$current);
							// 				$ride["home_to_school"]=0;
							// 				$ride["school_to_home"]=1;
							// 				$db->insert_query($ride,"Ride");
							// 			}

							// 			$current=strtotime($stepVal,$current);
							// 		}
							// 		$result['status'] = '1';
							// 	}

							// }

						
						
					}else{
						if($date_range == 1){
							$today_date=date("Y-m-d");
							
							if($today_date == $start_date){
								date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
								if(date('H:i:s') < $Home_to_school_time){
									
									$data=array();
									$data["advance_ride"]=$advance_ride;
									$data["instant_ride"]=$instant_ride;
									$data["both_ride"]=$both_ride;
									$data["availability"]=$availability;
									$where="parent=$parent";
									
									$db->update_query($data,"RideRegistrationDetail",$where);


									$dataD=array();
									$dataD["parent"]=$parent;
									$dataD["start_date"]=$start_date;
									$dataD["end_date"]=$end_date;
									$dataD["date_range"]=$date_range;
									
									$db->insert_query($dataD,"RideScheduleDates");

									$format='Y-m-d';
									$current = strtotime($start_date);
									$end = strtotime($end_date);
									$stepVal = '+1 day';
									while($current <= $end){
										if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){	

											$dt=date($format,$current);
											$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
											$CheckDate=	$db->get_result($sqlCheckDate);

								       		if( !isset($CheckDate[0]))
								       		{
								       			$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=date($format,$current);
												$ride["home_to_school"]=1;
												$ride["school_to_home"]=0;
												$db->insert_query($ride,"Ride");

												$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=date($format,$current);
												$ride["home_to_school"]=0;
												$ride["school_to_home"]=1;
												$db->insert_query($ride,"Ride");

								       		}
										}

										$current=strtotime($stepVal,$current);
									}
									$result['status'] = '1';
								}else if(date('H:i:s') < $School_to_home_time ){
									
									$data=array();
									$data["advance_ride"]=$advance_ride;
									$data["instant_ride"]=$instant_ride;
									$data["both_ride"]=$both_ride;
									$data["availability"]=$availability;
									$where="parent=$parent";
									
									$db->update_query($data,"RideRegistrationDetail",$where);


									$dataD=array();
									$dataD["parent"]=$parent;
									$dataD["start_date"]=$start_date;
									$dataD["end_date"]=$end_date;
									$dataD["date_range"]=$date_range;
									
									$db->insert_query($dataD,"RideScheduleDates");

									$format='Y-m-d';

									if(date("l",strtotime($start_date)) != "Saturday" && date("l",strtotime($start_date)) != "Sunday"){

										$dt=$start_date;
										$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
										$CheckDate=	$db->get_result($sqlCheckDate);

											if( !isset($CheckDate[0]))
											{
												$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=$start_date;
												$ride["home_to_school"]=0;
												$ride["school_to_home"]=1;
												$db->insert_query($ride,"Ride");

											}
									}

									$tomorrow = date('Y-m-d',strtotime($start_date.'+1 day'));
									$current = strtotime($tomorrow);
									$end = strtotime($end_date);
									$stepVal = '+1 day';
									while($current <= $end){
										if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){	

											$dt=date($format,$current);
											$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
											$CheckDate=	$db->get_result($sqlCheckDate);

											if(! isset($CheckDate[0]))
											{
												$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=date($format,$current);
												$ride["home_to_school"]=1;
												$ride["school_to_home"]=0;
												$db->insert_query($ride,"Ride");

												$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=date($format,$current);
												$ride["home_to_school"]=0;
												$ride["school_to_home"]=1;
												$db->insert_query($ride,"Ride");
											}								
										
										}

										$current=strtotime($stepVal,$current);
									}
									$result['status'] = '1';
								}else{

									$data=array();
									$data["advance_ride"]=$advance_ride;
									$data["instant_ride"]=$instant_ride;
									$data["both_ride"]=$both_ride;
									$data["availability"]=$availability;
									$where="parent=$parent";
									
									$db->update_query($data,"RideRegistrationDetail",$where);


									$dataD=array();
									$dataD["parent"]=$parent;
									$dataD["start_date"]=$start_date;
									$dataD["end_date"]=$end_date;
									$dataD["date_range"]=$date_range;
									
									$db->insert_query($dataD,"RideScheduleDates");

									$format='Y-m-d';
									$tomorrow = date('Y-m-d',strtotime($start_date.'+1 day'));
									$current = strtotime($tomorrow);
									$end = strtotime($end_date);
									$stepVal = '+1 day';
									while($current <= $end){
										if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){

											$dt=date($format,$current);
											$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
											$CheckDate=	$db->get_result($sqlCheckDate);

											if(! isset($CheckDate[0]))
											{
												$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=date($format,$current);
												$ride["home_to_school"]=1;
												$ride["school_to_home"]=0;
												$db->insert_query($ride,"Ride");

												$ride=array();
												$ride["parent"]=$parent;
												$ride["both_ride"]=$both_ride;
												$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
												$ride["created_at"]=date("Y-m-d");
												$ride["instant_ride"]=$instant_ride;
												$ride["advance_ride"]=$advance_ride;
												$ride["date"]=date($format,$current);
												$ride["home_to_school"]=0;
												$ride["school_to_home"]=1;
												$db->insert_query($ride,"Ride");
											}									
											
										}

										$current=strtotime($stepVal,$current);
									}
									$result['status'] = '1';
								}
							}else{
								
								$data=array();
								$data["advance_ride"]=$advance_ride;
								$data["instant_ride"]=$instant_ride;
								$data["both_ride"]=$both_ride;
								$data["availability"]=$availability;
								$where="parent=$parent";
								
								$db->update_query($data,"RideRegistrationDetail",$where);


								$dataD=array();
								$dataD["parent"]=$parent;
								$dataD["start_date"]=$start_date;
								$dataD["end_date"]=$end_date;
								$dataD["date_range"]=$date_range;
								
								$db->insert_query($dataD,"RideScheduleDates");

								$format='Y-m-d';
								$current = strtotime($start_date);
								$end = strtotime($end_date);
								$stepVal = '+1 day';
								while($current <= $end){
									if(date("l",$current) != "Saturday" && date("l",$current) != "Sunday"){

										$dt=date($format,$current);
										$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
										$CheckDate=	$db->get_result($sqlCheckDate);

										if(! isset($CheckDate[0]))
										{
											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=1;
											$ride["school_to_home"]=0;
											$db->insert_query($ride,"Ride");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=date($format,$current);
											$ride["home_to_school"]=0;
											$ride["school_to_home"]=1;
											$db->insert_query($ride,"Ride");
										}									
										
									}

									$current=strtotime($stepVal,$current);
								}
								$result['status'] = '1';

							}
								
						}else{

							$dt=$start_date;
							$sqlCheckDate="select * from Ride where parent=$parent and date='$dt'";
							$CheckDate=	$db->get_result($sqlCheckDate);

							if(! isset($CheckDate[0]))
							{
								$today_date=date("Y-m-d");
								if(date("l",strtotime($start_date)) != "Saturday" && date("l",strtotime($start_date)) != "Sunday"){

									if($today_date == $start_date){

										date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
										if(date('H:i:s') < $Home_to_school_time){
											$data=array();
											$data["advance_ride"]=$advance_ride;
											$data["instant_ride"]=$instant_ride;
											$data["both_ride"]=$both_ride;
											$data["availability"]=$availability;
											$where="parent=$parent";
											
											$db->update_query($data,"RideRegistrationDetail",$where);


											$dataD=array();
											$dataD["parent"]=$parent;
											$dataD["start_date"]=$start_date;
											$dataD["end_date"]=$end_date;
											$dataD["date_range"]=$date_range;
											
											$db->insert_query($dataD,"RideScheduleDates");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=$start_date;
											$ride["home_to_school"]=1;
											$ride["school_to_home"]=0;
											$db->insert_query($ride,"Ride");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=$start_date;
											$ride["home_to_school"]=0;
											$ride["school_to_home"]=1;
											$db->insert_query($ride,"Ride");

											$result['status'] = '1';
										}else if(date('H:i:s') < $School_to_home_time ){
											$data=array();
											$data["advance_ride"]=$advance_ride;
											$data["instant_ride"]=$instant_ride;
											$data["both_ride"]=$both_ride;
											$data["availability"]=$availability;
											$where="parent=$parent";
											
											$db->update_query($data,"RideRegistrationDetail",$where);


											$dataD=array();
											$dataD["parent"]=$parent;
											$dataD["start_date"]=$start_date;
											$dataD["end_date"]=$end_date;
											$dataD["date_range"]=$date_range;
											
											$db->insert_query($dataD,"RideScheduleDates");

											$ride=array();
											$ride["parent"]=$parent;
											$ride["both_ride"]=$both_ride;
											$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
											$ride["created_at"]=date("Y-m-d");
											$ride["instant_ride"]=$instant_ride;
											$ride["advance_ride"]=$advance_ride;
											$ride["date"]=$start_date;
											$ride["home_to_school"]=0;
											$ride["school_to_home"]=1;
											$db->insert_query($ride,"Ride");

											$result['status'] = '1';
										}else{
											$result['status'] = '1';
										}
									}else{
										$data=array();
										$data["advance_ride"]=$advance_ride;
										$data["instant_ride"]=$instant_ride;
										$data["both_ride"]=$both_ride;
										$data["availability"]=$availability;
										$where="parent=$parent";
										
										$db->update_query($data,"RideRegistrationDetail",$where);


										$dataD=array();
										$dataD["parent"]=$parent;
										$dataD["start_date"]=$start_date;
										$dataD["end_date"]=$end_date;
										$dataD["date_range"]=$date_range;
										
										$db->insert_query($dataD,"RideScheduleDates");

										$ride=array();
										$ride["parent"]=$parent;
										$ride["both_ride"]=$both_ride;
										$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
										$ride["created_at"]=date("Y-m-d");
										$ride["instant_ride"]=$instant_ride;
										$ride["advance_ride"]=$advance_ride;
										$ride["date"]=$start_date;
										$ride["home_to_school"]=1;
										$ride["school_to_home"]=0;
										$db->insert_query($ride,"Ride");

										$ride=array();
										$ride["parent"]=$parent;
										$ride["both_ride"]=$both_ride;
										$ride["no_of_seats"]=$userRDetails[0]['no_of_seats'];
										$ride["created_at"]=date("Y-m-d");
										$ride["instant_ride"]=$instant_ride;
										$ride["advance_ride"]=$advance_ride;
										$ride["date"]=$start_date;
										$ride["home_to_school"]=0;
										$ride["school_to_home"]=1;
										$db->insert_query($ride,"Ride");

										$result['status'] = '1';
									}
									
								}
							}else{
								$result['status'] = '1';
							}

							
						}
					}

					
				//}
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;

		}

		//reserve advance ride
		function student_ride_find()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$ride_type = $_REQUEST['ride_type'];
			$no_of_seats = $_REQUEST['no_of_seats'];
			$start_date = $_REQUEST['start_date'];
			$end_date = $_REQUEST['end_date'];
		
   			if($ride_type == 1)
   				$sql="select a.first_name,b.id,b.date,b.no_of_seats from UserProfile a inner join Ride b on a.id=b.parent where b.date between '$start_date' and '$end_date' and b.home_to_school=1 and b.no_of_seats >=$no_of_seats";
   			else
   				$sql="select a.first_name,b.id,b.date,b.no_of_seats from UserProfile a inner join Ride b on a.id=b.parent where b.date between '$start_date' and '$end_date' and b.school_to_home=1 and b.no_of_seats >=$no_of_seats";

   			$ret = $db->get_result($sql);

   			$result=array(
			"echo"=>1,
			"totalrecords" =>count($ret),
			"totaldisplayrecords"=>count($ret),
			"data"=>$ret,
			"status"=>'1'
			);
       	
			return $result;
		}

		function ride_request()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$studentId = $_REQUEST['studentId'];
			$ride_id = $_REQUEST['ride_id'];

			$sqlFindS = "select * from StudentRegister where id=$studentId";
       		$SInfo = $db->get_result($sqlFindS);

       		if( isset($SInfo[0]))
       		{
       			$sqlP="select * from Ride where id=$ride_id";
       			$findRide=$db->get_result($sqlP);

       			$data=array();
				$data["driver_parent"]=$findRide[0]['parent'];
				$data["rider_parent"]=$SInfo[0]['parent'];
				$data["rider_student"]=$studentId;
				$data["ride"]=$ride_id;
				$data["created_at"]=date("Y-m-d");
				$data["requested"]=1;
				$data["rejected"]=0;
				$data["accepted"]=0;
				$data["confirmed"]=0;
				$data["completed"]=0;
				date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
				$data["modified_at"]=date("Y-m-d H:i:s");
				$data["state"]='requested';

				$db->insert_query($data,"RideRequest");

				$ride=array();
				$ride["booked_seats"]=1;
				$where="id=$ride_id";
				
				$db->update_query($ride,"Ride",$where);

       			$result["status"]='1';
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		} 

		function show_requests()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$ReqDid = $_REQUEST['ReqDid'];

			$sqlFindD = "select * from UserProfile where id=$ReqDid and is_driverparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		$today_date=date("Y-m-d");

       		if( isset($DInfo[0]))
       		{
       			$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.rider_parent inner join Ride b on b.id=c.ride where c.driver_parent=$ReqDid and c.requested=1 and c.rejected !=1 and b.date>='$today_date'";

       			
       			$ret = $db->get_result($sql);
       			//print_r($ret);

       			$result=array(
				"echo"=>1,
				"totalrecords" =>count($ret),
				"totaldisplayrecords"=>count($ret),
				"data"=>$ret,
				"status"=>'1'
				);
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function ride_accept()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$request_id = $_REQUEST['request_id'];

			$sqlFindR = "select * from RideRequest where id=$request_id";
       		$RInfo = $db->get_result($sqlFindR);

       		if( isset($RInfo[0]))
       		{
       			$sqlR="select * from Ride where id=$RInfo[0]['ride']";
       			$rideInfo=$db->get_result($sqlR);

       			$data=array();
				$data["accepted"]=1;
				$data["requested"]=0;
				$data["state"]='accepted';
				date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
				$data["modified_at"]=date("Y-m-d H:i:s");
				$where="id=$request_id";
				
				$db->update_query($data,"RideRequest",$where);

				$result["status"]='1';

       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function ride_reject()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$request_id = $_REQUEST['request_id'];

			$sqlFindR = "select * from RideRequest where id=$request_id";
       		$RInfo = $db->get_result($sqlFindR);
       		$rideId=$RInfo[0]['ride'];
       		$sqlFindRide ="select * from Ride where id=$rideId";
       		$RideInfo = $db->get_result($sqlFindRide);

       		if( isset($RInfo[0]))
       		{

       			if($RInfo[0]['confirmed']==1){
       				$data=array();
					$data["rejected"]=1;
					$data["state"]='rejected';
					date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
					$data["modified_at"]=date("Y-m-d H:i:s");
					$where="id=$request_id";
					
					$db->update_query($data,"RideRequest",$where);

					$booked_seats=$RideInfo[0]['booked_seats'];
					$no_of_seats=$RideInfo[0]['no_of_seats'];

					$ride=array();
					$ride["no_of_seats"]=$no_of_seats + $booked_seats;
					$where="id=$rideId";
					
					$db->update_query($ride,"Ride",$where);

       			}else{
       				$data=array();
					$data["rejected"]=1;
					$data["state"]='rejected';
					date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
					$data["modified_at"]=date("Y-m-d H:i:s");
					$where="id=$request_id";
					
					$db->update_query($data,"RideRequest",$where);
       			}

				$result["status"]='1';

       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function show_confirm_requests()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parent_id = $_REQUEST['parent_id'];
			$today_date=date("Y-m-d");

			$sqlFindD = "select * from UserProfile where id=$parent_id and is_riderparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
       			$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,c.booked_seats,c.date,d.id,d.request_method,d.rider_student,d.riders_list from UserProfile a inner join RideRequest d on a.id=d.driver_parent inner join Ride c on c.id=d.ride where d.rider_parent=$parent_id and d.accepted=1 and d.rejected !=1 c.date>='$today_date'";

       			$ret = $db->get_result($sql);
       			//print_r($ret);

       			$result=array(
				"echo"=>1,
				"totalrecords" =>count($ret),
				"totaldisplayrecords"=>count($ret),
				"data"=>$ret,
				"status"=>'1'
				);
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function ride_confirm()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$request_id = $_REQUEST['request_id'];

			$sqlFindR = "select * from RideRequest where id=$request_id";
       		$RInfo = $db->get_result($sqlFindR);

       		if( isset($RInfo[0]))
       		{

       			$ride_id=$RInfo[0]['ride'];
	       		
   				$sqlR="select * from Ride where id=$ride_id";
       			$rideInfo=$db->get_result($sqlR);
       			$no_of_seats=$rideInfo[0]['no_of_seats'];
       			$booked_seats=$rideInfo[0]['booked_seats'];

       			$data=array();
				$data["confirmed"]=1;
				$data["accepted"]=0;
				$data["state"]='confirmed';
				date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
				$data["modified_at"]=date("Y-m-d H:i:s");
				$where="id=$request_id";
				
				$db->update_query($data,"RideRequest",$where);

				$ride=array();
				$ride["request_id"]=$request_id;
				$ride["no_of_seats"]=$no_of_seats - $booked_seats;
				$where="id=$ride_id";
				
				$db->update_query($ride,"Ride",$where);
				$result["status"]='1';

	       			

       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}


		function parent_ride_request()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parentId = $_REQUEST['parentId'];
			$ride_id = $_REQUEST['ride_id'];
			$studentIds=$_REQUEST['studentIds'];

			$no_of_seats=explode(",",$studentIds);

			$size=sizeof($no_of_seats);

			$sqlFindS = "select * from UserProfile where id=$parentId and is_riderparent=1";
       		$SInfo = $db->get_result($sqlFindS);

       		if( isset($SInfo[0]))
       		{
       			$sqlP="select * from Ride where id=$ride_id";
       			$findRide=$db->get_result($sqlP);

       			if($findRide[0]['no_of_seats'] >= $size){
       				$data=array();
					$data["driver_parent"]=$findRide[0]['parent'];
					$data["rider_parent"]=$parentId;
					$data["request_method"]=1;
					$data["ride"]=$ride_id;
					$data["created_at"]=date("Y-m-d");

					$data["riders_list"]=$studentIds;
					$data["requested"]=1;
					$data["rejected"]=0;
					$data["accepted"]=0;
					$data["confirmed"]=0;
					$data["completed"]=0;
					date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
					$data["modified_at"]=date("Y-m-d H:i:s");
					$data["state"]='requested';

					$db->insert_query($data,"RideRequest");

					$ride=array();
					$ride["booked_seats"]=$size;
					$where="id=$ride_id";
					
					$db->update_query($ride,"Ride",$where);

	       			$result["status"]='1';
       			}else{
       				$result["status"]='0';
       			}

       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function liststudent()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parentId = $_REQUEST['parentId'];

			$sql="select * from StudentRegister where parent=$parentId";
   			$ret = $db->get_result($sql);

   			$result=array(
			"echo"=>1,
			"totalrecords" =>count($ret),
			"totaldisplayrecords"=>count($ret),
			"data"=>$ret,
			"status"=>'1'
			);
       	
			return $result;
		}

		function liststudentdetails()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$studentId = $_REQUEST['studentId'];
			$request_method = $_REQUEST['request_method'];

			$sqlR="select * from RideRequest where id=$studentId";
       		$findRideR=$db->get_result($sqlR);

			if($request_method == 1){
				$id=explode(",",$findRideR[0]['riders_list']);

				$size=sizeof($id);
				$where="id=";
				for($i=0;$i<$size;$i=$i+1){
					if($i==0)
						$where=$where.$id[$i];
					else
						$where=$where." or id=".$id[$i]; 
				}

				$sql="select * from StudentRegister where $where";
			}
			else{
				$sql="select * from StudentRegister where id=$studentId";
			}

   			$ret = $db->get_result($sql);

   			$result=array(
			"echo"=>1,
			"totalrecords" =>count($ret),
			"totaldisplayrecords"=>count($ret),
			"data"=>$ret,
			"status"=>'1'
			);
       	
			return $result;
		}

		function show_active_ride()
		{
			$Home_to_school_start_time=$GLOBALS['Home_to_school_start_time'];
			$School_to_home_start_time=$GLOBALS['School_to_home_start_time'];

			$Home_to_school_end_time=$GLOBALS['Home_to_school_end_time'];
			$School_to_home_end_time=$GLOBALS['School_to_home_end_time'];

			$result=array("status" => 0);
			$db = new DBC();

			$ReqDid = $_REQUEST['ReqDid'];

			$sqlFindD = "select * from UserProfile where id=$ReqDid and is_driverparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
       			$today_date=date("Y-m-d");

				date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
				if(date('H:i:s') > $Home_to_school_start_time && date('H:i:s') < $Home_to_school_end_time){
					
					$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.rider_parent inner join Ride b on b.id=c.ride where c.driver_parent=$ReqDid and c.confirmed=1 and c.rejected !=1 and b.date='$today_date' and b.home_to_school=1";
					$ret = $db->get_result($sql);

				}else if(date('H:i:s') > $School_to_home_start_time && date('H:i:s') < $School_to_home_end_time ){
					
					$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.rider_parent inner join Ride b on b.id=c.ride where c.driver_parent=$ReqDid and c.confirmed=1 and c.rejected !=1 and b.date='$today_date' and b.school_to_home=1";
					$ret = $db->get_result($sql);
				}else{
					$ret = "";
				}

       			
       			//print_r($ret);

       			$result=array(
				"echo"=>1,
				"totalrecords" =>count($ret),
				"totaldisplayrecords"=>count($ret),
				"data"=>$ret,
				"status"=>'1'
				);
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function show_future_ride()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$ReqDid = $_REQUEST['ReqDid'];
			$today_date=date("Y-m-d");

			$sqlFindD = "select * from UserProfile where id=$ReqDid and is_driverparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
			
				$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.rider_parent inner join Ride b on b.id=c.ride where c.driver_parent=$ReqDid and c.confirmed=1 and c.rejected !=1 and b.date>'$today_date'";
				$ret = $db->get_result($sql);
       			//print_r($ret);

       			$result=array(
				"echo"=>1,
				"totalrecords" =>count($ret),
				"totaldisplayrecords"=>count($ret),
				"data"=>$ret,
				"status"=>'1'
				);
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function show_active_parent_ride()
		{
			$Home_to_school_start_time=$GLOBALS['Home_to_school_start_time'];
			$School_to_home_start_time=$GLOBALS['School_to_home_start_time'];

			$Home_to_school_end_time=$GLOBALS['Home_to_school_end_time'];
			$School_to_home_end_time=$GLOBALS['School_to_home_end_time'];

			$result=array("status" => 0);
			$db = new DBC();

			$ReqRid = $_REQUEST['ReqRid'];

			$sqlFindD = "select * from UserProfile where id=$ReqRid and is_riderparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
       			$today_date=date("Y-m-d");

				date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)
				if(date('H:i:s') > $Home_to_school_start_time && date('H:i:s') < $Home_to_school_end_time){
					
					$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.driver_parent inner join Ride b on b.id=c.ride where c.rider_parent=$ReqRid and c.confirmed=1 and c.rejected !=1 and b.date='$today_date' and b.home_to_school=1";
					$ret = $db->get_result($sql);

				}else if(date('H:i:s') > $School_to_home_start_time && date('H:i:s') < $School_to_home_end_time){
					
					$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.driver_parent inner join Ride b on b.id=c.ride where c.rider_parent=$ReqRid and c.confirmed=1 and c.rejected !=1 and b.date='$today_date' and b.school_to_home=1";
					$ret = $db->get_result($sql);
				}else{
					$ret = "";
				}

       			
       			//print_r($ret);

       			$result=array(
				"echo"=>1,
				"totalrecords" =>count($ret),
				"totaldisplayrecords"=>count($ret),
				"data"=>$ret,
				"status"=>'1'
				);
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function show_future_parent_ride()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$ReqRid = $_REQUEST['ReqRid'];
			$today_date=date("Y-m-d");

			$sqlFindD = "select * from UserProfile where id=$ReqRid and is_riderparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
			
				$sql="select a.first_name,a.profile_image_name,a.last_name,a.mobile,a.student_school,b.booked_seats,b.date,c.id,c.request_method,c.rider_student,c.riders_list from RideRequest c inner join UserProfile a on a.id=c.driver_parent inner join Ride b on b.id=c.ride where c.rider_parent=$ReqRid and c.confirmed=1 and c.rejected !=1 and b.date>'$today_date'";
				$ret = $db->get_result($sql);
       			//print_r($ret);

       			$result=array(
				"echo"=>1,
				"totalrecords" =>count($ret),
				"totaldisplayrecords"=>count($ret),
				"data"=>$ret,
				"status"=>'1'
				);
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function driver_details()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parentId = $_REQUEST['parentId'];

			$sqlFindD = "select * from UserProfile where id=$parentId and is_driverparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
				$sql="select a.*,b.*,c.*,d.* from UserProfile a inner join UserAccountDetails b on a.id=b.parent inner join UserAddress c on a.id=c.parent inner join UserVehicleDetails d on a.id=d.parent where a.id=$parentId and c.is_default=1";
				$userDetails=$db->get_result($sql);

				if( isset($userDetails[0]) )
				{
					$result["data"]=$userDetails[0];
					$result["status"]='1';
				}
				
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function rider_parent_details()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$parentId = $_REQUEST['parentId'];

			$sqlFindD = "select * from UserProfile where id=$parentId and is_riderparent=1";
       		$DInfo = $db->get_result($sqlFindD);

       		if( isset($DInfo[0]))
       		{
				$sql="select a.*,b.*,c.* from UserProfile a inner join UserAccountDetails b on a.id=b.parent inner join UserAddress c on a.id=c.parent where a.id=$parentId and c.is_default=1";
				$userDetails=$db->get_result($sql);

				$sqlS="select * from StudentRegister where parent=$parentId";
				$studentDetails=$db->get_result($sqlS);

				if( isset($userDetails[0]) )
				{
					$result["data"]=$userDetails[0];
					$result["student"]=$studentDetails;
					$result["status"]='1';
				}
				
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		function student_details()
		{
			$result=array("status" => 0);
			$db = new DBC();

			$studentId = $_REQUEST['studentId'];

			$sql = "select * from StudentRegister where id=$studentId";
       		$userDetails = $db->get_result($sql);

       		$parentId=$userDetails[0]['parent'];

       		if( isset($userDetails[0]))
       		{
				
				$sqlP="select a.*,b.* from UserProfile a inner join UserAddress b on a.id=b.parent where a.id=$parentId and b.is_default=1";
				$parentDetails=$db->get_result($sqlP);

				$result["data"]=$userDetails[0];
				$result["parent"]=$parentDetails[0];
				$result["status"]='1';
				
       		}else
       		{
       			$result["status"]='0';
       		}

			return $result;
		}

		



    }
?>
