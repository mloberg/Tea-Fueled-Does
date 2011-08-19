<?php

	class General extends Tea{
	
		public static function action($arg){
			if(empty($arg[2])){
				echo "Looking for help?";
			}else{
				// run the sent command
				self::$arg[2]($arg);
			}
		}
		
		static function auth_key(){
			echo 'Generating new auth key...';
			$new_auth_key = uniqid();
			// load the file into an array
			$conf = file(CONF_DIR.'general'.EXT);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('AUTH_KEY'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "define('AUTH_KEY', '{$new_auth_key}'); // a custom key to validate users, change this\n";
			}
			// delete config file
			unlink(CONF_DIR.'general'.EXT);
			// create new file
			$fp = fopen(CONF_DIR.'general'.EXT, 'c');
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
			$conf = file(CONF_DIR.'general'.EXT);
			// serach for the line
			$match = preg_grep('/'.preg_quote("define('MAINTENANCE_MODE'").'/', $conf);
			// repalce it
			foreach($match as $line => $value){
				$conf[$line] = "define('MAINTENANCE_MODE', {$mode});\n";
			}
			// delete config file
			unlink(CONF_DIR.'general'.EXT);
			// create new file
			$fp = fopen(CONF_DIR.'general'.EXT, 'c');
			// write config file
			foreach($conf as $l){
				fwrite($fp, $l);
			}
			// close file
			fclose($fp);
			echo "Turned maintenance mode {$args[3]}.\n";
		}
	
	}