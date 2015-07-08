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
			"user"=>$this->user
		);
		

		return $GLOBALS["output"]['data'] = $result;
	}
	
	



}
