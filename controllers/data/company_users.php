<?php
namespace controllers\data;

use models as models;

class company_users extends _data {
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
			"users" => $this->users()

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
		


		$this->companyID = $result['ID'];


		return $GLOBALS["output"]['data'] = $result;
	}

	function users() {

		$result = array();
		
		//test_array($result); 

		return $GLOBALS["output"]['data'] = $result;
	}




}
