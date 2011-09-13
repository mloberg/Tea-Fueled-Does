<?php

	class User extends Tea{
	
		static function action($arg){
			if(empty($arg[2]) || $arg[2] == 'help'){
				$commands = array(
					'add' => 'Add a user to the database.'
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
			parent::db()->insert(Database::$config['users_table'], $user);
			echo "User added.\n";
		}
	
	}