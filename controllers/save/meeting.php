<?php
namespace controllers\save;

use models as models;

class meeting extends _save {
	private static $instance;
	private $errors;
	public $meetingID;
	public $companyID;

	function __construct() {
		parent::__construct();


	}

	function post($key, $required = false, $default="") {
		$val = isset($_POST[$key]) ? $_POST[$key] : "";
		if ($required && $val == "") {
			$this->errors[$key] = $required === true ? "" : $required;
		}
		if ($default!="" && $val ==""){
			$val = $default;
		}
		return $val;
	}




	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}




	function attending() {
		$result = array();
		$user = $this->user;
		$userID = isset($_GET['userID']) ? $_GET['userID'] : "";
		$meetingID = isset($_REQUEST['meetingID']) ? $_REQUEST['meetingID'] : "";


		$return = array(
			"userID"=>$userID,
			"meetingID"=>$meetingID
		);

	//test_array($return); 

		if ($userID && $meetingID){
			$obj = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_user_attendance");
			$obj->load("userID='{$userID}' AND meetingID='{$meetingID}'");
			if (!$obj->dry()){

				$obj->erase();
			} else {
				$obj->meetingID = $meetingID;
				$obj->userID = $userID;
				$obj->save();
			}
			
		}
		
		

		
		return $GLOBALS["output"]['data'] = $return;
	}



}
