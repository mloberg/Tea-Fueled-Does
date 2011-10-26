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
			'i' => 'init',
			'c' => 'create_table_prompt',
			'd' => 'drop_table_prompt',
			'a' => 'add_column_prompt'
		);
		private static $aliases = array(
			'create-table' => 'create_table_prompt',
			'drop-table' => 'drop_table_prompt',
			'add-columns' => 'add_column_prompt',
			'add-column' => 'add_column_prompt'
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
			
			if(isset(self::$aliases[$run])) $run = self::$aliases[$run];
			
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

	-h, --help            This page
	-i, --init            Setup the database
	-c, --create-table    Create a new table

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
		
		public static function scan_db(){
			$tables = self::list_tables();
			$db = array();
			foreach($tables as $table){
				$db[$table] = self::list_columns($table);
			}
			return $db;
		}
		
		public static function create_table($table, $columns = array()){
			if(empty($columns)){
				echo "Columns is empty. Exiting...\n";
				exit(0);
			}
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
				}
				return false;
			}catch(\TFD\Exception $e){
				return false;
			}
		}
		
		public static function drop_table($table){
			if(!self::table_exists($table)){
				echo "{$table} does not exist! Exiting...";
				exit(0);
			}
			$query = "DROP TABLE `{$table}`";
			try{
				if(MySQL::query($query)){
					return true;
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
					$exit = false;
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
					self::create_table($table, $columns);
				}
			}
			
			// create other tables
			do{
				if(Tea::yes_no("Add a table?")){
					self::create_table_prompt();
				}else{
					$exit = true;
				}
			}while($exit !== true);
			
			echo "Database setup.\n";
		}
		
		protected static function create_table_prompt($table = null, $columns = array()){
			// table name
			if(empty($table)){
				do{
					echo "Table name: ";
					$table = Tea::response();
					if(self::table_exists($table)){
						$table = '';
						echo "\033[1;31mError:\033[0m Table exists!";
					}
				}while(empty($table));
			}elseif(self::table_exists($table)){
				echo "\033[1;31mError:\033[0m Table exists!";
				exit(0);
			}
			// columns
			$columns = self::add_columns_prompt($columns);
			
			// generate migration file?
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [{$table}]: ";
				$name = Migrations::name_response($table);
				
				$col_str = var_export($columns, true);
				$up = "Database::create_table('{$table}', {$col_str});";
				$down = "Database::drop_table('{$table}');";
				$number = Migrations::create_migration($name, $up, $down);
			}
			self::create_table($table, $columns);
			echo "Created table {$table}.\n";
		}
		
		protected static function drop_table_prompt($table = null){
			if(empty($table)){
				$tables = self::list_tables();
				if(empty($tables)){
					echo "No tables. Exiting...\n";
					exit(0);
				}
				echo "Tables:\n";
				foreach($tables as $index => $value){
					echo "  {$index}: {$value}\n";
				}
				echo "Which table would you like to drop? ";
				do{
					$resp = Tea::response();
					if(isset($tables[$resp])){
						$table = $tables[$resp];
					}else{
						echo "That's not a valid selection: ";
					}
				}while(empty($table));
			}elseif(!self::table_exists($table)){
				echo "Not a valid table! Exiting...\n";
				exit(0);
			}
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [Drop{$table}]: ";
				$name = Migrations::name_response('Drop'.$table);
				
				$columns = self::list_columns($table);
				$col_str = var_export($columns, true);
				
				$up = "Database::drop_table('{$table}');";
				$down = "Database::create_table('{$table}', {$col_str});";
				$number = Migrations::create_migration($name, $up, $down);
			}
			self::drop_table($table);
			echo "Dropped table {$table}.\n";
		}
		
		public static function add_column_prompt($table = null){
			if(empty($table)){
				$tables = self::list_tables();
				if(empty($tables)){
					echo "No tables! Exiting...\n";
					exit(0);
				}
				echo "Tables:\n";
				foreach($tables as $index => $value){
					echo "  {$index}: {$value}\n";
				}
				echo "Which table would you like to add columns to? ";
				do{
					$resp = Tea::response();
					if(isset($tables[$resp])){
						$table = $tables[$resp];
					}else{
						echo "That's not a valid selection: ";
					}
				}while(empty($table));
			}elseif(!self::table_exists($table)){
				echo "Table does not exist! Exiting...\n";
				exit(0);
			}
			
			$original_columns = self::list_columns($table);
			$columns = self::add_columns_prompt($original_columns);
			
			foreach($original_columns as $k => $v){
				unset($columns[$k]);
			}
			
			print_r($columns);
			
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [{$table}Cols]: ";
				$name = Migrations::name_response($table.'Cols');
				
				
			}
		}
		
		/**
		 * 
		 */
		
/*
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
*/
		
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