<?php namespace TFD\Tea;

	/**
	 * Get TFD ready to go
	 */
	
	class Init{
	
		public static function action($args){
			if(!empty($arg)){
				echo "We're not expecting an argument...\n";
			}
			// Migrations init
			Migrations::init();
			// database init
			Database::init();
			// Add a user
			if(Tea::yes_no("Add a user?")){
				User::add();
			}
		}
	
	}