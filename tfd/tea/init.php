<?php namespace TFD\Tea;

	/**
	 * Get TFD ready to go
	 */
	
	class Init{
	
		public static function action($args){
			if(!empty($arg)){
				echo "We're not expecting an argument...\n";
			}
			echo "Migrations Init:\n";
			Migrations::init();
			echo "\nDatabase Init:\n";
			Database::init();
			echo "\n";
			// Add a user
			if(Tea::yes_no("Add a user?")){
				User::add();
			}
		}
	
	}