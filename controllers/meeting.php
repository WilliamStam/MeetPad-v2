<?php
namespace controllers;
use \timer as timer;
use \models as models;
class meeting extends _ {
	function __construct(){
		parent::__construct();
		if ($this->user['ID']=="")$this->f3->reroute("/login");
	}
	function page(){
		$user = $this->f3->get("user");
		
		
		
	
		
		
		
		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section"    => "meeting",
			"sub_section"=> "meeting",
			"template"   => "meeting",
			"meta"       => array(
				"title"=> "Meeting",
			),
			"css"=>"",
			"js"=>"",
		);
		$tmpl->menu = models\user::getInstance()->menu()->show();;
		$tmpl->data = models\meeting::getInstance()->get($this->f3->get("PARAMS['ID']"),($user['global_admin']=='1')?"":$user['ID'])->show();
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();
		
		
		

	}


}
