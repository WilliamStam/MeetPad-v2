<?php
namespace models;
use \timer as timer;

class _ {

	function __construct() {
		$this->f3 = \Base::instance();
		$this->user = $this->f3->get("user");
	}


	public static function dbStructure($table, $additionalFields = array()) {
		$f3 = \Base::instance();
		$result = array();
		foreach ($f3->get("DB")->exec("EXPLAIN $table;") as $key => $value) {
			$result[$value["Field"]] = "";
		}
		foreach ($additionalFields as $key => $value) {
			if ($key) {
				$result[$key] = $value;
			} else {
				$result[$value] = "";
			}
		}
		return $result;
	}
}
