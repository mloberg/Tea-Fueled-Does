<?php namespace Content;

	use TFD\Core\Response;
	
	class Ajax{
	
		private static $method;
		
		public function __construct($method){
			self::$method = $method;
		}
		
		public function __toString(){
			if(method_exists(__CLASS__, self::$method) && (($method = new \ReflectionMethod(__CLASS__, self::$method)) && $method->isPublic())){
				$method = self::$method;
				return self::$method();
			}else{
				return Response::make('', 404)->send();
			}
		}
		
		public static function test(){
			return 'foobar';
		}
	
	}