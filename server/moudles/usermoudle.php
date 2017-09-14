<?php
class UserMoudle{

    public function addUser($data){		
        if ($data->nickName==null || $data->nickName=='') return 'please enter nickName';		
		if ($data->password==null || $data->password=='') return 'please enter password';
		if ($data->email==null || $data->email=='') return 'please enter email';
		if($this->checkIFNickNameIsTaken($data->nickName)) return array("error"=>"this nickName is taken");		
		if ($data->userImage==null || $data->userImage=='') $data->userImage='0000';
		$bonus = array();
		$password = $data->password;
		//$password = sha1($data->password);
		global $db;
		//ad user to DB
		$row= $db->smartQuery(array(
		'sql' => "INSERT INTO `users` (`nickName`, `password`, `userImage`, `email`, `userType`, `bonus`) VALUES (:nickName, :password, :userImage,:email,:userType,:bonus);",
		'par' => array('nickName'=>$data->nickName, 'password'=>$password, 'userImage'=>$data->userImage, 'email'=>$data->email,'userType'=>$data->userType,'bonus'=>$bonus),
		'ret' => 'result'));
		if($row =='true')
		  { //get user id
	        $row= $db->smartQuery(array(
			'sql' => "SELECT * FROM users where nickName=:nickName and password=:password",
			'par' => array('nickName'=>$data->nickName,'password' => $password),
			'ret' => 'fetch-assoc'));	
            //ad verification code to users row (by id)
			$userId = $row['userId'];
	        $verifyCode = rand (1001,9999);
			$row = $db->smartQuery(array(
			'sql' => "UPDATE `users` SET `verifyCode`=:verifyCode WHERE userId =:userId;",
			'par' => array('verifyCode'=>$verifyCode,'userId'=>$userId),
			'ret' => 'result' ));
            $this->sendVerificationMail($data->email,$verifyCode,$data->nickName);			
		  if($row==true){
			  $this->adPoints($userId,4);
			  return array('userId'=>$userId);
			  } else return array('error'=>'an error has occurred');
		  } else return array('error'=>'an error has occurred');		
	}
	
	public function login($data){
	
		if ($data->password==null || $data->password=='' || $data->nickName==null || $data->nickName=='') return array("error"=>"please submit nickName and password");		
		global $db;
		global $RatingMoudle;
		$password = $data->password;
		//$password = sha1($data->password);
		// get user data from DB
		 
		$row= $db->smartQuery(array(
			'sql' => "SELECT * FROM users where nickName=:nickName and password=:password",
			'par' => array('password' => $password, 'nickName' => $data->nickName),
			'ret' => 'fetch-assoc'));	
			if ($row!=false)
			{
				if ($row['verified'] == false) return array ("error"=>"please verify password","userId"=>$row['userId']);
				$token=(uniqid(rand(),true));
				$token = str_replace('.','', $token);
				$row['token']= $token;			  
				$this->writeTokenToUsersRow($row['userId'],$token,false);
				$userRatings = $RatingMoudle->getUserRatings($row['userId']);
				$row['userRatings'] =  (string) sizeof($userRatings);
				return $row;								
			}else return array ("error"=>"wrong nickName or password"); 
			
	}
	
    public function isLogin($data){
		global $db;
		$row= $db->smartQuery(array(
			'sql' => "SELECT * FROM users where userId=:userId",
			'par' => array('userId' => $data->userId),
			'ret' => 'fetch-assoc'));
        if(isset($row['token']))			
        if($row['token']== $data->token)return true;			 
		else return false;   
	}	
	
	public function logOut ($data){
		if($dat->token != 'anonymous')
		  {
			global $db;
			$row = $db->smartQuery(array(
			'sql' => "UPDATE `users` SET `token`=:token WHERE userId =:userId;",
			'par' => array('token'=>'00','userId'=>$data->userId),
			'ret' => 'result' ));		
			if ($row==true) return array("message"=>"see you next time");		  
		  } else{
			      return array("message"=>"see you next time");
		        }
		
	}
	
	public function editUserData($data){
	    if ($data->UPuserImage==null || $data->UPuserImage=='') $data->UPuserImage = $data->userImage;	
		if($this->checkIFNickNameIsTaken($data->UPnickname)) return array("error"=>"this nickName is taken");
		global $db; 
		$row= $db->smartQuery(array(
		'sql' => "UPDATE `users` SET `nickName`=:nickName,`userImage`=:userImage ,`email`=:email WHERE userId =:userId;",
		'par' => array('nickName'=>$data->UPnickname,'userImage'=>$data->UPuserImage,'email'=>$data->UPemail,'userId'=>$data->userId),
		'ret' => 'result' ));
		if($row =='true')return array("message"=>"success");
		else return array('error'=>'an error has occurred'); 
	}
	
	public function getUserById($data){
		global $db;
		$row= $db->smartQuery(array('sql' => "SELECT * FROM `users` WHERE userId=:userId",
		'par' => array('userId'=>$data->userId),
		'ret' => 'all'));		
	    $ratings= $db->smartQuery(array('sql' => "SELECT * FROM ratings where `userId`=".$data->userId,
						   'par' => array(),
						   'ret' => 'all'));
        $ratings['numberOfRatings']= sizeof($ratings);
	    $row[0]['bonusData'] = $data->bonusListData;
        $row[0]['ratings'] = $ratings;	
        return $row;		
	}
	
	public function sendVerificationMail($email,$verifyCode,$nickName){
		$msg = "hi ".$nickName.", your kidpick verification code is: ".$verifyCode;
		$subject = "kidpick verification code";
		$from = "from: kidpick";
		mail($email,$subject,$msg,$from);	
	}
	
	public function verifyCode($userId,$verificationCode){
		//return $verificationCode."---".$userId;
		global $db;
		$row= $db->smartQuery(array(
		'sql' => "SELECT * FROM users where userId=:userId and verifyCode=:verifyCode",
		'par' => array('userId'=>$userId,'verifyCode' => $verificationCode),
		'ret' => 'fetch-assoc'));	
		if($row!=false)
		  {
			$token=(uniqid(rand(),true));
			$token = str_replace('.','', $token);
			$row['token']= $token;			  
			$this->writeTokenToUsersRow($row['userId'],$token,true);
            return $row;			
		  }
		  else return array ("error"=>"wrong verification code");
	}
	
	public function writeTokenToUsersRow($userId,$token,$isVerification){
		if($isVerification)
		  {
			global $db;
			$row= $db->smartQuery(array(
			'sql' => "UPDATE `users` SET `token`=:token,`verified` =:verified WHERE userId =:userId;",
			'par' => array('userId'=>$userId,'verified'=>true,'token'=>$token),
			'ret' => 'result'));		  
		  }else{
					global $db;
					$row= $db->smartQuery(array(
					'sql' => "UPDATE `users` SET `token`=:token WHERE userId =:userId;",
					'par' => array('userId'=>$userId,'token'=>$token),
					'ret' => 'result'));			  
		       }
					
	}
	
	public function checkIFNickNameIsTaken($nickName){
	    global $db;	
		$row = $db->smartQuery(array(
		'sql' => "SELECT * FROM users where nickName=:nickName",
		'par' => array('nickName' => $nickName),
		'ret' => 'fetch-assoc'));	//result /fetch-assoc
		if($row!=false) return true;
		else return false;
	}
	
	public function forgotPassword ($data){
		global $db;
		$nickName = $data->nickName;
		$row = $db->smartQuery(array(
		'sql' => "SELECT * FROM users where nickName=:nickName",
		'par' => array('nickName' => $nickName),
		'ret' => 'fetch-assoc'));
		if (isset($row['email'])){
			$this->sendEmail($row['email'],$row['password']); 
			return array("message"=>"the login information has been sent to user's email");
			} else return array("error"=>"no such nick name is registerd");
		}
	    
	public function sendEmail($email,$password){
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'e-mail address not valid'; 
		$subject = "login information";
	    $from = "From: kidpick_administrator@kidpick.com". "\r\n";
		$message = "hi, did you forget your password? no big deal :) we are here to help. your password is: ".$password;
		
				
        mail($email, $subject, $message, $from);
		}
	
	public function CheckScore($userId){
		global $db;
	 	$row= $db->smartQuery(array('sql' => "SELECT points FROM `users` WHERE userId=:userId",
		'par' => array('userId'=>$userId),
		'ret' => 'all'));		
		return $row[0]['points'];
		
		}
	
	public function adPoints($userId,$points){
        global $db; 
        $p = $this->CheckScore($userId);
		$points = $points + $p;		
		$row= $db->smartQuery(array(
		'sql' => "UPDATE `users` SET `points`=:points WHERE userId =:userId;",
		'par' => array('points'=>$points,'userId'=>$userId),
		'ret' => 'result'));
		}

    public function getBonusListData($data){
	     global $db; 
         $bonusList =  $data->bonus;		
		 $bonusList_Data = array();
		 
		 for($i=0;$i<sizeof($bonusList);$i++){
			$row= $db->smartQuery(array('sql' => "SELECT * FROM `bonuslist` WHERE bonusId=:bonusId",
			'par' => array('bonusId'=>$bonusList[$i]),
			'ret' => 'all'));
			$businessId = $row[0]['businessId'];
			$bonusDiscription = $row[0]['bonusName']; 
			
			$business= $db->smartQuery(array('sql' => "SELECT * FROM `businesses` WHERE businessId=:businessId",
			'par' => array('businessId'=>$businessId),
			'ret' => 'all'));			
			
			
			
			$bonusList_Data[]= array("bonusDiscription"=>$bonusDiscription,"bonusId"=>$bonusList[$i],"businessData"=>$business); 
			 }
		 
		return $bonusList_Data;
		}		
/////////////////////////////////////////////////////////////////	
////////////////////////testing zone/////////////////////////////
/////////////////////////////////////////////////////////////////
	
	public function test(){
	$arr = array ('1','2','3');
	$key = '2';
	$arr =  array_diff($arr,array($key));
	return $arr;
	$arr = array ('1','3');
	
	
       return 12%10;

		print_r('hh');
		return 'eyal';
		$to = "neyal@cambium.co.il";
		$subject = "kidpick verification code";
		$msg = "hi eyal your kidpick verification code is: 5658";
		$from = "from: kidpick";
		mail($to,$subject, $msg,$from);			
	}
	

}

?>


