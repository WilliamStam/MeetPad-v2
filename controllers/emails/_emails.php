<?php
namespace controllers\emails;
use models as models;

class _emails extends \controllers\_ {
	function __construct() {
		parent::__construct();
		$this->user = $this->f3->get("user");
		
		$this->f3->set("__NoEndStuff__", true);
	}



}
