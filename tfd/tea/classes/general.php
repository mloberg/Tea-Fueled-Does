<?php

	class General extends Tea{
	
		public static function action($arg){
			$args = array(
				'h' => 'help',
				'm' => 'maintenance',
				'a' => 'auth_key'
			);
			if(preg_match('/^\-('.implode('|', array_keys($args)).')$/', trim($arg[2]))){
				$run = $args[trim(str_replace('-', '', $arg[2]))];
			}else{
				$run = $arg[2];
			}
			if(empty($run) || $run == 'help'){
				$commands = array(
					'auth_key' => 'Generate a new admin auth key.',
					'maintenance' => 'Turn maintenance mode on or off (takes 1 argument).'
				);
				echo "Looking for help?\n";
				echo "Commands:\n";
				foreach($commands as $name => $description){
					echo "\t{$name}: {$description}\n";
				}
			}else{
				// run the sent command
				self::$run($arg);
			}
		}
		
		static function auth_key(){
			echo 'Generating new auth key...';
			$new_auth_key = uniqid();
			// load the file into an array
			$conf = file(CONF_FILE);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('AUTH_KEY'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "\t\tdefine('AUTH_KEY', '{$new_auth_key}'); // a custom key to validate users, change this\n";
			}
			// delete config file
			unlink(CONF_FILE);
			// create new file
			$fp = fopen(CONF_FILE, 'c');
			// write config file
			foreach($conf as $l){
				fwrite($fp, $l);
			}
			// close file
			fclose($fp);
			echo "\nNew auth key generated.\n";
		}
		
		static function maintenance($args){
			$mode = ($args[3] == 'on') ? 'true' : 'false';
			// load the file into an array
			$conf = file(CONF_FILE);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('MAINTENANCE_MODE'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "\t\tdefine('MAINTENANCE_MODE', {$mode});\n";
			}
			// delete config file
			unlink(CONF_FILE);
			// create new file
			$fp = fopen(CONF_FILE, 'c');
			// write config file
			foreach($conf as $l){
				fwrite($fp, $l);
			}
			// close file
			fclose($fp);
			echo "Turned maintenance mode {$args[3]}.\n";
		}
	
	}