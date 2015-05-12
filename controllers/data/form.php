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
		$result['company'] = models\company::getInstance()->get($companyID,true)->format()->show();
	
		
		
		return $GLOBALS["output"]['data'] = $result;
	}




}
