<?php
namespace models;
use \timer as timer;

class user extends _ {
	function __construct() {
		parent::__construct();
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
		return $return;
	}
	function login($username, $password) {
		$f3 = \Base::instance();
		$timer = new timer();

		$ID = "";


		setcookie("username", $username, time() + 31536000, "/");


		$password_hash = $password;

		$password_hash = md5(md5("meet").$password.md5("pad"));

		$result = $f3->get("DB")->exec("
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
		return $return;
	}
}
