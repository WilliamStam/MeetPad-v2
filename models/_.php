<?php
namespace models;
use \timer as timer;

class _ {
	private static $instance;
	function __construct() {
		$this->f3 = \Base::instance();
		$this->user = $this->f3->get("user");
	}


	public static function dbStructure($table, $additionalFields = array()) {
		$f3 = \Base::instance();
		$result = array();
		foreach ($f3->get("DB")->exec("EXPLAIN $table;") as $key => $value) {
			$result[$value["Field"]] = "";
		}
		foreach ($additionalFields as $key => $value) {
			if ($key) {
				$result[$key] = $value;
			} else {
				$result[$value] = "";
			}
		}
		return $result;
	}
	
	
	
	function lookup($ID, $table) {
		$art = new \DB\SQL\Mapper($this->f3->get("DB"), $table);
		$art->load("ID ='$ID'");
		
		return $art;
	}
	
	
	public function _log($typeID, $id = array(), $text = "", $data = array()) {
		
		//save($id=array("contentID"=>"","commentID"=>"","meetingID"=>"","companyID"=>"","answerID"=>""),$text="",$userID="") {
		$timer = new timer();
		$f3 = \base::instance();
		
		$user = $f3->get("user");
		$userID = $user['ID'];
		
		$values = array(
				'typeID' => $typeID,
				'userID' => (isset($id["userID"])) ? $id["userID"] : $userID,
				'contentID'=>(isset($id["contentID"])) ? $id["contentID"] : null,
				'commentID'=>(isset($id["commentID"])) ? $id["commentID"] : null,
				'meetingID'=>(isset($id["meetingID"])) ? $id["meetingID"] : null,
				'companyID'=>(isset($id["companyID"])) ? $id["companyID"] : null,
				'optionID'=>(isset($id["optionID"])) ? $id["optionID"] : null,
				'fileID'=>(isset($id["fileID"])) ? $id["fileID"] : null,
				'text'=>$text,
				'data'=>json_encode($data),
				'sessionID'=>session_id()
		);
		
		
		
		if ($values['fileID'] != null && $values['contentID'] == null) {
			$values['contentID'] = $this->lookup($values['fileID'], 'mp_content_files')->contentID;
		}
		
		if ($values['optionID'] != null && $values['contentID'] == null) {
			$values['contentID'] = $this->lookup($values['optionID'], 'mp_content_poll_answers')->contentID;
		}
		
		if ($values['commentID'] != null && $values['contentID'] == null) {
			$values['contentID'] = $this->lookup($values['commentID'], 'mp_content_comments')->contentID;
		}
		
		if ($values['contentID'] != null && $values['meetingID'] == null) {
			$values['meetingID'] = $this->lookup($values['contentID'], 'mp_content')->meetingID;
		}
		
		if ($values['meetingID'] != null && $values['companyID'] == null) {
			$values['companyID'] = $this->lookup($values['meetingID'], 'mp_meetings')->companyID;
		}
		
		
		$art = new \DB\SQL\Mapper($this->f3->get("DB"), 'mp_logs');
		foreach ($values as $key => $value) {
			if (isset($art->$key) && $key != "ID") {
				$art->$key = $value;
			}
		}
		
		$art->save();
		
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $values;
	}
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
