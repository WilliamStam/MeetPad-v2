<?php
namespace controllers\data;
use models as models;

class meeting extends _data {
	private static $instance;
	public $meetingID;
	public $companyID;
	function __construct($meetingID='') {
		parent::__construct();
		if ($meetingID)$this->meetingID = $meetingID;

	}

	public static function getInstance(){
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	function data() {
		$domain = $this->f3->get("domain");
		$result = array();

		$result = array(
			"meeting"=>$this->meeting(),
			"company"=>$this->company(),
			"agenda"=>$this->agenda(),
			"item"=>$this->item(),
			
		);
		
		if ($result['meeting']['attending']!='1' && $result['company']['admin']!='1' ){
			$result['item'] = array();
		}
		if ($result['item']['deleted']=='1'){
			$result['item'] = array();
		}
		$this->f3->set("meeting",$result['meeting']);
		$this->f3->set("company",$result['company']);
		return $GLOBALS["output"]['data'] = $result;
	}
	
	
	function meeting() {
		
		$meetingID = isset($_GET['meetingID'])?$_GET['meetingID']:$this->meetingID;

		$object = models\meeting::getInstance();
		$result =  $object->get($meetingID,true);
		
		//test_array($result); 
		$result['groups'] = $object->getGroups($meetingID);
		
		$this->meetingID = $result['ID'];
		$this->companyID = $result['companyID'];


		$users = $object->getUsers($result['ID']);
		$u = array(
			"yes"=>array(),
			"no"=>array()
		);
		
		foreach($users as $item){
			if ($item['attending']=='1'){
				$u['yes'][] = $item;
			} else {
				$u['no'][] = $item;
			}
		}
		
		
		
		$result['users'] = $u;
		
		
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function agenda() {


 
		$object = models\item::getInstance();
		$result =  $object->getAll("meetingID ='{$this->meetingID}' and deleted !='1'","mp_categories.orderby ASC, datein ASC",'',array("userID"=>$this->user['ID']));

		$ids = array();
		foreach ($result as $item) {
			$ids[] = $item['ID'];
		}
		$voted = array();
		$comments = array();
		if (count($ids)){
			$strids = implode(',',$ids);
			
			$voteds = $this->f3->get("DB")->exec("SELECT * FROM mp_content_poll_voted WHERE contentID in ($strids) AND userID = '{$this->user['ID']}'");
			foreach ($voteds as $item){
				$voted[$item['contentID']] = $item['answerID'];
			}
			
			$voteds = $this->f3->get("DB")->exec("SELECT * FROM mp_content_comments WHERE contentID in ($strids) ORDER BY datein DESC");
			foreach ($voteds as $item){
				if ($item['userID']==$this->user['ID']){
					$comments[$item['contentID']]['me'] = $item['userID'];
				}
				if (!isset($comments[$item['contentID']]['last'])){
					$comments[$item['contentID']]['last']= $item['userID'];
				}
				
				
			}
			
			
			
			
			
		}
		
		
	
		
		$items = array();
		foreach ($result as $item){
			
			$item['voted'] = isset($voted[$item['ID']])?$voted[$item['ID']]:'';
			$item['has_poll'] = $item['poll']?1:0;


			$item['has_commented'] = isset($comments[$item['ID']]['me'])?1:0;
			$item['last_commented'] = isset($comments[$item['ID']]['last'])&&$comments[$item['ID']]['last']==$this->user['ID']?1:0;
			$item['has_resolution'] = $item['resolution']!=''?1:0;
			
			
			unset($item['description']);
			unset($item['discussion_link']);
			unset($item['resolution']);
			unset($item['poll']);
			unset($item['poll_allow_nr_votes']);
			unset($item['poll_show_result']);
			unset($item['poll_anonymous']);
			
			
			$items['catID'.$item['categoryID']]["ID"] = $item['categoryID'];
			$items['catID'.$item['categoryID']]["category"] = $item['category'];
			$items['catID'.$item['categoryID']]["items"][] = $item;

		}
		$result = array();
		foreach ($items as $item) $result[] = $item;


		//test_array($result);
		return $GLOBALS["output"]['data'] = $result;
	}
	function item() {
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		$object = models\item::getInstance();
		
		
		$result =  $object->get($itemID,true);
		//$result['poll_show_result'] = '0';
		
		if ($result['poll_show_result']=='1'){
			$n = array();
			foreach ($result['poll']['options'] as $item){
				$item['percent']= number_format($item['votes'] >0?($item['votes'] / $result['poll']['votes'])*100:0, 2, '.', '');
				$n[] = $item;
			}
			
			
			
			
		} else {
			$n = array();
			if (isset($result['poll']['options'])){
				foreach ($result['poll']['options'] as $item){
					unset($item['votes']);
					$n[] = $item;
				}
			}

		}
		$result['poll']['options'] = $n;
		

		//test_array($result); 
		if ($result['meetingID']!=$this->meetingID && $itemID){
			$result =  $object->get("a",true);
		}

		$result['groups'] = $object->getGroups($result['ID']);
//test_array($this->companyID); 

		$result['files']= models\item_file::getInstance()->getAll("contentID='{$result['ID']}'","datein DESC");
		$result['comments']= models\item_comment::getInstance()->getAll("contentID='{$result['ID']}'","datein ASC",'',array("companyID"=>$this->companyID));
		
		
		
		
		
	//	test_array($result); 
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function item_comments() {
		$itemID = isset($_GET['itemID'])?$_GET['itemID']:"";

		
		$result['comments']= models\item_comment::getInstance()->getAll("contentID='{$itemID['ID']}'","datein ASC");
		
		return $GLOBALS["output"]['data'] = $result;
	}
	function comment_history() {
		$ID = isset($_GET['ID'])?$_GET['ID']:"";
		$r = $this->f3->get("DB")->exec("SELECT *, (SELECT name FROM mp_users WHERE ID = mp_logs.userID) as name FROM mp_logs WHERE commentID = '{$ID}' ORDER BY datein ASC");
		$result = array();
		
		foreach($r as $item){
			$data = json_decode($item['data']);
			$commentN = "";
			$commentW = "";
			
			unset($item['data']);
			foreach ($data as $ii){
				if ($ii->f=='comment'){
					$commentN = $ii->n;
					$commentW = $ii->w;
				}
			}
			if ($commentW){
				$granularity = new \cogpowered\FineDiff\Granularity\Character;
				$diff = new \cogpowered\FineDiff\Diff($granularity);
				$comment = $diff->render($commentW, $commentN);
				$comment = html_entity_decode($comment);
			} else {
				$comment = $commentN;
			}
			
			
			$item['comment'] = $comment;
			$result[] = $item;
		}
		
		return $GLOBALS["output"]['data']['history'] = $result;
	}

	function company() {
		$domain = $this->f3->get("domain");
		$result = array();

		$result =  models\company::getInstance()->get($this->companyID,true);
		unset($result['invitecode']);
	
		
		return $GLOBALS["output"]['data'] = $result;
	}




}
