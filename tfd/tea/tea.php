<?php namespace TFD\Tea;

	class Tea{
	
		public static function help(){
			echo <<<MAN
A CLI to interface with Tea-Fueled Does.

	Usage: tea <command> <args>

Tea Commands:

	init:        Quickly setup TFD.
	user:        Manage users.
	update:      Update TFD.
	config:      Change a config option.
	database:    Make changes to the database.
	migrations:  Manage database migrations.


Args:

Each command has it's own set of commands,
to see args for a specific comamnd run:

	tea <command> -h

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
		}
		
		static function db(){
			include_once(TEA_DIR.'db'.EXT);
			return new DB();
		}
		
		/**
		 * Get the user's response
		 * 
		 * params:
		 *  default - (string) if response is empty, return this
		 */
		
		public static function response($default = null){
			$response = trim(fgets(STDIN));
			if(!is_null($default) && empty($response)){
				return $default;
			}
			return $response;
		}
		
		/**
		 * This is the same method as above, but returns the string's lowercase
		 */
		
		public static function response_to_lower($default = null){
			return strtolower(self::response($default));
		}
		
		public static function response_to_upper($default = null){
			return strtoupper(self::response($default));
		}
		
		public static function yes_no($question){
			do{
				echo $question.' [y/n]: ';
				$response = self::response_to_lower();
				if($response == 'y'){
					return true;
				}elseif($response == 'n'){
					return false;
				}
			}while(!$exit);
		}
	
	}