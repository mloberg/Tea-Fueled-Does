<?php

	$users_table = USERS_TABLE;
	
	class Tea{
	
		function __construct(){
			spl_autoload_register('Tea::loader');
		}
		
		static function loader($name){
			$file = TEA_DIR.'classes'.DIRECTORY_SEPARATOR.$name.EXT;
			if(!file_exists($file)){
				echo "Invalid class called: {$name}";
				exit(0);
			}
			include_once($file);
			// so the constructor is called
			$class = new $name();
		}
		
		function command($arg){
			if(empty($arg[1]) || $arg[1] == 'help'){
				$commands = array(
					'general' => 'Make changes to the config.',
					'database' => 'Automatically setup a database for TFD or create tables with ease.',
					'user' => 'Add a user to the database.'
				);
				echo "Looking for help?\n";
				echo "Classes:\n";
				foreach($commands as $name => $description){
					echo "\t{$name}: {$description}\n";
				}
			}else{
				$arg[1]::action($arg);
			}
		}
		
		static function db(){
			include_once(TEA_DIR.'db'.EXT);
			return new DB();
		}
	
	}