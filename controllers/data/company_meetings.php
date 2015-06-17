<?php
namespace controllers\data;

use models as models;

class company_meetings extends _data {
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
		$this->f3->set("company",$result['company']);
		return $GLOBALS["output"]['data'] = $result;
	}




	function company() {
		$domain = $this->f3->get("domain");
		$result = array();
		$userID = ($this->user['global_admin'] == '1') ? "" : "{$this->user['ID']}";
		$ID = isset($_GET['companyID']) ? $_GET['companyID'] : "";

		$company = models\company::getInstance();
		$result = $company->get($ID, true);
		


		$this->companyID = $result['ID'];


		return $GLOBALS["output"]['data'] = $result;
	}

	function meetings() {
		$selectedpage = isset($_GET['page']) ? $_GET['page'] : "1";
		$userSQL = ($this->user['global_admin']=='1')?"":"(mp_users_group.userID='{$this->user['ID']}' OR mp_users_company.admin = '1') AND ";
		
		$recordCount = count(models\meeting::getInstance()->getAll(" $userSQL mp_meetings.companyID = '{$this->companyID}'"));

		$pagination = new \pagination("page");
		$pagination = $pagination->calculate_pages($recordCount, "10", $selectedpage, "10", "0");
		
		
		
		
		
		
		
		$records = models\meeting::getInstance()->getAll(" $userSQL mp_meetings.companyID = '{$this->companyID}'", "timeEnd DESC", $pagination['limit'],array("groups"=>true));

		$result = array(
			"active"=>array(),
			"future"=>array(),
			"past"=>array(),
			"pagination"=>$pagination,
			"count"=>$recordCount
		);
		
		foreach($records as $item){
			$g = "past";
			if ($item['active']=='1'){
				$g = "active";
			}
			if ($item['future']=='1'){
				$g = "future";
			}
			$result[$g][] = $item;
		}
		
		
		
		//test_array($result); 

		return $GLOBALS["output"]['data'] = $result;
	}




}
