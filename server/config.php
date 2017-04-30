<?php

$conf=new stdClass();


$conf->DB=new stdClass();
$conf->s3=new stdClass();


//local-adham
$conf->DB->host="localhost";
$conf->DB->DBName="kidpick";
$conf->DB->userName="root";
$conf->DB->pass="";
$conf->s3->bucket=null;
/**/

/*
//tigris
$conf->DB->host="localhost";
$conf->DB->DBName="kidpickdb";
$conf->DB->userName="kidpickDB";
$conf->DB->pass="kpdb1";
$conf->s3->bucket="null";
/**/




$conf->DB->logError="log/sqlError.log";
$conf->dynamicFilePath="dynamic/";

$conf->adminPass="123456";

