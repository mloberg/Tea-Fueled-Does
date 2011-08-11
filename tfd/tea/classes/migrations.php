<?php

	class Migrations extends Tea{
	
		static function action($arg){
			if(empty($arg[2])){
				echo "Looking for migrations help?\n";
			}else{
				self::$arg[2]();
			}
		}
		
		static function test(){
			$db_conf = include_once(TEA_CONFIG.'migrations'.EXT);
			try{
				if(!empty($db_conf['sock']) && DB_HOST == 'localhost'){
					$dsn = 'mysql:unix_socket='.$db_conf['sock'].';dbname='.DB;
				}else{
					$dsn = 'mysql:host='.DB_HOST.';port='.$db_conf['port'].';dbname='.DB;
				}
				$dbh = new PDO($dsn, DB_USER, DB_PASS);
				$stmt = $dbh->prepare(sprintf("SHOW TABLES FROM %s", DB));
				$stmt->execute();
				$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$schema = array();
				foreach($rows as $key => $value){
					foreach($value as $k => $table){
						$stm = $dbh->prepare(sprintf("SHOW FIELDS FROM %s", $table));
						$stm->execute();
						$table_columns = $stm->fetchAll(PDO::FETCH_ASSOC);
						print_r($table_columns);
					}
				}
				$db = null;
			}catch(PDOException $e){
				print "Error: " . $e->getMessage()."\n";
				exit(0);
			}
		}
		
		static function init(){
			if(file_exists(TEA_CONFIG.'migrations'.EXT)){
				do{
					echo "You have already set up migrations. Overwrite them? [y/n]: ";
					$resp = trim(fgets(STDIN));
				}while(empty($resp));
				if(strtolower($resp) === 'y'){
					unlink(TEA_CONF.'migrations'.EXT);
				}else{
					echo "Exiting...\n";
					exit(0);
				}
			}elseif(DB_HOST == ''){
				echo "Please set up your database config in /content/_config/environments.php.\n";
				exit(0);
			}
			echo "Creating inital migration...\n\n";
			echo "We must set some config stuff.\n";
			if(DB_HOST == 'localhost'){
				// socket
				echo "\tLocation of the MySQL Socket [/var/mysql/mysql.sock]: ";
				$resp = trim(fgets(STDIN));
				$sock = (!empty($resp)) ? $resp : '/var/mysql/mysql.sock';
			}else{
				// port
				echo "\tMySQL port [3306]: ";
				$resp = trim(fgets(STDIN));
				$port = (!empty($resp)) ? $resp : '3306';
			}
			echo "\tMigrations Table [migrations]: ";
			$resp = trim(fgets(STDIN));
			$table = (!empty($resp)) ? $resp : 'migrations';
			// write to a config file
			$conf_file = <<<CONF
<?php
return array(
	'sock' => '$sock',
	'port' => '$port',
	'migrations_table' => '$table'
);
CONF;
			$fp = fopen(TEA_CONFIG.'migrations.php', 'w');
			if(!fwrite($fp, $conf_file)){
				echo "Error saving the config file!\n";
				fclose($fp);
				exit(0);
			}
			fclose($fp);
			echo "Config file saved.";
			// get the current db schema
			try{
				$db = new PDO("mysql:host=$host;port=8889;dbname=$db", $user, $pass);
				foreach($db->query('SELECT * FROM posts') as $row){
					print_r($row);
				}
				$db = null;
			}catch(PDOException $e){
				print "Error: " . $e->getMessage()."\n";
				exit(0);
			}
/*
			try{
				$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB, DB_USER, DB_PASS);
			}
*/
		}
	
	}

/*
// include some essential files
require_once 'tfd/bootstrap.php';
$app = new TFD();

// STDIN
if(!defined('STDIN')) define('STDIN', fopen("php://stdin", 'r'));

if($_SERVER['argv'][1] == 'help'){
	echo 'Help coming soon.';
}elseif($_SERVER['argv'][1] == 'list'){
	// list migrations
	$files = glob('migrations/*.php');
	if(empty($files)){
		echo "No migrations found.\n";
	}else{
		foreach($files as $migration){
			echo str_replace('.php', '', str_replace('_', ' ', str_replace('migrations/', '', $migration)))."\n";
		}
	}
}elseif($_SERVER['argv'][1] == 'init'){
	// create the inital migration
	$files = glob('migrations/*.php');
	if(!empty($files)){
		echo "Migrations exists. Exiting...\n";
	}else{
		// create the migration file
		echo "Creating the migrations file...\n";
		// get the database layout
		
		// create the migration table
		$sql = <<<TABLE
	CREATE TABLE migrations(
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`timestamp` datetime NOT NULL,
		`active` tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
	);
TABLE;
		$app->mysql->qry($sql);
		// add migration to db
		
	}
}else{
	echo "We're not sure what to do with that command...\n";
}

exit(0);
*/