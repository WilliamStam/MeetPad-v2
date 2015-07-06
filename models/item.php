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
		if ($userID===true){
			$userID =  $this->user['global_admin']=='1'?false:$this->user['ID'];
		}
		

		if ($userID) {
			
			$sql = "
			SELECT DISTINCT  mp_content.*, mp_categories.category, (SELECT count(ID) FROM mp_content_comments WHERE contentID = mp_content.ID AND mp_content_comments.deleted is null) AS commentCount
			FROM (((mp_content INNER JOIN mp_content_group ON mp_content.ID = mp_content_group.contentID) INNER JOIN mp_users_group ON mp_content_group.groupID = mp_users_group.groupID) INNER JOIN mp_categories ON mp_content.categoryID = mp_categories.ID) INNER JOIN mp_users_company ON mp_users_group.userID = mp_users_company.userID
			WHERE mp_content.ID = '$ID' AND mp_users_group.userID = '$userID' AND  mp_users_company.userID = '$userID'
		";
		}

		$result = $this->f3->get("DB")->exec($sql);
		if (count($result)) {
			$return = $result[0];
			
			$return['poll']= array(
				"question"=>$return['poll'],
				"options"=>$this->f3->get("DB")->exec("SELECT mp_content_poll_answers.*, count(mp_content_poll_voted.userID) AS voted FROM mp_content_poll_answers LEFT JOIN mp_content_poll_voted ON mp_content_poll_voted.answerID = mp_content_poll_answers.ID AND mp_content_poll_voted.userID = '{$this->user['ID']}'  WHERE mp_content_poll_answers.contentID = '{$return['ID']}' GROUP BY mp_content_poll_answers.ID ORDER BY orderby ASC")
			);

			
			
		} else {
			$return = parent::dbStructure("mp_content");
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
			SELECT DISTINCT  mp_content.*, mp_categories.category, (SELECT count(ID) FROM mp_content_comments WHERE contentID = mp_content.ID AND mp_content_comments.deleted is null) AS commentCount
			FROM (((mp_content INNER JOIN mp_content_group ON mp_content.ID = mp_content_group.contentID) INNER JOIN mp_users_group ON mp_content_group.groupID = mp_users_group.groupID) INNER JOIN mp_categories ON mp_content.categoryID = mp_categories.ID) INNER JOIN mp_users_company ON mp_users_group.userID = mp_users_company.userID
			$where
			$orderby
			$limit
		", $options['args'], $options['ttl']
		);


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
			"
		);
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


	static function save($ID,$values){
		$timer = new timer();
		$f3 = \base::instance();
		//	test_array($values); 

		$art = new \DB\SQL\Mapper($f3->get("DB"), "mp_content");
		$art->load("ID='$ID'");


		//test_array($this->get("14")); 
		foreach ($values as $key => $value) {
			if (isset($art->$key)) {
				$art->$key =  $f3->scrub($value,$f3->get("TAGS"));;
			}

		}

		$art->save();
		$ID = ($art->ID) ? $art->ID : $art->_id;

		if (isset($values['groups'])){
			$f3->get("DB")->exec("DELETE FROM mp_content_group WHERE contentID = '{$ID}'");
			$n = array();
			foreach ($values['groups'] as $item){
				if ($item) $n[] = "('$ID','$item')";
			}
			if (count($n)){
				$str = implode(",",$n);
				$f3->get("DB")->exec("INSERT INTO mp_content_group (contentID,groupID) VALUES $str");
			}
		}
		if (isset($values['poll_options'])){

			$pollO = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_poll_answers");
			foreach($values['poll_options'] as $item){
				$pollO->load("ID='{$item['ID']}'");
				
				if ($item['answer']==""){
					$pollO->erase();
				} else {
					$pollO->contentID = $ID;
					$pollO->answer = $item['answer'];
					$pollO->orderby = $item['orderby'];
					$pollO->save();
				}
				
				
				
				$pollO->reset();
				
			}
			
			
		}
		if (isset($values['files'])){
			$fileO = new \DB\SQL\Mapper($f3->get("DB"), "mp_content_files");
			foreach($values['files'] as $item){
				$fileO->load("ID='{$item['ID']}'");

				if ($item['filename']==""){
					$fileO->erase();
				} else {
					$fileO->contentID = $ID;
					$fileO->filename = $item['filename'];
					$fileO->filesize = $item['filesize'];
					$fileO->store_filename = $item['store_filename'];
					$fileO->description = $item['description'];
					$fileO->save();
				}



				$fileO->reset();

			}
			
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
