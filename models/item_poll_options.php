<?php
namespace models;

use timer as timer;

class item_poll_options extends _ {
	private static $instance;

	//private $method;
	function __construct() {
		parent::__construct();
	}

	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function get($ID) {
		$timer = new timer();
		$data = array();
		if (is_array($ID)) {
			$data = $ID;
		} else {
			$data = item::getInstance()->get($ID);

		}
		$ID = $data['ID'];
		$return = null;


		if ($data['poll']) {
			
			$sql_select = "";
			
			if ($data['poll_anonymous']!='1'){
				$sql_select = ", GROUP_CONCAT(mp_users.name ORDER BY mp_users.name ASC SEPARATOR ', ')  AS voted";
			}
			
			
			$options = $this->f3->get("DB")->exec("
				SELECT mp_content_poll_answers.*, count(mp_content_poll_voted.userID) AS votes $sql_select
				FROM (mp_content_poll_answers LEFT JOIN mp_content_poll_voted ON mp_content_poll_voted.answerID = mp_content_poll_answers.ID)LEFT JOIN mp_users ON mp_users.ID = mp_content_poll_voted.userID  
				WHERE mp_content_poll_answers.contentID = '{$ID}' 
				GROUP BY mp_content_poll_answers.ID 
				ORDER BY orderby ASC
		"
			);

			
			$return = array();
			$votes = 0;
			foreach ($options as $item) {
				$votes = $votes + $item['votes'];
			}
			
			$o = array();
			foreach ($options as $item) {
				$item['percent'] = $item['votes']?($item['votes'] / $votes)*100:0  ;
				$o[] = $item;
				
			}


			$return = array(
				"question" => $data['poll'],
				"options" => $o,
				"votes" => $votes

			);
			
			//test_array($return); 
		}



		//test_array($return);
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		
		//	test_array($values); 
		$IDorig = $ID;$changes = array();
		
		
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_poll_answers");
		$art->load("ID='$ID'");
		
		foreach ($values as $key => $value) {
			$value = $f3->scrub($value,$f3->get("TAGS"));
			if ($art->$key != $value) {
				$changes[] = array(
						"f" => $key,
						"w" => $art->$key,
						"n" => $value
				);
			}
			if (isset($art->$key)) {
				$art->$key =  $value;
			}
			
		}
		
		
				
		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;
		
		
		if (count($changes)) {
			$heading = "Edited Poll Option";
			if ($IDorig != $ID) {
				$heading = "Added Poll Option";
			}
			
			parent::getInstance()->_log(7, array('optionID' => $ID), $heading, $changes);
		}
		
		
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}
	
	static function remove($ID){
		$timer = new timer();
		$f3 = \base::instance();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_poll_answers");
		$art->load("ID='$ID'");
		
		parent::getInstance()->_log(7, array('optionID' => $ID), 'Removed Poll Option', $art);
		$art->erase();
		$art->save();
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";
	}
	



}
