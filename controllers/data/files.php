<?php
namespace controllers\data;
use models as models;

class files extends _data {
	private static $instance;
	public $meetingID;
	public $companyID;
	function __construct() {
		parent::__construct();

	}

	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	function view() {
		$domain = $this->f3->get("domain");
		$result = array();

		$ID = isset($_GET['ID']) ? $_GET['ID'] : "";
		$data = models\item_file::getInstance()->get($ID, true);
		
		$result =$data;

		return $GLOBALS["output"]['data'] = $result;
	}
	
	



}
