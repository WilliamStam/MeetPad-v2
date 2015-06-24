<?php
namespace controllers\emails;
use \timer as timer;
use \models as models;
class user extends _emails {
	function __construct(){
		parent::__construct();
	}
	function forgot($userID=""){
		$userID=isset($_REQUEST["uID"])?$_REQUEST["uID"]:$userID;
		
		$user = models\users::getInstance()->get($userID);
		
	//	test_array($user); 
		
		
	
		
		$tmpl = new \template("template.twig","app/emails/");
		$tmpl->page = array(
			"template"=>"forgot",
			"title"=>"Forgot Password"
		);
		$tmpl->data = $user;
		$tmpl->key = md5("woof");
		
		
		

		echo $tmpl->load();
		
		
		

	}


}
