<?php namespace TFD\Tea;

	use TFD\Admin;
	use TFD\Crypter;
	use TFD\DB\MySQL;
	use TFD\Config as C;
	
	class User{
	
		private static $commands = array(
			'h' => 'help',
			'a' => 'add',
			'p' => 'password',
			'r' => 'remove',
		);
		
		public static function action($arg){
			if(empty($arg)) self::help();
			
			if(preg_match('/^\-\-([\w|\-]+)(.+)?/', $arg, $match)){
				$run = $match[1];
				$args = trim($match[2]);
			}elseif(preg_match('/^\-(\w)(.+)?/', $arg, $match)){
				$run = self::$commands[$match[1]];
				$args = trim($match[2]);
			}elseif(preg_match('/([\w|\-]+)(.+)?/', $arg, $match)){
				$run = $match[1];
				$args = trim($match[2]);
			}
			
			if(!method_exists(__CLASS__, $run) || (($method = new \ReflectionMethod(__CLASS__, $run)) && $method->isPrivate())){
				echo "\033[0;31mError:\033[0m '{$arg}' is not a valid argument!\n";
				exit(0);
			}else{
				self::$run($args);
			}
		}
		
		public static function help(){
			echo <<<MAN
Add, update, and remove users.

	Usage: tea user <args>

Arguments:

	-h, --help     This page
	-a, --add      Add a user

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
			exit(0);
		}
		
		public static function add(){
			do{
				echo "Username: ";
				$username = Tea::response();
			}while(empty($username));
			do{
				echo "Password: ";
				system('stty -echo');
				$password = Tea::response();
				system('stty echo');
			}while(empty($password));
			// add user
			if(Admin::add_user($username, $password)){
				echo "\n{$username} added!\n";
			}else{
				echo "\nCould not add user!\n";
			}
		}
		
		public static function password(){
			echo 'Username: ';
			$username = Tea::response();
			if(empty($username)) exit(0);
			$user = MySQL::table(C::get('admin.table'))->where('username', '=', $username)->limit(1)->get();
			if(empty($user)) exit(0);
			do{
				echo 'Password: ';
				system('stty -echo');
				$password = Tea::response();
				system('stty echo');
			}while(empty($password));
			// update password
			if(MySQL::table(C::get('admin.table'))->where('username', '=', $username)->set('hash', Crypter::hash($password))){
				echo "\nPassword updated.\n";
			}else{
				echo "\nCould not update password.\n";
			}
		}
		
		public static function remove(){
			echo 'Username: ';
			$username = Tea::response();
			if(empty($username)) exit(0);
			$user = MySQL::table(C::get('admin.table'))->where('username', '=', $username)->delete();
			echo "User {$username} removed.\n";
		}
	
	}