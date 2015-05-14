<?php
namespace controllers\data;
use models as models;

class form extends _data {
	private static $instance;
	public $meetingID;
	public $companyID;
	function __construct() {
		parent::__construct();

	}

	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	

	function company() {
		$result = array();
		
		$ID = isset($_GET['ID'])?$_GET['ID']:"";

		$result =  models\company::getInstance()->get($ID,true)->getGroups()->getCategories()->format()->show();

		
	
		
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function meeting() {
		$result = array();
		
		$ID = isset($_GET['ID'])?$_GET['ID']:"";
		$IDparts = explode("-",$ID);
		$companyID = "";
		if (isset($IDparts[1])){
			$companyID = $IDparts[1];
			$ID = $IDparts[0];
		}
		
		$result =  models\meeting::getInstance()->get($ID,true)->getGroups()->format()->show();

		if ($result['companyID'])$companyID = $result['companyID'];
		$result['company'] = models\company::getInstance()->get($companyID,true)->getGroups()->format()->show();
	
		$groups = $result['company']['groups'];
		$meetingGroups = $result['groups'];
		$g = array();
		$gr = array();
		
		foreach ($meetingGroups as $item)$g[] = $item['ID'];
		foreach ($groups as $item){
			$item['active']='0';
			if (in_array($item['ID'],$g)){
				$item['active']='1';
			}
			$gr[] = $item;
		}
		$result['groups'] = $gr;
		
		
		//test_array($result['groups']); 
		
		
		
		
		return $GLOBALS["output"]['data'] = $result;
	}

function item() {
		$result = array();
		
		$ID = isset($_GET['ID'])?$_GET['ID']:"";
		$IDparts = explode("-",$ID);
		$mID = "";
		$cID = "";
		if (isset($IDparts[1])){
			$mID = $IDparts[1];
			$ID = $IDparts[0];
		}
	if (isset($IDparts[2])){
		$cID = $IDparts[2];
	}
		
		$result =  models\item::getInstance()->get($ID,true)->getGroups()->format()->show();

	if ($result['meetingID'])$mID = $result['meetingID'];
	$result['meeting'] = models\meeting::getInstance()->get($mID,true)->getGroups()->format()->show();

	if ($result['meeting']['companyID']) $cID = $result['meeting']['companyID'];
	$result['company'] = models\company::getInstance()->get($cID,true)->getGroups()->format()->show();
	
		
		//test_array($result['groups']); 
		
		
		
		
		return $GLOBALS["output"]['data'] = $result;
	}




}
