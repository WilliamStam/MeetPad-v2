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
			$userID = $this->user['ID'];
		}
		
		if ($userID == $this->user['ID']){
			$userDetails = $this->user;
		} else {
			$userDetails = users::getInstance()->get($this->user['ID']);
		}
		$sql = "
			SELECT mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active, if (mp_meetings.timeEnd<= now(),1,0) AS locked
			FROM mp_meetings INNER JOIN mp_companies ON mp_companies.ID = mp_meetings.companyID
			WHERE $where;
		";

		if ($userID && $userDetails['global_admin']!='1'){

			
			
			
			$sql = "
SELECT  mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active,
				if (mp_users.global_admin='1','1',mp_users_company.admin) as admin,
				if((SELECT datein FROM mp_user_attendance WHERE meetingID =mp_meetings.ID AND userID ='{$userID}')is not null,1,0) AS attending, if (mp_meetings.timeEnd<= now(),1,0) AS locked
				FROM (((mp_meetings INNER JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) INNER JOIN mp_users_group ON mp_meetings_group.groupID = mp_users_group.groupID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID) INNER JOIN (mp_users_company LEFT JOIN mp_users ON mp_users_company.userID = mp_users.ID AND mp_users_company.userID = '{$userID}') ON mp_meetings.companyID = mp_users_company.companyID
				WHERE  mp_meetings.ID = '$ID'  AND mp_users.ID = '$userID' AND (mp_users_group.userID ='$userID' OR mp_users_company.admin='1')
				GROUP BY mp_meetings.ID
		";
		}
		
		//test_string($sql);
	
		$result = $this->f3->get("DB")->exec($sql);

	//	test_array($result); 
		//test_string($sql);
		if (count($result)) {
			$return = $result[0];
			
			if ($userDetails['global_admin']=='1'){
				$return['attending'] = '1';
			}
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
			"groups" => isset($options['groups'])&&$options['groups'] ? $options['groups'] : false,
			"userID" => isset($options['userID'])&&$options['userID'] ? $options['userID'] : false,
		);
		$return = array();

		

		if ($where) {
			$where = " WHERE " . $where . "";
		} else {
			$where = " ";
		}
		
	//	test_array($where); 
		
		if ($orderby) {
			$orderby = " ORDER BY " . $orderby;
		}
		if ($limit) {
			$limit = " LIMIT " . $limit;
		}
		$groupSQL = "";
		if ($options['groups']){
			$groupSQL = ", (SELECT GROUP_CONCAT(DISTINCT gg.group SEPARATOR ', ') FROM mp_groups gg INNER JOIN mp_meetings_group mm ON gg.ID = mm.groupID WHERE mm.meetingID = mp_meetings.ID) AS groups";
		}


		if ($options['userID'] == $this->user['ID']){
			$userDetails = $this->user;
			} else {
			$userDetails = users::getInstance()->get($this->user['ID']);
		}
		
		

		$sql = "
			SELECT  mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active
			$groupSQL
			FROM (mp_meetings INNER JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID
		
			$where
			GROUP BY mp_meetings.ID
			$orderby
			$limit
		";
		
		
		if ($options['userID'] && $userDetails['global_admin']!='1'){
			$where = $where . " AND mp_users.ID = '{$options['userID']}' AND (mp_users_group.userID ='{$options['userID']}' OR mp_users_company.admin='1')";
			$sql = "
				SELECT  mp_meetings.*, mp_companies.company, if (mp_meetings.timeStart>=now() and mp_meetings.timeEnd<= now(),1,0) AS active,
				if (mp_users.global_admin='1','1',mp_users_company.admin) as admin
				$groupSQL
				FROM (((mp_meetings INNER JOIN mp_meetings_group ON mp_meetings.ID = mp_meetings_group.meetingID) INNER JOIN mp_users_group ON mp_meetings_group.groupID = mp_users_group.groupID) INNER JOIN mp_companies ON mp_meetings.companyID = mp_companies.ID) INNER JOIN (mp_users_company LEFT JOIN mp_users ON mp_users_company.userID = mp_users.ID AND mp_users_company.userID = '{$options['userID']}') ON mp_meetings.companyID = mp_users_company.companyID
				$where
				GROUP BY mp_meetings.ID
				$orderby
				$limit
			";
		}
		
		
		$result = $this->f3->get("DB")->exec($sql, $options['args'],$options['ttl']);
		$return = $result;
		
		//test_array($return); 
		
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
		function getUsers($meetingID) {
		$timer = new timer();

			$sql = "
				SELECT mp_users.*,  COALESCE(NULLIF(mp_users_company.tag,''), mp_users.tag) as tag,  mp_users_company.admin, if(mp_user_attendance.datein is not null,1,0) AS attending
				FROM mp_user_attendance RIGHT JOIN ((((mp_users INNER JOIN mp_users_group ON mp_users.ID = mp_users_group.userID) INNER JOIN mp_meetings_group ON mp_users_group.groupID = mp_meetings_group.groupID) INNER JOIN mp_meetings ON mp_meetings_group.meetingID = mp_meetings.ID) INNER JOIN mp_users_company ON (mp_users_company.companyID = mp_meetings.companyID) AND (mp_users.ID = mp_users_company.userID)) ON mp_user_attendance.userID = mp_users.ID AND mp_user_attendance.meetingID = '{$meetingID}'
		
				WHERE mp_meetings.ID = '{$meetingID}'
				GROUP BY mp_users.ID
				ORDER BY mp_users.name ASC
			";

//test_string($sql);

			$result = $this->f3->get("DB")->exec($sql);


			$result = users::format($result);
			
			
		
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;

	}
	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
	//	test_array($values); 
		$IDorig = $ID;$changes = array();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_meetings");
		$art->load("ID='$ID'");


		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			$value = $f3->scrub($value,$f3->get("TAGS"));
			if (isset($art->$key)) {
				if ($art->$key != $value) {
					$changes[] = array(
							"f" => $key,
							"w" => $art->$key,
							"n" => $value
					);
				}
				
				
				$art->$key =  $value;;
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
		
		if (count($changes)) {
			$heading = "Edited Meeting - ";
			if ($IDorig != $ID) {
				$heading = "Added Meeting -";
			}
			
			parent::getInstance()->_log(3, array('meetingID' => $ID), $heading . '' . $art->meeting, $changes);
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
		$f3 = \Base::instance();
		$user = $f3->get("user");
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
			$item['companyurl'] = toAscii($item['company']);
			
			$item['admin'] = $user['global_admin']=='1'?'1':$item['admin'];
			
			$n[] = $item;
		}

	//	test_array($n); 
		if ($single) $n = $n[0];
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $n;
	}
	
}
