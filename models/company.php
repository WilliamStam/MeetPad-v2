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
		$where = "mp_companies.ID = '$ID'";
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
			WHERE mp_companies.ID='{$ID}' AND mp_users_company.userID = '{$userID}'
			";
		}
		
		
		
		
		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
		} else {
			$return = parent::dbStructure("mp_companies", array("administrator" => "0"));
		}

		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return  self::format($return);
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
			SELECT DISTINCT mp_companies.*
			FROM mp_companies LEFT JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID
			$where
			$orderby
			$limit
		", $options['args'],$options['ttl']);


		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($result);
		
	}
	
	function getGroups($cID) {
		$timer = new timer();

		
		$result = $this->f3->get("DB")->exec("
			SELECT mp_groups.*
			FROM mp_groups
			WHERE companyID = '{$cID}'
			ORDER BY mp_groups.orderby ASC
		");


		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;

	}
	function getCategories($cID) {
		$timer = new timer();
		$result = $this->f3->get("DB")->exec("
			SELECT mp_categories.*
			FROM mp_categories
			WHERE companyID = '{$cID}'
			ORDER BY mp_categories.orderby ASC
		");


		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;

	}


	public static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_companies");
		$art->load("ID='$ID'");

		
		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key) && $key != "ID") {
				$art->$key = $value;
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;


		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}
	
	public static function saveGroups($companyID,$values){
		$timer = new timer();
		$f3 = \base::instance();


		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_groups");
		foreach ($values as $item){
			$art->load("ID='{$item['ID']}'");
			$item['companyID'] = $companyID;
			foreach ($item as $key => $value) {
				if (isset($art->$key) && $key != "ID") {
					$art->$key = $value;
				}

			}

			if (isset($item['group'])&& $item['group']==""){
				$art->erase();
			} else {
				$art->save();
			}
			
			$art->reset();
			
		}
		
		

		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";
	}
	public static function saveCategories($companyID,$values){
		$timer = new timer();
		$f3 = \base::instance();

		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_categories");
		foreach ($values as $item){
			$art->load("ID='{$item['ID']}'");
			$item['companyID'] = $companyID;
			foreach ($item as $key => $value) {
				if (isset($art->$key) && $key != "ID") {
					$art->$key = $value;
				}

			}
			if (isset($item['category'])&& $item['category']==""){
				$art->erase();
			} else {
				$art->save();
			}
			$art->reset();
			
		}
		
		

		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";;
	}
	function remove($ID){
		$timer = new timer();
		$f3 = \base::instance();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_companies");
		$art->load("ID='$ID'");
		$art->erase();

	
		$art->save();


	

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";
	}

	
	public static function addUser($userID,$companyID,$admin=false) {
		$timer = new timer();
		$ID = $companyID;
		$f3 = \base::instance();



		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_users_company");
		$art->load(array('companyID=? AND userID=?', $ID, $userID));

		//	test_array($art);
		$art->userID = $userID;
		$art->companyID = $ID;
		$art->admin = $admin ? 1 : 0;
		$art->save();

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";
	}
	
	
	static function format($data){
		$timer = new timer();
		$f3 = \base::instance();
		
		
		
		$single = false;
		if (isset($data['ID'])) {
			$single = true;
			$data = array($data);
		}


		$i = 1;
		$n = array();
		//test_array($items); 

		foreach ($data as $item){
			$item['url'] = toAscii($item['company']);
			$n[] = $item;
		}

		if ($single) $n = $n[0];



		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		
		return $n;
	}
	
}
