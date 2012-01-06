<?php namespace TFD\Tea;

	use TFD\Config;
	use TFD\Tea\Config as General;
	use TFD\DB\MySQL;
	
	class Migrations{
	
		public static function __flags(){
			return array(
				'i' => 'init',
				'h' => 'help',
				's' => 'status',
				'u' => 'up',
				'd' => 'down',
				'l' => 'list_migrations'
			);
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
	--latest                SUpdate to latest migration

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
			exit(0);
		}

		public static function get(){
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

		public static function info(){
			$active = (Config::is_set('migrations.active')) ? Config::get('migrations.active') : MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->limit(1)->get('number');
			$migrations = (Config::is_set('migrations.list')) ? Config::get('migrations.list') : self::get();
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

		public static function init(){
			if(!Config::is_set('migrations.table')){
				do{
					echo "Migration table [migrations]: ";
					$table = Tea::response('migrations');
					if(Database::table_exists($table)){
						$table = '';
						echo "\033[1;31mError:\033[0m Table already exists.\n";
					}
				}while(empty($table));

				Generate::table();

				General::add_tea_config('migrations.table', $table);
			}

			if(!Database::table_exists(Config::get('migrations.table'))){
				Generate::table();
			}

			$migrations = glob(Config::get('migrations.dir').'*'.EXT);
			if(empty($migrations)){
				echo "Tea is scanning your database for the current schema.";
				// scan database
				$db = Database::scan();
				// unset the migration table
				unset($db[Config::get('migrations.table')]);

				echo "Migration name (letters only, make it short) [init]: ";
				$name = Generate::name('init');

				$up = $down = '';
				foreach($db as $table => $columns){
					$columns = var_export($columns, true);
					$up .= sprintf("Database::create_table('%s', %s);\n", $table, $columns);
					$down .= sprintf("Database::drop_table('%s');\n", $table);
				}

				$number = Generate::migration($name, $up, $down);
			}elseif(Tea::yes_no('Run migrations?')){
				self::latest();
			}
		}
		
		public static function status(){
			$info = self::info();
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
				foreach($migrations as $number => $name){
					// get the class name
					$class = '\Content\Migrations\\'.$name.'_'.$number;
					// and run the up method
					$class::up();
					// clear the active migration
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
					MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $number));
					// and make sure we know what's the latest migration
					Config::set('migrations.active', $number);
				}
			}
		}
		
		public static function down($arg){
			$info = self::get_migration_info();
			$migrations = $info['migrations'];
			// determine the migration
			if(isset($migrations[$arg]) && $arg < $info['active']){
				$migration = $arg;
			}elseif(self::list_migrations(true)){
				echo "Select migration (0 to run all down migrations): ";
				do{
					$migration = Tea::response();
					if(!isset($migrations[$migration]) && $migration != 0){
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
				foreach($migrations as $number => $name){
					// get class name
					$class = '\Content\Migrations\\'.$name.'_'.$number;
					// run the down method
					$class::down();
					// clear the active migration
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
					// set the active migration
					MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $number));
					// and make sure we know what's the latest migration
					Config::set('migrations.active', $number);
				}
				if($migration == 0){
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
				}
			}
		}
		
		public static function latest(){
			$info = self::get_migration_info();
			self::up($info['max']);
		}
	
	}

	class Generate{
		
		public static function name($default = null){
			$response = Tea::response_to_lower($default);
			$response = ucwords($response);
			return preg_replace('/[^a-zA-Z]/', '', $response);
		}

		public static function migration($name, $up, $down, $add = true){
			$migrations = Migrations::get();
			if($migrations === false){
				$number = 1;
			}else{
				$number = max(array_keys($migrations)) + 1;
			}
			$name = $name.'_'.$number;

			$file = <<<FILE
<?php namespace Content\DB\Migrations;

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
				fclose($fp);
				throw new \Exception('Could not save migration');
			}
			fclose($fp);
			if($add){
				MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
				MySQL::table(Config::get('migrations.table'))->insert(array('number' => $number, 'active' => 1));
			}
			return $number;
		}

		public static function table(){
			$columns = array(
				'id' => array(
					'type' => 'init',
					'length' => 11,
					'null' => false,
					'default' => false,
					'extra' => 'auto_increment',
					'key' => 'primary key'
				),
				'number' => array(
					'type' => 'init',
					'length' => 11,
					'null' => false,
					'default' => false,
					'extra' => '',
					'key' => 'unique key'
				),
				'timestamp' => array(
					'type' => 'timestamp',
					'length' => false,
					'null' => false,
					'default' => 'CURRENT_TIMESTAMP',
					'extra' => 'on update current_timestamp',
					'key' => ''
				),
				'active' => array(
					'type' => 'tinyint',
					'length' => 1,
					'null' => false,
					'default' => '0',
					'extra' => '',
					'key' => ''
				)
			);
			
			if(!Database::create_table($table, $columns)){
				throw new \Exception('Could not create migration table.');
			}
		}

	}