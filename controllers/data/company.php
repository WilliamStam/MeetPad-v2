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
			"meetings" => $this->meetings()

		);

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
		
		$userSQL = ($this->user['global_admin']=='1')?"":"(mp_users_group.userID='{$this->user['ID']}' OR mp_users_company.admin = '1') AND ";
		$records = models\meeting::getInstance()->getAll(" $userSQL mp_meetings.companyID = '{$this->companyID}'", "timeEnd DESC", "0,10");

		$result = array(
			"active"=>array(),
			"past"=>array()
		);
		
		foreach($records as $item){
			$result[$item['active']=='1'?"active":"past"][] = $item;
		}
		
		
		
		//test_array($result); 

		return $GLOBALS["output"]['data'] = $result;
	}




}
