<?php
/**
 * User: William
 * Date: 2012/07/24 - 10:43 AM
 */

class update {
	function __construct($cfg){


	}
	public static function code($cfg,$alreadyRun=false){
		$root_folder = dirname(dirname(__FILE__));


		chdir($root_folder);
		$return = "";
		if (!file_exists($root_folder."\\.git")) {
			shell_exec('git init');
		} else {

			//shell_exec('git stash');
			shell_exec('git reset --hard HEAD');
		}



		$output = shell_exec('git pull https://'.$cfg['git']['username'] .':'.$cfg['git']['password'] .'@'.$cfg['git']['path'] .' ' . $cfg['git']['branch'] . ' 2>&1');


		if (strpos($output, "Please move or remove them before you can merge.") && $alreadyRun != true) {
			shell_exec('git stash');
			self::code($cfg, true);
		}

		
	//	$str = str_replace(".git","",$cfg['git']['path']);
	//	$output = str_replace("From $str","", $output);
		$output = str_replace("* branch            ". $cfg['git']['branch'] ."     -> FETCH_HEAD","", $output);
		$output .= "</hr>\n\n";
		$output .= shell_exec('php composer.phar update');
		
		$return .= trim($output);

		return $return;
	}

	public static function db($cfg){
		$link = mysqli_connect($cfg['DB']['host'], $cfg['DB']['username'], $cfg['DB']['password'], $cfg['DB']['database']);

		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		$sql = 'SELECT `value` FROM system WHERE `system`="db_version" LIMIT 1';

		$version = "";
		if ($result=mysqli_query($link,$sql)){
			// Fetch one and one row

			$version = $result->fetch_array();
			// Free result set
			mysqli_free_result($result);
		}

		if (isset($version['value'])){
			$version = $version['value'];
		} else {
			$version = "0";
		}


		
		

		

		$v = $version*1;

		include_once("db_update.php");

		$uv = key(array_slice($sql, -1, 1, TRUE));
		$updates = 0;
		$result = "";
		$return = "";
		$filename = "backup_cv" . $v;



		if ($uv != $v) {

			$nsql = array();

			foreach ($sql as $version=> $exec) {
				$version = $version * 1;
				if ($version > $v) {
					foreach ($exec as $t) {

						$nsql[] = $t;
					}

				}
			}
			$sql = array_values($nsql);

			if (count($nsql) > 0) $filename = "update_cv" . $v;
			$result = self::db_backup($cfg, $filename);


			foreach ($sql as $e) {
				//echo $e . "<br>";
				if ($e) {
					//echo substr($e, 0, 5);
					$updates = $updates + 1;
					if (substr($e, 0, 5) == "file:") {
						$file = str_replace("file:","",$e);
						$e= @file_get_contents($file);


					}
					self::db_execute($cfg,$e);
				//	mysql_query($e, $link) or die(mysql_error());

					

				}
			}


			if ($v){
				mysqli_query($link,"UPDATE system SET `value`='$uv' WHERE `system`='db_version'") or die(mysqli_error($link));
			} else {
				mysqli_query($link, "INSERT INTO system(`system`, `value`) VALUES('db_version','$uv')") or die(mysqli_error($link));
			}


		} else {

			$result = self::db_backup($cfg, $filename);
		}

		if ($result){
			$return .= "Backup name: " . $result."<br>";
		}
		if ($updates!=0){
			$return .= "Updates: " . $updates."<br>";
		} else {
			$return .= "Already up-to-date.<br>";
		}

		return $return;
	}
	static function db_backup($cfg,$append_file_name){

		$filename = $cfg['backup'] . date("Y_m_d_H_i_s") . "_". $append_file_name. "_" . $cfg['git']['branch'] . ".sql";

		$dbhost = $cfg['DB']['host'];
		$dbuser = $cfg['DB']['username'];
		$dbpwd = $cfg['DB']['password'];
		$dbname = $cfg['DB']['database'];
		$gzip = $cfg['gzip'];

		if (!file_exists($cfg['backup'])) @mkdir($cfg['backup'], 0777, true);
		
		$str = "mysqldump --opt --single-transaction --host=$dbhost --user=$dbuser --password=$dbpwd $dbname > $filename";
	//	echo $str;
//exit(); 
		passthru($str);
		
		return "$filename";// passthru("tail -1 $filename");


	}
	static function db_execute($cfg,$sql){
		$link = mysqli_connect($cfg['DB']['host'], $cfg['DB']['username'], $cfg['DB']['password'], $cfg['DB']['database']);

		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}

		$query = $sql;

		/* execute multi query */
		if (mysqli_multi_query($link, $query)) {
			do {
				/* store first result set */
				if ($result = mysqli_store_result($link)) {
					while ($row = mysqli_fetch_row($result)) {
						//printf("%s\n", $row[0]);
					}
					mysqli_free_result($result);
				}
				/* print divider */
				if (mysqli_more_results($link)) {
					//printf("-----------------\n");
				}
			} while (mysqli_next_result($link));
		}

		/* close connection */
		mysqli_close($link);

	}
}
