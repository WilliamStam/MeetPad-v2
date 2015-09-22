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

		$logger = models\_::getInstance();
	

		if ($this->user['ID'] && $itemID!=""){
			
			$g = models\item::getInstance()->get($itemID);
			
			
		
			
			$pollO = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_content_poll_voted");
			$pollO->load("userID='{$this->user['ID']}' AND contentID='{$itemID}'");
			//test_array(array($pollOption->ID,$answerID)); 		
			
			
			
			
			$pollOption = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_content_poll_answers");
			$pollOption->load("ID='$answerID'");
			$ar = array(
					"f"=>'answer',
					'w'=>'',
					'n'=>'',
			);
			
			if ($answerID==""){
				$heading = 'Vote Removed - ';
				$pollO->erase();
				$ar['n'] = 'Removed';
				$ar['w'] = $pollOption->answer;
			} else {
				if ($pollO->dry()){
					$heading = 'Vote Added - ';
					$ar['n'] = $pollOption->answer;
					$ar['w'] = '';
				} else {
					$heading = 'Vote Changed - ';
					
					$pollOptionN = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_content_poll_answers");
					$pollOptionN->load("ID='{$pollO->answerID}'");
					
					$ar['n'] = $pollOption->answer;
					$ar['w'] = $pollOptionN->answer;
				}
				
				$pollO->contentID = $itemID;
				$pollO->answerID = $answerID;
				$pollO->userID = $this->user['ID'];
				$pollO->save();
			}
			$logger->_log(7,array('optionID'=>$answerID),$heading.$g['heading'],$ar);
			
			
		}
		
			

		
		return $GLOBALS["output"]['data'] = $return;
	}



}
