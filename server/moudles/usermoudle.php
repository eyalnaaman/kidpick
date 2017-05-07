<?php
class UserMoudle{

    public function adUser($data){		
        if ($data->nickName==null || $data->nickName=='') return 'please enter nickName';		
		if ($data->password==null || $data->password=='') return 'please enter password';
		if ($data->email==null || $data->email=='') return 'please enter email';		
		if ($data->userImage==null || $data->userImage=='') $data->userImage='0000';		
		$password = sha1($data->password);
		$passwordTaken = $this->checkIFPasswordIsTaken($password); 
		if($passwordTaken) return array("error"=>"this password is taken");
		global $db;
		//ad user to DB
		$row= $db->smartQuery(array(
		'sql' => "INSERT INTO `users` (`nickName`, `password`, `userImage`, `email`, `userType`) VALUES (:nickName, :password, :userImage,:email,:userType);",
		'par' => array('nickName'=>$data->nickName, 'password'=>$password, 'userImage'=>$data->userImage, 'email'=>$data->email,'userType'=>$data->userType),
		'ret' => 'result'));
		if($row =='true')
		  { //get user id
	        $row= $db->smartQuery(array(
			'sql' => "SELECT * FROM users where email=:email and password=:password",
			'par' => array('email'=>$data->email,'password' => $password),
			'ret' => 'fetch-assoc'));	
            //ad verification code to users row (by id)
			$userId = $row['userId'];
	        $verifyCode = rand (1000,9999);
			$row = $db->smartQuery(array(
			'sql' => "UPDATE `users` SET `verifyCode`=:verifyCode WHERE userId =:userId;",
			'par' => array('verifyCode'=>$verifyCode,'userId'=>$userId),
			'ret' => 'result' ));
            $this->sendVerificationMail($data->email,$verifyCode,$data->nickName);			
			if($row==true)return array('userId'=>$userId); else return array('error'=>'an error has occurred');
		  } else return array('error'=>'an error has occurred');		
	}
	
	public function login($data){
		if ($data->password==null || $data->password=='' || $data->email==null || $data->email=='') return array("error"=>"please submit email and password");		
		global $db;
		$password = sha1($data->password);
		// get user data from DB
		$row= $db->smartQuery(array(
			'sql' => "SELECT * FROM users where email=:email and password=:password",
			'par' => array('password' => $password, 'email' => $data->email),
			'ret' => 'fetch-assoc'));	
			if ($row!=false)
			{
				if ($row['verified'] == false) return array ("error"=>"please verify password","userId"=>$row['userId']);
				$token=(uniqid(rand(),true));
				$token = str_replace('.','', $token);
				$row['token']= $token;			  
				$this->writeTokenToUsersData($row['userId'],$token,false);
				return $row;								
			}else return array ("error"=>"wrong email or password"); 
			
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
	    if ($data->UPuserImage==null || $data->UPuserImage=='') $data->UPuserImage=$data->userImage;		
		global $db; 
		$row= $db->smartQuery(array(
		'sql' => "UPDATE `users` SET `nickName`=:nickName,`userImage`=:userImage ,`email`=:email WHERE userId =:userId;",
		'par' => array('nickName'=>$data->UPnickName,'userImage'=>$data->UPuserImage,'email'=>$data->UPemail,'userId'=>$data->userId),
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
	
        $row[0]['ratings'] = $ratings;	
        return $row;		
	}
	
	public function sendVerificationMail($email,$verifyCode,$nickName){
		$msg = "hi ".$nickName." your kidpick verification code is: ".$verifyCode;
		$subject = "kidpick verification code";
		$from = "from: kidpick";
		mail($email,$subject,$msg,$from);	
	}
	
	public function verifyCode($userId,$verificationCode){
	//	return $verificationCode;
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
			$this->writeTokenToUsersData($row['userId'],$token,true);
            return $row;			
		  }
		  else return array ("error"=>"wrong verification code");
	}
	
	public function writeTokenToUsersData($userId,$token,$isVerification){
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
	
	public function checkIFPasswordIsTaken($password){
	    global $db;	
		$row = $db->smartQuery(array(
		'sql' => "SELECT * FROM users where password=:password",
		'par' => array('password' => $password),
		'ret' => 'fetch-assoc'));	//result /fetch-assoc
		if($row!=false) return true;
		else return false;
	}
	
	
	
	
	
	
	
	
	
	
	public function test(){
		return 'eyal';
		$to = "neyal@cambium.co.il";
		$subject = "kidpick verification code";
		$msg = "hi eyal your kidpick verification code is: 5658";
		$from = "from: kidpick";
		mail($to,$subject, $msg,$from);			
	}
	

}

?>


