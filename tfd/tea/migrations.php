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
			'u' => 'run_up',
			'd' => 'run_down',
			'l' => 'latest',
			'r' => 'delete_migration'
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
Create and run database migrations.

	Usage: tea migrations <args>

Arguments:

	-h, help         This page
	-i, init         Set up migrations

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
		
		public static function list_migrations(){
			$info = self::get_migration_info();
			extract($info);
			if(empty($migrations)){
				echo "There are no migrations.\n";
				return false;
			}elseif($max == $active){
				echo "You are running the latest migration.\n";
				return false;
			}else{
				echo "Migrations:\n";
				ksort($migrations);
				foreach($migrations as $key => $value){
					echo "  - {$key}";
					echo ($key == $active) ? " (active)\n" : "\n";
				}
				return true;
			}
		}
		
		public function up($arg){
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
						echo "\033[1;31mError:\033[0m Migration less then current migration. Use 'tea migrations -d' if you want to roll-back to a migration.\n";
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
				foreach($migrations as $number => $name){
					// get the class name
					$class = '\Content\Migrations\\'.$name.'_'.$number;
					// and run the up method
					$class::up();
					// set the active in the db
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->update(array('active' => 0));
					MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $number));
				}
			}
		}
		
		public static function run_up($arg){
			// list migrations
			$info = self::_list_migrations();
			if($info === true){
				echo "You are running the latest migration.\n";
				exit(0);
			}
			// match keys to value
			$migrations = array();
			foreach($info['migrations'] as $m){
				$migrations[$m] = $m;
			}
			if(!empty($arg[3])){
				$run = $migrations[$arg[3]];
				if(empty($run)){
					echo "That's an invalid migration.\n";
					exit(0);
				}
			}else{
				do{
					echo "Which migration would you like update to? ";
					$run = $migrations[trim(fgets(STDIN))];
				}while(empty($run));
			}
			// run all migrations from active up to selected migration
			for($i = $info['active'] + 1; $i <= $run; $i++){
				$n = (strlen($i) == 1) ? '0'.$i : $i;
				include_once(MIGRATIONS_DIR.$n.EXT);
				$migration_name = 'TeaMigrations_'.$n;
				$migration_name::up();
				self::$db->where('active', 1)->update(self::$table, array('active' => 0));
				$tmp = self::$db->where('number', $i)->get(self::$table);
				if(empty($tmp)){
					self::$db->insert(self::$table, array('number' => $i, 'active' => 1));
				}else{
					self::$db->where('number', $i)->update(self::$table, array('active' => 1));
				}
			}
		}
		
		public static function latest(){
			$active_migration = self::$db->where('active', 1)->limit(1)->get(self::$table);
			$migration_files = glob(MIGRATIONS_DIR.'*'.EXT);
			$migrations = array();
			$latest = 0;
			foreach($migration_files as $i => $m){
				if(preg_match('/\/(\d+)'.preg_quote(EXT).'$/', $m, $match)){
					$migrations[$match[1]] = $m;
					$latest = ($match[1] > $latest) ? $match[1] : $latest;
				}
			}
			for($i = $active_migration['number'] + 1; $i <= $latest; $i++){
				$n = (strlen($i) == 1) ? '0'.$i : $i;
				include_once($migrations[$n]);
				$migration_name = 'TeaMigrations_'.$n;
				$migration_name::up();
				self::$db->where('active', 1)->update(self::$table, array('active' => 0));
				$tmp = self::$db->where('number', $i)->get(self::$table);
				if(empty($tmp)){
					self::$db->insert(self::$table, array('number' => $i, 'active' => 1));
				}else{
					self::$db->where('number', $i)->update(self::$table, array('active' => 1));
				}
			}
			echo "Database updated to latest version.\n";
		}
		
		public static function run_down($arg){
			// list migrations
			$info = self::_list_migrations(false);
			$migrations = array();
			foreach($info['migrations'] as $m){
				$migrations[$m] = $m;
			}
			if(!empty($arg[3])){
				$run = $migrations[$arg[3]];
				if(empty($run)){
					echo "That's an invalid migration.\n.";
					exit(0);
				}
			}else{
				do{
					echo "Which migration would like to go back to? ";
					$run = $migrations[trim(fgets(STDIN))];
				}while(empty($run));
			}
			// run all migrations from active down to selected migration
			for($i = $info['active']; $i > $run; $i--){
				$n = (strlen($i) == 1) ? '0'.$i : $i;
				include_once(MIGRATIONS_DIR.$n.EXT);
				$migration_name = 'TeaMigrations_'.$n;
				$migration_name::down();
			}
			self::$db->where('active', 1)->update(self::$table, array('active' => 0));
			self::$db->where('number', preg_replace('/^0/', '', $run))->update(self::$table, array('active' => 1));
		}
		
/*
		public static function status(){
			$active = self::$db->where('active', 1)->limit(1)->get(self::$table);
			$migrations = glob(MIGRATIONS_DIR.'*'.EXT);
			sort($migrations);
			preg_match('/\/(\d+)'.preg_quote(EXT).'$/', max($migrations), $match);
			$max = preg_replace('/^0/', '', $match[1]);
			echo "Currently on migration {$active['number']} of {$max}\n";
		}
*/
		
		public static function delete_migration(){
			// we can only delete the latest migration right now
			echo "Delete the latest migration? [y/n]: ";
			if(strtolower(trim(fgets(STDIN))) !== 'y'){
				exit(0);
			}
			$active = self::$db->where('active', 1)->limit(1)->get(self::$table);
			$migrations = glob(MIGRATIONS_DIR.'*'.EXT);
			sort($migrations);
			// get the highest migration
			preg_match('/\/(\d+)'.preg_quote(EXT).'$/', max($migrations), $match);
			$max = preg_replace('/^0/', '', $match[1]);
			// if that's not the latest, run up to it
			if($max !== $active['number']){
				self::run_up(array(3 => $match[1]));
			}
			// run the down method
			include_once(MIGRATIONS_DIR.$match[1].EXT);
			$migration_name = 'TeaMigrations_'.$match[1];
			$migration_name::down();
			self::$db->where('number', $max - 1)->update(self::$table, array('active' => 1));
			// delete file
			unlink(MIGRATIONS_DIR.$match[1].EXT);
			// delete from db
			self::$db->where('number', $max)->delete(self::$table);
			echo "Migration {$max} deleted.\n";
		}
		
		public static function generate_migration_file($up, $down, $add_to_db = false){
			$migrations = glob(MIGRATIONS_DIR.'*'.EXT);
			foreach($migrations as $key => $value){
				if(preg_match('/\/(\d+)'.preg_quote(EXT).'$/', $value, $match)){
					$migrations[$key] = $match[1];
				}else{
					unset($migrations[$key]);
				}
			}
			if(empty($migrations)){
				$number = 1;
			}else{
				$max = max(array_values($migrations));
				$number = $max + 1;
			}
			if($add_to_db === true){
				self::$db->where('active', '1')->update(self::$table, array('active' => 0));
				self::$db->insert(self::$table, array('number' => $number, 'active' => 1));
			}
			self::write_migration_file($number, $up, $down);
		}
		
		private static function write_migration_file($number, $up, $down){
			if(strlen($number) == 1) $number = '0'.$number;
			$file = <<<FILE
<?php

	class TeaMigrations_$number extends Migrations{
	
		function up(){
			$up
		}
		
		function down(){
			$down
		}
	
	}
FILE;
			$fp = fopen(MIGRATIONS_DIR.$number.EXT, 'c');
			fwrite($fp, $file);
			fclose($fp);
			return $number;
		}
	
	}