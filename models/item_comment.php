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


	function get($ID, $userID = "") {
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
			SELECT DISTINCT  mp_content_comments.*, mp_content.meetingID, mp_meetings.companyID, mp_users.name
			FROM ((mp_content_comments INNER JOIN mp_users ON mp_content_comments.userID = mp_users.ID) LEFT JOIN mp_content ON mp_content_comments.contentID = mp_content.ID) INNER JOIN mp_meetings ON mp_content.meetingID = mp_meetings.ID
			$where
			$orderby
			$limit
		", $options['args'], $options['ttl']
		);


		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);

	}




	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		//	test_array($values); 

		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_comments");
		$art->load("ID='$ID'");


		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key)) {
				$art->$key =  $f3->scrub($value,$f3->get("TAGS"));;
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;

		



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
			$n[] = $item;
		}

		if ($single) $n = $n[0];


		//test_array($n); 


		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $n;
	}

}
