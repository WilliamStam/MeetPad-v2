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
		}
		$sql = "
			SELECT mp_meetings.*, mp_companies.company
			FROM mp_meetings INNER JOIN mp_companies ON mp_companies.ID = mp_meetings.companyID
			WHERE $where;
		";

		if ($userID){
			$sql = "
			SELECT DISTINCT mp_meetings.*, mp_companies.company
			FROM (((mp_meetings INNER JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) LEFT JOIN mp_users_group ON mp_meetings_group.groupID = mp_users_group.groupID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID) LEFT JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID
			WHERE mp_meetings.ID = '$ID' AND mp_users_group.userID = '$userID'
		";
		}
		
		
		$result = $this->f3->get("DB")->exec($sql);

		//test_string($sql);
		if (count($result)) {
			$return = $result[0];
		} else {
			$return = parent::dbStructure("mp_meetings",array("company"));
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
			FROM mp_meetings
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
			SELECT DISTINCT mp_meetings.*, mp_companies.company
			FROM (((mp_meetings INNER JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) LEFT JOIN mp_users_group ON mp_meetings_group.groupID = mp_users_group.groupID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID) LEFT JOIN mp_users_company ON mp_companies.ID = mp_users_company.companyID

			$where
			$orderby
			$limit
		", $options['args'],$options['ttl']);


	//	test_array($where); 
		
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		$this->method = __FUNCTION__;
		return $this;

	}
	function getGroups() {
		$timer = new timer();
		
		$result = $this->f3->get("DB")->exec("
			SELECT mp_groups.*
			FROM mp_meetings_group INNER JOIN mp_groups ON mp_meetings_group.groupID = mp_groups.ID
			WHERE mp_meetings_group.meetingID = '{$this->return['ID']}'
			ORDER BY mp_groups.orderby ASC
		");

		$this->return['groups'] = $result;
		
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $this;

	}

	
	function show(){
		return $this->return;
	}
	function format(){
		$timer = new timer();
		$items = $this->return;
		$single = false;
	//	test_array($items); 
		if (isset($items['ID'])) {
			$single = true;
			$items = array($this->return);
		}
		//test_array($items);
		
				$i = 1;
		$n = array();
		//test_array($items); 
		
		foreach ($items as $item){
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
				"raw"=>$item['timeStart'],
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
			
			$item['url'] = toAscii($item['meeting']);
			
			$n[] = $item;
		}

		if ($single) $n = $n[0];


		//test_array($n); 
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $n;
		return $this;
	}
	
}
