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
	function post($key,$required=false){
		$val = isset($_POST[$key])?$_POST[$key]:"";
		if ($required && $val=="" ){
			$this->errors[$key] = $required===true?"":$required;
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
			"company"=>$this->post("company",true),
			"invitecode"=>$this->post("invitecode",true),
			"admin_email"=>$this->post("admin_email","Required"),
			
		);
		$errors = $this->errors;
		
	
		
		
		
		
		
		
		$groups = array();
		$categories = array();
		
		foreach ($_POST as $key=>$val){
			if (strpos($key,"group-edit-")>-1){
				$groups[] = array(
					"ID"=>str_replace("group-edit-",'',$key),
					"companyID"=>	$ID,
					"group"=>$val,
					"orderby"=>count($groups)
				);
			}
			if (strpos($key,"category-edit-")>-1){
				$categories[] = array(
					"ID"=>str_replace("category-edit-",'',$key),
					"companyID"=>	$ID,
					"category"=>$val,
					"orderby"=>count($categories)
				);
			}
		}
		
		if (count($groups)==0){
			$errors['company-groups'] = "No Groups Added, Please add at least 1 group to the company";
		}
		if (count($categories)==0){
			$errors['company-categories'] = "No Categories Added, Please add at least 1 category to the company";
		}
		
		
		
		//test_array(array("co"=>$values,"groups"=>$groups,"categories"=>$categories,"post"=>$_POST)); 
		
		

		$return = array(
			"result"=>$result,
			"errors"=>$errors
		);




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
