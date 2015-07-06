<?php
namespace controllers;

use models as models;

class meeting extends _ {
	function __construct() {
		parent::__construct();
		if ($this->user['ID'] == "") $this->f3->reroute("/login");
	}

	function page() {
		$user = $this->f3->get("user");



		$data = models\meeting::getInstance()->get($this->f3->get("PARAMS['ID']"), true);

	//	test_array($data); 


		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section" => "meeting",
			"sub_section" => "meeting",
			"template" => "meeting",
			"meta" => array(
				"title" => "MeetPad | {$data['company']} | {$data['meeting']}",
			),
			"css" => "",
			"js" => "",
		);
		$tmpl->data = $data;
		$tmpl->get = $_GET;
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();
	}
	
	


}
