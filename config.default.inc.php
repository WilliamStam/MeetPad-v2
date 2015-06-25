<?php


$cfg['email'] = "online@meetpad.org";



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


$cfg['backup'] = $_SERVER['DOCUMENT_ROOT'] . "/media/backups/";