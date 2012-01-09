<?php namespace TFD\Tea;

	use TFD\Config;
	use TFD\DB\MySQL;
	use TFD\Tea\Config as General;
	
	class Database{
	
		protected static $field_types = array(
			'varchar', 'int', 'text', 'timestamp', 'enum',
			'float', 'double', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal',
			'tinytext', 'mediumtext', 'longtext', 'bit', 'char',
			'date', 'datetime', 'time', 'year'
		);
		protected static $default_values = array(
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
		
		public static function __flags(){
			return array(
				'h' => 'help',
				'i' => 'init',
				'create-table' => 'add_table',
				'c' => 'add_table',
				'drop-table' => 'remove_table',
				'd' => 'remove_table',
				'add-columns' => 'add_column',
				'add-column' => 'add_column',
				'a' => 'add_column',
				'drop-columns' => 'remove_column',
				'drop-column' => 'remove_column',
				'add-key' => 'add_key',
				'drop-key' => 'remove_key',
				's' => 'seed',
			);
		}
		
		public static function __callStatic($method, $args){
			if(method_exists('\TFD\Tea\Worker', $method)){
				return call_user_func_array('\TFD\Tea\Worker::'.$method, $args);
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
		
		public static function init(){
			if(!Config::is_set('mysql.host')){
				throw new \Exception('Database config is empty');
			}
			
			// check for a user table
			if(!Worker::table_exists(Config::get('admin.table'))){
				if(Tea::yes_no('Setup user table?')){
					echo 'Table name ['.Config::get('admin.table').']: ';
					$table = Tea::response(Config::get('admin.table'));
					if($table !== Config::get('admin.table')){
						General::user_table(array($table));
					}
					// default columns
					$columns = array(
						'id' => array(
							'type' => 'int',
							'length' => 11,
							'null' => false,
							'default' => false,
							'extra' => 'auto_increment',
							'key' => 'primary key'
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
						$columns = Prompt::columns($columns);
					}
					
					// create table
					if(!Worker::create_table($table, $columns)){
						throw new \Exception("Could not create admin table");
					}
				}
			}
			
			// create other tables
			do{
				if(Tea::yes_no("Add a table?")){
					Prompt::add_table(array());
				}else{
					$exit = true;
				}
			}while($exit !== true);

			echo "Database setup\n";
		}

		public static function add_table($arg){ // --create-table > Worker::create_table
			$table = $arg[0];
			if(empty($table)){
				echo "Table name: ";
				$table = Tea::response();
			}
			if(Worker::table_exists($table)){
				throw new \Exception('Table exists');
			}

			$columns = Prompt::columns();

			Migrations::create($table, sprintf("Database::create_table('%s', %s);", $table, var_export($columns, true)), sprintf("Database::drop_table('%s');", $table));

			Worker::create_table($table, $columns);
			echo "Table created.\n";
		}

		public static function remove_table($arg){ // --drop-table > Worker::drop_table
			$table = $arg[0];
			if(empty($table)){
				$tables = Worker::list_tables();
				if(empty($tables)){
					throw new \Exception('No tables');
				}
				echo "Tables:\n";
				foreach($tables as $key => $value){
					echo "\t{$key}: {$value}\n";
				}
				do{
					echo 'Which table would you like to drop? ';
					$table = $tables[Tea::response()];
				}while(empty($table));
			}
			if(!Worker::table_exists($table)){
				throw new \Exception('Table does not exist');
			}

			Migrations::create('Drop'.$table, sprintf("Database::drop_table('%s');", $table), sprintf("Database::create_table('%s', %s);", $table, var_export(Worker::list_columns($table), true)));

			Worker::drop_table($table);
			echo "Table dropped.\n";
		}

		public static function add_column($arg){ // --add-columns > Worker::create_columns
			$table = $arg[0];
			if(empty($table)){
				$tables = Worker::list_tables();
				if(empty($tables)){
					throw new \Exception('No tables');
				}
				echo "Tables:\n";
				foreach($tables as $key => $value){
					echo "\t{$key}: {$value}\n";
				}
				do{
					echo 'Which table would you like to add a column to? ';
					$table = $tables[Tea::response()];
				}while(empty($table));
			}

			if(!Worker::table_exists($table)){
				throw new \Exception('Table does not exist');
			}

			$cols = Worker::list_columns($table);
			echo "Current columns:\n";
			foreach($cols as $name => $info){
				echo "\t- {$name}\n";
			}
			$columns = Prompt::columns($cols);
			foreach($cols as $key => $value){
				unset($columns[$key]);
			}

			Migrations::create($table.'cols', sprintf("Database::create_columns('%s', %s);", $table, var_export($columns, true)), sprintf("Database::drop_columns('%s', %s);", $table, var_export(array_keys($columns), true)));

			Worker::create_columns($table, $columns);
			echo "Columns added.\n";
		}

		public static function remove_column($arg){ // --drop-columns > Worker::drop_columns
			$table = $arg[0];
			if(empty($table)){
				$tables = Worker::list_tables();
				if(empty($tables)){
					throw new \Exception('No tables');
				}
				echo "Tables:\n";
				foreach($tables as $key => $value){
					echo "\t{$key}: {$value}\n";
				}
				do{
					echo 'Which table would you like to drop columns from? ';
					$table = $tables[Tea::response()];
				}while(empty($table));
			}

			if(!Worker::table_exists($table)){
				throw new \Exception('Table does not exist');
			}

			$cols = Worker::list_columns($table);
			$columns = array_keys($cols);
			$drop = array();

			echo "Columns:\n";
			foreach($columns as $key => $value){
				echo "\t{$key}: {$value}\n";
			}
			do{
				echo 'Which column would you like to drop? ("q" when done): ';
				$resp = Tea::response();
				if($resp == 'q'){
					$exit = true;
				}elseif(!isset($columns[$resp])){
					echo "Not a valid selection.\n";
				}else{
					$drop[] = $columns[$resp];
					unset($columns[$resp]);
				}
				if(empty($columns)) $exit = true;
			}while($exit !== true);

			$down = array();
			foreach($drop as $col){
				$down[$col] = $cols[$col];
			}
			Migrations::create($table.'DropCols', sprintf("Database::drop_columns('%s', %s);", $table, var_export($drop, true)), sprintf("Database::add_columns('%s', %s);", $table, var_export($down, true)));

			Worker::drop_columns($table, $drop);
			echo "Columns dropped.\n";
		}

		public static function add_key($arg){ // --add-key > Worker::create_key
			$table = $arg[0];
			if(empty($table)){
				$tables = Worker::list_tables();
				if(empty($tables)){
					throw new \Exception('No tables');
				}
				echo "Tables:\n";
				foreach($tables as $key => $value){
					echo "\t{$key}: {$value}\n";
				}
				do{
					echo 'Which table would you like to add a key to? ';
					$table = $tables[Tea::response()];
				}while(empty($table));
			}

			if(!Worker::table_exists($table)){
				throw new \Exception('Table does not exist');
			}

			$cols = Worker::list_columns($table);
			$columns = array_keys($cols);

			echo "Columns:\n";
			foreach($columns as $key => $value){
				echo "\t{$key}: {$value}\n";
			}
			do{
				echo "Which column would you like to add the key to? ";
				$col = $columns[Tea::response()];
			}while(empty($col));

			$keys = array('primary key', 'unique key', 'key');
			echo "Key types:\n";
			foreach($keys as $index => $value){
				echo "\t{$index}: {$value}\n";
			}
			do{
				echo 'Key type: ';
				$key = $keys[Tea::response()];
			}while(empty($key));

			Migrations::create($table.'Key', sprintf("Database::create_key('%s', '%s', '%s');", $table, $col, $key), sprintf("Database::drop_key('%s', '%s');", $table, $col));

			Worker::create_key($table, $col, $key);
			echo "Key added.\n";
		}

		public static function remove_key($arg){ // --drop-key > Worker::drop_key
			$table = $arg[0];
			$col = $arg[1];
			if(empty($table)){
				$tables = Worker::list_tables();
				if(empty($tables)){
					throw new \Exception('No tables');
				}
				echo "Tables:\n";
				foreach($tables as $key => $value){
					echo "\t{$key}: {$value}\n";
				}
				do{
					echo "Which table would you like to drop the key from? ";
					$table = $tables[Tea::response()];
				}while(empty($table));
			}

			if(!Worker::table_exists($table)){
				throw new \Exception('Table does not exist');
			}

			$columns = array_filter(Worker::list_columns($table), function($var){
				return !empty($var['key']);
			});
			
			if(empty($columns)){
				throw new \Exception('No keys in the table');
			}elseif(!empty($col) && !isset($columns[$col])){
				throw new \Exception('No column with that name');
			}elseif(empty($col)){
				echo "Columns:\n";
				$cols = array_keys($columns);
				foreach($cols as $key => $value){
					echo "\t{$key}: {$value}\n";
				}
				do{
					echo 'Which column would you like to drop the key? ';
					$col = $cols[Tea::response()];
				}while(empty($col));
			}

			Migrations::create($table.'DropKey', sprintf("Database::drop_key('%s', '%s');", $table, $col), sprintf("Database::create_key('%s', '%s', '%s');", $table, $col, $columns[$col]['key']));

			Worker::drop_key($table, $col);
		}

		public static function seed(){
			$seed = include(CONTENT_DIR.'db/seed'.EXT);
			if(!empty($seed)){
				foreach($seed as $table => $fields){
					echo "Seeding {$table}...\n";
					if($fields['clear_data'] === true){
						MySQL::table($table)->delete(true);
						MySQL::query(sprintf("ALTER TABLE `%s` AUTO_INCREMENT = 1", $table));
						unset($fields['clear_data']);
					}
					foreach($fields as $field){
						MySQL::table($table)->insert($field);
					}
				}
				echo "Database seeded.\n";
			}
		}
	
	}
	
	class Worker{
	
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
			$fields = MySQL::query("SHOW FIELDS FROM `{$table}`", array(), true);
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

		public static function scan(){
			$tables = self::list_tables();
			$db = array();
			foreach($tables as $table){
				$db[$table] = self::list_columns($table);
			}
			return $db;
		}
		
		public static function create_table($table, $columns){
			if(empty($columns) || !is_array($columns)){
				throw new \Exception('Columns are empty');
			}
			$query = '';
			$keys = array();
			foreach($columns as $name => $info){
				if($info['length'] === false || empty($info['length'])){
					$type = $info['type'];
				}else{
					$type = sprintf("%s(%s)", $info['type'], $info['length']);
				}
				
				if($info['null'] === true && $info['default'] === false){
					$default = 'DEFAULT NULL';
				}elseif($info['null'] === true && $info['type'] == 'timestamp'){
					$default = 'DEFAULT CURRENT_TIMESTAMP';
				}elseif($info['null'] === true){
					$default = sprintf("DEFAULT '%s'", $info['default']);
				}elseif($info['null'] === false && $info['default'] === false){
					$default = 'NOT NULL';
				}elseif($info['null'] === false && $info['type'] == 'timestamp'){
					$default = 'NOT NULL DEFAULT CURRENT_TIMESTAMP';
				}elseif($info['null'] === false){
					$default = sprintf("NOT NULL DEFAULT '%s'", $info['default']);
				}
				
				$query .= sprintf("`%s` %s %s %s,", $name, $type, $default, strtoupper($info['extra']));
				
				if(!empty($info['key'])){
					$keys[$name] = $info['key'];
				}
			}
			
			foreach($keys as $field => $type){
				$key = ($type !== 'primary key') ? $key = sprintf("`%s`", $field) : '';
				$query .= sprintf("%s %s (`%s`),", strtoupper($type), $key, $field);
			}
			$query = sprintf("CREATE TABLE `%s` (%s)", $table, substr($query, 0, -1));
			
			return MySQL::query($query);
		}

		public static function drop_table($table){
			if(!self::table_exists($table)){
				throw new \Exception('Table does not exist');
			}
			return MySQL::query(sprintf("DROP TABLE `%s`", $table));
		}

		public static function create_columns($table, $columns){
			if(!self::table_exists($table)){
				throw new \Exception('Table does not exist');
			}
			$keys = array();
			$query = sprintf("ALTER TABLE `%s` ", $table);
			foreach($columns as $name => $info){
				if($info['length'] === false || empty($info['length'])){
					$type = $info['type'];
				}else{
					$type = sprintf("%s(%s)", $info['type'], $info['length']);
				}

				if($info['null'] === true && $info['default'] === false){
					$default = 'DEFAULT NULL';
				}elseif($info['null'] === true && $info['type'] == 'timestamp'){
					$default = 'DEFAULT CURRENT_TIMESTAMP';
				}elseif($info['null'] === true){
					$default = sprintf("DEFAULT '%s'", $info['default']);
				}elseif($info['null'] === false && $info['default'] === false){
					$default = 'NOT NULL';
				}elseif($info['null'] === false && $info['type'] == 'timestamp'){
					$default = 'NOT NULL DEFAULT CURRENT_TIMESTAMP';
				}elseif($info['null'] === false){
					$default = sprintf("NOT NULL DEFAULT '%s'", $info['default']);
				}

				$query .= sprintf("ADD COLUMN `%s` %s %s %s,", $name, $type, $default, strtoupper($info['extra']));

				if(!empty($info['key'])){
					$keys[$name] = $info['key'];
				}
			}

			if(!empty($keys)){
				foreach($keys as $name => $type){
					$query .= sprintf("ADD %s (`%s`),", strtoupper($type), $name);
				}
			}

			return MySQL::query(substr($query, 0, -1));
		}

		public static function drop_columns($table, $cols){
			if(!self::table_exists($table)){
				throw new \Exception('Table does not exist');
			}
			$query = sprintf("ALTER TABLE `%s` ", $table);
			foreach($cols as $col){
				$query .= sprintf("DROP `%s`,", $col);
			}
			return MySQL::query(substr($query, 0, -1));
		}

		public static function create_key($table, $col, $key){
			if(!self::table_exists($table)){
				throw new \Exception('Table does not exist');
			}
			return MySQL::query(sprintf("ALTER TABLE `%s` ADD %s (`%s`)", $table, strtoupper($key), $col));
		}

		public static function drop_key($table, $col){
			if(!self::table_exists($table)){
				throw new \Exception('Table does not exist');
			}
			return MySQL::query(sprintf("ALTER TABLE `%s` DROP KEY `%s`", $table, $col));
		}
	
	}
	
	class Prompt extends Database{
	
		public static function columns($columns = array()){
			if(empty($columns['id'])){
				if(Tea::yes_no('Create an id column?')){
					$columns['id'] = array(
						'type' => 'int',
						'value' => 11,
						'null' => false,
						'default' => false,
						'extra' => 'auto_increment',
						'key' => 'primary key'
					);
				}
			}
			do{
				$exit = false;
				$type = $length = $null = $default = $extra = $key = null;
				echo 'Field name ("q" when done): ';
				$field = Tea::response();
				if($field == 'q'){
					$exit = true;
				}elseif(array_key_exists($field, $columns)){
					echo "\033[0;31mError:\033[0m Field exists!\n";
				}elseif(!empty($field)){
					echo "Field types:\n";
					foreach(parent::$field_types as $index => $type){
						echo "\t{$index}:  {$type}\n";
					}
					do{
						echo "Field type. Enter a number above: ";
						$type = Tea::response();
						$type = (isset(parent::$field_types[$type])) ? parent::$field_types[$type] : null;
					}while(is_null($type));

					if(parent::$default_values[$type] !== false && isset(parent::$default_values[$type])){
						$default_length = parent::$default_values[$type];
						echo "Length [{$default_length}]: ";
						$length = Tea::response($default_length);
					}

					$null = Tea::yes_no('Allow NULL?');

					echo 'Default value (NULL for none): ';
					$default = Tea::response();
					if($null === false && $default == 'NULL'){
						$default = false;
					}elseif($default == 'NULL'){
						$null = true;
						$default = false;
					}

					$key_types = array('primary key', 'unique key', 'key');
					foreach($key_types as $index => $key){
						echo "\t{$index}:  {$key}\n";
					}
					do{
						echo 'Key (blank for none): ';
						$response = Tea::response();
						if(empty($response)){
							$key = '';
							$done = true;
						}elseif(isset($key_types[$response])){
							$key = $key_types[$response];
							$done = true;
						}
					}while($done !== true);

					echo 'Extra: ';
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
	
	}