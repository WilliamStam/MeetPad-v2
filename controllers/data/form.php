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




}
