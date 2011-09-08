<?php

	class Database extends Tea{
	
		private static $env;
		public static $config = array();
		private static $db;
		
		function __construct(){
			global $environment;
			self::$env = strtolower(substr($environment, 0, 3));
			self::$db = parent::db();
			global $users_table;
			self::$config['users_table'] = $users_table;
			self::$config['migrations_table'] = include(TEA_CONFIG.'migrations'.EXT);
		}
		
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
				// stop calls to private methods
				$check = new ReflectionMethod(__CLASS__, $arg[2]);
				if(!$check->isPrivate()){
					self::$arg[2]();
				}else{
					echo "Error: Call to private method.\n";
					exit(0);
				}
			}
		}
		
		public static function init(){
			if(DB_HOST === ''){
				echo "It seems your database config is empty.\nPlease edit /content/_config/environments.php\n";
				exit(0);
			}
			echo 'MySQL Host: '.DB_HOST."\n";
			echo 'MySQL User: '.DB_USER."\n";
			echo 'MySQL Pass: '.DB_PASS."\n";
			echo 'MySQL Database: '.DB."\n";
			echo "Is this information correct? [y/n]: ";
			$resp = trim(fgets(STDIN));
			if(strtolower($resp) !== 'y'){
				echo "Please edit /content/_config/environments.php\n";
				exit(0);
			}
			// users table
			do{
				echo "Setup users table? [y/n] ";
				$resp = trim(fgets(STDIN));
			}while(!preg_match('/[y|n]/', strtolower($resp)));
			if(strtolower($resp) === 'y'){
				$setup_users_table = true;
				echo 'Users table name ['.self::$config['users_table'].']: ';
				$resp = trim(fgets(STDIN));
				$user_table_name = (!empty($resp)) ? $resp : self::$config['users_table'];
				// rewrite /content/_config/general.php config file with user table name
				if($user_table_name !== self::$config['users_table']){
					self::update_users_table_config($user_table_name);
				}
			}
			// create user table
			if($setup_users_table){
				self::create_users_table($user_table_name);				
				// set up an admin user?
				echo "Add an admin user? [y/n]: ";
				$resp = trim(fgets(STDIN));
				if(strtolower($resp) === 'y'){
					User::add();
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
					}elseif(self::$db->table_exists($table)){
						echo "\tError: The table {$table} already exists.\n";
					}elseif(!empty($table)){
						self::create_table($table);
					}
				}while(!$exit);
			}
			
			// Migrations
			echo "Set up migrations? [y/n]: ";
			if(strtolower(trim(fgets(STDIN))) === 'y'){
				Migrations::init();
			}
			
			echo "Database setup.\n";
		}
		
		public static function create_table($table_name = null, $columns = array()){
			if(self::$db->table_exists($table_name)){
				echo "\tError: Table '{$table_name}' already exits.\n";
				exit(0);
			}
			if(is_null($table_name)){
				do{
					echo "Table Name: ";
					$table_name = trim(fgets(STDIN));
					if(self::$db->table_exists($table_name)){
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
							if(preg_match('/(primary|unique|key)/', $key) || empty($key)) $pass = true;
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
			self::$db->create_table($table_name, $columns);
			echo "Table {$table_name} created.\n";
		}
		
		public static function create_users_table($table = null){
			if(is_null($table)){
				echo "Name of the users table [".self::$config['users_table']."]: ";
				$resp = trim(fgets(STDIN));
				$table = (!empty($resp)) ? $resp : self::$config['users_table'];
				if($table !== self::$config['users_table']){
					self::update_users_table_config($table);
				}
			}
			if(self::$db->table_exists($table)){
				self::$db->drop_table($table);
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
			$conf = file(CONF_FILE);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('USERS_TABLE'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "\t\tdefine('USERS_TABLE', '".self::$config['users_table']."'); // the MySQL table the user info is store in\n";
			}
			self::$config['users_table'] = $user_table_name;
			// delete config file
			unlink(CONF_FILE);
			// create new file
			$fp = fopen(CONF_FILE, 'c');
			// write config file
			foreach($conf as $l){
				fwrite($fp, $l);
			}
			// close file
			fclose($fp);
		}
		
		private function get_db_tables(){
			$sql = sprintf("SHOW TABLES FROM `%s`", DB);
			$tmp_tables = self::$db->query($sql, true);
			$tables = array();
			foreach($tmp_tables as $t){
				$table = $t['Tables_in_'.DB];
				if($table != self::$config['migrations_table']) $tables[] = $table;
			}
			return $tables;
		}
		
		/**
		 * 
		 */
		
		public static function add_column(){
			// add column
			$tables = self::get_db_tables();
			foreach($tables as $index => $table){
				echo "{$index}: {$table}\n";
			}
			do{
				echo "Which table would you like to add the column to?: ";
				$table = $tables[trim(fgets(STDIN))];
			}while(empty($table));
			
				// which table?
				// insert after what column?
				// name of column?
				// type, length, default of column
			
			// if migrations are setup, generate migration
			if(!empty(self::$config['migrations_table'])){
				
				// add migration to database
				
			}
		}
	
	}