<?php
namespace models;

use timer as timer;

class item extends _ {
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
		$where = "ID = '$ID'";
		
		$sql = "
			SELECT *
			FROM mp_content
			WHERE $where;
		";
		if ($userID === true) {
			$userID = $this->user['ID'];
		}
		if ($userID == $this->user['ID']) {
			$userDetails = $this->user;
		} else {
			$userDetails = users::getInstance()->get($this->user['ID']);
		}
		
		if ($userID && $userDetails['global_admin'] != '1') {
			$sql = "
				SELECT mp_content.*, mp_categories.category, (SELECT count(ID) FROM mp_content_comments WHERE contentID = mp_content.ID AND mp_content_comments.deleted is null) AS commentCount
				
				FROM (((mp_content INNER JOIN mp_categories ON mp_content.categoryID = mp_categories.ID) INNER JOIN mp_content_group ON mp_content.ID = mp_content_group.contentID) INNER JOIN mp_users_group ON mp_content_group.groupID = mp_users_group.groupID) INNER JOIN mp_users_company ON mp_categories.companyID = mp_users_company.companyID AND mp_users_company.userID = '$userID'
				WHERE mp_content.ID = '$ID' AND (mp_users_company.admin='1' OR mp_users_group.userID='$userID')
				GROUP BY mp_content.ID
			";
			
			
		}
		
		//test_string($sql); 
		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
			
			$return['poll'] = item_poll::getInstance()->get($return);
			$return['poll']["voted"] = ($this->f3->get("DB")->exec("SELECT answerID FROM mp_content_poll_voted WHERE contentID='{$return['ID']}' AND userID='{$this->user['ID']}'"));
			
			$return['poll']['voted'] = $return['poll']['voted'][0]['answerID'];
			
			
		} else {
			$return = parent::dbStructure("mp_content");
		}
		//	test_array($return);
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);
	}
	
	function getAll($where = "", $orderby = "", $limit = "", $options = array()) {
		$timer = new timer();
		$options = array(
				"ttl" => isset($options['ttl']) ? $options['ttl'] : "",
				"args" => isset($options['args']) ? $options['args'] : array(),
				"userID" => isset($options['userID']) && $options['userID'] ? $options['userID'] : false,
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
		
		
		if ($options['userID'] == $this->user['ID']) {
			$userDetails = $this->user;
		} else {
			$userDetails = users::getInstance()->get($this->user['ID']);
		}
		
		$sql = "
			SELECT mp_content.*, mp_categories.category, 
				(SELECT count(ID) FROM mp_content_comments WHERE contentID = mp_content.ID AND mp_content_comments.deleted is null) AS commentCount,
				(SELECT count(ID) FROM mp_content_files WHERE mp_content_files.contentID =mp_content.ID and mp_content_files.deleted !='1' ) as filesCount
			FROM mp_content INNER JOIN mp_categories ON mp_content.categoryID = mp_categories.ID
			$where
			$orderby
			$limit
		";
		
		
		if ($options['userID'] && $userDetails['global_admin'] != '1') {
			$where = $where . " AND (mp_users_company.admin='1' OR mp_users_group.userID='{$options['userID']}')";
			$sql = "
				SELECT mp_content.*, mp_categories.category, (SELECT count(ID) FROM mp_content_comments WHERE contentID = mp_content.ID AND mp_content_comments.deleted is null) AS commentCount,
				(SELECT count(ID) FROM mp_content_files WHERE mp_content_files.contentID =mp_content.ID and mp_content_files.deleted !='1' ) as filesCount
			
			FROM (((mp_content INNER JOIN mp_categories ON mp_content.categoryID = mp_categories.ID) INNER JOIN mp_content_group ON mp_content.ID = mp_content_group.contentID) INNER JOIN mp_users_group ON mp_content_group.groupID = mp_users_group.groupID) INNER JOIN mp_users_company ON mp_categories.companyID = mp_users_company.companyID AND mp_users_company.userID = '{$options['userID']}'
			
			
			
			
			
				$where
				GROUP BY mp_content.ID
				$orderby
				$limit
			";
		}
		
		//	test_string($sql);
		
		$result = $this->f3->get("DB")->exec($sql, $options['args'], $options['ttl']);
		
		
		//	test_array($result); 
		
		
		$return = $result;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return self::format($return);
		
	}
	
	function getGroups($ID, $meetingID = false) {
		$timer = new timer();
		
		$result = $this->f3->get("DB")->exec("
				SELECT DISTINCT mp_groups.*
				FROM mp_content_group INNER JOIN mp_groups ON mp_content_group.groupID = mp_groups.ID
				WHERE mp_content_group.contentID = '{$ID}'
				ORDER BY mp_groups.orderby ASC
			");
		if ($meetingID) {
			$groups = meeting::getInstance()->getGroups($meetingID);
			
			
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
	
	
	static function save($ID, $values) {
		$timer = new timer();
		$IDorig = $ID;
		$changes = array();
		$f3 = \base::instance();
		//	test_array($values); 
		
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content");
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
				$art->$key = $value;
				
			}
			
			
		}
		
		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;
		
		if (isset($values['groups'])) {
			$f3->get("DB")->exec("DELETE FROM mp_content_group WHERE contentID = '{$ID}'");
			$n = array();
			foreach ($values['groups'] as $item) {
				if ($item) $n[] = "('$ID','$item')";
			}
			if (count($n)) {
				$str = implode(",", $n);
				$f3->get("DB")->exec("INSERT INTO mp_content_group (contentID,groupID) VALUES $str");
			}
		}
		
		
		if (count($changes)) {
			$heading = "Edited Item - ";
			if ($IDorig != $ID) {
				$heading = "Added Item - ";
			}
			
			parent::getInstance()->_log(2, array('contentID' => $ID), $heading . '' . $art->heading, $changes);
		}
		if (isset($values['poll_options'])) {
			foreach ($values['poll_options'] as $item) {
				$values_ = array(
						"contentID" => $ID,
						"answer" => $item['answer'],
						"orderby" => $item['orderby'],
				);
				
				if ($values_['answer']==''){
					item_poll_options::remove($item['ID']);
				} else {
					item_poll_options::save($item['ID'], $values_);
				}
			}
			
		}
		
		if (isset($values['files'])) {
			foreach ($values['files'] as $item) {
				$values_ = array(
						"contentID" => $ID,
						"filename" => $item['filename'],
						"filesize" => $item['filesize'],
						"store_filename" => $item['store_filename'],
						"description" => $item['description'],
				);
				
				if ($values_['filename']==''){
					item_file::remove($item['ID']);
				} else {
					item_file::save($item['ID'], $values_);
				}
			}
			
		}
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $ID;
	}
	
	static function remove($ID) {
		$timer = new timer();
		$f3 = \base::instance();
		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content");
		$art->load("ID='$ID'");
		parent::getInstance()->_log(2, array('contentID' => $ID), 'Removed Item - ' . $art->heading, $art);
		$art->deleted = '1';
		
		
		$art->save();
		
		
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return "done";
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
			$datein = strtotime($item['datein']);
			$item['date'] = array(
					"raw" => $item['datein'],
					"long" => array(
							"date" => date("d F Y", ($datein)),
							"time" => date("H:i:sa", ($datein))
					),
					"short" => array(
							"date" => date("d M y", ($datein)),
							"time" => date("H:i", ($datein))
					),
					"disp" => date("d M (D) G:i A", ($datein))
			
			);
			$item['url'] = toAscii($item['heading']);
			
			$n[] = $item;
		}
		
		if ($single) $n = $n[0];
		
		
		//test_array($n); 
		
		
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $n;
	}
	
}
