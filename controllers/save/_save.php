<?php
namespace controllers\save;
use models as models;

class _save extends \controllers\_ {
	function __construct() {
		parent::__construct();
		$this->user = $this->f3->get("user");
		$this->cfg = $this->f3->get("cfg");
		

		$this->f3->set("__runJSON", true);
	}


	

	
}
