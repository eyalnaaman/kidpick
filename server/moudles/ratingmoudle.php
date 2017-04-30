<?php
class RatingMoudle{

    public function addRating($data){	
		global $db;
		//ad rating to DB
		$row= $db->smartQuery(array(
		'sql' => "INSERT INTO `ratings` (`businessId`,`userId`,`rate`,`picUrl`) VALUES (:businessId, :userId, :rate, :picUrl);",
		'par' => array('businessId'=>$data->businessId,'rate'=>$data->rate,'userId'=>$data->userId,'picUrl'=>$data->picUrl),
		'ret' => 'result'));
		if($row =='true')
		   {
			   $row= $db->smartQuery(array('sql' => "SELECT * FROM ratings where `userId`=".$data->userId,
			                               'par' => array(),
										   'ret' => 'all'));
                return array("ratings"=> sizeof($row));										   
		   }else return array('error'=>'an error has occurred'); 			   
	}
	
}

?>