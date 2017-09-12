<?php
class RatingMoudle{

    public function addRating($data){
		global $db;
		 $timestamp = time();
		 //$date = date('d-m-y',$timestamp);
        $canRate = $this->checkIfCanRate($timestamp,$data->businessId,$data->userId);

        if($canRate["canRate"] == false) return array("message"=>"you can rate again this restaurant in ". $canRate["timeleft"]);
		//ad rating to DB
		$row = $db->smartQuery(array(
		'sql' => "INSERT INTO `ratings` (`businessId`,`userId`,`rate`,`picUrl`,`timeStamp`) VALUES (:businessId, :userId, :rate, :picUrl, :ts);",
		'par' => array('businessId'=>$data->businessId,'rate'=>$data->rate,'userId'=>$data->userId,'picUrl'=>$data->picUrl,"ts"=>$timestamp),
		'ret' => 'result'));
		if($row =='true')
		   {
	        $userRatings = $this->getUserRatings($data->userId);
			return array("userRatings"=> sizeof($userRatings));										   
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


}

?>