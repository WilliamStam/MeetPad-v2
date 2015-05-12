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



		$data = models\meeting::getInstance()->get($this->f3->get("PARAMS['ID']"), true)->show();

		test_array($data); 


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
		$tmpl->menu = models\user::getInstance()->menu()->show();;
		$tmpl->data = $data;
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();




	}

	function edit() {
		$user = $this->f3->get("user");



		$data = models\meeting::getInstance()->get($this->f3->get("PARAMS['ID']"), ($user['global_admin'] == '1') ? "" : $user['ID'])->show();



		$tmpl = new \template("template.twig");
		$tmpl->page = array(
			"section" => "meeting",
			"sub_section" => "meeting",
			"template" => "meeting_edit",
			"meta" => array(
				"title" => "MeetPad | {$data['company']} | {$data['meeting']}",
			),
			"css" => "",
			"js" => "",
		);
		$tmpl->menu = models\user::getInstance()->menu()->show();;
		$tmpl->data = $data;
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();




	}


}
