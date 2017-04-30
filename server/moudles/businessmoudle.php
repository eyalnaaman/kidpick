<?php
class BusinessMoudle{

    public function addBusiness($data){	
	    if($data->userType=='admin')
		  {
				if ($data->businessName==null || $data->businessName=='' || $data->geoLocation==null || $data->geoLocation=='') return array("error"=>"please submit business name email and geoLocation");				
				global $db;
				//ad business to DB
				$row= $db->smartQuery(array(
				'sql' => "INSERT INTO `businesses` (`businessName`,`geoLocation`) VALUES (:businessName, :geoLocation);",
				'par' => array('businessName'=>$data->businessName, 'geoLocation'=>$data->geoLocation),
				'ret' => 'result'));
				if($row =='true')return array("message"=>"success");
				else return array('error'=>'an error has occurred'); 			  
		  }else return array("error"=>"you have no authorization for this action");
		
	}
	
	public function editBusinessData($data){
	    if($data->userType=='admin')
		  {
				if ($data->userImage==null || $data->userImage=='') $data->userImage='0000';		
				global $db; 
				$row= $db->smartQuery(array(
				'sql' => "UPDATE `businesses` SET `businessName`=:businessName,`geoLocation`=:geoLocation WHERE businessId =:businessId;",
				'par' => array('businessName'=>$data->businessName,'geoLocation'=>$data->geoLocation,'businessId'=>$data->businessId),
				'ret' => 'result' ));
				if($row =='true')return array("message"=>"success");
				else return array('error'=>'an error has occurred');			  
		  }else return array("error"=>"you have no authorization for this action");					
	}

	public function deleteBusiness($data){
		if ($userType=='admin')
		  {
			global $db;
			$row= $db->smartQuery(array(
			'sql' => "DELETE FROM `businesses` WHERE businessId =:businessId;",
			'par' => array('businessId'=>$data->businessId),
			'ret' => 'result'
		    ));
			if($row =='true')return array("message"=>"success");
			else return array('error'=>'an error has occurred');			  
		  }else return array("error"=>"you have no authorization for this action");	
	}
	
	public function getBusinesses($data){
		global $db;
		$row= $db->smartQuery(array('sql' => "SELECT * FROM businesses",
		                            'par' => array(),
									'ret' => 'all'));
        return $row;											
	}

}

?>


