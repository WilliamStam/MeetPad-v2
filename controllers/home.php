<?php
namespace controllers;
use \timer as timer;
use \models as models;
class home extends _ {
	function __construct(){
		parent::__construct();
		test_array($this->user['ID']); 
		if ($this->user['ID']=="")$this->f3->reroute("/login");
	}
	function page(){
		$user = $this->f3->get("user");
		
		
		
	
		
		
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section"    => "home",
			"sub_section"=> "home",
			"template"   => "home",
			"meta"       => array(
				"title"=> "MeetPad",
			),
			"css"=>"",
			"js"=>"",
		);

		$tmpl->output();
		
		
		

	}


}
