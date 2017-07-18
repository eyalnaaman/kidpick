<?php
date_default_timezone_set ( "Asia/Jerusalem" );
require 'db.php';
require 'config.php';
require 'moudles/usermoudle.php';
require 'moudles/businessmoudle.php';
require 'moudles/ratingmoudle.php';

$db = new Db($conf->DB->host,$conf->DB->DBName,$conf->DB->userName,$conf->DB->pass,$conf->DB->logError);

$UserMoudle = new UserMoudle();
$BusinessMoudle = new BusinessMoudle();
$RatingMoudle = new RatingMoudle();

$type=isset($_GET["type"])?$_GET["type"]:null;
$data = json_decode(file_get_contents("php://input"));
if(!$data) $data=(object)array();
if(isset($data->userId))
   {
	 $userData = $UserMoudle->getUserById($data);  
	 $data->userType =$userData[0]['userType'];	   
	 $data->nickName =$userData[0]['nickName'];	   
	 $data->email =$userData[0]['email'];	   
	 $data->userImage =$userData[0]['userImage'];	   
   }else $data->userId=0;

switch($type)
 {
	                   //////usermoudle/////
	case "addUser":
		$ret = $UserMoudle->addUser($data);
	break;
	case "verifyCode":
		$ret = $UserMoudle->verifyCode($data->userId,$data->verificationCode); 
	break;
	case "login":
		$ret = $UserMoudle->login($data);   
	break;	
	case "isLogin":
		$ret = $UserMoudle->isLogin($data);   
	break;	
	case "logOut":
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $UserMoudle->logOut($data);   
	break;
	case "editUserData": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $UserMoudle->editUserData($data);   
	break;
	case "getUserById": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $UserMoudle->getUserById($data);   
	break;
	                    //////businessmoudle/////
	case "getDistance": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $BusinessMoudle->getDistance($data->lat, $data->lon, $data->lat_temp, $data->lon_temp, 'K');   
	break;						
						
	case "addBusiness": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $BusinessMoudle->addBusiness($data);   
	break;
	case "editBusinessData": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $BusinessMoudle->editBusinessData($data);   
	break;		
	case "getBusinesses": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $BusinessMoudle->getBusinesses();   
	break;
	case "getBusinessesNearby": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $BusinessMoudle->getBusinessesNearby($data);   
	break;	
	
	
	case "deleteBusiness": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $BusinessMoudle->deleteBusiness($data);   
	break;
	                  ////ratingmoudle/////
	case "addRating": 
	    if ($UserMoudle->isLogin($data) == false ) $ret =  array("error" => "not login");
		else $ret = $RatingMoudle->addRating($data);   
	break;					  
					  
					  
					  
	case "test":
		$ret = $UserMoudle->test();
	break;	
	default:
		$ret=array('error'=>'wrong type'); 
	 	 
 }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Thu, 1 Jan 1970 00:00:00 GMT");
echo json_encode($ret);


?>