<?php namespace TFD\Tea;

	class Config{
	
		private static $commands = array(
			'h' => 'help',
			'a' => 'auth_key',
			'm' => 'maintenance'
		);
		private static $config_file;
		
		public static function action($arg){
			// arg comes in a string, let's parse it
			if(preg_match('/^\-\-([\w|\-|:]+)(.+)?/', $arg, $match)){
				$run = $match[1];
				$args = trim($match[2], ' =');
				unset($match);
			}elseif(preg_match('/^\-(\w)(.+)?/', $arg, $match)){
				$run = self::$commands[$match[1]];
				$args = trim($match[2], ' =');
				unset($match);
			}else{
				$arg = explode(' ', $arg);
				$run = $arg[0];
				$args = $arg[1];
			}
			if(empty($run) || $run == 'help'){
				self::help();
			}elseif(method_exists(__CLASS__, $run)){
				self::$config_file = CONTENT_DIR.'config'.EXT;
				self::$run($args);
			}else{
				echo $run." is not a valid argument!\n";
				exit(0);
			}
		}
		
		public static function help(){
			echo <<<MAN
Set TFD config options.

	Usage: tea config <args>

Arguments:

	-h, help            This page
	-a, --auth_key      Generate a new global auth key
	-m, --maintenance   Turn maintence mode on/off

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
		}
		
		public static function auth_key(){
			echo 'Generating new auth key...';
			$new_auth_key = uniqid();
			// load the file
			$conf = file_get_contents(self::$config_file);
			// reaplace the line
			$new_conf = preg_replace("/('admin\.auth_key' \=\> ')(.+)(',)/", '${1}'.$new_auth_key.'$3', $conf);
			// delete config file
			unlink(self::$config_file);
			// create new file
			$fp = fopen(self::$config_file, 'c');
			// write config file
			fwrite($fp, $new_conf);
			// close file
			fclose($fp);
			echo "\nNew auth key generated.\n";
		}
		
		public static function maintenance($arg){
			if(!preg_match('/on|off/', $arg)){
				echo 'Error: expects "on" or "off"!'.PHP_EOL;
				exit(0);
			}
			$mode = ($arg == 'on') ? 'true' : 'false';
			// load the file
			$conf = file_get_contents(self::$config_file);
			// replace the line
			$new_conf = preg_replace("/('site\.maintenance' \=\> )(\w+)(,)/", '${1}'.$mode.'$3', $conf);
			// delete config file
			unlink(self::$config_file);
			// create new file
			$fp = fopen(self::$config_file, 'c');
			// write config file
			fwrite($fp, $new_conf);
			// close file
			fclose($fp);
			echo "Turned maintenance mode {$arg}.\n";
		}
	
	}