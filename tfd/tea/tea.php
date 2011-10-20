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
	
	}