<?php
namespace controllers;
use \timer as timer;
use \models as models;
class company_meetings extends _ {
	function __construct(){
		parent::__construct();
		if ($this->user['ID']=="")$this->f3->reroute("/login");
	}
	
	function page(){
		$user = $this->f3->get("user");
		
		
		$data = models\company::getInstance()->get($this->f3->get("PARAMS['ID']"),true);
		//test_array($menu); 
		
	
		
		//test_array($meetings); 
		//test_array(" $userSQL mp_meetings.companyID = '{$data['ID']}'"); 
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section"    => "company",
			"sub_section"=> "meetings",
			"template"   => "company_meetings",
			"meta"       => array(
				"title"=> "MeetPad | {$data['company']} | Meetings",
			),
			"css"=>"",
			"js"=>"",
		);
		$tmpl->data = $data;
		$tmpl->co_dropdown_append = "/meetings";
		$tmpl->dropdownLabel = $data['company'];
		

		$tmpl->output();
		
		
		

	}

}
