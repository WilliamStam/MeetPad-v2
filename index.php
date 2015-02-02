<?php
date_default_timezone_set('Africa/Johannesburg');
setlocale(LC_ALL, 'en_ZA.UTF8');
$errorPath = dirname(ini_get('error_log'));
$errorFile = $errorPath . DIRECTORY_SEPARATOR . basename(__DIR__) . "-errors.log";
ini_set("error_log", $errorFile);

if (session_id() == "") {
	$SID = @session_start();
} else $SID = session_id();
if (!$SID) {
	session_start();
	$SID = session_id();
}
$GLOBALS["output"] = array();
$GLOBALS["models"] = array();
require_once('vendor/twig/Twig/lib/Twig/Autoloader.php');
Twig_Autoloader::register();
$f3 = require('vendor/bcosca/fatfree/lib/base.php');
require('inc/timer.php');
require('inc/template.php');
require('inc/functions.php');
$GLOBALS['page_execute_timer'] = new timer(true);
$cfg = array();
require_once('config.default.inc.php');
if (file_exists("config.inc.php")) {
	require_once('config.inc.php');
}

$f3->set('AUTOLOAD', './|lib/|controllers/|inc/|/modules/');
$f3->set('PLUGINS', 'vendor/bcosca/fatfree/lib/');
$f3->set('CACHE', true);

$f3->set('DB', new DB\SQL('mysql:host=' . $cfg['DB']['host'] . ';dbname=' . $cfg['DB']['database'] . '', $cfg['DB']['username'], $cfg['DB']['password']));
$f3->set('cfg', $cfg);
$f3->set('DEBUG',3);


//$f3->set('QUIET', TRUE);

$f3->set('UI', 'app/');
$f3->set('MEDIA', './media/');
$f3->set('TZ', 'Africa/Johannesburg');

//$f3->set('ERRORFILE', $errorFile);
//$f3->set('ONERROR', 'Error::handler');
$f3->set('ONERRORd',
	function($f3) {
		// recursively clear existing output buffers:
		while (ob_get_level())
			ob_end_clean();
		// your fresh page here:
		echo $f3->get('ERROR.text');
		print_r($f3->get('ERROR.stack'));
	}
);

$version = date("YmdH");
if (file_exists("./.git/refs/heads/" . $cfg['git']['branch'])) {
	$version = file_get_contents("./.git/refs/heads/" . $cfg['git']['branch']);
	$version = substr(base_convert(md5($version), 16, 10), -10);
}

$minVersion = preg_replace("/[^0-9]/", "", $version);
$f3->set('version', $version);
$f3->set('v', $minVersion);

$f3->route('GET /txt', function ($f3) {
	echo "Hallo World";

}
);
$f3->route('GET /', function ($f3) {
	$page = array(
		"template" => "home.twig", 
		"title" => "Testing", "description" => "",
	);


	$tmpl = new \template("index.twig", "app");
	$tmpl->page = $page;

	$tmpl->output();


}
);
$f3->route('GET /500', function ($f3) {
	$this->f3->get("get");

}
);
$f3->route('GET /json', function ($f3) {
	$t = new \timer();
	$f3->set("__runJSON", true);
	$result = Array(
		"test" => "this"
	);
	$t->stop("test");
	return $GLOBALS["output"]['data'] = $result;

});

$f3->route('GET /php', function () {
	phpinfo();
	exit();
});

$f3->run();

$models = $GLOBALS['models'];
$t = array();
foreach ($models as $model) {
	$c = array();
	foreach ($model['m'] as $method) {
		$c[] = $method;
	}
	$model['m'] = $c;
	$t[] = $model;
}

$models = $t;
$pageTime = $GLOBALS['page_execute_timer']->stop("Page Execute");

$GLOBALS["output"]['timer'] = $GLOBALS['timer'];
$GLOBALS["output"]['models'] = $models;

$GLOBALS["output"]['page'] = array(
	"page" => $_SERVER['REQUEST_URI'],
	"time" => $pageTime
);

if ($f3->get("ERROR")){
	exit();
}

if (($f3->get("AJAX") && ($f3->get("__runTemplate")==false) || $f3->get("__runJSON"))) {
	header("Content-Type: application/json");

	echo json_encode($GLOBALS["output"]);
} else {


	echo '
					<script type="text/javascript">
				       updatetimerlist(' . json_encode($GLOBALS["output"]) . ');
					</script>
				';

}


?>
