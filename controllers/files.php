<?php
namespace controllers;

use models as models;

class files extends _ {
	function __construct() {
		parent::__construct();
	//	if ($this->user['ID'] == "") $this->f3->reroute("/login");
	}

	function thumbnail() {
		$this->f3->set("NOTIMERS",true);
		$ID = $this->f3->get("PARAMS['ID']");
		$data = models\item_file::getInstance()->get($ID, true);
		
		if (!is_numeric($ID)){
			$data['filename'] =  $this->f3->get("PARAMS['filename']");
			$data['store_filename'] =  $this->f3->get("PARAMS['filename']");
			$data['companyID'] =  $this->f3->get("PARAMS['cID']");
			$data['meetingID'] =  $this->f3->get("PARAMS['mID']");
		} 
		
		$cfg = $this->f3->get("cfg");
		$width = $this->f3->get("PARAMS['width']");
		$height = $this->f3->get("PARAMS['height']");
		$crop = isset($_GET['crop'])?true:false;

		$filename = $data['store_filename'];
		$folder = $cfg['media'] .  $data['companyID'] . DIRECTORY_SEPARATOR . $data['meetingID'] . DIRECTORY_SEPARATOR;
		$file = $folder . $filename;
		$folder_stub = $data['companyID'] . DIRECTORY_SEPARATOR . $data['meetingID'] . DIRECTORY_SEPARATOR;
		
		//test_string($file);
		if (file_exists($file)){
			$file_extension = strtolower(substr(strrchr($data['store_filename'], "."), 1));

			if ($file_extension == "pdf") {
				$thumb = "thumb" . DIRECTORY_SEPARATOR . str_replace(".pdf", ".png", $filename);
				if (!file_exists($folder . "thumb" . DIRECTORY_SEPARATOR)) mkdir($folder . "thumb" . DIRECTORY_SEPARATOR, 01777, true);

				if (!file_exists($folder . $thumb) && file_exists($file)) {
					$exportPath = $folder . $thumb;
					$res = "96";
					$pdf = $folder . $filename;

					$str = "gs -dCOLORSCREEN -dNOPAUSE -box -sDEVICE=png16m -dUseCIEColor -dTextAlphaBits=4 -dFirstPage=1 -dLastPage=1 -dGraphicsAlphaBits=4 -o$exportPath -r$res  $pdf";

					//test_string($str);
					exec($str);

					//self::remove_white($folder . $thumb);
				}

				$filename = $thumb;
				
				
				
			}

		
			if (file_exists($folder . $filename)) {
				$thumb = new \Image($folder_stub . $filename, FALSE, $cfg['media']);
				$thumb->resize($width, $height, $crop);
				$thumb->render();
			}
			exit();
		} else {
			$this->f3->error(404);
		}

	

		
	}
	function download(){
		$this->f3->set("NOTIMERS",true);
		$ID = $this->f3->get("PARAMS['ID']");
		$data = models\item_file::getInstance()->get($ID, true);

		$userID = isset($_GET['uID'])?$_GET['uID']:$this->user['ID'];
		//test_array($userID);
		//test_array($data);
		$counter = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_user_seen_content");
		$counter->load("contentID='{$data['contentID']}' AND fileID='{$data['ID']}' AND userID='{$userID}'");

		$counter->contentID = $data['contentID'];
		$counter->fileID = $data['ID'];
		$counter->userID = $userID;
		$counter->level = '2';
		$counter->viewed = $counter->viewed?$counter->viewed+1:1;

		$counter->save();
		
		
		
		

		if (!is_numeric($ID)){
			$data['filename'] =  $this->f3->get("PARAMS['filename']");
			$data['store_filename'] =  $this->f3->get("PARAMS['filename']");
			$data['companyID'] =  $this->f3->get("PARAMS['cID']");
			$data['meetingID'] =  $this->f3->get("PARAMS['mID']");
		}

		$cfg = $this->f3->get("cfg");


		$filename = $data['store_filename'];
		$folder = $cfg['media'] .  $data['companyID'] . DIRECTORY_SEPARATOR . $data['meetingID'] . DIRECTORY_SEPARATOR;
		$file = $folder . $filename;
		$folder_stub = $data['companyID'] . DIRECTORY_SEPARATOR . $data['meetingID'] . DIRECTORY_SEPARATOR;

		if (file_exists($file)){
			$o = new \Web();
			header('Content-Type: '.$o->mime($file));
			header('Content-Disposition: attachment; '.
			       'filename='.basename($data['filename']));
			header('Accept-Ranges: bytes');
			header('Content-Length: '.$size=filesize($file));

			echo readfile($file);


			exit();
		} else {
			$this->f3->error(404);
		}
		
	}
	
	function view(){
		$this->f3->set("NOTIMERS",true);
		$ID = $this->f3->get("PARAMS['ID']");
		$data = models\item_file::getInstance()->get($ID, true);

		$userID = isset($_GET['uID'])?$_GET['uID']:$this->user['ID'];
		//test_array($userID);
		//test_array($data);
		$counter = new \DB\SQL\Mapper($this->f3->get("DB"), "mp_user_seen_content");
		$counter->load("contentID='{$data['contentID']}' AND fileID='{$data['ID']}' AND userID='{$userID}'");

		$counter->contentID = $data['contentID'];
		$counter->fileID = $data['ID'];
		$counter->userID = $userID;
		$counter->level = '1';
		$counter->viewed = $counter->viewed?$counter->viewed+1:1;

		$counter->save();



		if (!is_numeric($ID)){
			$data['filename'] =  $this->f3->get("PARAMS['filename']");
			$data['store_filename'] =  $this->f3->get("PARAMS['filename']");
			$data['companyID'] =  $this->f3->get("PARAMS['cID']");
			$data['meetingID'] =  $this->f3->get("PARAMS['mID']");
		}
		$cfg = $this->f3->get("cfg");

		$filename = $data['store_filename'];
		$folder = $cfg['media'] .  $data['companyID'] . DIRECTORY_SEPARATOR . $data['meetingID'] . DIRECTORY_SEPARATOR;
		$file = $folder . $filename;
		$folder_stub = $data['companyID'] . DIRECTORY_SEPARATOR . $data['meetingID'] . DIRECTORY_SEPARATOR;
		
		if (file_exists($file)){
			$o = new \Web();
			header('Content-Type: '.$o->mime($file));
			header('Accept-Ranges: bytes');
			header('Content-Length: '.$size=filesize($file));
			echo readfile($file);
			exit();
		} else {
			$this->f3->error(404);
		}
	}
	public static function remove_white($thumb) {
		$img = imagecreatefrompng($thumb);
		//find the size of the borders
		$b_top = 0;
		$b_btm = 0;
		$b_lft = 0;
		$b_rt = 0;

//top
		for (; $b_top < imagesy($img); ++$b_top) {
			for ($x = 0; $x < imagesx($img); ++$x) {
				if (imagecolorat($img, $x, $b_top) != 0xFFFFFF) {
					break 2; //out of the 'top' loop
				}
			}
		}

//bottom
		for (; $b_btm < imagesy($img); ++$b_btm) {
			for ($x = 0; $x < imagesx($img); ++$x) {
				if (imagecolorat($img, $x, imagesy($img) - $b_btm - 1) != 0xFFFFFF) {
					break 2; //out of the 'bottom' loop
				}
			}
		}

//left
		for (; $b_lft < imagesx($img); ++$b_lft) {
			for ($y = 0; $y < imagesy($img); ++$y) {
				if (imagecolorat($img, $b_lft, $y) != 0xFFFFFF) {
					break 2; //out of the 'left' loop
				}
			}
		}

//right
		for (; $b_rt < imagesx($img); ++$b_rt) {
			for ($y = 0; $y < imagesy($img); ++$y) {
				if (imagecolorat($img, imagesx($img) - $b_rt - 1, $y) != 0xFFFFFF) {
					break 2; //out of the 'right' loop
				}
			}
		}

//copy the contents, excluding the border
		$newimg = imagecreatetruecolor(imagesx($img) - ($b_lft + $b_rt), imagesy($img) - ($b_top + $b_btm));

		imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));

//finally, output the image


		imagepng($newimg, $thumb);
	}
	


}
