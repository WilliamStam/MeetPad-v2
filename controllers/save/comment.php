<?php
namespace controllers\save;

use models as models;

class comment extends _save {
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

	function item() {
		$result = array();
		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$itemID = isset($_GET['itemID']) ? $_GET['itemID'] : "";
		$parentID = isset($_GET['parentID']) ? $_GET['parentID'] : "";
		$html = isset($_GET['html']) && $_GET['html']=="false" ? false : true;
		
		
		$values = array(
			"contentID"=>$itemID,
			"parentID"=>$parentID,
			"comment"=>($this->post("comment",true))
		);
		if ($ID==""){
			$values['userID'] = $this->user['ID'];
		}
		if ($html===false){
			$values['comment'] = str_replace("\n", "</p>\n<p>", '<p>'.$values['comment'].'</p>');
			$values['comment'] = str_replace("<p>\r</p>", "<p>&nbsp;</p>", $values['comment']);
		}
		
		
		//test_array($values); 
		
		$ID = models\item_comment::save($ID,$values);
		
		//test_array($values); 

		$result['ID']=$ID;
		$result['comments']= models\item_comment::getInstance()->getAll("contentID='{$itemID}'","datein ASC");
		
		return $GLOBALS["output"]['data'] = $result;
	}



}
