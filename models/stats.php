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
	function get($where){
		$timer = new timer();
		$data = $this->data($where);
		
		
		//test_array($data); 
		
		$return = array(
			"activity"=>""
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
	
	
}
