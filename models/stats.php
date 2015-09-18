<?php
namespace models;

use timer as timer;

class stats extends _ {
	private static $instance;
	private $data;
	
	//private $method;
	function __construct() {
		parent::__construct();
	}
	
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	function get($where,$map_points){
		$timer = new timer();
			$data = $this->data($where);
		
		$return = array(
			"activity"=>$this->map($data,$map_points)
		);
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
	function data($where) {
		$timer = new timer();
		$return = array();
		
		if ($where) $where = " WHERE " . $where;
		
		$return = $this->f3->get("DB")->exec("SELECT datein FROM mp_logs $where ORDER BY datein ASC");
		
		
		$this->data = $return;
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
	
	
	function map($where,$points=24) {
		$timer = new timer();
		$return = array();
		if (is_array($where)) {
			$data = $where;
		} else {
			$data = $this->data($where);
		}
		
		if (count($data)>1){
			$total = count($data);
			$dateStart = $data[0]['datein'];
			$dateEnd = $data[$total - 1]['datein'];
			
			$dateStartNum = strtotime($dateStart);
			$dateEndNum = strtotime($dateEnd);
			
			//$points = 24;
			
			$diff = ($dateEndNum - $dateStartNum);
			$interval = floor($diff / $points);
			
			
			$output = array_fill(0, $points, 0);;
			$info = array();
			$highest = 0;
			
			foreach ($data as $item){
				$t = strtotime($item['datein']);
				$f = $t - $dateStartNum;
				$n = floor($f / ($interval+1));
				
				$info[] = $n . " | ".$item['datein']." | " .$f;
				
				$num = $output[$n] + 1;
				if ($num > $highest)$highest = $num;
				
				$output[$n] = $num;
				
			}
			
			$outputP = array_fill(0, $points, 0);;
			
			
			
			foreach ($output as $key=>$val){
				if ($val!=0){
					$val = ($val / $highest)*100; // work out the percent of the highest number
					$val = ceil($val / 20); // turn it into a 1 - 5 scale
				} else {
					
				}
				
				
				
				$outputP[$key] = $val;
			}
			
			
			
			
			
			
			
			$return = array(
					"start" => $dateStart . " | " . $dateStartNum,
					"end" => $dateEnd . " | " . $dateEndNum,
					"points" => $points,
					"diff" => $diff,
					"interval" => $interval,
					"info" => $info,
					"total" => $total,
					"heighest" => $highest,
					"output" => $output,
					"outputP" => $outputP,
					"data" => $data
			);
			
			
			
			$return = $outputP;
		
		
		
		
		
		
		
		}
		$timer->_stop(__NAMESPACE__, __CLASS__, __FUNCTION__, func_get_args());
		return ($return);
	}
	
	
}
