<?php
namespace models;

use timer as timer;

class item_file extends _ {
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
		$where = "mp_content_files.ID = '$ID'";

		$sql = "
			SELECT mp_content_files.*, mp_content.meetingID, mp_meetings.companyID
			FROM (mp_content_files LEFT JOIN mp_content ON mp_content_files.contentID = mp_content.ID) INNER JOIN mp_meetings ON mp_content.meetingID = mp_meetings.ID
			WHERE $where;
		";
		
		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
			
			
			
		} else {
			$return = parent::dbStructure("mp_content_files");
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
			SELECT DISTINCT  mp_content_files.*, mp_content.meetingID, mp_meetings.companyID, 
				(SELECT GROUP_CONCAT(CONCAT(mp_users.name,' (',mp_user_seen_content.viewed,')') SEPARATOR ', ') AS seen FROM mp_users INNER JOIN mp_user_seen_content ON mp_users.ID = mp_user_seen_content.userID WHERE fileID =  mp_content_files.ID AND mp_user_seen_content.`level` = '2' ) AS seen,
				(SELECT GROUP_CONCAT(CONCAT(mp_users.name,' (',mp_user_seen_content.viewed,')') SEPARATOR ', ') AS seen FROM mp_users INNER JOIN mp_user_seen_content ON mp_users.ID = mp_user_seen_content.userID WHERE fileID =  mp_content_files.ID AND mp_user_seen_content.`level` = '3' ) AS downloaded
			FROM (mp_content_files LEFT JOIN mp_content ON mp_content_files.contentID = mp_content.ID) INNER JOIN mp_meetings ON mp_content.meetingID = mp_meetings.ID
			$where
			$orderby
			$limit
		", $options['args'], $options['ttl']
		);

		if (count($result)){
			//test_array($result);
		}
		

		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);

	}




	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		//	test_array($values); 

		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_files");
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

		$f3 = \base::instance();
		$cfg = $f3->get('cfg');
		$fileIcons = $cfg['file-icons'];

		foreach ($data as $item) {
			$icon = "";
			$ext = $ext = pathinfo($item['store_filename'], PATHINFO_EXTENSION);;
			$item['filesize'] = file_size($item['filesize']);
			$item['icon'] = isset($fileIcons[$ext])?$fileIcons[$ext]:"";

			$n[] = $item;
		}

		if ($single) $n = $n[0];


		//test_array($n); 


		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $n;
	}

}
