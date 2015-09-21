<?php
namespace models;

use timer as timer;

class item_comment extends _ {
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
		$where = "mp_content_comments.ID = '$ID'";

		$sql = "
			SELECT mp_content_comments.*, mp_content.meetingID, mp_meetings.companyID, mp_users.name
			FROM ((mp_content_comments INNER JOIN mp_users ON mp_content_comments.userID = mp_users.ID) LEFT JOIN mp_content ON mp_content_comments.contentID = mp_content.ID) INNER JOIN mp_meetings ON mp_content.meetingID = mp_meetings.ID
			WHERE $where;
		";
		
		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
			
			
			
		} else {
			$return = parent::dbStructure("mp_content_comments");
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
		//test_array($options);

		if ($where) {
			$where = " " . $where . "";
		} else {
			$where = " 1 ";
		}

		if ($orderby) {
			$orderby = " ORDER BY " . $orderby;
		}
		if ($limit) {
			$limit = " LIMIT " . $limit;
		}
		
		$sql = "
			SELECT DISTINCT  mp_content_comments.*, mp_content.meetingID, mp_meetings.companyID, mp_users.name, if (mp_meetings.timeEnd<= now(),1,0) AS locked,
			if(mp_content_comments.edited_by,(SELECT name FROM mp_users where mp_users.ID = mp_content_comments.edited_by),null) as edited_name
			FROM ((mp_content_comments LEFT JOIN mp_users ON mp_content_comments.userID = mp_users.ID) INNER JOIN mp_content ON mp_content_comments.contentID = mp_content.ID) INNER JOIN mp_meetings ON mp_content.meetingID = mp_meetings.ID
			WHERE $where
			$orderby
			$limit
		";
		if ($options['companyID']){
			$sql = "
				SELECT DISTINCT  mp_content_comments.*, mp_content.meetingID, mp_meetings.companyID, mp_users.name, COALESCE(NULLIF(mp_users_company.tag,''), mp_users.tag) as tag, if (mp_meetings.timeEnd<= now(),1,0) AS locked, 
				if(mp_content_comments.edited_by,(SELECT name FROM mp_users where mp_users.ID = mp_content_comments.edited_by),null) as edited_name
				FROM ((mp_users_company INNER JOIN (mp_content_comments LEFT JOIN mp_users ON mp_content_comments.userID = mp_users.ID) ON mp_users_company.userID = mp_users.ID) INNER JOIN mp_content ON mp_content_comments.contentID = mp_content.ID) INNER JOIN mp_meetings ON mp_content.meetingID = mp_meetings.ID
				WHERE $where AND mp_users_company.companyID = '{$options['companyID']}'
				$orderby
				$limit
			";
		}
		
		//test_string($sql); 
		
		$result = $this->f3->get("DB")->exec($sql, $options['args'], $options['ttl']);


		//test_array($result); 
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);

	}




	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		$user = $f3->get("user");
		//	test_array($values); 
		$IDorig = $ID;$changes = array();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_comments");
		$art->load("ID='$ID'");
	//	test_array(array($art->ID,$ID));
		if ($ID){
			$art->edited_by = $user['ID'];
			$art->edited_date = date("Y-m-d H:i:s");
		}

		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			$value = $f3->scrub($value,$f3->get("TAGS"));
			if (isset($art->$key) && $art->$key != $value) {
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
			$heading = "Edited Comment";
			if ($IDorig != $ID) {
				$heading = "Added Comment";
			}
			if (isset($values['contentID']) && $values['contentID']){
				$a = new \DB\SQL\Mapper($f3->get("DB"), "mp_content");
				$a->load("ID='{$values['contentID']}'");
				$heading = $heading . ' - ' . $a->heading;
			}
			parent::getInstance()->_log(6, array('commentID' => $ID), $heading, $changes);
		}



		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}

	

	
	static function format($data) {
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


		foreach ($data as $item) {
			$item['timeago'] = timesince($item['datein']);
			$item['children'] = array();
			$n[] = $item;
		}

		if ($single) $n = $n[0];

		
		$records = $n;
		if (count($records)&&!isset($n['ID'])){
			$rows = array();

			foreach ($records as $row) {
				$row['children'] = array();
				$rows[$row['ID']] = $row;
			}



			foreach ($rows as $k => &$v) {
				if ($v['parentID'] == $v['ID']) continue;
				if (isset($rows[$v['parentID']]))	{
					$rows[$v['parentID']]['children'][] = & $v;
				}
			}

			foreach ($rows as $item){
				if ($item['parentID'])unset($rows[$item['ID']]);
			}

			//	array_splice($rows, 2);
			//test_array($rows);

			$n = $rows;
			$nn = array();
			foreach ($n as $key=>$item){
				$nn[] = $item;
			}
			$n = $nn;
		}
		
		
		

		//test_array($n); 


		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $n;
	}

}
