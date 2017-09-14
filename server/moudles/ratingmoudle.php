<?php
class RatingMoudle{

    public function addRating($data){
	    if ($data->userType == 'anonymous') return array("massage"=>"please register in order to rate");
	    global $UserMoudle;
		global $BusinessMoudle;
		global $db;
		$message = array();
		$timestamp = time();
        $canRate = $this->checkIfCanRate($timestamp,$data->businessId,$data->userId);

       // if($canRate["canRate"] == false) return array("message"=>"you can rate again this restaurant in ". $canRate["timeleft"]);
   
		//ad rating to DB
		$row = $db->smartQuery(array(
		'sql' => "INSERT INTO `ratings` (`businessId`,`userId`,`rate`,`picUrl`,`timeStamp`) VALUES (:businessId, :userId, :rate, :picUrl, :ts);",
		'par' => array('businessId'=>$data->businessId,'rate'=>$data->rate,'userId'=>$data->userId,'picUrl'=>$data->picUrl,"ts"=>$timestamp),
		'ret' => 'result'));
		if($row =='true')
		   {
	        $userRatings = $this->getUserRatings($data->userId);
			$UserMoudle->adPoints($data->userId,2);
			$points = $UserMoudle->CheckScore($data->userId);
			if ($points%10 ==0){			
			$bonus =  $this->giveBonus($data->userId);
		 	$bonunDiscription = $bonus['bonusName'];			
			$BusinessName = $BusinessMoudle->getBusinessById($bonus['businessId']);
			$BusinessName = $BusinessName[0]['businessName'];
			$message = array("bonus"=>$bonunDiscription,"business"=>$BusinessName);
				}
			return array("userRatings"=> sizeof($userRatings),"points"=>$points,"message"=>$message);										   
		   }else return array('error'=>'an error has occurred'); 			   
	}
	
	public function getUserRatings($userId){
	    global $db;
		$row= $db->smartQuery(array('sql' => "SELECT * FROM ratings where `userId`=".$userId,
								   'par' => array(),
								   'ret' => 'all'));
		return $row;			
		}

    public function checkIfCanRate($ts,$bi,$ui){
        global $db;
        $t=0;
        $timeLeft=0;
        $hours=0;
        $minutes=0;
        $canRate = false;
         $row= $db->smartQuery(array('sql' => "SELECT * FROM ratings where `userId`=".$ui." AND `businessId`=".$bi,
            'par' => array(),
            'ret' => 'all'));
       if(sizeof($row) == 0) $canRate = true;
       else $t = $row[0]['timeStamp'];
        foreach ($row as $rate){
            if ($t<$rate['timeStamp']) $t =  $rate['timeStamp'];
        }
            if ($ts-86400 > $t) $canRate = true;
            else {
                $timeLeft = $t - ($ts-86400);
                $hours = floor($timeLeft / 3600);
                $minutes = floor(($timeLeft % 3600) / 60);
            }

    if ($hours==0) return array("canRate"=>$canRate,"timeleft"=> $minutes." minutes");
    return array("canRate"=>$canRate,"timeleft"=>$hours." hours and ".$minutes." minutes");

    }

    public function giveBonus($userId){
	    global $db;		
			$rows = $db->smartQuery(array(
			'sql' => "SELECT * FROM `bonusList` WHERE `bonusStock`>0",
			'par' => array(),
			'ret' => 'all'));		
	        $count = sizeof($rows);
		    $bonunNumber = rand(0,$count-1);
			$rows[$bonunNumber]['bonusId'];
			$this->updatBonusInUsersData($rows[$bonunNumber]['bonusId'],$userId);
		    return $rows[$bonunNumber];
   
		}

	public function updatBonusInUsersData($bonusId,$userId){
	    global $db;
	    //getting user bonuses list and updating it
	    $row= $db->smartQuery(array('sql' => "SELECT `bonus` FROM `users` WHERE userId=:userId",
		'par' => array('userId'=>$userId),
		'ret' => 'all'));
	
	     $bonusList = json_decode($row[0]['bonus']); 
	     array_push($bonusList,$bonusId);
	     $bonusList = json_encode($bonusList);
	  
		$row= $db->smartQuery(array(
		'sql' => "UPDATE `users` SET `bonus`=:bonus WHERE userId =:userId;",
		'par' => array('bonus'=>$bonusList,'userId'=>$userId),
		'ret' => 'result'));
		
	    //deducting bonus from stock  

	    $bonusStock= $db->smartQuery(array('sql' => "SELECT `bonusStock` FROM `bonuslist` WHERE bonusId=:bonusId",
		'par' => array('bonusId'=>$bonusId),
		'ret' => 'all'));
	     $bonusStock = $bonusStock[0]['bonusStock'];
	     $bonusStock = $bonusStock-1; 
	  
		$row= $db->smartQuery(array(
		'sql' => "UPDATE `bonuslist` SET `bonusStock`=:bonusStock WHERE bonusId =:bonusId;",
		'par' => array('bonusStock'=>$bonusStock,'bonusId'=>$bonusId),
		'ret' => 'result'));		
		
		}

	public function deleteBonusFromDB($data){
	    global $db;
    	$bonusList = $data->bonus;
		$newBonusList = array();
		$bonusIDToDelete = $data->bonusIDToDelete;
		
		for($i=0;$i<sizeof($bonusList);$i++){
		     if($bonusList[$i] != $bonusIDToDelete){
	             array_push($newBonusList,$bonusList[$i]);	             		 
				 } else $bonusIDToDelete = null;		
			} 
	   $newBonusList = json_encode($newBonusList);

	   $row= $db->smartQuery(array(
       'sql' => "UPDATE `users` SET `bonus`=:bonus WHERE userId =:userId;",
	   'par' => array('bonus'=>$newBonusList,'userId'=>$data->userId),
	   'ret' => 'result'));
		
		return array("message"=>"bonus deleted successfully");
	}
}

?>