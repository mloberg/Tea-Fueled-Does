<?php namespace TFD\Tea;

	use TFD\Test;
	
	class Tests{
	
		public static function __flags(){
			return array(
				'h' => 'help',
			);
		}
		
		public static function help(){
			echo <<<MAN
NAME
	Tea\Tests

DESCRIPTION
	

USAGE
	tea tests [command] [args]

COMMANDS
	

SEE ALSO
	TFD: http://teafueleddoes.com/
	Tea: http://teafueleddoes.com/docs/tea/index.html
	Tea\test: 

MAN;
			exit(0);
		}
		
		public static function run($args){
			$test = $args[0];
			print_r(Test::cli($test));
		}
	
	}