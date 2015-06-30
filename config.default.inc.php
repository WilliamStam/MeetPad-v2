<?php


$cfg['email'] = "no-reply@meetpad.org";
$cfg['contact'] = "team@meetpad.org";



$cfg['DB']['host'] = "localhost";
$cfg['DB']['username'] = "";
$cfg['DB']['password'] = "";
$cfg['DB']['database'] = "MeetPad-v2";

$cfg['git'] = array(
	'username'=>"",
	"password"=>"",
	"path"=>"github.com/WilliamStam/MeetPad-v2.git",
	"branch"=>"master"
);


$cfg['media'] = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR. "media" . DIRECTORY_SEPARATOR;
$cfg['backup'] = $cfg['media'] . "backups" . DIRECTORY_SEPARATOR;

$cfg['file-icons'] = array(
	"doc"=>"fa-file-word-o",
	"docx"=>"fa-file-word-o",
	"xls"=>"fa-file-excel-o",
	"xlsx"=>"fa-file-excel-o",
	"txt"=>"fa-file-text-o",
	"mp3"=>"fa-file-audio-o",
	"ppt"=>"fa-file-powerpoint-o",
	"pdf"=>"thumbnail",
	"jpg"=>"thumbnail",
	"png"=>"thumbnail",
	"gif"=>"thumbnail",
	"tif"=>"thumbnail",
);