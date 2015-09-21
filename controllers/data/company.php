<?php
namespace controllers\data;

use models as models;

class company extends _data {
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
	
	
	function data() {
		$result = array();
		$result = array(
				"company" => $this->company(),
				"meetings" => $this->meetings(),
				"stats" => $stats = models\stats::getInstance()->get("companyID='{$this->companyID}'")
		
		);
		$this->f3->set("company", $result['company']);
		
		
		return $GLOBALS["output"]['data'] = $result;
	}
	
	
	function company() {
		$domain = $this->f3->get("domain");
		$result = array();
		$userID = ($this->user['global_admin'] == '1') ? "" : "{$this->user['ID']}";
		$ID = isset($_GET['companyID']) ? $_GET['companyID'] : "";
		
		
		$company = models\company::getInstance();
		$result = $company->get($ID, true);
		$result['groups'] = $company->getGroups($result['ID']);
		$result['categories'] = $company->getCategories($result['ID']);
		
		
		$this->companyID = $result['ID'];
		
		
		return $GLOBALS["output"]['data'] = $result;
	}
	
	function meetings() {
		
		$records = models\meeting::getInstance()->getAll("mp_meetings.companyID = '{$this->companyID}' AND (timeEnd>= now() AND timeStart <= now())", "timeEnd DESC", "", array(
				"groups" => true,
				"userID" => $this->user['ID']
		));
		
		$result = array(
				"active" => array(),
				"past" => array()
		);
		
		foreach ($records as $item) {
			$result[$item['active'] == '1' ? "active" : "past"][] = $item;
		}
		
		
		//test_array($result); 
		
		return $GLOBALS["output"]['data'] = $result;
	}
	
	
}
