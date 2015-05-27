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

		$result = models\company::getInstance()->get($ID, $userID)->getGroups()->getCategories()->format()->show();


		$this->companyID = $result['ID'];


		return $GLOBALS["output"]['data'] = $result;
	}

	function meetings() {
		
		$userID = ($this->user['global_admin']=='1')?"":$this->user['ID'];
		$userSQL = ($this->user['global_admin']=='1')?"":"mp_users_group.userID='{$this->user['ID']}' AND ";
		$records = models\meeting::getInstance()->getUser(" $userSQL mp_meetings.companyID = '{$this->companyID}'", "timeEnd DESC")->format()->show();

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
