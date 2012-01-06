<?php namespace TFD\Tea;

	// load the config file
	Config::load_config_file();
	
	use TFD\Config as C;
	
	class Config{
	
		private static $config_file = null;
		
		public static function load_config_file(){
			self::$config_file = CONTENT_DIR.'config'.EXT;;
		}
		
		public static function __flags(){
			return array(
				'h' => 'help',
				'a' => 'auth_key',
				'auth-key' => 'auth_key',
				'm' => 'maintenance',
				'u' => 'user_table',
				'user-table' => 'user_table',
			);
		}
		
		public static function __callStatic($method, $args){
			call_user_func_array('self::'.$method, $args);
		}
		
		public static function help(){
			echo <<<MAN
Set TFD config options.

	Usage: tea config <args>

Arguments:

	-h, help            This page
	-a, --auth-key      Generate a new global auth key
	-m, --maintenance   Turn maintence mode on/off
	-u, --user-table    Set the user table

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
		
		public static function maintenance($args){
			if(!preg_match('/on|off/', $args[0])){
				throw new \Exception('Expects "on" or "off"');
			}
			$mode = ($args[0] == 'on') ? 'true' : 'false';
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
			echo "Turned maintenance mode {$args[0]}.\n";
		}
		
		public static function user_table($args){
			$table = $args[0];
			while(empty($table)){
				echo 'User table name ['.C::get('admin.table').']: ';
				$table = Tea::response(C::get('admin.table'));
			}
			// if table name did not change (response was empty after asking) do nothing
			if(!empty($table)){
				$conf = file_get_contents(self::$config_file);
				$new_conf = preg_replace("/('admin\.table' \=\> ')(\w+)(',)/", '${1}'.$table.'$3', $conf);
				unlink(self::$config_file);
				$fp = fopen(self::$config_file, 'c');
				fwrite($fp, $new_conf);
				fclose($fp);
				echo "User table is {$table}.\n";
				C::set('admin.table', $table);
			}
		}
		
		private static function add_tea_config($key, $value){
			$file = CONTENT_DIR.'tea-config'.EXT;
			// load the file
			$conf = file_get_contents($file);
			// add the line
			$new_conf = preg_replace("/(\\n\)\)\; \/\/ end of tea config)/", "\n\t'{$key}' => '{$value}',".'${1}', $conf);
			// delete file
			unlink($file);
			// create new config file
			$fp = fopen($file, 'c');
			fwrite($fp, $new_conf);
			fclose($fp);
			C::set($key, $value);
		}
	
	}