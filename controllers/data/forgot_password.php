<?php
namespace controllers\data;
use models as models;

class forgot_password extends _data {
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
		$email = isset($_REQUEST['email'])?$_REQUEST['email']:"";
		$errors = array();



		if ($email==''){
			$errors['email'] = "Please enter your email address";
		}
		
		
		
		
		
		
		
		$result = array(
			"email"=>$email,
			"errors"=>$errors
		);
		

		return $GLOBALS["output"]['data'] = $result;
	}
	
	




}
