<?php
namespace controllers\save;

use models as models;

class form extends _save {
	private static $instance;
	private $errors;
	public $meetingID;
	public $companyID;

	function __construct() {
		parent::__construct();


	}

	function post($key, $required = false) {
		$val = isset($_POST[$key]) ? $_POST[$key] : "";
		if ($required && $val == "") {
			$this->errors[$key] = $required === true ? "" : $required;
		}
		return $val;
	}




	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}




	function company() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";

		$values = array(
			"company" => $this->post("company", true),
			"invitecode" => $this->post("invitecode"),
			"admin_email" => $this->post("admin_email", "A valid email is Required"),

		);
		$errors = $this->errors;


		if ($values['invitecode'] == "") $values['invitecode'] = $values['company'] . "-" . md5($values['company'] . "meetpad" . date("dmyhis"));

		
		
		
		$exists = models\company::getInstance()->getAll("company='{$values['company']}'")->show();
		$exists = isset($exists[0])?$exists[0]:false;
		if ($exists && $exists['ID']!=$ID){
			$errors['company'] = "A company with that name already exists<br> Admin Contact: {$exists['admin_email']}";
		}
		//test_array($errors); 
		
		
		
		
		



		$groups = array();
		$categories = array();
		$groups_id = array();
		$categories_id = array();

		foreach ($_POST as $key => $val) {
			if (strpos($key, "group-edit-") > -1) {
				$itemID = str_replace("group-edit-", '', $key);
				$groups_id[] = $itemID;
				$groups[] = array(
					"ID" => $itemID,
					"group" => $val,
					"orderby" => count($groups)
				);
			}
			if (strpos($key, "group-add-") > -1) {
				$groups[] = array(
					"ID" => "",
					"group" => $val,
					"orderby" => count($groups)
				);
			}
			if (strpos($key, "category-add-") > -1) {
				$categories[] = array(
					"ID" => "",
					"category" => $val,
					"orderby" => count($categories)
				);
			}
			if (strpos($key, "category-edit-") > -1) {
				$itemID = str_replace("category-edit-", '', $key);
				$categories_id[] = $itemID;
				$categories[] = array(
					"ID" => str_replace("category-edit-", '', $key),
					"category" => $val,
					"orderby" => count($categories)
				);
			}
		}

		if (count($groups) == 0) {
			$errors['company-groups'] = "No Groups Added, Please add at least 1 group to the company";
		}
		if (count($categories) == 0) {
			$errors['company-categories'] = "No Categories Added, Please add at least 1 category to the company";
		}

		$company = models\company::getInstance()->get($ID)->getGroups()->getCategories()->format()->show();
		$group_remove_list = array();
		foreach ($company['groups'] as $item) {
			if (!in_array($item['ID'], $groups_id)) {
				$group_remove_list[] = $item['ID'];
			}
		}
		$category_remove_list = array();
		foreach ($company['categories'] as $item) {
			if (!in_array($item['ID'], $categories_id)) {
				$category_remove_list[] = $item['ID'];
			}
		}






		$result = models\company::getInstance()->get($ID);

		if (count($errors)==0){
			$result = $result->save($values)->saveGroups($groups)->removeGroups($group_remove_list)->saveCategories($categories)->removeCategories($category_remove_list)->show();
			
		} else {
			$result = $result->show();
		}
		
		
		
		
		
		


		



		$return = array(
			"result" => $result,
			"errors" => $errors
		);

	//	test_array($return); 
/*
		test_array(array(
			           "co" => $values,
			           "groups" => $groups,
			           "groups-del" => $group_remove_list,
			           "categories" => $categories,
			           "categories-del" => $category_remove_list,
			           "post" => $_POST
		           )
		);
*/
		return $GLOBALS["output"]['data'] = $return;
	}

	function meeting() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$IDparts = explode("-", $ID);
		$companyID = "";
		if (isset($IDparts[1])) {
			$companyID = $IDparts[1];
			$ID = $IDparts[0];
		}

		$result = models\meeting::getInstance()->get($ID, true)->getGroups($companyID)->format()->show();




		//test_array($result['groups']); 




		return $GLOBALS["output"]['data'] = $result;
	}

	function item() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$IDparts = explode("-", $ID);
		$mID = "";
		$cID = "";
		if (isset($IDparts[1])) {
			$mID = $IDparts[1];
			$ID = $IDparts[0];
		}
		if (isset($IDparts[2])) {
			$cID = $IDparts[2];
		}

		$resultO = models\item::getInstance()->get($ID, true);
		$result = $resultO->format()->show();



		if ($result['meetingID']) $mID = $result['meetingID'];
		$result['meeting'] = models\meeting::getInstance()->get($mID, true)->getGroups()->format()->show();

		if ($result['meeting']['companyID']) $cID = $result['meeting']['companyID'];
		$result['company'] = models\company::getInstance()->get($cID, true)->getGroups()->getCategories()->format()->show();



		$resultG = $resultO->getGroups($result['company']['ID'])->show();
		$result['groups'] = $resultG['groups'];

		//test_array($resultG); 




		return $GLOBALS["output"]['data'] = $result;
	}




}
