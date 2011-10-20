<?php namespace TFD\Tea;

	class Tea{
	
		function __construct(){
			spl_autoload_register('Tea::loader');
		}
		
		static function loader($name){
			$file = TEA_DIR.'classes'.DIRECTORY_SEPARATOR.$name.EXT;
			if(!file_exists($file)){
				echo "Invalid class called: {$name}";
				exit(0);
			}
			include_once($file);
			// so the constructor is called
			$class = new $name();
		}
		
		public static function help(){
			$man_page = <<<MAN
A CLI to interface with Tea-Fueled Does

	Usage: tea <command> <args>

Tea Commands:

  init:		Quickly setup TFD.
  user:		Manage users.
  update:	Update TFD.
  config:	Change a config option.
  database:	Make changes to the database.
  migrations:	Manage database migrations.


Args:

Each command has it's own set of commands,
to see args for a specific comamnd run:

	tea <command> -h

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
			echo $man_page;
		}
		
		function command($arg){
			$args = array(
				'd' => 'Database',
				'u' => 'User',
				'm' => 'Migrations',
				'h' => 'help',
				'i' => 'init'
			);
			if(preg_match('/^\-('.implode('|', array_keys($args)).')$/', trim($arg[1]))){
				$run = $args[trim(str_replace('-', '', $arg[1]))];
			}else{
				$run = $arg[1];
			}
			if(empty($run) || $run == 'help'){
				$commands = array(
					'general' => 'Make changes to the config.',
					'database' => 'Automatically setup a database for TFD or create tables with ease.',
					'user' => 'Add a user to the database.'
				);
				echo "Looking for help?\n";
				echo "Classes:\n";
				foreach($commands as $name => $description){
					echo "\t{$name}: {$description}\n";
				}
			}elseif($run == 'init'){
				Database::init();
			}else{
				$run::action($arg);
			}
		}
		
		static function db(){
			include_once(TEA_DIR.'db'.EXT);
			return new DB();
		}
	
	}