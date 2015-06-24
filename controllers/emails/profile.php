<?php
namespace controllers\emails;
use \timer as timer;
use \models as models;
class profile extends _emails {
	private static $instance;
	function __construct(){
		parent::__construct();
	}
	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	function forgot($userID=""){
		$return = "0";
		$userID=isset($_REQUEST["uID"])?$_REQUEST["uID"]:$userID;
		
		$user = models\users::getInstance()->get($userID);
		
		if ($user['ID']!=""){

			$s1 = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 3)), 0, 3);
			$s2 = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 5);

			$key = $user['ID'].$s1.strtotime("+24 hour")."ID".$s2;
			
			
			$tmpl = new \template("template.twig","app/emails/");
			$tmpl->page = array(
				"template"=>"forgot",
				"title"=>"Forgot Password"
			);
			$tmpl->data = $user;
			$tmpl->key = $key;


			$cfg = $this->f3->get("CFG");
			$to = $user['email'];

			$subject = 'Meetpad | Password Reset';

			$headers = "From: " . $cfg['email'] . "\r\n";
			$headers .= "Reply-To: ". $cfg['email'] . "\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

			$message = $tmpl->load();

			$return = @mail($to, $subject, $message, $headers)?"1":"0";
			
			


			 
		}


	//	test_array($return);




		return $return;
		

	}

	

}
