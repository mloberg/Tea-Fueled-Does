<?php namespace TFD\Tea;

	use TFD\Config;
	use TFD\Tea\Config as General;
	use TFD\DB\MySQL;
	
	class Migrations{
	
		function __construct(){
			self::$db = parent::db();
			$conf_file = TEA_CONFIG.'migrations'.EXT;
			if(file_exists($conf_file)) self::$table = include($conf_file);
		}
		
		private static $commands = array(
			'i' => 'init',
			'h' => 'help',
			's' => 'status',
			'u' => 'up',
			'd' => 'down',
			'l' => 'list_migrations'
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
			$run = str_replace('-', '_', $run);
			
			if(!method_exists(__CLASS__, $run) || (($method = new \ReflectionMethod(__CLASS__, $run)) && $method->isPrivate())){
				echo "\033[0;31mError:\033[0m '{$arg}' is not a valid argument!\n";
				exit(0);
			}else{
				self::$run($args);
			}
		}
		
		public static function help(){
			echo <<<MAN
Create and run database migrations.

	Usage: tea migrations <args>

Arguments:

	-h, --help              This page
	-i, --init              Set up migrations
	-s, --status            Get current migration
	-l, --list-migrations   List migrations
	-u, --up                Update to a migration
	-d, --down              Roll-back to a migration
	--latest                Update to latest migration

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
			exit(0);
		}
		
		private static function name_response($default = null){
			$response = Tea::response_to_lower($default);
			$response = ucwords($response);
			$response = preg_replace('/[^a-zA-Z]/', '', $response);
			return $response;
		}
		
		private static function get_migrations(){
			$files = glob(Config::get('migrations.dir').'*'.EXT);
			if(empty($files)) return false;
			$migrations = array();
			foreach($files as $file){
				if(preg_match('/([a-zA-Z]+)_(\d+)'.preg_quote(EXT).'$/', $file, $match)){
					$migrations[$match[2]] = $match[1];
				}
			}
			return $migrations;
		}
		
		public static function create_migration($name, $up, $down, $add = true){
			$migrations = self::get_migrations();
			if($migrations === false){
				$number = 1;
			}else{
				$number = max(array_keys($migrations)) + 1;
			}
			$name = $name.'_'.$number;
			
			$file = <<<FILE
<?php namespace Content\Migrations;

	use TFD\Tea\Database;
	
	class $name{
	
		public static function up(){
			$up
		}
		
		public static function down(){
			$down
		}
	
	}
FILE;
			$fp = fopen(Config::get('migrations.dir').$name.EXT, 'c');
			if(!fwrite($fp, $file)){
				echo "Could not write migration!";
				fclose($fp);
				exit(0);
			}
			fclose($fp);
			if($add){
				try{
					MySQL::table(Config::get('migrations.table'))->insert(array('number' => $number, 'active' => 1));
				}catch(\TFD\Exception $e){
					echo $e->getMessage()."\nExiting...\n";
					exit(0);
				}
			}
			return $number;
		}
		
		public static function init(){
			if(!Config::is_set('migrations.table')){
				echo "This is the migrations class provided by Tea.\nMigrations is an easy way to \"version control\" your database schema.\nFirst we need to setup the table that Tea will use to track active migrations.\n";
				echo "Migrations table name [migrations]: ";
				do{
					$table = Tea::response('migrations');
					if(Database::table_exists($table)){
						$table = '';
						echo "\033[1;31mError:\033[0m Table already exists.\nPlease enter a new table name: ";
					}
				}while(empty($table));
				// write config file
				General::add_tea_config('migrations.table', $table);
				$columns = array(
					'id' => array(
						'type' => 'int',
						'length' => 11,
						'null' => false,
						'default' => false,
						'extra' => 'auto_increment',
						'key' => 'primary key'
					),
					'number' => array(
						'type' => 'int',
						'length' => 11,
						'null' => false,
						'default' => false,
						'extra' => '',
						'key' => 'unique key',
					),
					'timestamp' => array(
						'type' => 'timestamp',
						'length' => false,
						'null' => false,
						'default' => 'CURRENT_TIMESTAMP',
						'extra' => 'on update current_timestamp',
						'key' => '',
					),
					'active' => array(
						'type' => 'tinyint',
						'length' => 1,
						'null' => false,
						'default' => '0',
						'extra' => '',
						'key' => '',
					)
				);
				if(!Database::create_table($table, $columns)){
					echo "Could not create migrations table! Exiting...\n";
					exit(0); 
				}
			}
			
			if(!Config::is_set('migrations.table')){
				echo "Migrations table is not set! Exiting...\n";
				exit(0);
			}
			
			$migration_files = glob(Config::get('migrations.dir').'*'.EXT);
			if(empty($migration_files)){
				echo "To get started, Tea will scan your current database and generate your first migration (not including the migration table).\n\n";
				// get database schema
				$db = Database::scan_db();
				// unset the migrations table
				unset($db[Config::get('migrations.table')]);
				
				echo "Each migration needs a short name, and when I say short, I just a few characters long. Names should only include letters (no numbers or underscores), having names with other characters can cause the migration not to run.\nWe recommend 'init' for this first migration.\n";
				// get migration name
				echo "Migration description [init]: ";
				$name = self::name_response('init');
				// our up and down methods
				$up = $down = '';
				foreach($db as $table => $columns){
					$columns = var_export($columns, true);
					$up .= "Database::create_table('{$table}', {$columns});\n";
					$down .= "Database::drop_table('{$table}');\n";
				}
				
				$number = self::create_migration($name, $up, $down);
			}
			
			// if the migrations table doesn't exist, create it
			if(!Database::table_exists(Config::get('migrations.table'))){
				$columns = array(
					'id' => array(
						'type' => 'int',
						'length' => 11,
						'null' => false,
						'default' => false,
						'extra' => 'auto_increment',
						'key' => 'primary key'
					),
					'number' => array(
						'type' => 'int',
						'length' => 11,
						'null' => false,
						'default' => false,
						'extra' => '',
						'key' => 'unique key',
					),
					'timestamp' => array(
						'type' => 'timestamp',
						'length' => false,
						'null' => false,
						'default' => 'CURRENT_TIMESTAMP',
						'extra' => 'on update current_timestamp',
						'key' => '',
					),
					'active' => array(
						'type' => 'tinyint',
						'length' => 1,
						'null' => false,
						'default' => '0',
						'extra' => '',
						'key' => '',
					)
				);
				if(!Database::create_table($table, $columns)){
					echo "Could not create migrations table! Exiting...\n";
					exit(0); 
				}
			}
			
			// make sure we're running the latest migration
			if(!empty($migration_files)){
				if(Tea::yes_no('Run migrations?')){
					//self::latest();
				}
			}
		}
		
		private static function get_migration_info(){
			$active = (Config::is_set('migrations.active')) ? Config::get('migrations.active') : MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->limit(1)->get('number');
			$migrations = (Config::is_set('migrations.list')) ? Config::get('migrations.list') : self::get_migrations();
			$max = (Config::is_set('migrations.max')) ? Config::get('migrations.max') : @max(array_keys($migrations));
			Config::load(array(
				'migrations.active' => $active,
				'migrations.list' => $migrations,
				'migrations.max' => $max
			));
			return array(
				'active' => $active['number'],
				'max' => $max,
				'migrations' => $migrations
			);
		}
		
		public static function status(){
			$info = self::get_migration_info();
			extract($info);
			if(empty($migrations)){
				echo "There are no migrations.\n";
			}else{
				echo "You are running migration {$active} of {$max}.\n";
			}
		}
		
		public static function list_migrations($down = false){
			$info = self::get_migration_info();
			extract($info);
			if(empty($migrations)){
				echo "There are no migrations.\n";
				return false;
			}elseif($max == $active && $down !== true){
				echo "You are running the latest migration.\n";
				return false;
			}else{
				echo "Migrations:\n";
				ksort($migrations);
				foreach($migrations as $key => $value){
					echo "  {$key}: {$value}";
					echo ($key == $active) ? " (active)\n" : "\n";
				}
				return true;
			}
		}
		
		public static function up($arg){
			$info = self::get_migration_info();
			$migrations = $info['migrations'];
			// determine migration
			if(isset($migrations[$arg]) && $arg > $info['active']){
				$migration = $arg;
			}elseif(self::list_migrations()){
				echo "Select migration: ";
				do{
					$migration = Tea::response();
					if(!isset($migrations[$migration])){
						$migration = '';
						echo "\033[1;31mError:\033[0m Not a valid migration. Please select a valid migration: ";
					}elseif($migration <= $info['active']){
						$migration = '';
						echo "\033[1;31mError:\033[0m Migration less than current migration. Use 'tea migrations -d' if you want to roll-back to a migration.\n";
						echo 'Please select a valid migration: ';
					}
				}while(empty($migration));
			}
			
			// run the migrations
			if(isset($migration)){
				// get the migrations from current to the up
				foreach($migrations as $key => $value){
					if($key > $migration || $key <= $info['active']){
						unset($migrations[$key]);
					}
				}
				// sort so we run in the right order
				ksort($migrations);
				// clear the active migration
				MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->update(array('active' => 0));
				foreach($migrations as $number => $name){
					// get the class name
					$class = '\Content\Migrations\\'.$name.'_'.$number;
					// and run the up method
					$class::up();
					// and make sure we know what's the latest migration
					Config::set('migrations.active', $number);
				}
				MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $migration));
			}
		}
		
		public static function down($arg){
			$info = self::get_migration_info();
			$migrations = $info['migrations'];
			if($info['active'] == 1){
				echo "You are running the first migration.\n";
				exit(0);
			}
			// determine the migration
			if(isset($migrations[$arg]) && $arg < $info['active']){
				$migration = $arg;
			}elseif(self::list_migrations(true)){
				echo "Select migration: ";
				do{
					$migration = Tea::response();
					if(!isset($migrations[$migration])){
						$migration = '';
						echo "\033[1;31mError:\033[0m Not a valid migration. Please select a valid migration: ";
					}elseif($migration > $info['active']){
						$migration = '';
						echo "\033[1;31mError:\033[0m Migration greater than current migration. Use 'tea migrations -u' if you want to update to a migration.\n";
						echo 'Please select a valid migration: ';
					}
				}while(empty($migration));
			}
			
			// run the migrations
			if(isset($migration)){
				// get the migrations from current down to selected
				foreach($migrations as $key => $value){
					if($key > $info['active'] || $key <= $migration){
						unset($migrations[$key]);
					}
				}
				// sort so we run in the right order
				krsort($migrations);
				// clear the active migration
				MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->update(array('active' => 0));
				foreach($migrations as $number => $name){
					// get class name
					$class = '\Content\Migrations\\'.$name.'_'.$number;
					// run the down method
					$class::down();
					// and make sure we know what's the latest migration
					Config::set('migrations.active', $number);
				}
				// set the active migration
				MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $migration));
			}
		}
		
		public static function latest(){
			$info = self::get_migration_info();
			self::up($info['max']);
		}
	
	}