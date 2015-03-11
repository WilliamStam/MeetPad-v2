<?php
namespace controllers;

class home {
	function __construct(){
		$this->f3 = \base::instance();
		$this->user = $this->f3->get("user");
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
				"title"=> "Meetings",
			),
			"css"=>"",
			"js"=>""
		);


		$tmpl->output();

	}


}
