<?php
namespace controllers;
use \timer as timer;
use \models as models;
class company extends _ {
	function __construct(){
		parent::__construct();
		if ($this->user['ID']=="")$this->f3->reroute("/login");
	}
	function page(){
		$user = $this->f3->get("user");
		
		$userID = ($user['global_admin']=='1')?"":$user['ID'];
		$userSQL = ($this->user['global_admin']=='1')?"":"mp_users_group.userID='{$user['ID']}' AND ";
		
		$data = models\company::getInstance()->get($this->f3->get("PARAMS['ID']"),$userID)->format()->show();
		
		//test_array($data); 
		
	
		
		//test_array($meetings); 
		//test_array(" $userSQL mp_meetings.companyID = '{$data['ID']}'"); 
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section"    => "company",
			"sub_section"=> "company",
			"template"   => "company",
			"meta"       => array(
				"title"=> "MeetPad | {$data['company']}",
			),
			"css"=>"",
			"js"=>"",
		);
		$tmpl->menu = models\user::getInstance()->menu()->show();;
		$tmpl->data = $data;
		
		$tmpl->dropdownLabel = $data['company'];
		

		$tmpl->output();
		
		
		

	}
	
	function edit(){
		$user = $this->f3->get("user");
		
		$userID = ($user['global_admin']=='1')?"":$user['ID'];
		$userSQL = ($this->user['global_admin']=='1')?"":"mp_users_group.userID='{$user['ID']}' AND ";
		
		$data = models\company::getInstance()->get($this->f3->get("PARAMS['ID']"),$userID)->format()->show();
		$menu = models\user::getInstance()->menu()->show();
		
		//$menu['meetings'] = array();
		//test_array($data); 
		
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section"    => "company",
			"sub_section"=> "company",
			"template"   => "company_edit",
			"meta"       => array(
				"title"=> "MeetPad | {$data['company']}",
			),
			"css"=>"",
			"js"=>"",
		);
		$tmpl->menu = $menu;
		$tmpl->data = $data;

		$tmpl->dropdownLabel = $data['company'];
		

		$tmpl->output();
		
		
		

	}


}
