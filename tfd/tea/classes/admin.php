<?php

	class Migrations extends Tea{
	
		static function action($arg){
			if(empty($arg[2])){
				echo "Looking for migrations help?\n";
			}else{
				self::$arg[2]();
			}
		}
		
		static function add_user(){
			global $app;
			echo "Add a new user.";
			do{
				echo "\tUsername: ";
				$username = trim(fgets(STDIN));
			}while(empty($username));
			//$app->admin->add_user();
		}
	
	}