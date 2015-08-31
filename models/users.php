<?php
namespace models;

use timer as timer;

class users extends _ {
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


	function get($ID, $companyID = "") {
		$timer = new timer();
		$where = "mp_users.ID = '$ID'";


		$sql = "
			SELECT mp_users.*
			FROM (mp_users LEFT JOIN mp_users_company ON mp_users.ID = mp_users_company.userID) LEFT JOIN mp_users_group ON mp_users.ID = mp_users_group.userID
			WHERE $where;
			";
		$userD = $this->f3->get("DB")->exec($sql);
		$userDs = '0';
		if (count($userD)) {
			$userDs = $userD[0];
		}
		
		
		
		
		if ($companyID && $userDs['global_admin']!='1') {
			$where = $where . " AND mp_users_company.companyID='{$companyID}'";
			$sql = "
			SELECT mp_users.*, COALESCE(NULLIF(mp_users_company.tag,''), mp_users.tag) as tag, mp_users_company.tag as cotag, if(mp_users.global_admin='1','1',mp_users_company.admin) as admin
			FROM (mp_users INNER JOIN mp_users_company ON mp_users.ID = mp_users_company.userID) LEFT JOIN mp_users_group ON mp_users.ID = mp_users_group.userID
			WHERE $where;
			";
			$result = $this->f3->get("DB")->exec($sql);
		} else {
			$result = $userD;
			
		}

		//$result = $this->f3->get("DB")->exec($sql);
		
		
		if (count($result)) {
			$return = $result[0];
			if ( $userDs['global_admin']=='1'){
				$return['admin']='1';
			}
			
		} else {
			$return = parent::dbStructure("mp_users", array());
		}

			//test_array($return); 

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);
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
			$select = ",  COALESCE(NULLIF(mp_users_company.tag,''), mp_users.tag) as tag,  mp_users_company.admin";
			$where = $where . " AND mp_users_company.companyID = '{$options['companyID']}'";
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
	//	test_string($sql); 

		$result = $this->f3->get("DB")->exec($sql, $options['args'], $options['ttl']);


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

	function setGroups($ID, $companyID, $groups) {
		$timer = new timer();

		$companyGroups = $this->f3->get("DB")->exec("SELECT * FROM mp_groups WHERE companyID = '$companyID'");
		$gid = array();
		foreach ($companyGroups as $item) {
			$gid[] = $item['ID'];
		}

		$groupstr = implode(",", $gid);
		if ($groupstr) {
			$this->f3->get("DB")->exec("DELETE FROM mp_users_group WHERE userID ='$ID' AND groupID in ($groupstr)");
		}




		$n = array();
		foreach ($groups as $item) {
			if ($item) $n[] = "('$ID','$item')";
		}
		//test_array(array($n,$groupstr)); 
		if (count($n)) {
			$str = implode(",", $n);
			$this->f3->get("DB")->exec("INSERT INTO mp_users_group (userID,groupID) VALUES $str");
		}



		$return = array();;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;

	}



	public static function save($ID, $values) {
		$timer = new timer();
		$f3 = \base::instance();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_users");
		$art->load("ID='$ID'");

		if (isset($values['password'])) {
			$values['password'] = md5(md5("meet") . $values['password'] . md5("pad"));
		}

		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key) && $key != "ID") {
				$art->$key = $f3->scrub($value, $f3->get("TAGS"));
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;





		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}


	function remove($ID, $companyID) {
		$timer = new timer();
		$f3 = \base::instance();
		$f3->get("DB")->exec("DELETE FROM mp_users_company WHERE userID = '{$ID}' AND companyID='{$companyID}'");




		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";
	}




	static function format($data) {
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

		foreach ($data as $item) {
			unset($item['password']);
			unset($item['reset_password']);
			unset($item['global_admin']);
			
			$lastActivity = $item['lastActivity']?$item['lastActivity']:null;
			if (!is_array($lastActivity)){
				$item['lastActivity'] = array(
					"raw"=>$lastActivity,
					"timeago"=>timesince($lastActivity)
				);
			}; 
			
			
			$n[] = $item;
		}

		if ($single) $n = $n[0];

//test_array($n); 

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());

		return $n;
	}

}
