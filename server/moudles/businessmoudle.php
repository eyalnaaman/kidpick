<?php
class BusinessMoudle{

    public function addBusiness($data){	
	    if($data->userType=='admin')
		  {
				if ($data->businessName==null || $data->businessName=='' || $data->lat==null || $data->lat=='' || $data->lon==null || $data->lon=='' ) return array("error"=>"please submit business name and geoLocation");				
				global $db;
				//ad business to DB
				$row= $db->smartQuery(array(
				'sql' => "INSERT INTO `businesses` (`businessName`,`lat`,`lon`) VALUES (:businessName, :lat, :lon);",
				'par' => array('businessName'=>$data->businessName, 'lat'=>$data->lat, 'lon'=>$data->lon),
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
				'sql' => "UPDATE `businesses` SET `businessName`=:businessName,`lat`=:lat,`lon`=:lon WHERE businessId =:businessId;",
				'par' => array('businessName'=>$data->businessName,'lat'=>$data->lat,'lon'=>$data->lon,'businessId'=>$data->businessId),
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
	
	public function getBusinesses(){
		global $db;
		$row= $db->smartQuery(array('sql' => "SELECT * FROM businesses",
		                            'par' => array(),
									'ret' => 'all'));
        return $row;											
	}

	public function getBusinessesNearby($data){
	   $radius = 0.6;
//$radius = 1000000;
	   $businessNearby = array();
	   $lat = $data->lat;
	   $lon = $data->lon;
	   $allBusinesses = $this->getBusinesses();
	   $businessesNum = sizeof($allBusinesses);
	   for($i=0;$i<$businessesNum;$i++){
		   $lat_temp = $allBusinesses[$i]['lat'];
		   $lon_temp = $allBusinesses[$i]['lon'];
		   $distance = $this->getDistance($lat, $lon, $lat_temp, $lon_temp, 'K');	
		   if($distance < $radius) $businessNearby[] = $allBusinesses[$i];		   
		   }
		return $businessNearby;
		}
		
	function getBusinessLocation($bId){
		global $db;
		$row= $db->smartQuery(array('sql' => "SELECT * FROM businesses WHERE businessId=:businessId",
		                            'par' => array('businessId'=>$bId),
									'ret' => 'all'));
        return $row;	
		
		}
		
		
	function getDistance($lat1, $lon1, $lat2, $lon2, $unit) {
	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);

	  if ($unit == "K") {
		return ($miles * 1.609344);
	  } else if ($unit == "N") {
		  return ($miles * 0.8684);
		} else {
			return $miles;
		  }
	}		
		
		
		
}

?>


