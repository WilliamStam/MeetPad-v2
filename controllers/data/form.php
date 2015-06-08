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

	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}




	function company() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";

		$company = models\company::getInstance();
		$result = $company->get($ID, true);
		$result['groups'] = $company->getGroups($result['ID']);
		$result['categories'] = $company->getCategories($result['ID']);





		return $GLOBALS["output"]['data'] = $result;
	}

	function meeting() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$IDparts = explode("-", $ID);
		$companyID = "";
		if (isset($IDparts[1])) {
			$companyID = $IDparts[1];
			$ID = $IDparts[0];
		}

		$object = models\meeting::getInstance();
		
		$result = $object->get($ID, true);
		$result['groups'] = array();
		
		
		if ($result['companyID'])$companyID = $result['companyID'];

		$result['company'] = models\company::getInstance()->get($companyID,true);
		$result['groups'] = $object->getGroups($result['ID'],$companyID);

		//test_array($result['groups']); 
		if ($result['ID']==''){
			$result['active'] = '1';
		}





		



		return $GLOBALS["output"]['data'] = $result;
	}

	function item() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$IDparts = explode("-", $ID);
		$mID = "";
		$cID = "";
		if (isset($IDparts[1])) {
			$mID = $IDparts[1];
			$ID = $IDparts[0];
		}
		if (isset($IDparts[2])) {
			$cID = $IDparts[2];
		}
		
		$object = models\item::getInstance();

		$result = $object->get($ID,true);
		
		
		//test_array($ID); 
	
		
		if ($result['meetingID']) $mID = $result['meetingID'];

		$result['groups'] = $object->getGroups($result['ID'],$mID);
		$result['meeting'] = models\meeting::getInstance()->get($mID, true);
		
		if ($result['meeting']['companyID']) $cID = $result['meeting']['companyID'];
		
		
		$companyObject = models\company::getInstance();
		$result['company'] = $companyObject->get($cID, true);
		$result['company']['categories'] = $companyObject->getCategories($cID);


	//	test_array($mID); 
	//	test_array($result['groups'] ); 
		
		
	//	test_array($result['groups']); 
		



		return $GLOBALS["output"]['data'] = $result;
	}




}
