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

	function user() {
		$result = array();
		$errors = $this->errors;

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$ID_orig = $ID;

		$values = array(
			"name"=>$this->post("name",true),
			"tag"=>$this->post("tag")
		);

		if ($values['name']==""){
			$errors['name'] = "";
		}

		if (isset($_POST['password'])&&$_POST['password']!=''){
			$values['password'] = $_POST['password'];
		}
		if (isset($_POST['email'])&&$_POST['email']!=''){
			$values['email'] = $_POST['email'];
		}
		if ($values['email']!=""){
			if (count(models\users::getInstance()->getAll("email LIKE '{$values['email']}' AND mp_users.ID != '{$ID}'"))){
				$errors['email'] = "The email address already exists";
			}
			
		}
		
		
		
		
		
		
		if (!count($errors)){
			//	test_array($values); 
			$ID = models\users::save($ID,$values);
		}
		$return = array(
			"ID" => $ID,
			"errors" => $errors
		);
		if ($ID_orig!=$ID){
			
			$return['new'] = "/?login_email={$values['email']}&login_password={$values['password']}";
		}
		return $GLOBALS["output"]['data'] = $return;
	}


}
