<?php namespace TFD\Tea;

	use TFD\Config;
	use TFD\DB\MySQL;
	use TFD\Tea\Config as General;
	
	class Database{
	
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
			'add-column' => 'add_column_prompt',
			'drop-columns' => 'drop_column_prompt',
			'drop-column' => 'drop_column_prompt',
			'add-key' => 'add_key_prompt',
			'drop-key' => 'drop_key_prompt'
		);
		
		private static $field_types = array(
			'varchar', 'int', 'text', 'timestamp', 'enum',
			'float', 'double', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal',
			'tinytext', 'mediumtext', 'longtext', 'bit', 'char',
			'date', 'datetime', 'time', 'year'
		);
		private static $default_types = array(
			'varchar' => 128,
			'int' => 11,
			'text' => false,
			'timestamp' => false,
			'enum' => "'option 1', 'option 2'",
			'float' => false,
			'double' => false,
			'tinyint' => 4,
			'smallint' => 6,
			'mediumint' => 9,
			'bigint' => 20,
			'decimal' => '10,0',
			'tinytext' => false,
			'mediumtext' => false,
			'longtext' => false,
			'bit' => 1,
			'char' => 1,
			'date' => false,
			'datetime' => false,
			'time' => false,
			'year' => 4
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
	-d, --drop-table      Drop a table
	-a, --add-column      Add a column(s)
	--drop-columns        Drop a column(s)
	--add-key             Add a key to a column
	--drop-key            Drop a key from a column

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
			}catch(\Exception $e){
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
				echo "Columns are empty. Exiting...\n";
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
			}catch(\Exception $e){
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
			}catch(\Exception $e){
				return false;
			}
		}
		
		public static function add_columns($table, $columns){
			if(!self::table_exists($table)){
				echo "{$table} does not exist! Exiting...\n";
				exit(0);
			}
			$keys = array();
			$query = "ALTER TABLE `{$table}`";
			foreach($columns as $name => $info){
				$query .= " ADD COLUMN `{$name}` ";
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
			if(!empty($keys)){
				foreach($keys as $name => $type){
					$query .= sprintf(" ADD %s (`%s`),", strtoupper($type), $name);
				}
			}
			try{
				MySQL::query(substr($query, 0, -1));
				echo "Columns added!\n";
			}catch(\Exception $e){
				echo "Could not add columns. Error: {$e->getMessage()}\n";
				exit(0);
			}
		}
		
		public static function drop_columns($table, $columns){
			if(!self::table_exists($table)){
				echo "{$table} does not exist! Exiting...\n";
				exit(0);
			}
			$query = "ALTER TABLE `{$table}`";
			foreach($columns as $column){
				$query .= " DROP `{$column}`,";
			}
			try{
				MySQL::query(substr($query, 0, -1));
				echo "Columns dropped!\n";
			}catch(\Exception $e){
				echo "Could not drop columns. Error: {$e->getMessage()}\n";
				exit(0);
			}
		}
		
		public static function add_key($table, $column, $type){
			if(!self::table_exists($table)){
				echo "{$table} does not exist! Exiting...\n";
				exit(0);
			}
			$query = sprintf("ALTER TABLE `%s` ADD %s (`%s`)", $table, strtoupper($type), $column);
			try{
				MySQL::query($query);
			}catch(\Exception $e){
				echo "Could not add key. Error: {$e->getMessage()}\n";
				exit(0);
			}
		}
		
		public static function drop_key($table, $column){
			if(!self::table_exists($table)){
				echo "{$table} does not exist! Exiting...\n";
				exit(0);
			}
			$query = sprintf("ALTER TABLE `%s` DROP KEY `%s`", $table, $column);
			try{
				MySQL::query($query);
			}catch(\Exception $e){
				echo "Could not drop key. Error: {$e->getMessage()}\n";
				exit(0);
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
					echo "Field types:\n";
					foreach(self::$field_types as $index => $type){
						echo "\t{$index}:  {$type}\n";
					}
					do{
						echo "Field type. Enter a number above: ";
						$type = Tea::response();
						$type = (isset($default_types[$type])) ? $default_types[$type] : null;
					}while(is_null($type));
					
					if(self::$default_values[$type] !== false && isset(self::$default_values[$type])){
						// get the default false
						$default_length = self::$default_values[$type];
						echo "Length: [{$default_length}] ";
						$length = Tea::response($default_values[$type]);
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
		
		public static function init(){
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
		
		public static function create_table_prompt($table = null, $columns = array()){
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
		
		public static function drop_table_prompt($table = null){
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
			echo "Current columns:\n";
			foreach($original_columns as $name => $info){
				echo "  - {$name}\n";
			}
			$columns = self::add_columns_prompt($original_columns);
			
			foreach($original_columns as $k => $v){
				unset($columns[$k]);
			}
			
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [{$table}Cols]: ";
				$name = Migrations::name_response($table.'Cols');
				
				$col_str = var_export($columns, true);
				$col_down = var_export(array_keys($columns), true);
				
				$up = "Database::add_columns('{$table}', {$col_str});";
				$down = "Database::drop_columns('{$table}', {$col_down});";
				$number = Migrations::create_migration($name, $up, $down);
			}
			
			self::add_columns($table, $columns);
		}
		
		public static function drop_column_prompt($table = null){
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
			$columns = array_keys($original_columns);
			$drop = array();
			
			echo "Columns:\n";
			foreach($columns as $index => $name){
				echo "  {$index}: {$name}\n";
			}
			do{
				echo "Which column would you like to drop? ('q' when done): ";
				$resp = Tea::response();
				if($resp == 'q'){
					$exit = true;
				}elseif(!isset($columns[$resp])){
					echo "Not a valid selection!\n";
				}else{
					$drop[] = $columns[$resp];
					unset($columns[$resp]);
				}
				if(empty($columns)) $exit = true;
			}while($exit !== true);
			
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [{$table}DropCols]: ";
				$name = Migrations::name_response($table.'DropCols');
				
				$up = var_export($drop, true);
				
				$down = array();
				foreach($drop as $col){
					$down[$col] = $original_columns[$col];
				}
				$down = var_export($down, true);
				
				$up = "Database::drop_columns('{$table}', {$up});";
				$down = "Database::add_columns('{$table}', {$down});";
				$number = Migrations::create_migration($name, $up, $down);
			}
			
			self::drop_columns($table, $drop);
		}
		
		public static function add_key_prompt($table = null){
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
				echo "Which table would you like to add a key to? ";
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
			$columns = array_keys($original_columns);
			
			echo "Columns:\n";
			foreach($columns as $index => $name){
				echo "  {$index}: {$name}\n";
			}
			do{
				echo "Which column would you like to add the key to?: ";
				$resp = Tea::response();
				if(!isset($columns[$resp])){
					echo "Not a valid selection!\n";
				}else{
					$col = $columns[$resp];
				}
			}while(empty($col));
			
			$keys = array('primary key', 'unique key', 'key');
			echo "Key type:\n";
			foreach($keys as $i => $v){
				echo "  {$i}: {$v}\n";
			}
			do{
				echo "Key type: ";
				$key = $keys[Tea::response()];
			}while(empty($key));
			
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [{$table}Key]: ";
				$name = Migrations::name_response($table.'Key');
				
				$up = "Database::add_key('{$table}', '{$col}', '{$key}');";
				$down = "Database::drop_key('{$table}', '{$col}');";
				$number = Migrations::create_migration($name, $up, $down);
			}
			
			self::add_key($table, $col, $key);
		}
		
		public static function drop_key_prompt($table = null){
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
				echo "Which table would you like to drop a key from? ";
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
			
			$columns = self::list_columns($table);
			$col_keys = array();
			
			echo "Columns:\n";
			$i = 0;
			foreach($columns as $name => $info){
				if(!empty($info['key'])){
					$col_keys[$i] = $name;
					echo "  {$i}: {$name}\n";
					$i++;
				}
			}
			if(empty($col_keys)){
				echo "No keys. Exiting...\n";
				exit(0);
			}
			do{
				echo "Which column would you like to drop the key from?: ";
				$resp = Tea::response();
				if(!isset($col_keys[$resp])){
					echo "Not a valid selection!\n";
				}else{
					$col = $col_keys[$resp];
				}
			}while(empty($col));
			
			if((Config::is_set('migrations.table') && self::table_exists(Config::get('migrations.table'))) && Tea::yes_no('Create migration file?')){
				echo "Migration name [{$table}DropKey]: ";
				$name = Migrations::name_response($table.'DropKey');
				
				$up = "Database::drop_key('{$table}', '{$col}');";
				$down = "Database::add_key('{$table}', '{$col}', '{$columns[$col]['key']}');";
				$number = Migrations::create_migration($name, $up, $down);
			}
			
			self::drop_key($table, $col);
		}
	
	}