<?php namespace TFD\Tea;

	/**
	 * Get TFD ready to go
	 */
	
	class Init{
	
		public static function action($args){
			if(!empty($arg)){
				echo "We're not expecting an argument...\n";
			}
			echo "\nDatabase Init:\n";
			Database::init();
			if(Tea::yes_no('Setup Migrations?')){
				Migrations::init();
				echo "\n";
			}
			// Add a user
			if(Tea::yes_no("Add a user?")){
				User::add();
			}
		}
	
	}