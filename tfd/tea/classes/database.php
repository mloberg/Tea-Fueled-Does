<?php

	class Database extends Tea{
	
		static private $env;
		static protected $config = array();
		static private $setup = array(
			'users_table' => false,
			'migrations' => false
		);
		
		static function action($arg){
			if(empty($arg[2])){
				echo "Looking for help?";
			}else{
				global $environment;
				self::$env = strtolower(substr($environment, 0, 3));
				// config stuff
				if(!file_exists(TEA_CONFIG.'database-'.self::$env.EXT) && $arg[2] !== 'init'){
					echo "You don't haven't set up Tea to use your database.\n\nPlease run 'tea database init' before any other command.\n";
					exit(0);
				}
				self::$config = include(TEA_CONFIG.'database-'.self::$env.EXT);
				self::$config['host'] = DB_HOST;
				self::$config['user'] = DB_USER;
				self::$config['pass'] = DB_PASS;
				self::$config['database'] = DB;
				// run the sent command
				self::$arg[2]();
			}
		}
		
		static function init(){
			if(DB_HOST == ''){
				echo "It seems your database config is empty.\nPlease edit /content/_config/environments.php.\n";
				exit(0);
			}
			// create a config file
			if(file_exists(TEA_CONFIG.'database-'.self::$env.EXT)){
				echo "It seems that you have already run this command.\n";
				echo "\tIf you want to do a migration, try running 'tea migrations'\n\tIf you need to redo your config, run 'tea database config'.\n";
				exit(0);
			}
			self::config();
			// create user table
			if(self::$setup['users_table']){
				self::create_users_table(self::$config['users_table']);				
				// set up an admin user?
				
			}
			// create other tables
			
			// create a migration file
			if(self::$setup['migrations']){
				
			}
			echo "Database setup.";
		}
		
		static function config(){
			echo 'MySQL Host: '.DB_HOST."\n";
			echo 'MySQL User: '.DB_USER."\n";
			echo 'MySQL Pass: '.DB_PASS."\n";
			echo 'MySQL Database: '.DB."\n";
			echo "If this information is incorrect, please edit /content/_config/environments.php\n";
			echo "Extra DB config for Tea.\n";
			if(DB_HOST == 'localhost'){
				// socket
				echo "\tLocation of the MySQL Socket [/var/mysql/mysql.sock]: ";
				$resp = trim(fgets(STDIN));
				$sock = (!empty($resp)) ? $resp : '/var/mysql/mysql.sock';
				self::$config['sock'] = $sock;
			}else{
				// port
				echo "\tMySQL port [3306]: ";
				$resp = trim(fgets(STDIN));
				$port = (!empty($resp)) ? $resp : '3306';
				self::$config['port'] = $port;
			}
			// users table
			do{
				echo "Setup users table? [y/n] ";
				$resp = trim(fgets(STDIN));
			}while(!preg_match('/[y|n]/', strtolower($resp)));
			if(strtolower($resp) === 'y'){
				self::$setup['users_table'] = true;
				echo 'Users table name ['.USERS_TABLE.']: ';
				$resp = trim(fgets(STDIN));
				$user_table_name = (!empty($resp)) ? $resp : USERS_TABLE;
				self::$config['users_table'] = $user_table_name;
				// rewrite /content/_config/general.php config file with user table name
				if($user_table_name !== USERS_TABLE){
					self::update_users_table_config($user_table_name);
				}
			}
			// set up migrations?
			do{
				echo "Do you want to setup migrations? (you can always do this later) [y/n] ";
				$resp = strtolower(trim(fgets(STDIN)));
			}while(!preg_match('/[y|n]/', $resp));
			if($resp === 'y'){
				self::$setup['migrations'] = true;
				echo "\tMigration table name [migrations]: ";
				$resp = trim(fgets(STDIN));
				$migrations_table = (!empty($resp)) ? $resp : 'migrations';
			}
			// write config file
			$conf_file = <<<CONF
<?php
return array(
	'sock' => '$sock',
	'port' => '$port',
	'migrations_table' => '$migrations_table'
);
CONF;
			if(file_exists(TEA_CONFIG.'database-'.self::$env.EXT)) unlink(TEA_CONFIG.'database-'.self::$env.EXT);
			$fp = fopen(TEA_CONFIG.'database-'.self::$env.EXT, 'w');
			if(!fwrite($fp, $conf_file)){
				echo "Error saving the config file!\n";
				fclose($fp);
				exit(0);
			}
			fclose($fp);
		}
		
		static function create_users_table($table = null){
			if(is_null($table)){
				echo "Name of the users table [".USERS_TABLE."]: ";
				$resp = trim(fgets(STDIN));
				$table = (!empty($resp)) ? $resp : 'users';
				if($table !== USERS_TABLE){
					self::update_users_table_config($table);
				}
			}
			$columns = array(
				'id' => array(
					'type' => 'int',
					'length' => '11',
					'null' => false,
					'default' => false,
					'extra' => 'auto_increment',
					'key' => 'primary'
				),
				'username' => array(
					'type' => 'varchar',
					'length' => '128',
					'null' => false,
					'default' => false,
					'extra' => '',
					'key' => 'unique'
				),
				'salt' => array(
					'type' => 'varchar',
					'length' => '512',
					'null' => false,
					'default' => false,
					'extra' => '',
					'key' => ''
				),
				'secret' => array(
					'type' => 'varchar',
					'length' => '512',
					'null' => false,
					'default' => '',
					'extra' => '',
					'key' => ''
				)
			);
			echo "If you wish to add custom fields to the users table, please enter them below.\n";
			do{
				$exit = false;
				echo "\tField name ('none' when you are done): ";
				$field = trim(fgets(STDIN));
				if($field == 'none'){
					$exit = true;
				}elseif(!empty($field)){
					echo "\tField type [varchar]: ";
					$type = trim(fgets(STDIN));
					$type = (!empty($type)) ? $type : 'varchar';
					if(!preg_match('/(float|double|tinytext|text|mediumtext|longtext|date|datetime|timestamp|time)/', $type)){
						switch($type){
							case 'varchar':
								$default = '128';
								break;
							case 'int':
								$default = '11';
								break;
						}
						do{
							echo "\tField Length/Content [$default]:";
							$length = trim(fgets(STDIN));
							if(!empty($default) && empty($length)) $length = $default;
						}while(empty($length));
					}
					// add to columns array
				}
			}while(!$exit);
			DBConnect::create_table($table, $columns);
		}
		
		static private function update_users_table_config($user_table_name){
			// load the file into an array
			$conf = file(CONF_DIR.'general'.EXT);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('USERS_TABLE'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "define('USERS_TABLE', '{$user_table_name}'); // the MySQL table the user info is store in\n";
			}
			// delete config file
			unlink(CONF_DIR.'general'.EXT);
			// create new file
			$fp = fopen(CONF_DIR.'general'.EXT, 'c');
			// write config file
			foreach($conf as $l){
				fwrite($fp, $l);
			}
			// close file
			fclose($fp);
		}
		
		static function test(){
			self::$config['sock'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
			$columns = array(
				'id' => array(
					'type' => 'int',
					'length' => '11',
					'null' => false,
					'default' => false,
					'extra' => 'auto_increment',
					'key' => 'primary'
				),
				'username' => array(
					'type' => 'varchar',
					'length' => '128',
					'null' => false,
					'default' => false,
					'extra' => '',
					'key' => 'unique'
				),
				'salt' => array(
					'type' => 'varchar',
					'length' => '512',
					'null' => false,
					'default' => false,
					'extra' => '',
					'key' => ''
				),
				'secret' => array(
					'type' => 'varchar',
					'length' => '512',
					'null' => false,
					'default' => '',
					'extra' => '',
					'key' => ''
				)
			);
			DBConnect::create_table('test', $columns);
		}
	
	}
	
	class DBConnect extends Database{
	
		static private $con;
		
		function __destruct(){
			self::$con = null;
		}
		
		static private function connect(){
			if(!is_resource(self::$con) || is_null(self::$con)){
				try{
					if(parent::$config['host'] == 'localhost'){
						$dsn = 'mysql:unix_socket='.parent::$config['sock'].';dbname='.parent::$config['database'];
					}else{
						$dsn = 'mysql:host='.parent::$config['host'].';port='.parent::$config['port'].';dbname='.parent::$config['database'];
					}
					self::$con = new PDO($dsn, parent::$config['user'], parent::$config['pass']);
				}catch(PDOException $e){
					print "Error: " . $e->getMessage()."\n";
					exit(0);
				}
			}
			return self::$con;
		}
		
		static function create_table($table, $columns){
			$link =& self::connect();
			$sql = sprintf("CREATE TABLE `%s` (", $table);
			$keys = array();
			foreach($columns as $name => $info){
				// is there a key?
				if(!empty($info['key'])){
					$keys[$name] = $info['key'];
				}
				switch($info['type']){
					case 'float':
					case 'double':
					case 'tinytext':
					case 'text':
					case 'mediumtext':
					case 'longtext':
					case 'date':
					case 'datetime':
					case 'timestamp':
					case 'time':
						$type = $info['type'];
						break;
					default:
						$type = $info['type'].'('.$info['length'].')';
				}
				if($info['default'] !== false){
					$default = sprintf(" DEFAULT '%s'", $info['default']);
				}elseif($info['null'] === true){
					$default = " DEFAULT NULL";
				}
				$null = ($info['null'] === false) ? ' NOT NULL' : '';
				$sql .= sprintf("`%s` %s%s%s %s,",
					$name, $type, $null, $default, strtoupper($info['extra']));
			}
			foreach($keys as $field => $type){
				$sql .= strtoupper($type).' KEY ';
				if($type != 'primary') $sql .= sprintf("`%s` ", $field);
				$sql .= sprintf("(`%s`),", $field);
			}
			$sql = substr($sql, 0, strlen($sql) -1);
			$sql .= ')';
			//print_r($sql);
			$link->exec($sql);
		}
	
	}