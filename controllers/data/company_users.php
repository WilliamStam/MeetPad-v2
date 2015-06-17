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

		$company = $this->company();
		$this->f3->set("company",$company);
		
		$users = $this->users();

		$groups = $company['groups'];
		$g = array();
		foreach ($groups as $item){
			$g[$item['ID']] = $item;
		}
		
		
		
		
		

		$result = array();
		
		
		$ug = array();
		foreach ($users as $item){
			$gr = explode(",",$item['groupIDs']);
			foreach ($gr as $o){
				if (isset($g[$o]))	$ug[$o][] = $item;
			}
			
		}

		foreach ($groups as $item){
			$item['users'] = isset($ug[$item['ID']])?$ug[$item['ID']]:array();;
			$result[] = $item;
		}

		

		$resultNoGroupsO = models\users::getInstance();
		$resultNoGroups = $resultNoGroupsO->getAll("mp_users_group.groupID is null","name ASC","",array("companyID"=>$this->companyID));
		$resultNoGroups = $resultNoGroupsO->format($resultNoGroups);
		//test_array($resultNoGroups);
	

		$result = array(
			"company"=>$company,
			"users_no_groups"=>$resultNoGroups,
			"users"=>$result,
			"userCount"=>count($users)
		);




		$this->f3->set("menu",array("companyID"=>$company['ID']));


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

		$usersO = models\users::getInstance();
		$result = $usersO->getAll("","name ASC","",array("companyID"=>$this->companyID));
		$result = $usersO->format($result);
		
		
		//test_array($result); 

		return $GLOBALS["output"]['data'] = $result;
	}




}
