<?php
namespace models;
use \timer as timer;

class meeting extends _ {
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
		$where = "mp_meetings.ID = '$ID'";
		if ($userID===true){
			$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";
			$userSQL = ($this->user['global_admin']=='1')?"":"(mp_users_group.userID='{$this->user['ID']}' OR mp_users_company.admin = '1') AND ";
		}
		$sql = "
			SELECT mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active
			FROM mp_meetings INNER JOIN mp_companies ON mp_companies.ID = mp_meetings.companyID
			WHERE $where;
		";

		if ($userID){
			$sql = "
			SELECT DISTINCT mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active,
			if(mp_users_group.userID,1,0) AS access
			FROM (((mp_meetings INNER JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) LEFT JOIN mp_users_group ON mp_meetings_group.groupID = mp_users_group.groupID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID) LEFT JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID
			WHERE $userSQL mp_meetings.ID = '$ID' 
		";
		}
		
		
		$result = $this->f3->get("DB")->exec($sql);

		//test_string($sql);
		if (count($result)) {
			$return = $result[0];
		} else {
			$return = parent::dbStructure("mp_meetings",array("company","active"=>"0"));
		}
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);
	}
	function getAll($where = "", $orderby = "", $limit = "", $options = array()) {
		$timer = new timer();
		$options = array(
			"ttl" => isset($options['ttl']) ? $options['ttl'] : "",
			"args" => isset($options['args']) ? $options['args'] : array(),
			"groups" => isset($options['groups']) ? $options['groups'] : false
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
		$groupSQL = "";
		if ($options['groups']){
			$groupSQL = "(SELECT GROUP_CONCAT(DISTINCT gg.group SEPARATOR ', ') FROM mp_groups gg INNER JOIN mp_meetings_group mm ON gg.ID = mm.groupID WHERE mm.meetingID = mp_meetings.ID) AS groups,";
		}
		
		
		$result = $this->f3->get("DB")->exec("
		
			SELECT DISTINCT mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active,
			$groupSQL
			if(mp_users_group.userID,1,0) AS access
			
			
			FROM ((((mp_meetings LEFT JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) LEFT JOIN mp_users_group ON mp_meetings_group.groupID = mp_users_group.groupID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID) LEFT JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID)
			$where
			$orderby
			$limit
		", $options['args'],$options['ttl']);
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);
		
	}
		function getGroups($meetingID,$companyID=false) {
		$timer = new timer();

			$result = $this->f3->get("DB")->exec("
				SELECT DISTINCT mp_groups.*
				FROM mp_meetings_group INNER JOIN mp_groups ON mp_meetings_group.groupID = mp_groups.ID
				WHERE mp_meetings_group.meetingID = '{$meetingID}'
				ORDER BY mp_groups.orderby ASC
			");
			
		if ($companyID){
			if ($companyID===true){
				$companyID = $this->get($meetingID);
				$companyID = $companyID['companyID'];
			}
			$groups = company::getInstance()->getGroups($companyID);

			
			$g = array();
			$gr = array();

			foreach ($result as $item)$g[] = $item['ID'];
			foreach ($groups as $item){
				$item['active']='0';
				if (in_array($item['ID'],$g)){
					$item['active']='1';
				}
				$gr[] = $item;
			}
			$result = $gr;
			
		} 


		

		
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;

	}
	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
	//	test_array($values); 

		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_meetings");
		$art->load("ID='$ID'");


		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key)) {
				$art->$key = $value;
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;
		
		if (isset($values['groups'])&&$values['companyID']){
			$f3->get("DB")->exec("DELETE FROM mp_meetings_group WHERE meetingID = '{$ID}'");
			
			
		
			//test_array($values['groups']); 
			$n = array();
			foreach ($values['groups'] as $item){
				if ($item) $n[] = "('$ID','$item')";
			}
			

			if (count($n)){
				$str = implode(",",$n);
				$f3->get("DB")->exec("INSERT INTO mp_meetings_group (meetingID,groupID) VALUES $str");
			}
			
			
			
				
		//	test_array(array($n,$r,$str)); 
			
		}



		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}

	function show($value=''){
		$return = $this->return;
		if ($value){
			$return = $return[$value];
		}
		return $return;
	}
	static function format($data){
		$timer = new timer();
		$single = false;
	//	test_array($items); 
		if (isset($data['ID'])) {
			$single = true;
			$data = array($data);
		}
		//test_array($items);
		
				$i = 1;
		$n = array();
		//test_array($items); 
		
		foreach ($data as $item){
			$timeStart = strtotime($item['timeStart']);
			$item['timeStart'] = array(
				"raw"=>$item['timeStart'],
				"long"=>array(
					"date"=>date("d F Y",($timeStart)),
					"time"=>date("H:i:sa",($timeStart)),
					"disp"=>date("d F Y (D) G:i A",($timeStart))
				),
				"short"=>array(
					"date"=>date("d M y",($timeStart)),
					"time"=>date("H:i",($timeStart)),
					"disp"=>date("d M (D) G:i A",($timeStart))
				),
				
				
				
			);

			$timeEnd = strtotime($item['timeEnd']);
			$item['timeEnd'] = array(
				"raw"=>$item['timeEnd'],
				"long"=>array(
					"date"=>date("d F Y",($timeEnd)),
					"time"=>date("H:i:sa",($timeEnd)),
					"disp"=>date("d F Y (D) G:i A",($timeEnd))
				),
				"short"=>array(
					"date"=>date("d M y",($timeEnd)),
					"time"=>date("H:i",($timeEnd)),
					"disp"=>date("d M (D) G:i A",($timeEnd))
				),
				

			);
			$percent = 0;
			if ($timeEnd < strtotime("now") ){
				$percent = 100;
			} else {
				$percent = round(((strtotime("now") - $timeStart) / ($timeEnd - $timeStart))*100,1);
			}
			$item['percent'] = $percent;
			$item['active'] =  (strtotime("now") >= $timeStart AND strtotime("now") <= $timeEnd ) ? 1: 0;
			$item['future'] =  (strtotime("now") <= $timeStart ) ? 1: 0;
			
			$item['url'] = toAscii($item['meeting']);
			
			$n[] = $item;
		}

		if ($single) $n = $n[0];
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $n;
	}
	
}
