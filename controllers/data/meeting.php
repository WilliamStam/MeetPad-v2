<?php
namespace controllers\data;
use models as models;

class meeting extends _data {
	private static $instance;
	public $meetingID;
	public $companyID;
	function __construct() {
		parent::__construct();

	}

	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	function data() {
		$domain = $this->f3->get("domain");
		$result = array();

		$result = array(
			"meeting"=>$this->meeting(),
			"company"=>$this->company(),
			"agenda"=>$this->agenda(),
			"item"=>$this->item(),
			
		);

		return $GLOBALS["output"]['data'] = $result;
	}
	
	
	function meeting() {
		
		$meetingID = isset($_GET['meetingID'])?$_GET['meetingID']:"";

		$object = models\meeting::getInstance();
		$result =  $object->get($meetingID,true);
		$result['groups'] = $object->getGroups($meetingID);
		
		$this->meetingID = $result['ID'];
		$this->companyID = $result['companyID'];
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function agenda() {

		$userSQL = ($this->user['global_admin']=='1')?"":"AND (mp_users_group.userID = '{$this->user['ID']}')";


		$object = models\item::getInstance();
		$result =  $object->getAll("meetingID ='{$this->meetingID}' {$userSQL}","mp_categories.orderby ASC, datein ASC");

		$ids = array();
		foreach ($result as $item) {
			$ids[] = $item['ID'];
		}
		$voted = array();
		$comments = array();
		if (count($ids)){
			$strids = implode(',',$ids);
			
			$voteds = $this->f3->get("DB")->exec("SELECT * FROM mp_content_poll_voted WHERE contentID in ($strids) AND userID = '{$this->user['ID']}'");
			foreach ($voteds as $item){
				$voted[$item['contentID']] = $item['answerID'];
			}
			
			$voteds = $this->f3->get("DB")->exec("SELECT * FROM mp_content_comments WHERE contentID in ($strids) ORDER BY datein DESC");
			foreach ($voteds as $item){
				if ($item['userID']==$this->user['ID']){
					$comments[$item['contentID']]['me'] = $item['userID'];
				}
				if (!isset($comments[$item['contentID']]['last'])){
					$comments[$item['contentID']]['last']= $item['userID'];
				}
				
				
			}
			
			
			
			
			
		}
		
		
	
		
		$items = array();
		foreach ($result as $item){
			
			$item['voted'] = isset($voted[$item['ID']])?$voted[$item['ID']]:'';
			$item['has_poll'] = $item['poll']?1:0;


			$item['has_commented'] = isset($comments[$item['ID']]['me'])?1:0;
			$item['last_commented'] = isset($comments[$item['ID']]['last'])&&$comments[$item['ID']]['last']==$this->user['ID']?1:0;
			
			
			
			unset($item['description']);
			unset($item['discussion_link']);
			unset($item['resolution']);
			unset($item['poll']);
			unset($item['poll_allow_nr_votes']);
			unset($item['poll_show_result']);
			unset($item['poll_anonymous']);
			
			
			$items['catID'.$item['categoryID']]["ID"] = $item['categoryID'];
			$items['catID'.$item['categoryID']]["category"] = $item['category'];
			$items['catID'.$item['categoryID']]["items"][] = $item;

		}
		$result = array();
		foreach ($items as $item) $result[] = $item;


		//test_array($result);
		return $GLOBALS["output"]['data'] = $result;
	}
	function item() {
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		$object = models\item::getInstance();
		
		
		$result =  $object->get($itemID,true);
		
		

		//test_array($result); 
		if ($result['meetingID']!=$this->meetingID && $itemID){
			$result =  $object->get("a",true);
		}

		$result['groups'] = $object->getGroups($result['ID']);
//test_array($result); 

		$result['files']= models\item_file::getInstance()->getAll("contentID='{$result['ID']}'","datein DESC");
		$result['comments']= models\item_comment::getInstance()->getAll("contentID='{$result['ID']}'","datein ASC");
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function item_comments() {
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		
		$result['comments']= models\item_comment::getInstance()->getAll("contentID='{$itemID['ID']}'","datein ASC");
		
		return $GLOBALS["output"]['data'] = $result;
	}

	function company() {
		$domain = $this->f3->get("domain");
		$result = array();
		$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		$result =  models\company::getInstance()->get($this->companyID,$userID);

		unset($result['invitecode']);
	//test_array($result); 
		
		
		return $GLOBALS["output"]['data'] = $result;
	}




}
