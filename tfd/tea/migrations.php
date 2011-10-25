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
				$args = $match[2];
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
		
		public static function create_migration($up, $down, $add = true){
			$files = glob(CONTENT_DIR.'migrations/*.php');
			if(empty($files)){
				$number = '1';
			}else{
				$sort = array();
				foreach($files as $f){
					if(preg_match('/(\d+)'.preg_quote(EXT).'/', $f, $match)){
						$sort[] = $match[1];
					}
				}
				$number = max($sort) + 1;
			}
			
			$file = <<<FILE
<?php namespace Content\Migrations;

	use TFD\Tea\Database;
	
	class TeaMigration_$number{
	
		public static function up(){
			$up
		}
		
		public static function down(){
			$down
		}
	
	}
FILE;
			$fp = fopen(CONTENT_DIR.'migrations/'.$number.EXT, 'c');
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
				echo "Migrations table name [migrations]: ";
				do{
					$table = Tea::response('migrations');
					if(Database::table_exists($table)){
						$table = '';
						echo "\033[0;31mError:\033[0m Table already exists.\nPlease enter a new table name: ";
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
			
			$migration_files = glob(CONTENT_DIR.'migrations/*.php');
			if(empty($migration_files)){
				if(Tea::yes_no('Scan database for current schema?')){
					// get database schema
					$db = Database::scan_db();
					// unset the migrations table
					unset($db[Config::get('migrations.table')]);
					// our up and down methods
					$up = $down = '';
					foreach($db as $table => $columns){
						$columns = var_export($columns, true);
						$up .= "Database::create_table('{$table}', {$columns});\n";
						$down .= "Database::drop_table('{$table}');\n";
					}
					
					$number = self::create_migration($up, $down);
				}
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
			$migrations = MySQL::table(Config::get('migrations.table'))->get('id');
			if(empty($migrations) && !empty($migration_files)){
				if(Tea::yes_no('Run migrations?')){
					//self::latest();
				}
			}
		}
		
		private static function _list_migrations($up = true){
			$active_migration = self::$db->where('active', 1)->limit(1)->get(self::$table);
			$migrations = glob(MIGRATIONS_DIR.'*'.EXT);
			$max = max(array_keys($migrations));
			foreach($migrations as $i => $m){
				if(preg_match('/\/(\d+)'.preg_quote(EXT).'$/', $m, $match)){
					echo ($active_migration['number'] == $match[1]) ? '* ' : '  ';
					echo "{$match[1]}\n";
					if($active_migration['number'] == $match[1] && $i == $max && $up === true) return true;
					$migrations[$i] = $match[1];
				}
			}
			return array(
				'active' => $active_migration['number'],
				'migrations' => $migrations
			);
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
		
		public static function status(){
			$active = self::$db->where('active', 1)->limit(1)->get(self::$table);
			$migrations = glob(MIGRATIONS_DIR.'*'.EXT);
			sort($migrations);
			preg_match('/\/(\d+)'.preg_quote(EXT).'$/', max($migrations), $match);
			$max = preg_replace('/^0/', '', $match[1]);
			echo "Currently on migration {$active['number']} of {$max}\n";
		}
		
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