<?php
namespace controllers\save;

use models as models;

class company extends _save {
	private static $instance;
	private $errors;
	public $meetingID;
	public $companyID;

	function __construct() {
		parent::__construct();


	}

	function post($key, $required = false) {
		$val = isset($_POST[$key]) ? $_POST[$key] : "";
		if ($required && $val == "") {
			$this->errors[$key] = $required === true ? "" : $required;
		}
		return $val;
	}




	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function add_user() {
		$result = array();
		$userID = isset($_GET['userID']) ? $_GET['userID'] : "";
		$companyID = isset($_GET['companyID']) ? $_GET['companyID'] : "";
		$groups = isset($_POST['groups'])?$_POST['groups']:array();

		//
		
		$values = array(
			"groups"=>$groups
		);
		$ID = models\users::save($userID,$values);
		$vals = array(
			"admin"=>false,
			"tag"=>$this->post("tag")
		);

		models\company::getInstance()->addUser($userID,$companyID,$vals);
		
		
		return $GLOBALS["output"]['data'] = "done";
	}

	function find_user() {
		$result = array();
		$user = $this->user;
		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$companyID = isset($_GET['companyID']) ? $_GET['companyID'] : "";
		$ID_orig = $ID;

		$values = array(
			"email" => $this->post("email","Need a valid email address"),
		);
		$errors = $this->errors;
		//test_array($_POST); 



		$response = array();
		$groups = array();
		
		if (count($errors)==0){
			
			$response = models\users::getInstance()->getAll("email LIKE '%{$values['email']}%'");
			if (isset($response[0])){
				$response = $response[0];
				$ID = $response['ID'];
			}
			$t = $this->f3->get("DB")->exec("SELECT tag, admin FROM mp_users_company WHERE userID = '{$response['ID']}' AND companyID = '{$companyID}'");
			$t = $t[0];
			$response['tag'] = $t['tag'];
			$response['admin'] = $t['admin'];
			
		//	test_array(array($response['ID'],$companyID,$t)); 
			
		
			
		//	test_array($response); 
			// $ID = models\company::save($ID,$values);
			
			
		
			
		}
		$groups = models\users::getInstance()->getGroups($ID,$companyID);
		
		
		$return = array(
			"companyID" => $companyID,
			"ID" => $ID,
			"response" => $response,
			"email" => $values['email'],
			"groups" => $groups,
			"errors" => $errors
		);
		
		return $GLOBALS["output"]['data'] = $return;
	}



}
