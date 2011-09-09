<?php

	class Migrations extends Tea{
	
		static protected $db;
		static private $table;
		
		function __construct(){
			self::$db = parent::db();
			$conf_file = TEA_CONFIG.'migrations'.EXT;
			if(file_exists($conf_file)) self::$table = include($conf_file);
		}
		
		static function action($arg){
			if(empty($arg[2]) || $arg[2] == 'help'){
				$commands = array(
					'init' => 'Set up migrations'
				);
				echo "Looking for help?\n";
				echo "Commands:\n";
				foreach($commands as $name => $description){
					echo "\t{$name}: {$description}\n";
				}
			}elseif($arg[2] == 'generate_migration_file'){
				echo "Invalid command.\n";
				exit(0);
			}elseif(empty(self::$table) && $arg[2] != 'init'){
				echo "You have not set up migrations. Please run 'tea migrations init'.\n";
				exit(0);
			}else{
				$check = new ReflectionMethod(__CLASS__, $arg[2]);
				if(!$check->isPrivate()){
					self::$arg[2]($arg);
				}else{
					echo "Error: Call to private method.\n";
					exit(0);
				}
			}
		}
		
		static function init(){
			if(empty(self::$table)){
				do{
					echo "Migrations table name [migrations]: ";
					$resp = trim(fgets(STDIN));
					$table = (empty($resp)) ? 'migrations' : $resp;
					if(self::$db->table_exists($table)){
						$table = '';
						echo "\tTable already exists.\n\tPlease enter a new table name.\n";
					}
				}while(empty($table));
				self::$table = $table;
				// write config file
				$conf_file = <<<CONF
<?php return '$table';
CONF;
				$file = TEA_CONFIG.'migrations'.EXT;
				if(file_exists($file)) unlink($file);
				$fp = fopen($file, 'c');
				if(!fwrite($fp, $conf_file)){
					echo "Error saving the config file!\n";
					fclose($fp);
					exit(0);
				}
				fclose($fp);
				$sql = sprintf("CREATE TABLE `%s` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`)
)", $table);
				try{
					self::$db->query($sql);
				}catch(Exception $e){
					echo $e->getMessage();
					exit(0);
				}
			}
			
			$migration_files = glob(MIGRATIONS_DIR.'*.php');
			if(empty($migration_files)){
				echo "Scan the database for current schema? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					$sql = sprintf("SHOW TABLES FROM `%s`", DB);
					$tables = self::$db->query($sql, true);
					foreach($tables as $table){
						$table = $table['Tables_in_'.DB];
						if($table !== self::$table){
							$sql = sprintf("SHOW FIELDS FROM `%s`", $table);
							$table_columns = self::$db->query($sql, true);
							$columns = array();
							$keys = array(
								'PRI' => 'primary',
								'UNI' => 'unique',
								'MUL' => 'index'
							);
							foreach($table_columns as $c){
								preg_match('/\((\d+)\)/', $c['Type'], $match);
								$type = str_replace(array($match[0], 'unsigned'), '', $c['Type']);
								$null = ($c['Null'] === 'NO') ? 'false' : 'true';
								$columns[$c['Field']] = array(
									'type' => $type,
									'length' => $match[1],
									'null' => $null,
									'default' => $c['Default'],
									'extra' => $c['Extra'],
									'key' => $keys[$c['Key']]
								);
							}
							$col_str = var_export($columns, true);
							$up .= "parent::\$db->create_table('{$table}', $col_str);\n";
							$down .= "parent::\$db->drop_table('{$table}');\n";
						}
					}
					$number = self::write_migration_file(1, $up, $down);
					// add migration to database so we don't run it
					self::$db->insert(self::$table, array('number' => $number, 'active' => 1));
				}
			}
			
			// run migrations if migrations are completely empty (in the database)
			if(!self::$db->table_exists(self::$table)){
				$sql = sprintf("CREATE TABLE `%s` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`)
)", self::$table);
				try{
					self::$db->query($sql);
				}catch(Exception $e){
					echo $e->getMessage();
					exit(0);
				}
			}
			$migrations = self::$db->get(self::$table);
			if(empty($migrations) && !empty($migration_files)){
				echo "Run migrations? [y/n]: ";
				if(strtolower(trim(fgets(STDIN))) === 'y'){
					foreach($migration_files as $file){
						preg_match('/\/(\d+)'.preg_quote(EXT).'/', $file, $match);
						include_once($file);
						$migration_name = 'TeaMigrations_'.$match[1];
						$migration_name::up();
						self::$db->where('active', '1')->update(self::$table, array('active' => 0));
						self::$db->insert(self::$table, array('number' => $match[1], 'active' => 1));
					}
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
					$run = $migrations[preg_replace('/^0/', '', trim(fgets(STDIN)))];
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
					$run = $migrations[preg_replace('/^0/', '', trim(fgets(STDIN)))];
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