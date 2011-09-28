<?php namespace Content;

	class Ajax{
	
		private static $method;
		
		function __construct($method){
			self::$method = $method;
		}
		
		public function __toString(){
			if(method_exists(__CLASS__, self::$method)){
				$method = self::$method;
				return self::$method();
			}elseif(file_exists(AJAX_DIR.$method.EXT)){
				include AJAX_DIR.$method.EXT;
			}else{
				return self::error();
			}
		}
		
		private static function error(){
			// send 404
			return '404';
		}
		
		private static function test(){
			return 'foobar';
		}
	
	}