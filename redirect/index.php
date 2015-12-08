<?php
require_once('../config.default.inc.php');
require_once('../config.inc.php');

$redirect = "https://www.meetpad.net/".$_SERVER['REQUEST_URI'];
header("HTTP/1.1 301 Moved Permanently");
header("Location: $redirect");

?>
