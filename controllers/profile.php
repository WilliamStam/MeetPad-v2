<?php
namespace controllers;

use models as models;

class profile extends _ {
	function __construct() {
		parent::__construct();
		if ($this->user['ID'] == "") $this->f3->reroute("/login");
	}

	function page() {
		$user = $this->f3->get("user");



	



		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section" => "profile",
			"sub_section" => "profile",
			"template" => "profile",
			"meta" => array(
				"title" => "MeetPad | Profile | {$user['name']}",
			),
			"css" => "",
			"js" => "",
		);
		$tmpl->get = $_GET;
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();
	}
	
	


}
