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
				'l' => 'list_m',
				'list' => 'list_m'
			);
		}
		
		public static function help(){
			echo <<<MAN
NAME
	Tea\Migrations

DESCRIPTION
	Version control your database.

USAGE
	tea migrations [command] [args]

COMMANDS
	-i init
		Set up migrations. Must run this first.
		No arguments.
	-s status
		Get current migration.
		No arguments.
	-l list
		List migrations.
		No arguments.
	-u up
		Update to a migration.
		Optional argument of migration number.
	-d down
		Roll-back to a migration.
		Optional argument of migration number.
	latest
		Update to latest migration.
		No arguments.

SEE ALSO
	TFD: http://teafueleddoes.com/
	Tea: http://teafueleddoes.com/docs/tea/index.html
	Tea\Migrations: http://teafueleddoes.com/docs/tea/migrations.html

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

		public static function update(){
			// move /content/migrations to /content/db/migrations
			$old = glob(CONTENT_DIR.'migrations/*');
			if(!empty($old)){
				if(!is_dir(Config::get('migrations.dir'))) mkdir(Config::get('migrations.dir'));
				foreach($old as $file){
					$migration = file_get_contents($file);
					// rename namespace from Content/Migrations to Content/DB/Migrations
					$migration = preg_replace('/namespace Content\\\Migrations/', 'namespace Content\\DB\\Migrations', $migration);
					// save new file
					\TFD\File::put(Config::get('migrations.dir').basename($file), $migration);
					// delete old file
					@unlink($file);
				}
			}
			// delete old migrations dir
			exec('rm -r content/migrations');
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

				Generate::table($table);

				General::add_tea_config('migrations.table', $table);
			}

			if(!Database::table_exists(Config::get('migrations.table'))){
				Generate::table(Config::get('migrations.table'));
			}

			$migrations = glob(Config::get('migrations.dir').'*'.EXT);
			if(empty($migrations)){
				echo "Tea is scanning your database for the current schema.\n";
				// scan database
				$db = Database::scan();
				// unset the migration table
				unset($db[Config::get('migrations.table')]);
				if(empty($db)){
					throw new \Exception('Database is empty');
				}

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

			echo "Migrations setup.\n";
		}
		
		public static function status(){
			extract(self::info());
			if(empty($migrations)){
				echo "There are no migrations.\n";
			}else{
				echo "You are running migration {$active} of {$max}.\n";
			}
		}

		public static function list_m($down = false){
			extract(self::info());
			if(empty($migrations)){
				echo "There are no migrations.\n";
				return false;
			}elseif($max == $active && $down !== true){
				echo "You are running on the latest migration.\n";
				return false;
			}else{
				echo "Migrations:\n";
				ksort($migrations);
				foreach($migrations as $key => $value){
					echo "\t{$key}: {$value}";
					if($key == $active) echo " (active)";
					echo "\n";
				}
				return true;
			}
		}
		
		public static function up($arg){
			extract(self::info());
			if(!empty($arg[0]) && $arg[0] <= $active){
				throw new \Exception('Must use migration greather than current');
			}elseif(!empty($arg[0]) && isset($migrations[$arg[0]])){
				$migration = $arg[0];
			}elseif(self::list_m()){
				do{
					echo 'Migration: ';
					$migration = Tea::response();
					if(!isset($migrations[$migration])){
						$migration = null;
						echo "\033[1;31mError:\033[0m Not a valid migration.\n";
					}elseif($migration <= $active){
						return self::down(array($migration));
					}
				}while(empty($migration));
			}
			
			// run the migrations
			if(isset($migration)){
				// get the migrations from current to the up
				foreach($migrations as $key => $value){
					if($key > $migration || $key <= $active){
						unset($migrations[$key]);
					}
				}
				// sort so we run in the right order
				ksort($migrations);
				foreach($migrations as $number => $name){
					// get the class name
					$class = '\Content\DB\Migrations\\'.$name.'_'.$number;
					// and run the up method
					try{
						$class::up();
					}catch(\Exception $e){
						throw new \Exception("Ran into issue with migration {$number}.\nError: {$e->getMessage()}");
					}
					// change db in case of error
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
					MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $number));
					// and make sure we know what's the latest migration
					Config::set('migrations.active', $number);
				}
			}
		}
		
		public static function down($arg){
			extract(self::info());
			if(!empty($arg[0]) && $arg[0] > $active){
				throw new \Exception('Must use migration less than current');
			}elseif(!empty($arg[0]) && isset($migrations[$arg[0]]) || $arg[0] == "0"){
				$migration = $arg[0];
			}elseif(self::list_m(true)){
				do{
					echo 'Migration (0 to run all down): ';
					$migration = Tea::response();
					if(!isset($migrations[$migration]) && $migration != "0"){
						$migration = null;
						echo "\033[1;31mError:\033[0m Not a valid migration.\n";
					}elseif($migration > $active){
						return self::up($migration);
					}
				}while(empty($migration) && $migration != "0");
			}
			
			// run the migrations
			if(isset($migration)){
				// get the migrations from current down to selected
				foreach($migrations as $key => $value){
					if($key > $active || $key <= $migration){
						unset($migrations[$key]);
					}
				}
				// sort so we run in the right order
				krsort($migrations);
				foreach($migrations as $number => $name){
					// get class name
					$class = '\Content\DB\Migrations\\'.$name.'_'.$number;
					if(!class_exists($class)){
						$class = '\Content\Migrations\\'.$name.'_'.$number;
					}
					// run the down method
					try{
						$class::down();
					}catch(\Exception $e){
						throw new \Exception("Ran into issue with migration {$number}. Error: {$e->getMessage()}");
					}
					// update db in case of error
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
					MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $number));
					Config::set('migrations.active', $number);
				}
				MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
				MySQL::query(sprintf("REPLACE INTO `%s` SET `number` = :number, `active` = 1", Config::get('migrations.table')), array('number' => $migration));
				Config::set('migrations.active', $migration);
				if($migration == 0){
					MySQL::table(Config::get('migrations.table'))->where('active', '=', 1)->set('active', 0);
				}
			}
		}
		
		public static function latest(){
			$info = self::info();
			self::up(array($info['max']));
		}

		public static function create($name, $up, $down, $add = true){
			if((Config::is_set('migrations.table')) && Tea::yes_no('Create migration?')){
				echo "Migration name [{$name}]: ";
				$name = Generate::name($name);
				return Generate::migration($name, $up, $down, $add);
			}
		}
	
	}

	class Generate{
		
		public static function name($default = null){
			$response = Tea::response_to_lower($default);
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

		public static function table($table){
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