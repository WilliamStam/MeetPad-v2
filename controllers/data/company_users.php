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


		$search = isset($_REQUEST['search'])?$_REQUEST['search']:"";
		$searchgroup = isset($_REQUEST['search-group'])?$_REQUEST['search-group']:"";
		$order = isset($_REQUEST['order'])&&$_REQUEST['order']!=''?$_REQUEST['order']:"name-asc";


		$where = "";
		if ($search != ""){
			$where = $where . " AND name LIKE '%$search%'";
		}
		if ($searchgroup != ""){
			$where = $where . " AND mp_users_group.groupID = '$searchgroup'";
		}

		$ordering = "name ASC";
		SWITCH ($order){
			CASE "name-desc":
				$ordering = "name DESC";
				break;
			CASE "lastActivity-asc":
				$ordering = "lastActivity DESC";
				break;
			CASE "lastActivity-desc":
				$ordering = "lastActivity ASC";
				break;
		}



		$users = $this->users($where,$ordering);

		$groups = $company['groups'];
		$g = array();
		foreach ($groups as $item){
			$g[$item['ID']] = $item;
		}
		
		

		//test_array($ordering); 

		$resultNoGroupsO = models\users::getInstance();
		$resultNoGroups = $resultNoGroupsO->getAll("mp_users_group.groupID is null ","name ASC","",array("companyID"=>$this->companyID));
		$resultNoGroups = $resultNoGroupsO->format($resultNoGroups);
		
		

		$result = array();
		$ug = array();
		
		$uids = array();
		foreach ($users as $item){
			$gr = explode(",",$item['groupIDs']);
			foreach ($gr as $o){
				if (isset($g[$o]))	{
					$uids[] = $item['ID'];
					$ug[$o][] = $item;
				}
			}
			
		}
		
		$uidsu = array();
		foreach ($resultNoGroups as $item){
			$uids[] = $item['ID'];
		}
		
		foreach ($users as $item){
			if (!in_array($item['ID'],$uids)){
				$resultNoGroups[] = $item;
			}
			//$uidsu[] = $item['ID'];
		}
		
		
		
		
		
		
		//test_array($resultNoGroups); 
		

		foreach ($groups as $item){
			$item['users'] = isset($ug[$item['ID']])?$ug[$item['ID']]:array();;
			$result[] = $item;
		}

		

		
		//test_array($resultNoGroups);
	

		$result = array(
			"order"=>($order),
			"company"=>$company,
			"users_no_groups"=>$resultNoGroups,
			"users"=>$result,
			"userCount"=>count($users),
			
			"search"=>array(
				"search"=>isset($_REQUEST['search'])?$_REQUEST['search']:"",
				"group"=>isset($_REQUEST['search-group'])?$_REQUEST['search-group']:"",
			)
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

	function users($where,$order) {

		$result = array();

		$usersO = models\users::getInstance();
		$result = $usersO->getAll("1 ".$where,$order,"",array("companyID"=>$this->companyID));
		$result = $usersO->format($result);
		
		
		//test_array($result); 

		return $GLOBALS["output"]['data'] = $result;
	}




}
