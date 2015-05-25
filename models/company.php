<?php
namespace models;
use \timer as timer;

class company extends _ {
	private static $instance;
	//private $method;
	function __construct() {
		parent::__construct();
	}
	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	
	function get($ID,$userID=""){
		$timer = new timer();
		$where = "ID = '$ID'";
		if ($userID===true){
			$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";
		}
		
		
		$sql = "
			SELECT *
			FROM mp_companies
			WHERE $where;
			";
		
		if ($userID){
			
			$sql = "
			SELECT DISTINCT mp_companies.*
			FROM mp_companies INNER JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID
			WHERE ID='{$ID}' AND userID = '{$userID}'
			";
		}
		
		
		
		
		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
		} else {
			$return = parent::dbStructure("mp_companies", array("administrator" => "0"));
		}
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		$this->method = __FUNCTION__;
		return $this;
	}
	function getAll($where = "", $orderby = "", $limit = "", $options = array()) {
		$timer = new timer();
		$options = array(
			"ttl" => isset($options['ttl']) ? $options['ttl'] : "",
			"args" => isset($options['args']) ? $options['args'] : array()
		);
		$return = array();

		

		if ($where) {
			$where = "WHERE " . $where . "";
		} else {
			$where = " ";
		}

		if ($orderby) {
			$orderby = " ORDER BY " . $orderby;
		}
		if ($limit) {
			$limit = " LIMIT " . $limit;
		}
		$result = $this->f3->get("DB")->exec("
			SELECT *
			FROM mp_companies
			$where
			$orderby
			$limit
		", $options['args'],$options['ttl']);


		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		$this->method = __FUNCTION__;
		return $this;
		
	}
	function getUser($where = "", $orderby = "", $limit = "", $options = array()) {
		$timer = new timer();
		$options = array(
			"ttl" => isset($options['ttl']) ? $options['ttl'] : "",
			"args" => isset($options['args']) ? $options['args'] : array()
		);
		$return = array();



		if ($where) {
			$where = "WHERE " . $where . "";
		} else {
			$where = " ";
		}

		if ($orderby) {
			$orderby = " ORDER BY " . $orderby;
		}
		if ($limit) {
			$limit = " LIMIT " . $limit;
		}
		$result = $this->f3->get("DB")->exec("
			SELECT DISTINCT mp_companies.*
			FROM mp_companies INNER JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID
			$where
			$orderby
			$limit
		", $options['args'],$options['ttl']);


		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		$this->method = __FUNCTION__;
		return $this;

	}
	function getGroups($cID=false) {
		$timer = new timer();

		if (!$cID){
			$cID = $this->return['ID'];
		}
		$result = $this->f3->get("DB")->exec("
			SELECT mp_groups.*
			FROM mp_groups
			WHERE companyID = '{$cID}'
			ORDER BY mp_groups.orderby ASC
		");

		$this->return['groups'] = $result;

		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;

	}
	function getCategories($cID=false) {
		$timer = new timer();
		if (!$cID){
			$cID = $this->return['ID'];
		}
		$result = $this->f3->get("DB")->exec("
			SELECT mp_categories.*
			FROM mp_categories
			WHERE companyID = '{$cID}'
			ORDER BY mp_categories.orderby ASC
		");

		$this->return['categories'] = $result;

		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;

	}


	function save($values){
		$timer = new timer();
		$ID = $this->return['ID'];

		$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_companies");
		$art->load("ID='$ID'");

		
		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key)) {
				$art->$key = $value;
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;


		$this->return = array_merge($this->return , $this->get($ID)->show());
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;
	}
	
	function saveGroups($values){
		$timer = new timer();
		$ID = $this->return['ID'];


		$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_groups");
		foreach ($values as $item){
			$art->load("ID='{$item['ID']}'");
			$item['companyID'] = $ID;
			foreach ($item as $key => $value) {
				if (isset($art->$key)) {
					$art->$key = $value;
				}

			}

			$art->save();
			$art->reset();
			
		}
		
		

		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;
	}
	function saveCategories($values){
		$timer = new timer();
		$ID = $this->return['ID'];


		$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_categories");
		foreach ($values as $item){
			$art->load("ID='{$item['ID']}'");
			$item['companyID'] = $ID;
			foreach ($item as $key => $value) {
				if (isset($art->$key)) {
					$art->$key = $value;
				}

			}

			$art->save();
			$art->reset();
			
		}
		
		

		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;
	}
	function remove(){
		$timer = new timer();
		$ID = $this->return['ID'];

		$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_companies");
		$art->load("ID='$ID'");
		$art->erase();

	
		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;


		$this->return = array_merge($this->return , $this->get($ID)->show());

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;
	}

	function removeGroups($values){
		$timer = new timer();
		$ID = $this->return['ID'];


		$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_groups");
		foreach ($values as $item){
			$art->load("ID='{$item}'");
			$art->erase();

			$art->save();
			$art->reset();

		}





		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;
	}
	function removeCategories($values){
		$timer = new timer();
		$ID = $this->return['ID'];


		$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_categories");
		foreach ($values as $item){
			$art->load("ID='{$item}'");
			$art->erase();

			

			$art->save();
			$art->reset();

		}





		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;
	}
	
	
	function show($value=''){
		$return = $this->return;
		if ($value){
			$return = $return[$value];
		}
		return $return;
	}
	function format(){
		$timer = new timer();
		$items = $this->return;
		$single = false;
		if (isset($items['ID'])) {
			$single = true;
			$items = array($this->return);
		}


		$i = 1;
		$n = array();
		//test_array($items); 

		foreach ($items as $item){
			$item['url'] = toAscii($item['company']);
			$n[] = $item;
		}

		if ($single) $n = $n[0];



		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $n;
		return $this;
	}
	
}
