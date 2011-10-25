<?php namespace TFD\Tea;

	use TFD\Config;
	use TFD\DB\MySQL;
	use TFD\Tea\Config as General;
	
	class Database{
	
		private static $env;
		public static $config = array();
		private static $db;
		
		private static $commands = array(
			'h' => 'help',
			'i' => 'init'
		);
		
		public static function action($arg){
			if(empty($arg)) self::help();
			
			if(preg_match('/^\-\-([\w|\-]+)(.+)?/', $arg, $match)){
				$run = $match[1];
				$args = trim($match[2]);
			}elseif(preg_match('/^\-(\w)(.+)?/', $arg, $match)){
				$run = self::$commands[$match[1]];
				$args = trim($match[2]);
			}elseif(preg_match('/([\w|\-]+)(.+)?/', $arg, $match)){
				$run = $match[1];
				$args = trim($match[2]);
			}
			
			if(!method_exists(__CLASS__, $run) || (($method = new \ReflectionMethod(__CLASS__, $run)) && $method->isPrivate())){
				echo "\033[0;31mError:\033[0m '{$arg}' is not a valid argument!\n";
				exit(0);
			}else{
				self::$run($args);
			}
		}
		
		public static function help(){
			echo <<<MAN
Interact with a database.

	Usage: tea database <args>

Arguments:

	-h, help            This page

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
			exit(0);
		}
		
		/**
		 * Database Methods
		 */
		
		public static function table_exists($table){
			$tables = MySQL::query("SHOW TABLES LIKE :table", array('table' => (string)$table), true);
			return (empty($tables)) ? false : true;
		}
		
		public static function list_tables(){
			$t = MySQL::query("SHOW TABLES", array(), true);
			$tables = array();
			foreach($t as $table){
				$v = array_values($table);
				$tables[] = $v[0];
			}
			return $tables;
		}
		
		public static function list_columns($table){
			try{
				$fields = MySQL::query("SHOW FIELDS FROM `{$table}`", array(), true);
			}catch(\TFD\Exception $e){
				echo $e->getMessage();
				echo MySQL::last_query();
			}
			$keys = array(
				'PRI' => 'primary key',
				'UNI' => 'unique key',
				'MUL' => 'key'
			);
			$columns = array();
			foreach($fields as $field){
				preg_match('/(\w+)(\((.+)\))?/', $field['Type'], $match);
				$type = $match[1];
				$length = (isset($match[3])) ? $match[3] : false;
				$columns[$field['Field']] = array(
					'type' => $type,
					'length' => $length,
					'null' => ($field['Null'] == 'YES') ? true : false,
					'default' => ($field['Default'] === null) ? false : $field['Default'],
					'extra' => $field['Extra'],
					'key' => $keys[$field['Key']]
				);
			}
			return $columns;
		}
		
		public static function test(){
			$files = glob(CONTENT_DIR.'migrations/*.php');
			$sort = array();
			foreach($files as $file){
				if(preg_match('/(\d+)'.preg_quote(EXT).'/', $file, $match)){
					$sort[$match[1]] = $file;
				}
			}
			echo max(array_keys($sort));
		}
		
		public static function scan_db(){
			$tables = self::list_tables();
			$db = array();
			foreach($tables as $table){
				$db[$table] = self::list_columns($table);
			}
			print_r($db);
			return $db;
		}
		
		public static function create_table($table, $columns = array()){
			$query = "CREATE TABLE `{$table}` (";
			$keys = array();
			foreach($columns as $name => $info){
				$query .= "`{$name}` ";
				// get type
				if($info['length'] === false || empty($info['length'])){
					$query .= $info['type'].' ';
				}else{
					$query .= "{$info['type']}({$info['length']}) ";
				}
				
				if($info['null'] === true && $info['default'] === false){
					$query .= "DEFAULT NULL ";
				}elseif($info['null'] === true && $info['type'] == 'timestamp'){
					$query .= "DEFAULT CURRENT_TIMESTAMP ";
				}elseif($info['null'] === true){
					$query .= "DEFAULT '{$info['default']}' ";
				}elseif($info['null'] === false && $info['default'] === false){
					$query .= "NOT NULL ";
				}elseif($info['null'] === false && $info['type'] == 'timestamp'){
					$query .= "NOT NULL DEFAULT CURRENT_TIMESTAMP ";
				}elseif($info['null'] == false){
					$query .= "NOT NULL DEFAULT '{$info['default']}' ";
				}
				
				$query .= strtoupper($info['extra']).',';
				
				// if there is a key, save it to the key array for later
				if(!empty($info['key'])){
					$keys[$name] = $info['key'];
				}
			}
			// add the keys to the query
			foreach($keys as $name => $type){
				$query .= strtoupper($type);
				if($type !== 'primary key'){
					$query .= " `{$name}`";
				}
				$query .= " (`{$name}`),";
			}
			$query = substr($query, 0, -1).')';
			try{
				if(MySQL::query($query)){
					return true;
					echo "{$table} created!";
				}
				return false;
			}catch(\TFD\Exception $e){
				return false;
			}
		}
		
		private static function add_columns_prompt($columns = array()){
			if(empty($columns['id'])){
				if(Tea::yes_no('Create an id column?')){
					$columns['id'] = array(
						'type' => 'int',
						'length' => 11,
						'null' => false,
						'default' => false,
						'extra' => 'auto_increment',
						'key' => 'primary key'
					);
				}
			}
			do{
				$exit = false;
				echo "Field name ('q' when done): ";
				$field = Tea::response();
				if($field == 'q'){
					$exit = true;
				}elseif(array_key_exists($field, $columns)){
					echo "\033[0;31mError:\033[0m Field exists!\n";
				}elseif(!empty($field)){
					// should get a list and make them a class variable
					$default_types = array(
						'varchar', 'int', 'text',
						'timestamp', 'enum'
					);
					// same with this one
					$default_values = array(
						'varchar' => 128,
						'int' => 11,
						'text' => false,
						'timestamp' => false
					);
					echo "Field types:\n";
					foreach($default_types as $index => $type){
						echo "\t{$index}:  {$type}\n";
					}
					echo "Enter a number above or another [valid] field type.\nField type: ";
					$type = Tea::response();
					$type = (isset($default_types[$type])) ? $default_types[$type] : $type;
					
					if($default_values[$type] !== false && isset($default_values[$type])){
						// get the default false
						echo "Length: [{$default_values[$type]}] ";
						$length = Tea::response($default_values[$type]);
					}elseif(!isset($default_values[$type])){
						echo "Length (FALSE for none): ";
						$length = Tea::response();
						if($length == 'FALSE') $length = false;
					}
					
					$null = Tea::yes_no('Allow NULL?');
					
					echo "Default value (NULL for none): ";
					$default = Tea::response();
					if($default == 'NULL'){
						$null = true;
						$default = false;
					}
					
					$key_types = array('primary key', 'unique key', 'key');
					foreach($key_types as $index => $key){
						echo "\t{$index}: {$key}\n";
					}
					do{
						echo "Choose an index type (or blank for none): ";
						$response = Tea::response();
						if(empty($response)){
							$key = '';
							$exit = true;
						}elseif(isset($key_types[$response])){
							$key = $key_types[$response];
							$exit = true;
						}
					}while(!$exit);
					
					echo "Extra: ";
					$extra = Tea::response_to_upper();
					
					$columns[$field] = array(
						'type' => $type,
						'length' => $length,
						'null' => $null,
						'default' => $default,
						'extra' => $extra,
						'key' => $key
					);
				}
			}while(!$exit);
			
			return $columns;
		}
		
		/**
		 * Class Methods
		 */
		
		protected static function init(){
			// if no database information was loaded, exit
			if(!Config::is_set('mysql.host')){
				echo "Empty database config. Exiting...\n";
				exit(0);
			}
			
			// check for user table
			if(!self::table_exists(Config::get('admin.table'))){
				if(Tea::yes_no('Setup user table?')){
					echo 'Table name ['.Config::get('admin.table').']: ';
					$table = Tea::response(Config::get('admin.table'));
					if($table !== Config::get('admin.table')){
						General::user_table($table);
					}
					// default columns
					$columns = array(
						'id' => array(
							'type' => 'int',
							'length' => 11,
							'null' => false,
							'default' => false,
							'extra' => 'auto_increment',
							'key' => 'primary'
						),
						'username' => array(
							'type' => 'varchar',
							'length' => 128,
							'null' => false,
							'default' => false,
							'extra' => '',
							'key' => 'unique'
						),
						'hash' => array(
							'type' => 'varchar',
							'length' => 1024,
							'null' => false,
							'default' => false,
							'extra' => '',
							'key' => ''
						),
						'secret' => array(
							'type' => 'varchar',
							'length' => 1024,
							'null' => false,
							'default' => '',
							'extra' => '',
							'key' => ''
						)
					);
					if(Tea::yes_no('Add custom fields to the table?')){
						$columns = self::add_columns_prompt($columns);
					}
					
					// create table
					
				}
			}
			
			exit(0);
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
				
		private static function create_users_table($table = null){
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
			// replace it
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
		
		private static function get_db_tables(){
			$sql = sprintf("SHOW TABLES FROM `%s`", DB);
			$tmp_tables = self::$db->query($sql, true);
			$tables = array();
			foreach($tmp_tables as $t){
				$table = $t['Tables_in_'.DB];
				if($table != self::$config['migrations_table']) $tables[] = $table;
			}
			return $tables;
		}
		
		private static function get_table_fields($table){
			$sql = sprintf("SHOW FIELDS FROM `%s`", $table);
			$tmp_fields = self::$db->query($sql, true);
			$fields = array();
			foreach($tmp_fields as $f){
				$fields[] = $f['Field'];
			}
			return $fields;
		}
		
		/**
		 * 
		 */
		
/*
		public static function create_table($table_name = null, $columns = array()){
			if(is_null($table_name)){
				do{
					echo "Table Name: ";
					$table_name = trim(fgets(STDIN));
					if(self::$db->table_exists($table_name)){
						echo "\tError: Table '{$table_name}' already exists.\n";
						$table_name = '';
					}
				}while(empty($table_name));
			}elseif(self::$db->table_exists($table_name)){
				echo "\tError: Table '{$table_name}' already exits.\n";
				exit(0);
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
			if(!empty(self::$config['migrations_table'])){
				echo "Create migration? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					$col_str = var_export($columns, true);
					$up = "parent::\$db->create_table('{$table_name}', {$col_str});\n";
					$down = "parent::\$db->drop_table('{$table_name}');\n";
					Migrations::generate_migration_file($up, $down, true);
				}
			}
			echo "Table {$table_name} created.\n";
		}
*/
		
		public function drop_table($table = null){
			if(is_null($table)){
				$tables = self::get_db_tables();
				foreach($tables as $index => $t){
					echo "{$index}: {$t}\n";
				}
				$max = max(array_keys($tables));
				$min = min(array_keys($tables));
				do{
					echo "Which table would you like to drop? [{$min} - {$max}]:";
					$table = $tables[trim(fgets(STDIN))];
				}while(empty($table));
			}
			if(!empty(self::$config['migrations_table'])){
				echo "Create migration? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					// get table info for down
					$sql = sprintf("SHOW FIELDS FROM `%s`", $table);
					$cols = self::$db->query($sql, true);
					$columns = array();
					$keys = array(
						'PRI' => 'primary',
						'UNI' => 'unique',
						'MUL' => 'index'
					);
					foreach($cols as $c){
						preg_match('/\((\d+)\)/', $c['Type'], $match);
						$type = str_replace(array($match[0], 'unsigned'), '', $c['Type']);
						$null = ($c['Null'] === 'NO') ? 'false' : 'true';
						$columns[$c['Field']] = array(
							'type' => $type,
							'length' => $match[1],
							'null' => $null,
							'default' => (is_null($c['Default'])) ? '' : $c['Default'],
							'extra' => $c['Extra'],
							'key' => (!empty($keys[$c['Key']])) ? $keys[$c['Key']] : ''
						);
					}
					$col_str = var_export($columns, true);
					$up = "parent::\$db->drop_table('{$table}');\n";
					$down = "parent::\$db->create_table('{$table}', {$col_str});\n";
					Migrations::generate_migration_file($up, $down, true);
				}
			}
			// drop table
			self::$db->drop_table($table);
			echo "Dropped table {$table}.\n";
		}
		
		public static function add_column(){
			// which table?
			$tables = self::get_db_tables();
			foreach($tables as $index => $table){
				echo "{$index}: {$table}\n";
			}
			do{
				echo "Which table would you like to add the column to? ";
				$table = $tables[trim(fgets(STDIN))];
			}while(empty($table));
			// after what column?
			$fields = self::get_table_fields($table);
			foreach($fields as $index => $field){
				echo "{$index}: {$field}\n";
			}
			do{
				echo "What field would you like to add it after? ";
				$after = $fields[trim(fgets(STDIN))];
			}while(empty($after));
			// name of column
			do{
				echo "Name of field? ";
				$column = trim(fgets(STDIN));
				if(array_search($column, $fields)) $column = '';
			}while(empty($column));
			// column info
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
				$info = array(
					'type' => $type,
					'length' => $length,
					'null' => $null,
					'default' => $default_val,
					'extra' => $extra,
					'key' => $key
				);
				self::$db->add_column($table, $column, $info, $after);
			}
			// if migrations are setup, generate migration
			if(!empty(self::$config['migrations_table'])){
				echo "Create migration? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					$col_str = var_export($info, true);
					$up = "parent::\$db->add_column('{$table}', '{$column}', {$col_str}, '{$after}');\n";
					$down = "parent::\$db->drop_column('{$table}', '{$column}');\n";
					Migrations::generate_migration_file($up, $down, true);
				}
			}
			echo "Column {$column} added to table {$table}.\n";
		}
		
		public static function drop_column($table = null, $col = null){
			if(is_null($column) || is_null($table)){
				if(is_null($table)){
					$tables = self::get_db_tables();
					foreach($tables as $index => $t){
						echo "{$index}: {$t}\n";
					}
					$max = max(array_keys($tables));
					$min = min(array_keys($tables));
					do{
						echo "Which table would you like to drop? [{$min} - {$max}]: ";
						$table = $tables[trim(fgets(STDIN))];
					}while(empty($table));
				}
				$cols = self::get_table_fields($table);
				foreach($cols as $index => $c){
					echo "{$index}: {$c}\n";
				}
				$max = max(array_keys($cols));
				$min = min(array_keys($cols));
				do{
					echo "Which column would you like to drop? [{$min} - {$max}]: ";
					$resp = trim(fgets(STDIN));
					$after = $cols[$resp - 1];
					$col = $cols[$resp];
				}while(empty($col));
			}
			// if migrations are setup, generate migration
			if(!empty(self::$config['migrations_table'])){
				echo "Create migration? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					$sql = sprintf("SHOW FIELDS FROM `%s` WHERE `Field` = '%s'", $table, $col);
					$col_info = self::$db->query($sql, true);
					$col_info = $col_info[0];
					$keys = array(
						'PRI' => 'primary',
						'UNI' => 'unique',
						'MUL' => 'index'
					);
					preg_match('/\((\d+)\)/', $col_info['Type'], $match);
					$type = str_replace(array($match[0], 'unsigned'), '', $col_info['Type']);
					$null = ($col_info['Null'] === 'NO') ? 'false' : 'true';
					$info = array(
						'type' => $type,
						'length' => $match[1],
						'null' => $null,
						'default' => $col_info['Default'],
						'extra' => $col_info['Extra'],
						'key' => $keys[$col_info['Key']]
					);
					$col_str = var_export($info, true);
					$up = "parent::\$db->drop_column('{$table}', '{$col}');\n";
					$down = "parent::\$db->add_column('{$table}', '{$col}', {$col_str}, '{$after}');\n";
					Migrations::generate_migration_file($up, $down, true);
				}
			}
			// drop column
			self::$db->drop_column($table, $col);
		}
		
		public static function add_key($table = null, $col = null, $type = null){
			if(is_null($column) || is_null($table)){
				if(is_null($table)){
					$tables = self::get_db_tables();
					foreach($tables as $index => $t){
						echo "{$index}: {$t}\n";
					}
					$max = max(array_keys($tables));
					$min = min(array_keys($tables));
					do{
						echo "Which table would you like to add the key to? [{$min} - {$max}]: ";
						$table = $tables[trim(fgets(STDIN))];
					}while(empty($table));
				}
				if(is_null($col)){
					$cols = self::get_table_fields($table);
					foreach($cols as $index => $c){
						echo "{$index}: {$c}\n";
					}
					$max = max(array_keys($cols));
					$min = min(array_keys($cols));
					do{
						echo "Which column would you like to add the key to? [{$min} - {$max}]: ";
						$resp = trim(fgets(STDIN));
						$col = $cols[$resp];
					}while(empty($col));
				}
			}
			if(is_null($type)){
				echo "Key type (primary, unique, index): ";
				$type = trim(fgets(STDIN));
			}
			if(!empty(self::$config['migrations_table'])){
				echo "Create migration? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					$up = "parent::\$db->add_key('{$table}', '{$col}', '{$type}');\n";
					$down = "parent::\$db->remove_key('{$table}', '{$col}');\n";
					Migrations::generate_migration_file($up, $down, true);
				}
			}
			self::$db->add_key($table, $col, $type);
		}
		
		public static function remove_key($table = null, $col = null){
			if(is_null($column) || is_null($table)){
				if(is_null($table)){
					$tables = self::get_db_tables();
					foreach($tables as $index => $t){
						echo "{$index}: {$t}\n";
					}
					$max = max(array_keys($tables));
					$min = min(array_keys($tables));
					do{
						echo "Which table would you like to add the key to? [{$min} - {$max}]: ";
						$table = $tables[trim(fgets(STDIN))];
					}while(empty($table));
				}
				if(is_null($col)){
					$cols = self::get_table_fields($table);
					foreach($cols as $index => $c){
						echo "{$index}: {$c}\n";
					}
					$max = max(array_keys($cols));
					$min = min(array_keys($cols));
					do{
						echo "Which column would you remove the key from? [{$min} - {$max}]: ";
						$resp = trim(fgets(STDIN));
						$col = $cols[$resp];
					}while(empty($col));
				}
			}
			if(!empty(self::$config['migrations_table'])){
				echo "Create migration? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					// get key type
					$sql = sprintf("SHOW FIELDS FROM `%s` WHERE `Field` = '%s'", mysql_real_escape_string($table), mysql_real_escape_string($column));
					$col_info = self::$db->query($sql, true);
					switch($col_info[0]['Key']){
						case 'UNI':
							$type = 'unique';
						case 'PRI':
							$type = 'primary';
						default:
							$type = 'index';
					}
					$up = "parent::\$db->remove_key('{$table}', '{$col}');\n";
					$down = "parent::\$db->add_key('{$table}', '{$col}', '{$type}');\n";
					Migrations::generate_migration_file($up, $down, true);
				}
			}
			self::$db->remove_key($table, $col);
		}
	
	}