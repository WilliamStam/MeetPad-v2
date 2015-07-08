<?php
namespace controllers\save;

use models as models;

class poll extends _save {
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




	function vote() {
		$result = array();
		$user = $this->user;
		$itemID = isset($_GET['itemID']) ? $_GET['itemID'] : "";
		$answerID = isset($_REQUEST['poll-answer']) ? $_REQUEST['poll-answer'] : "";


		$return = array(
			"itemID"=>$itemID,
			"answerID"=>$answerID
		);

	

		if ($this->user['ID'] && $itemID!=""){
			$pollO = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_content_poll_voted");
			$pollO->load("userID='{$this->user['ID']}' AND contentID='{$itemID}'");
			if ($answerID==""){
				
				$pollO->erase();
			} else {
				$pollO->contentID = $itemID;
				$pollO->answerID = $answerID;
				$pollO->userID = $this->user['ID'];
				$pollO->save();
			}
			
		}
		
			

		
		return $GLOBALS["output"]['data'] = $return;
	}



}
