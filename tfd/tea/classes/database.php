<?php

	class Database extends Tea{
	
		private static $env;
		protected static $config = array();
		private static $setup = array(
			'users_table' => false,
			'migrations' => false
		);
		
		public static function action($arg){
			if(empty($arg[2]) || $arg[2] == 'help'){
				$commands = array(
					'init' => 'Set up the database class for the current evironment. (Must be run before any other command can be run.)',
					'create_table' => 'Create a table in the database.',
					'config' => 'Update your database config.'
				);
				echo "Looking for help?\n";
				echo "Commands:\n";
				foreach($commands as $name => $description){
					echo "\t{$name}: {$description}\n";
				}
			}else{
				global $environment;
				self::$env = strtolower(substr($environment, 0, 3));
				// config stuff
				if(!file_exists(TEA_CONFIG.'database-'.self::$env.EXT) && $arg[2] !== 'init'){
					echo "You don't haven't set up Tea to use your database.\n\nPlease run 'tea database init' before any other command.\n";
					exit(0);
				}
				self::load_config();
				// run the sent command
				self::$arg[2]();
			}
		}
		
		public static function load_config(){
			global $environment;
			self::$env = strtolower(substr($environment, 0, 3));
			if(!file_exists(TEA_CONFIG.'database-'.self::$env.EXT)) return false;
			self::$config = include(TEA_CONFIG.'database-'.self::$env.EXT);
			self::$config['host'] = DB_HOST;
			self::$config['user'] = DB_USER;
			self::$config['pass'] = DB_PASS;
			self::$config['database'] = DB;
			return true;
		}
		
		protected static function create_table($table_name = null, $columns = array()){
			if(DBConnect::table_exists($table_name)){
				echo "\tError: Table '{$table_name}' already exits.\n";
				exit(0);
			}
			if(is_null($table_name)){
				do{
					echo "Table Name: ";
					$table_name = trim(fgets(STDIN));
					if(DBConnect::table_exists($table_name)){
						echo "\tError: Table '{$table_name}' already exists.\n";
						$table_name = '';
					}
				}while(empty($table_name));
			}
			if(empty($columns['id'])){
				echo "Create an id column? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					$columns['id'] = array(
						'type' => 'int',
						'length' => '11',
						'null' => (bool)false,
						'default' => false,
						'extra' => 'auto_increment',
						'key' => 'primary'
					);
				}
			}
			do{
				$exit = false;
				echo "Field name ('none' when you are done): ";
				$field = trim(fgets(STDIN));
				if($field == 'none'){
					$exit = true;
				}elseif(array_key_exists($field, $columns)){
					echo "\tError: There is already a field with the name of '{$field}'.\n";
				}elseif(!empty($field)){
					echo "Field type [varchar]: ";
					$type = trim(fgets(STDIN));
					$type = (!empty($type)) ? $type : 'varchar';
					if(!preg_match('/(float|double|tinytext|text|mediumtext|longtext|date|datetime|timestamp|time|varchar|int|tinyint|smallint|mediumint|bigint|decimal|bit|char|mediumtext|year|enum)/', $type)){
						echo "\tError: We do not support that field type.\n";
					}else{
						if(!preg_match('/(float|double|tinytext|text|mediumtext|longtext|date|datetime|timestamp|time)/', $type)){
							switch($type){
								case 'tinyint':
									$default = '4';
									break;
								case 'smallint':
									$default = '6';
									break;
								case 'mediumint':
									$default = '9';
									break;
								case 'int':
									$default = '11';
									break;
								case 'bigint':
									$default = '20';
									break;
								case 'char':
									$default = '1';
								case 'varchar':
									$default = '128';
									break;
								case 'bit':
									$default = '1';
									break;
								case 'decimal':
									$default = '10,0';
									break;
								case 'year':
									$default = '4';
									break;
							}
							do{
								echo "Field Length/Content [$default]:";
								$length = trim(fgets(STDIN));
								if(!empty($default) && empty($length)) $length = $default;
							}while(empty($length));
						}
						do{
							echo "Allow Null (true, false) [true]: ";
							$null = trim(fgets(STDIN));
							$null = (!empty($null)) ? $null : 'true';
						}while(!preg_match('/(true|false)/', $null));
						echo "Default value: ";
						$default_val = trim(fgets(STDIN));
						echo "Extra (auto_increment, etc.): ";
						$extra = trim(fgets(STDIN));
						do{
							$pass = false;
							echo "Index (primary, unique, key): ";
							$key = trim(fgets(STDIN));
							if(preg_match('/(primary|unique|key)/', $key)) $pass = true;
							if(empty($key)) $pass = true;
						}while(!$pass);
						// add to columns array
						$columns[$field] = array(
							'type' => $type,
							'length' => $length,
							'null' => (bool)$null,
							'default' => $default_val,
							'extra' => $extra,
							'key' => $key
						);
					}
				}
			}while(!$exit);
			DBConnect::create_table($table_name, $columns);
			echo "Table {$table_name} created.\n";
		}
		
		public static function init(){
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
				echo "Add an admin user? [y/n]: ";
				$resp = trim(fgets(STDIN));
				if(strtolower($resp) === 'y'){
					user::add();
				}
			}
			// create other tables
			echo "Add other tables? [y/n]: ";
			$resp = trim(fgets(STDIN));
			if(strtolower($resp) === 'y'){
				do{
					$exit = false;
					echo "Table name ('none' when you are done): ";
					$table = trim(fgets(STDIN));
					if($table === 'none'){
						$exit = true;
					}elseif(DBConnect::table_exists($table)){
						echo "\tError: The table {$table} already exists.\n";
					}elseif(!empty($table)){
						self::create_table($table);
					}
				}while(!$exit);
			}
			// create a migration file
			if(self::$setup['migrations']){
				
			}
			echo "Database setup.";
		}
		
		public static function config(){
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
		
		public static function create_users_table($table = null){
			if(is_null($table)){
				echo "Name of the users table [".USERS_TABLE."]: ";
				$resp = trim(fgets(STDIN));
				$table = (!empty($resp)) ? $resp : 'users';
				if($table !== USERS_TABLE){
					self::update_users_table_config($table);
				}
			}
			if(DBConnect::table_exists($table)){
				DBConnect::drop_table($table);
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
			echo "If you wish to add custom fields to the users table, please enter them below.\n\n";
			self::create_table($table, $columns);
			echo "Users table created.\n";
		}
		
		private static function update_users_table_config($user_table_name){
			// load the file into an array
			$conf = file(CONF_DIR.'general'.EXT);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('USERS_TABLE'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "define('USERS_TABLE', '{$user_table_name}'); // the MySQL table the user info is store in\n";
			}
			define('USERS_TABLE', $user_table_name);
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
			DBConnect::drop_table('users');
		}
	
	}
	
	class DBConnect extends Database{
	
		private static $con;
		
		private static function connect(){
			parent::load_config();
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
		
		private static function close(){
			self::$con = null;
		}
		
		static function table_exists($table){
			$link =& self::connect();
			$qry = $link->prepare("SHOW TABLES LIKE ?");
			$qry->execute(array($table));
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			self::close();
			return (empty($result)) ? false : true;
		}
		
		static function drop_table($table){
			$link =& self::connect();
			$qry = $link->exec(sprintf("DROP TABLE IF EXISTS `%s`", $table));
			self::close();
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
				if($info['key'] !== 'primary'){
					if(!empty($info['default'])){
						$default = sprintf(" DEFAULT '%s'", $info['default']);
					}elseif($info['null'] == true){
						$default = " DEFAULT NULL";
					}
					$null = ($info['null'] == false) ? ' NOT NULL' : '';
				}
				$sql .= sprintf("`%s` %s%s%s %s,",
					$name, $type, $null, $default, strtoupper($info['extra']));
			}
			foreach($keys as $field => $type){
				$type = ($type === true) ? 'KEY' : strtoupper($type).' KEY';
				$sql .= $type.' ';
				if($type != 'primary') $sql .= sprintf("`%s` ", $field);
				$sql .= sprintf("(`%s`),", $field);
			}
			$sql = substr($sql, 0, strlen($sql) -1);
			$sql .= ')';
			$link->exec($sql);
			self::close();
		}
		
		public static function insert($table, $info){
			$link =& self::connect();
			foreach($info as $key => $value){
				$fields .= $key.', ';
				$values .= ':'.$key.', ';
			}
			$fields = substr($fields, 0, -2);
			$values = substr($values, 0, -2);
			$qry = $link->prepare(sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields, $values));
			$qry->execute($info);
			self::close();
		}
	
	}