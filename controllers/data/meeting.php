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
			"agenda"=>$this->agenda(),
			"item"=>$this->item(),
			"company"=>$this->company()
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

		$userSQL = ($this->user['global_admin']=='1')?"":"AND mp_users_group.userID = '{$this->user['ID']}'";


		$object = models\item::getInstance();
		$result =  $object->getAll("meetingID ='{$this->meetingID}' {$userSQL}","mp_categories.orderby ASC, datein ASC");
		
		
		
		
		
		$items = array();
		foreach ($result as $item){
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

	function company() {
		$domain = $this->f3->get("domain");
		$result = array();
		$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		$result =  models\company::getInstance()->get($this->companyID,$userID);

		
	
		
		
		return $GLOBALS["output"]['data'] = $result;
	}




}
