<?php
namespace controllers\data;
use models as models;

class _data extends \controllers\_ {
	function __construct() {
		parent::__construct();
		$this->user = $this->f3->get("user");
		

		$this->f3->set("__runJSON", true);
		
		
	}



}
