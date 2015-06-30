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

	function post($key, $required = false, $default="") {
		$val = isset($_POST[$key]) ? $_POST[$key] : "";
		if ($required && $val == "") {
			$this->errors[$key] = $required === true ? "" : $required;
		}
		if ($default!="" && $val ==""){
			$val = $default;
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
		$user = $this->user;
		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$ID_orig = $ID;

		$values = array(
			"company" => $this->post("company", true),
			"invitecode" => $this->post("invitecode"),
			"admin_email" => $this->post("admin_email", "A valid email is Required"),

		);
		$errors = $this->errors;


		if ($values['invitecode'] == "") $values['invitecode'] = $values['company'] . "-" . md5($values['company'] . "meetpad" . date("dmyhis"));

		
		$companyO = models\company::getInstance();
		
		
		$exists = $companyO->getAll("company='{$values['company']}'");
		$exists = isset($exists[0])?$exists[0]:false;
		if ($exists && $exists['ID']!=$ID){
			$errors['company'] = "A company with that name already exists<br> Admin Contact: {$exists['admin_email']}";
		}
		//test_array($errors); 
		
		
		
		
		



		$groups = array();
		$categories = array();
		$groups_id = array();
		$categories_id = array();
		$groupCount = 0;
		$catCount = 0;

		foreach ($_POST as $key => $val) {
			if (strpos($key, "group-edit-") > -1) {
				$itemID = str_replace("group-edit-", '', $key);
				$groups_id[] = $itemID;
				$groups[] = array(
					"ID" => $itemID,
					"group" => $val,
					"orderby" => count($groups)
				);
				if ($val!="")$groupCount=$groupCount+1;
			}
			if (strpos($key, "group-add-") > -1) {
				$groups[] = array(
					"ID" => "",
					"group" => $val,
					"orderby" => count($groups)
				);
				if ($val!="")$groupCount=$groupCount+1;
			}
			if (strpos($key, "category-add-") > -1) {
				$categories[] = array(
					"ID" => "",
					"category" => $val,
					"orderby" => count($categories)
				);
				if ($val!="")$catCount=$catCount+1;
			}
			if (strpos($key, "category-edit-") > -1) {
				$itemID = str_replace("category-edit-", '', $key);
				$categories_id[] = $itemID;
				$categories[] = array(
					"ID" => str_replace("category-edit-", '', $key),
					"category" => $val,
					"orderby" => count($categories)
				);
				if ($val!="")$catCount=$catCount+1;
			}
		}

		if ($groupCount<=0) {
			$errors['company-groups'] = "No Groups Added, Please add at least 1 group to the company";
		}
		if ($catCount <=0) {
			$errors['company-categories'] = "No Categories Added, Please add at least 1 category to the company";
		}

	

		
		//test_array($categories);



		
		
		if (count($errors)==0){
			$ID = models\company::save($ID,$values);
			
			
			models\company::saveGroups($ID,$groups);
			models\company::saveCategories($ID,$categories);
			
			
			
			
			
		//	->saveGroups($groups)->removeGroups($group_remove_list)->saveCategories($categories)->removeCategories($category_remove_list)->show();
			
			if ($ID_orig!=$ID){
				models\company::addUser($this->user["ID"],$ID,true);
				
			}
			
			
		} 
		
		
		
		
		$return = array(
			"ID" => $ID,
			"errors" => $errors
		);
		if ($ID_orig!=$ID){
			$return['new'] = toAscii($values['company']);;
		}
		return $GLOBALS["output"]['data'] = $return;
	}

	function meeting() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$ID_orig = $ID;
		$companyID = isset($_GET['cID']) ? $_GET['cID'] : "";
		$IDparts = explode("-", $ID);
		if (isset($IDparts[1])) {
			$companyID = $IDparts[1];
			$ID = $IDparts[0];
		}

		//test_array($_POST); 
		
		$errors = $this->errors;

		$values = array(
			"timeStart" => $this->post("timeStart", "Please enter a start date and time"),
			"timeEnd" => $this->post("timeEnd", "Please enter a start date and time"),
			"companyID"=>$companyID
		);

		if (isset($_POST['meeting'])) $values['meeting'] = $this->post("meeting", true);
		if (isset($_POST['note'])) $values['note'] = $this->post("note");
		if (isset($_POST['meeting'])) {
			$values['groups'] = isset($_POST['groups'])?$this->post("groups"):array();
		}
		
		if (isset($values['meeting'])&&$values['meeting']=="")$errors['meeting'] = "";
		
		
	//	test_array($errors); 
		
		if (isset($values['groups']) && count($values['groups'])<=0){
			$errors['groups'] = "No Groups Selected, Please add at least 1 group for the meeting";
		}
		
		if (isset($values['timeStart']) && $values['timeStart']=="")$values['timeStart'] = date("Y-m-d H:i:s");
		if (isset($values['timeEnd']) && $values['timeEnd']=="")$values['timeEnd'] = date("Y-m-d H:i:s");
		
		//test_array($values); 
		
		if (!count($errors)){
			$ID = models\meeting::save($ID,$values);
			
			
		}
			
		
		
		//test_array($c); 

		//$result['company'] = $company;
	//	test_array($values); 




		



		$return = array(
			"ID" => $ID,
			"companyID" => $companyID,
			"errors" => $errors
		);

		if ($ID_orig!=$ID){
			$c = models\meeting::getInstance()->get($ID);
			$return['new'] = toAscii($c['company'])."/".$c['url'];;
		}
		return $GLOBALS["output"]['data'] = $return;
	}

	function item() {
		$result = array();
		$errors = $this->errors;
		
		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		
		$IDparts = explode("-", $ID);
		$mID = "";
		$mID = isset($_GET['mID']) ? $_GET['mID'] : "";;
		$cID = isset($_GET['cID']) ? $_GET['cID'] : "";;
		if (isset($IDparts[1])) {
			$mID = $IDparts[1];
			$ID = $IDparts[0];
		}
		if (isset($IDparts[2])) {
			$cID = $IDparts[2];
		}
	

		$values = array(
			"heading" => $this->post("heading", true),
			"description" => $this->post("description"),
			"resolution" => $this->post("resolution"),
			"discussion_link" => $this->post("discussion_link"),
			"meetingID"=>$mID,
			"categoryID"=>$this->post("categoryID"),
			"files"=>array()
		);

		if (isset($_POST['heading'])) {
			$values['groups'] = isset($_POST['groups'])?$this->post("groups"):array();
			//test_array($values['groups']); 
			if (!count($values['groups'])){
				$errors['groups'] = "You need to specify at least 1 group";
			}
		}
		if (isset($_POST['poll'])) {
			$values['poll'] = $this->post("poll");
			$values['poll_options'] = array();
			$poorderby = 0;
			foreach($_POST as $key=>$value){
				if (strpos($key,"poll-option-")>-1){
					$poid = str_replace("poll-option-","",$key);
					if (!is_numeric($poid)){
						$poid = "";
					}
					
					$po = array(
						"ID"=>$poid,
						"answer"=>$value,
						"orderby"=>$poorderby
					);
					$poorderby = $poorderby + 1;
					$values['poll_options'][] = $po;
				}
				
				
			}
			$values['poll_show_result']=$this->post("poll_show_result",false,"0");
			$values['poll_anonymous']=$this->post("poll_anonymous",false,"0");
			
			
		}
		
		$filepath = $this->cfg['media'] . $cID . DIRECTORY_SEPARATOR . $mID . DIRECTORY_SEPARATOR;
		//test_array($filepath); 
		
		foreach($_POST as $key=>$value){
			if (strpos($key,"file-filename-")>-1){
				$poid = str_replace("file-filename-","",$key);
				$poidOrig = $poid;
				if (!is_numeric($poid)){
					$poid = "";
				}
				$store_filename = $_POST["file-store_filename-{$poidOrig}"];
				$filesize = file_exists($filepath.$store_filename)?filesize($filepath.$store_filename):0;
				$po = array(
					"ID"=>$poid,
					"filename"=>$value,
					"filesize"=>$filesize,
					"store_filename"=>$_POST["file-store_filename-{$poidOrig}"],
					"description"=>$_POST["file-desc-{$poidOrig}"],
				);
				
				$values['files'][] = $po;
			}


		}
		
		
		
	//	test_array(array($values,$_POST)); 
		
		if ($values['heading']==""){
			$errors['heading'] = "";
		}
	//	test_array(array($values,$errors,$this->post("heading"))); 
		
		if (!$ID){
			
			$values['userID'] = $this->user['ID'];
		}
		
		//test_array($values); 

		if (!count($errors)){
			$ID = models\item::save($ID,$values);
			
		}
		
		



		$return = array(
			"ID" => $ID,
			"meetingID" => $mID,
			"errors" => $errors
		);
		

		return $GLOBALS["output"]['data'] = $return;
	}
	function item_upload() {
		$result = array();
		$errors = $this->errors;

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$iID = isset($_GET['itemID']) ? $_GET['itemID'] : "";;
		$mID = isset($_GET['mID']) ? $_GET['mID'] : "";;
		$cID = isset($_GET['cID']) ? $_GET['cID'] : "";;

		$cfg = $this->f3->get("cfg");
		
		

		$return = array();

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");



		/* 
		// Support CORS
		header("Access-Control-Allow-Origin: *");
		// other CORS headers if any...
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit; // finish preflight CORS requests here
		}
		*/

// 5 minutes execution time
		@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Settings
		
		


		$targetDir =  $cfg['media'] . $cID .DIRECTORY_SEPARATOR . $mID . DIRECTORY_SEPARATOR ;
//$targetDir = 'uploads';
	//	$cleanupTargetDir = true; // Remove old files
		//$maxFileAge = 5 * 3600; // Temp file age in seconds


// Create target dir
		if (!file_exists($targetDir)) {
			mkdir($targetDir, 0777, true);
		}

// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;



// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				$return = ('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				$return = ('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				$return = ('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
		}

// Return Success JSON-RPC response
		$return = ('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

		$return = array(
			"itemID"=>$iID,
			"meetingID"=>$mID,
			"companyID"=>$cID,
			"status"=>json_decode($return)
		);

		return $GLOBALS["output"]['data'] = ($return);
	}
	function usercompany() {
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$ID_orig = $ID;
		$companyID = isset($_GET['cID']) ? $_GET['cID'] : "";
		$IDparts = explode("-", $ID);
		if (isset($IDparts[1])) {
			$companyID = $IDparts[1];
			$ID = $IDparts[0];
		}

		//test_array($this->post("admin")); 

		$errors = $this->errors;

		$values = array(
			"name" => $this->post("name", true),
		);

		if (isset($_POST['email'])&&$_POST['email']!=''){
			$values['email'] = $_POST['email'];
		}
		
		if (isset($_POST['password'])&&$_POST['password']!="") $values['password'] = $this->post("password");
		
		$values['groups'] = isset($_POST['groups'])?$this->post("groups"):array();
		
		if (isset($values['groups']) && count($values['groups'])<=0){
		//	$errors['groups'] = "No Groups Selected, Please add at least 1 group for the user";
		}
		
		


		if (!count($errors)){
			$ID = models\users::save($ID,$values);
			
			$vals = array(
				"admin"=>$this->post("admin")?true:false,
				"tag"=>$this->post("tag")
			);
			
			models\company::getInstance()->addUser($ID,$companyID,$vals);
			models\users::getInstance()->setGroups($ID,$companyID,$values['groups']);
			
				
		}










		$return = array(
			"ID" => $ID,
			"companyID" => $companyID,
			"errors" => $errors
		);

		
		return $GLOBALS["output"]['data'] = $return;
	}

	function user() {
		$result = array();
		$errors = $this->errors;

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$ID_orig = $ID;

		$values = array(
			"name"=>$this->post("name",true),
			"tag"=>$this->post("tag")
		);

		if ($values['name']==""){
			$errors['name'] = "";
		}

		if (isset($_POST['password'])&&$_POST['password']!=''){
			$values['password'] = $_POST['password'];
		}
		if (isset($_POST['email'])&&$_POST['email']!=''){
			$values['email'] = $_POST['email'];
		}
		if ($values['email']!=""){
			if (count(models\users::getInstance()->getAll("email LIKE '{$values['email']}' AND mp_users.ID != '{$ID}'"))){
				$errors['email'] = "The email address already exists";
			}
			
		}
		
		
		
		
		
		
		if (!count($errors)){
			//	test_array($values); 
			$ID = models\users::save($ID,$values);
		}
		$return = array(
			"ID" => $ID,
			"errors" => $errors,
			"status"=>"1"
		);
		if ($ID_orig!=$ID){
			$ret = \controllers\emails\profile::getInstance()->newuser($ID);
			
			$return['new'] = "/?login_email={$values['email']}&login_password={$values['password']}";
			$return['status'] = $ret;
		}
		return $GLOBALS["output"]['data'] = $return;
	}


}
