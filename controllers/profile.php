<?php
namespace controllers;

use models as models;

class profile extends _ {
	function __construct() {
		parent::__construct();
	
	}

	
	function forgot() {
		$user = $this->f3->get("user");
		$key = $this->f3->get("PARAMS['key']");
		
		
		$key = preg_split('/([a-z]+)/i', $key);
		
		$userID = $key[0];
		$time = $key[1];
		
		$key_expired = true;
		if ($time > strtotime("now")){
			$key_expired = false;
		}
		
		$user = models\users::getInstance()->get($userID);
		
		
		
		


	



		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section" => "user",
			"sub_section" => "forgot",
			"template" => "user_forgot",
			"meta" => array(
				"title" => "MeetPad | Forgot Password",
			),
			"css" => "",
			"js" => "/app/_js/login.js",
		);
		$tmpl->key_expired = $key_expired;
		$tmpl->user = $user;
		$tmpl->output();
	}
	function activate() {
		$key = $this->f3->get("PARAMS['key']");
		
		
		$key = preg_split('/([a-z]+)/i', $key);
		
		$userID = $key[0];
		$time = $key[1];
		
		$key_expired = true;
		if ($time > strtotime("now")){
			$key_expired = false;
		}
		
		$user = models\users::getInstance()->get($userID);
		//test_array($user); 
		if ($user['ID']){
			models\users::getInstance()->save($user['ID'],array("activated"=>"1"));
			$this->f3->reroute("/?msg=Activated");
			
			
		}
		
	}
	
	
	


}
