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


		$ret = "0";
		
		if (!count($errors)){
			
			$u = models\users::getInstance()->getAll("email LIKE '{$email}'","ID DESC");
			$u = isset($u[0])?$u[0]:false;
			
			if ($u['ID']){
				$ret = \controllers\emails\profile::getInstance()->forgot($u['ID']);
			} else {
				$errors['email'] = "No such email found in our slave ship. Please 'guess' again :P";
			}
			
		}
		
		
		
		
		$result = array(
			"status"=>$ret,
			"email"=>$email,
			"errors"=>$errors
		);
		

		return $GLOBALS["output"]['data'] = $result;
	}
	
	




}
