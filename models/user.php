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
		
		return self::format($return);
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

			if ($ID!=""){
				$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_users");
				$art->load("ID = '{$ID}'");
				$art->lastLoggedin = date("Y-m-d H:i:s");
				$art->save();
			}
			
			
			$_SESSION['uID'] = $ID;
			if (isset($_COOKIE['username'])) {
				$_COOKIE['username'] = $result['email'];
			} else {
				setcookie("username", $result['email'], time() + 31536000, "/");
			}
			
			$this->_auth_logging($ID);
		} else {
			$this->_auth_logging(false);
		}

		$return = $ID;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;
	}
	
	function menu($data){
		$timer = new timer();
		$return = array();
		$user = $this->f3->get("user");
		
		//test_array($data); 
		
		if ($user['ID']){
			
			
			if ($user['global_admin']=='1'){
				$whereC = "1";
			} else {
				$whereC = "userID='{$user['ID']}'";
			}
			$meetings = meeting::getInstance()->getAll(" ( DATE(mp_meetings.timeEnd) >= DATE(NOW()) )","timeEnd ASC",'',array("userID"=>$this->user['ID']));
			
			//test_array($meetings); 
			$am = array();
			foreach ($meetings as $item){
				$item['activeMeetings'] = 0;
				if (!isset($am[$item['companyID']]))$am[$item['companyID']] = 0;

				$am[$item['companyID']] = $am[$item['companyID']] + 1;
			}



			$companies = company::getInstance()->getAll('',"company ASC",'',array("userID"=>$user['ID']));

			//test_array($companies); 
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
			
		

			$companyID = "";
			if (isset($data['meeting']['companyID'])){
				$companyID = $data['meeting']['companyID'];
				$return['meeting'] = $data['meeting'];
			}
			//test_array($data['company']['ID']); 
			if (isset($data['company']['ID'])){
				$companyID = $data['company']['ID'];
			}
			
			
			if ($companyID){
				$u = $this->f3->get("user");
				$u = users::getInstance()->get($u['ID'],$data['company']['ID']);

				// test_array(array($u['ID'],$data['company']['ID'],$u['admin'])); 

				$return['company_admin'] =$u['admin'];
				$return['company_users_no_groups'] = count(users::getInstance()->getAll("mp_users_group.groupID is null AND mp_users_company.companyID = '{$data['company']['ID']}'"));
			}

			

		}
		
		
		
		

		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return $return;
	}
	function show(){
		return $this->return;
	}
	function setActivity($ID){
		if ($ID!=""){
			$art = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_users");
			$art->load("ID = '{$ID}'");
			$art->lastActivity = date("Y-m-d H:i:s");
			$art->save();
		}
		
		
		
		return $this;
	}
	static function format($data){
		$timer = new timer();
		$f3 = \base::instance();

 

		$single = false;
		if (isset($data['ID'])) {
			$single = true;
			$data = array($data);
		}

		$i = 1;
		$n = array();
		//test_array($items); 

		foreach ($data as $item){
			$n[] = $item;
		}

		if ($single) $n = $n[0];



		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());

		return $n;
	}
	function _auth_logging($uID){
		$userID = $this->user['ID'];
		
		if ($uID)$userID = $uID;
		
		$proxyIP = "";
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$proxyIP = $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$proxyIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		
		
		
		$art = new \DB\SQL\Mapper($this->f3->get("DB"), 'mp_sessions');
		$art->userID = $userID;
		$art->sessionID = session_id();
		$art->HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		$art->IP = $_SERVER['REMOTE_ADDR'];
		$art->proxyIP =$proxyIP;
		
		$art->save();
		
		
		
	}
	

}
