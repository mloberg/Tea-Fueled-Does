<?php namespace TFD\Tea;

	use TFD\Config as C;
	
	class Config{
	
		private static $commands = array(
			'h' => 'help',
			'a' => 'auth_key',
			'm' => 'maintenance'
		);
		private static $config_file;
		
		private static function __fake_construct(){
			self::$config_file = CONTENT_DIR.'config'.EXT;;
		}
		
		public static function __callStatic($name, $arguments){
			if(method_exists(__CLASS__, $name)){
				self::__fake_construct();
				call_user_func_array('self::'.$name, $arguments);
			}
		}
		
		public static function action($arg){
			self::__fake_construct();
			// arg comes in a string, let's parse it
			if(preg_match('/^\-\-([\w|\-]+)(.+)?/', $arg, $match)){
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
			$method = new ReflectionMethod(__CLASS__, $run);
			if(empty($run) || $run == 'help'){
				self::help();
			}elseif(method_exists(__CLASS__, $run) && ($method->isPublic() || $method->isProtected())){
				self::$run($args);
			}else{
				echo $run." is not a valid argument!\n";
				exit(0);
			}
		}
		
		private static function help(){
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
		
		private static function auth_key(){
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
		
		private static function maintenance($arg){
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
		
		private static function user_table($arg = ''){
			// if nothing was passed, ask for table name
			if(empty($arg)){
				echo 'User table name ['.C::get('admin.table').']: ';
				$arg = trim(fgets(STDIN));
			}
			// if table name did not change (response was empty after asking) do nothing
			if(!empty($arg)){
				$conf = file_get_contents(self::$config_file);
				$new_conf = preg_replace("/('admin\.table' \=\> ')(\w+)(',)/", '${1}'.$arg.'$3', $conf);
				unlink(self::$config_file);
				$fp = fopen(self::$config_file, 'c');
				fwrite($fp, $new_conf);
				fclose($fp);
				echo "User table is {$arg}.\n";
			}
		}
	
	}