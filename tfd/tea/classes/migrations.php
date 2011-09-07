<?php

	class Migrations extends Tea{
	
		static private $db;
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
					$sql = sprintf("SHOW TABLES FROM `%s`", DB);
					$tables = self::$db->query($sql, true);
					foreach($tables as $table){
						
					}
				}
			}
			
			$migrations = self::$db->get(self::$table);
			if(empty($migrations)){
				
			}
		}
	
	}