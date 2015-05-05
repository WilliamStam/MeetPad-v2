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
	
	function agenda() {
		$domain = $this->f3->get("domain");
		$result = array();

		$userSQL = ($this->user['global_admin']=='1')?"":"AND mp_users_group.userID = '{$this->user['ID']}'";
		
		$result = models\item::getInstance()->getAll("meetingID ='{$this->meetingID}' {$userSQL}","mp_categories.orderby ASC, datein ASC")->format()->show();

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
	function meeting() {
		$domain = $this->f3->get("domain");
		$result = array();
		
		$meetingID = isset($_GET['meetingID'])?$_GET['meetingID']:"";
		$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";

		$result =  models\meeting::getInstance()->get($meetingID,$userID)->getGroups()->format()->show();

		$this->meetingID = $result['ID'];
		$this->companyID = $result['companyID'];
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function item() {
		$domain = $this->f3->get("domain");
		$result = array();
		$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		$result =  models\item::getInstance()->get($itemID,$userID)->getGroups()->format()->show();

		//test_array($result); 
		if ($result['meetingID']!=$this->meetingID){
			$result =  models\item::getInstance()->get("A",$userID)->getGroups()->format()->show();
		}

//test_array($result); 
	
		
		
		return $GLOBALS["output"]['data'] = $result;
	}

	function company() {
		$domain = $this->f3->get("domain");
		$result = array();
		$userID = ($this->user['global_admin']=='1')?"":"{$this->user['ID']}";
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		$result =  models\company::getInstance()->get($this->companyID,$userID)->format()->show();

		
	
		
		
		return $GLOBALS["output"]['data'] = $result;
	}




}
