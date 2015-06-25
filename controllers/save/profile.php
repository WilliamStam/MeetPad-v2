<?php
namespace controllers\save;

use models as models;

class profile extends _save {
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




	
	function resetpassword() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		

		$errors = $this->errors;

		$values = array(
			"password" => $this->post("password", true),
		);
		
		if ($values['password']==""){
			$errors['password'] = "A valid password is required";
		}

		$user = models\users::getInstance()->get($ID);
		if ($user['ID']=="") {
			$errors['name'] = "User not found";	
		}
		$user['password'] = $values['password'];
		if (!count($errors) &&$user['ID'] ){
			models\users::save($user['ID'],$values);
		}










		$return = array(
			"ID" => $ID,
			"user"=>$user,
			"errors" => $errors
		);

		
		return $GLOBALS["output"]['data'] = $return;
	}

	function resend() {
		$result = array();
		$errors = $this->errors;

		$ID = isset($_GET['ID']) ? $_GET['ID'] :$this->user['ID'];

		$user = models\users::getInstance()->get($ID);
		$status = \controllers\emails\profile::getInstance()->newuser($user['ID']);
		



		$return = array(
			"user"=>$user,
			"status"=>$status
		);
		
	
		return $GLOBALS["output"]['data'] = $return;
	}


}
