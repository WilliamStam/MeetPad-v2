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
require_once('vendor/autoload.php');

$f3 = \base::instance();
require('inc/timer.php');
require('inc/template.php');
require('inc/functions.php');
require('inc/pagination.php');
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

$f3->set('UI', 'app/|media/');
$f3->set('MEDIA', './media/|'.$cfg['media']);
$f3->set('TZ', 'Africa/Johannesburg');

$f3->set('TAGS', 'p,br,b,strong,i,italics,em,h1,h2,h3,h4,h5,h6,div,span,blockquote,pre,cite,ol,li,ul');

$f3->set("menu", array());
$f3->set("company", array());

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
$f3->set('_version', $version);
$f3->set('_v', $minVersion);



$uID = isset($_SESSION['uID']) ? $_SESSION['uID'] : "";
$username = isset($_REQUEST['login_email']) ? $_REQUEST['login_email'] : "";
$password = isset($_REQUEST['login_password']) ? $_REQUEST['login_password'] : "";

$userO = new \models\user();
//$uID = "2";





if ($username && $password) {
	$uID = $userO->login($username, $password);
		
	$uri = $_SERVER['REQUEST_URI'];
	$uri = str_replace("login_email=","",$uri);
	$uri = str_replace("login_password=","",$uri);
	if (isset($_GET['login_email'])) $uri = str_replace($_GET['login_email'],"",$uri);
	if (isset($_GET['login_password'])) $uri = str_replace($_GET['login_password'],"",$uri);

	$uri = str_replace("&&","&",$uri);
	$uri = str_replace("&&","&",$uri);
	$uri = str_replace("&&","&",$uri);
	$uri = str_replace("&&","&",$uri);
	$uri = str_replace("&&","&",$uri);
	$uri = str_replace("?&","?",$uri);
	if ($uri=="?")$uri = "";
	
	$url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $uri;
	$f3->reroute($url);
}
$user = $userO->get($uID);
if (isset($_GET['auID']) && $user['su']=='1'){
	$_SESSION['uID'] = $_GET['auID'];
	$user = $userO->get($_GET['auID']);
}

//test_array($_SERVER['REQUEST_URI']); 
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/keepalive'){
	$userO->setActivity($uID);
}



$f3->set('user', $user);




$f3->route('GET /txt', function ($f3) {
	echo "Hallo World";

}
);

$f3->route('GET|POST /login', 'controllers\login->page');
$f3->route('GET|POST /', 'controllers\home->page');

$f3->route('GET|POST /user/forgot/@key', 'controllers\profile->forgot');
$f3->route('GET|POST /user/activate/@key', 'controllers\profile->activate');

$f3->route('GET|POST /content/@ID/@url', 'controllers\company->page');
$f3->route('GET|POST /content/@ID/@url/users', 'controllers\company_users->page');
$f3->route('GET|POST /content/@ID/@url/meetings', 'controllers\company_meetings->page');

$f3->route('GET|POST /content/@ID/@company/@url', 'controllers\meeting->page');
$f3->route('GET|POST /print/@ID/@company/@url', 'controllers\meeting->_print');
$f3->route('GET|POST /content/@ID/@company/@url/users', 'controllers\meeting_users->page');



//$f3->route('GET|POST /thumbnail/@ID/@width/@height/*', 'controllers\thumbnail->attachment');
//$f3->route('GET|POST /thumbnail/@cID/@mID/@width/@height/@file', 'controllers\thumbnail->attachment');
$f3->route('GET|POST /thumbnail/@ID/@cID/@mID/@width/@height/@filename', 'controllers\files->thumbnail');
$f3->route('GET|POST /file/download/@ID/@cID/@mID/@filename', 'controllers\files->download');
$f3->route('GET|POST /file/view/@ID/@cID/@mID/@filename', 'controllers\files->view');
$f3->route('GET|POST /viewer/view/@ID/@cID/@mID/@filename', 'controllers\files->view');





$f3->route('GET|POST /logout', function ($f3, $params) use ($user) {
	session_unset();
	//session_destroy();
	$f3->reroute("/login");
});

$f3->route('GET|POST /iframe/loading', function ($f3, $params) use ($user) {
	echo 'loading...';
	exit();
});



//test_array(md5(md5("meet")."123".md5("pad"))); 




$f3->route("GET|POST /save/@function", function ($app, $params) {
	$app->call("controllers\\admin\\save\\save->" . $params['function']);
}
);
$f3->route("GET|POST /save/@class/@function", function ($app, $params) {
	$app->call("controllers\\save\\" . $params['class'] . "->" . $params['function']);
}
);
$f3->route("GET|POST /save/@folder/@class/@function", function ($app, $params) {
	$app->call("controllers\\save\\" . $params['folder'] . "\\" . $params['class'] . "->" . $params['function']);
}
);
$f3->route("GET|POST /data/@function", function ($app, $params) {
	$app->call("controllers\\data\\data->" . $params['function']);
}
);
$f3->route("GET|POST /data/@class/@function", function ($app, $params) {
	//test_array($params); 
	$app->call("controllers\\data\\" . $params['class'] . "->" . $params['function']);
}
);
$f3->route("GET|POST /data/@folder/@class/@function", function ($app, $params) {
	$app->call("controllers\\data\\" . $params['folder'] . "\\" . $params['class'] . "->" . $params['function']);
}
);

$f3->route("GET|POST /internal/emails/@class/@function", function ($app, $params) {
	$app->call("controllers\\emails\\" . $params['class'] . "->" . $params['function']);
}
);






$f3->route("GET|POST /keepalive", function ($app, $params) {
	$user = $app->get("user");
	unset($user["password"]);
	unset($user["global_admin"]);
	test_array($user);	
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

if ($user['global_admin']=='1'){
	$GLOBALS["output"]['models'] = $models;
}



$GLOBALS["output"]['page'] = array(
	"page" => $_SERVER['REQUEST_URI'],
	"time" => $pageTime
);

$tt = array(
	"company"=>$f3->get("company"),
	"meeting"=>$f3->get("meeting"),
);
//test_array($tt); 
$GLOBALS["output"]['menu'] = models\user::getInstance()->menu($tt);

if ($f3->get("ERROR")){
	exit();
}

if (($f3->get("AJAX") && ($f3->get("__runTemplate")==false) || $f3->get("__runJSON"))) {
	header("Content-Type: application/json");
	echo json_encode($GLOBALS["output"]);
} else {

	//if (strpos())
	if ($f3->get("NOTIMERS")){
		exit();
	}
	

	echo '
					<script type="text/javascript">
				      updatetimerlist(' . json_encode($GLOBALS["output"]) . ');
					</script>
					</body>
</html>';

}



?>
