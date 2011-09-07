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
			}else{
				self::$arg[2]();
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
					$up = <<<UP
function up(){

UP;
					$down = <<<DOWN
function down(){

DOWN;
					$sql = sprintf("SHOW TABLES FROM `%s`", DB);
					$tables = self::$db->query($sql, true);
					foreach($tables as $table){
						$table = $table['Tables_in_'.DB];
						$sql = sprintf("SHOW FIELDS FROM `%s`", $table);
						$table_columns = self::$db->query($sql, true);
						$columns = "array(";
						$keys = array(
							'PRI' => 'primary',
							'UNI' => 'unique',
							'MUL' => 'index'
						);
						foreach($table_columns as $c){
							preg_match('/\((\d+)\)/', $c['Type'], $match);
							$type = str_replace(array($match[0], 'unsigned'), '', $c['Type']);
							$null = ($c['Null'] === 'NO') ? 'false' : 'true';
							$columns .= "'{$c['Field']}' => array('type' => '{$type}', 'length' => {$match[1]}, 'null' => {$null}, 'default' => '{$c['Default']}', 'extra' => '{$c['Extra']}', 'key' => '{$keys[$c['Key']]}'),";
						}
						$columns = substr($columns, 0, -1).')';
						$up .= <<<UP
			parent::\$db->create_table('$table', $columns);

UP;
						$down .= <<<DOWN
			parent::\$db->drop_table('$table');

DOWN;
					}
					$up .= "\t\t}";
					$down .= "\t\t}";
					$number = self::write_migration_file(1, $up, $down);
					// add migration to database so we don't run it
					self::$db->insert(self::$table, array('number' => $number, 'active' => 1));
				}
			}
			
			$migrations = self::$db->get(self::$table);
			if(empty($migrations)){
				
			}
		}
		
		function write_migration_file($number, $up, $down){
			if(strlen($number) == 1) $number = '0'.$number;
			$file = <<<FILE
<?php

	class TeaMigrations_$number extends Migrations{
	
		$up
		
		$down
	
	}
FILE;
			$fp = fopen(MIGRATIONS_DIR.$number.EXT, 'c');
			fwrite($fp, $file);
			fclose($fp);
			return $number;
		}
	
	}