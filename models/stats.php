<?php
namespace models;

use timer as timer;

class stats extends _ {
	private static $instance;
	private $data;
	
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
	
	function get($where) {
		$timer = new timer();
		$data = $this->data($where);
		
		
		//test_array($data); 
		
		$return = array(
				"activity" => ""
		);
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
	function data($where) {
		$timer = new timer();
		$return = array();
		
		if ($where) $where = " WHERE " . $where;
		
		$return = $this->f3->get("DB")->exec("SELECT * FROM mp_logs $where ORDER BY datein ASC");
		
		
		$this->data = $return;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
	function meeting($ID) {
		$timer = new timer();
		$return = array();
		$items = item::getInstance()->getAll("meetingID='{$ID}' AND deleted!='1'");
		$attending = meeting::getInstance()->getUsers($ID);
		$u = array(
				"yes" => array(),
				"no" => array()
		);
		foreach ($attending as $item) {
			if ($item['attending'] == '1') {
				$u['yes'][] = $item;
			} else {
				$u['no'][] = $item;
			}
		}
		
		
		$return = array(
				"attending" => count($u['yes']),
				"not_attending" => count($u['no']),
				"items" => count($items),
				
				"resolutions" => 0,
				"polls" => 0,
				"files" => 0,
				
				"comments" => 0,
				"voted" => 0,
				
		
		);
		foreach ($items as $item) {
			if ($item['resolution'] != '') {
				$return['resolutions'] = $return['resolutions'] + 1;
			}
			if ($item['poll'] != '') {
				$return['polls'] = $return['polls'] + 1;
				
				$p = item_poll::getInstance()->get($item);
				$return['voted'] = $return['voted'] + $p['votes'];
				
				//test_array($p); 
				
				
			}
			
			$return['comments'] = $return['comments'] + $item['commentCount'];
			$return['files'] = $return['files'] + $item['filesCount'];
			
			
			
		}
		
		//test_array(array('return' => $return,'items' => $items));
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
}
