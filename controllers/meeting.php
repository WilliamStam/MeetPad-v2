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

		//test_array($data); 


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
			"print"=>"/print/{$data['ID']}/{$data['company']}/{$data['url']}"
		);
		$tmpl->data = $data;
		$tmpl->get = $_GET;
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();
	}
	function _print() {
		$user = $this->f3->get("user");
		$data = models\meeting::getInstance()->get($this->f3->get("PARAMS['ID']"), true);

		$this->f3->set("NOTIMERS",true);

		//test_array($result); 
		$data['groups'] =  models\meeting::getInstance()->getGroups($data['ID']);

		$users = models\meeting::getInstance()->getUsers($data['ID']);
		$u = array(
			"yes"=>array(),
			"no"=>array()
		);

		foreach($users as $item){
			if ($item['attending']=='1'){
				$u['yes'][] = $item;
			} else {
				$u['no'][] = $item;
			}
		}
		$data['attending'] = $u;
		
		
		
		
		

		$object = models\item::getInstance();
		$result =  $object->getAll("meetingID ='{$data['ID']}' AND deleted !='1'","mp_categories.orderby ASC, datein ASC",'',array("userID"=>$this->user['ID']));



		$ids = array();
		foreach ($result as $item) {
			$ids[] = $item['ID'];
		}
		$voted = array();
		$comments = array();
		
		
		

		



		$items = array();
		foreach ($result as $item){

			$item['voted'] = isset($voted[$item['ID']])?$voted[$item['ID']]:'';
			$item['has_poll'] = $item['poll']?1:0;

			$comments = models\item_comment::getInstance()->getAll("contentID='{$item['ID']}'","datein ASC",'',array("companyID"=>$data['companyID']));

			$item['comments'] =  $comments;

			$attachments = models\item_file::getInstance()->getAll("contentID='{$item['ID']}'","datein DESC");

			$item['attachments'] =  $attachments;
			
			$item['poll'] =  models\item_poll::getInstance()->get($item);

			$items['catID'.$item['categoryID']]["ID"] = $item['categoryID'];
			$items['catID'.$item['categoryID']]["category"] = $item['category'];
			$items['catID'.$item['categoryID']]["items"][] = $item;

		}
		$result = array();
		foreach ($items as $item) $result[] = $item;
		
		
		
		
		



		$data['content'] = $result;


		

//test_array($data); 



		$tmpl = new \template("template.twig","app/print/");
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
		$tmpl->date = date("d F Y   H:i:s");
		$tmpl->data = $data;
		$tmpl->get = $_GET;
		$tmpl->dropdownLabel = $tmpl->data['meeting'];
		$tmpl->output();
	}
	
	


}
