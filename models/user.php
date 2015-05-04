<?php
namespace models;
use \timer as timer;

class user extends _ {
	private static $instance;
	//private $method;
	function __construct() {
		parent::__construct();
	}
	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	
	function get($ID){
		$timer = new timer();
		$where = "ID = '$ID'";
		if (!is_numeric($ID)) {
			$where = "email = '$ID'";
		}


		$result = $this->f3->get("DB")->exec("
			SELECT *
			FROM mp_users
			WHERE $where;
		"
		);


		if (count($result)) {
			$return = $result[0];

		
		} else {

			$return = parent::dbStructure("mp_users", array("administrator" => "0"));
			
		}
		//test_array($return);
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		$this->method = __FUNCTION__;
		
		return $this;
	}
	function login($username, $password) {
		$timer = new timer();

		$ID = "";


		setcookie("username", $username, time() + 31536000, "/");


		$password_hash = $password;

		$password_hash = md5(md5("meet").$password.md5("pad"));

		$result = $this->f3->get("DB")->exec("
			SELECT ID, email FROM mp_users WHERE email ='$username' AND password = '$password_hash'
		");


		if (count($result)) {
			$result = $result[0];
			$ID = $result['ID'];
			$_SESSION['uID'] = $ID;
			if (isset($_COOKIE['username'])) {
				$_COOKIE['username'] = $result['email'];
			} else {
				setcookie("username", $result['email'], time() + 31536000, "/");
			}
		}

		$return = $ID;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		return $this;
	}
	
	function menu(){
		$timer = new timer();
		$return = array();
		$user = $this->f3->get("user");
		
		if ($user['global_admin']=='1'){
			$whereC = "1";
			$whereM = "1";
		} else {
			$whereC = "userID='{$user['ID']}'";
			$whereM = "mp_users_group.userID='{$user['ID']}'";
		}
		$meetings = meeting::getInstance()->getUser($whereM." AND ( DATE(mp_meetings.timeStart) <= DATE(NOW()) AND DATE(mp_meetings.timeEnd) >= DATE(NOW()) )","timeEnd ASC")->format()->show();
		$am = array();
		foreach ($meetings as $item){
			$item['activeMeetings'] = 0;
			if (!isset($am[$item['companyID']]))$am[$item['companyID']] = 0;
			
			$am[$item['companyID']] = $am[$item['companyID']] + 1;
		}
		
		
		
		$companies = company::getInstance()->getUser($whereC,"company ASC")->format()->show();
		
		//test_array($whereC); 
		$n = array();
		foreach ($companies as $item){
			$item['activeMeetings'] = isset($am[$item['ID']])?$am[$item['ID']] : 0;
			$n[] = $item;
		}
		$companies = $n;
		
		
		
		
		
		$return = array(
			"companies"=>$companies,
			"meetings"=>$meetings
		);
		
		
		
	//	test_array($return); 
		

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		$this->return = $return;
		return $this;
	}
	function show(){
		return $this->return;
	}
}
