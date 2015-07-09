<?php
namespace controllers\data;
use models as models;

class home extends _data {
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
			"user"=>$this->user,
			"meetings" => $this->meetings()
		);
		

		return $GLOBALS["output"]['data'] = $result;
	}

	function meetings() {

		
		$records = models\meeting::getInstance()->getAll("(timeEnd>= now() AND timeStart <= now())", "timeEnd DESC", "",array("groups"=>true,"userID"=>$this->user['ID']));

		$result = array(
			"active"=>array(),
			"past"=>array()
		);

		foreach($records as $item){
			$result[$item['active']=='1'?"active":"past"][] = $item;
		}



		//test_array($result); 

		return $GLOBALS["output"]['data'] = $result;
	}




}
