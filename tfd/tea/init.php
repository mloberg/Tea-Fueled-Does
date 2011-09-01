<?php

	class Tea{
	
		function __construct(){
			echo "== Tea Fueled Does Version ".TFD_VERSION." ==\n";
			spl_autoload_register('Tea::loader');
		}
		
		static function loader($name){
			include_once(TEA_DIR.'classes'.DIRECTORY_SEPARATOR.$name.EXT);
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
	
	}