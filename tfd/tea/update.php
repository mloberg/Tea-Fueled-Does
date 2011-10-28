<?php namespace TFD\Tea;

	class Update{
	
		private static $commands = array(
			'h' => 'help'
		);
		
		public static function action($arg){
			if(empty($arg)) self::help();
		}
		
		public static function help(){
			echo <<<MAN
Update TFD.

	Usage: tea update <args>

Arguments:

	-h, --help    This page

TFD Homepage: http://teafueleddoes.com/
Tea Homepage: http://teafueleddoes.com/v2/tea

MAN;
			exit(0);
		}
	
	}