<?php

	class User extends Tea{
	
		static function action($arg){
			if(empty($arg[2])){
				echo "Looking for admin help?\n";
			}else{
				self::$arg[2]();
			}
		}
		
		static function add(){
			echo "Add a new user.\n";
			do{
				echo "Username: ";
				$username = trim(fgets(STDIN));
			}while(empty($username));
			do{
				echo "Password: ";
				$password = trim(fgets(STDIN));
			}while(empty($password));
			global $app;
			$salt = $app->admin->hash_pass($password);
			$secret = uniqid('', true);
			$user = array(
				'username' => $username,
				'salt' => $salt,
				'secret' => $secret
			);
			if(!Database::load_config()){
				echo "Run database config first...\n";
				exit(0);
			}
			DBConnect::insert(USERS_TABLE, $user);
		}
	
	}