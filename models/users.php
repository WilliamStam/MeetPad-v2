<?php
namespace models;
use \timer as timer;

class users extends _ {
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
	
	
	function get($ID,$companyID=""){
		$timer = new timer();
		$where = "mp_users.ID = '$ID'";
		
		
		$sql = "
			SELECT mp_users.*
			FROM (mp_users LEFT JOIN mp_users_company ON mp_users.ID = mp_users_company.userID) LEFT JOIN mp_users_group ON mp_users.ID = mp_users_group.userID
			WHERE $where;
			";
		if ($companyID){
			$sql = "
			SELECT mp_users.*, mp_users_company.tag, mp_users_company.admin
			FROM (mp_users LEFT JOIN mp_users_company ON mp_users.ID = mp_users_company.userID) LEFT JOIN mp_users_group ON mp_users.ID = mp_users_group.userID
			WHERE $where;
			";
		}




		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
		} else {
			$return = parent::dbStructure("mp_users", array());
		}

		//test_array($return); 
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return  self::format($return);
	}
	function getAll($where = "", $orderby = "", $limit = "", $options = array()) {
		$timer = new timer();
		$options = array(
			"ttl" => isset($options['ttl']) ? $options['ttl'] : "",
			"args" => isset($options['args']) ? $options['args'] : array(),
			"companyID" => isset($options['companyID']) ? $options['companyID'] : false
		);
		$return = array();
		
		

		if ($where) {
			$where = "WHERE " . $where . "";
		} else {
			$where = "WHERE 1 ";
		}

		$select = "";
		if ($options['companyID']) {
			$select = ",  mp_users_company.tag";
			$where = $where. " AND mp_users_company.companyID = '{$options['companyID']}'";
		}
		

		if ($orderby) {
			$orderby = " ORDER BY " . $orderby;
		}
		if ($limit) {
			$limit = " LIMIT " . $limit;
		}
		
		$sql = "
			SELECT DISTINCT mp_users.*, GROUP_CONCAT(DISTINCT mp_users_group.groupID) as groupIDs, GROUP_CONCAT(DISTINCT mp_users_company.companyID) as companyIDs $select
			FROM (mp_users LEFT JOIN mp_users_company ON mp_users.ID = mp_users_company.userID) LEFT JOIN mp_users_group ON mp_users.ID = mp_users_group.userID
			$where
			GROUP BY mp_users.ID
			$orderby
			$limit
		";
		//test_string($where); 
		
		$result = $this->f3->get("DB")->exec($sql, $options['args'],$options['ttl']);


		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($result);
		
	}

	function getGroups($ID, $companyID = false) {
		$timer = new timer();

		$result = $this->f3->get("DB")->exec("
				SELECT DISTINCT mp_groups.*
				FROM mp_users_group INNER JOIN mp_groups ON mp_users_group.groupID = mp_groups.ID
				WHERE mp_users_group.userID = '{$ID}'
				ORDER BY mp_groups.orderby ASC
			"
		);
		if ($companyID) {
			$groups = company::getInstance()->getGroups($companyID);


			$g = array();
			$gr = array();

			foreach ($result as $item) $g[] = $item['ID'];
			foreach ($groups as $item) {
				$item['active'] = '0';
				if (in_array($item['ID'], $g)) {
					$item['active'] = '1';
				}
				$gr[] = $item;
			}
			$result = $gr;


		}





		//test_array($result);





		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;

	}



	public static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_users");
		$art->load("ID='$ID'");

		if (isset($values['password'])){
			$values['password'] = md5(md5("meet").$values['password'].md5("pad"));
		}
		
		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key) && $key != "ID") {
				$art->$key = $f3->scrub($value,$f3->get("TAGS"));
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;

		if (isset($values['groups'])){
			$f3->get("DB")->exec("DELETE FROM mp_users_group WHERE userID = '{$ID}'");
			$n = array();
			foreach ($values['groups'] as $item){
				if ($item) $n[] = "('$ID','$item')";
			}
			if (count($n)){
				$str = implode(",",$n);
				$f3->get("DB")->exec("INSERT INTO mp_users_group (userID,groupID) VALUES $str");
			}
		}
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}
	
	
	function remove($ID,$companyID){
		$timer = new timer();
		$f3 = \base::instance();
		$f3->get("DB")->exec("DELETE FROM mp_users_company WHERE userID = '{$ID}' AND companyID='{$companyID}'");


	

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
			unset($item['password']);
			unset($item['reset_password']);
			unset($item['global_admin']);
			$n[] = $item;
		}

		if ($single) $n = $n[0];



		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		
		return $n;
	}
	
}
