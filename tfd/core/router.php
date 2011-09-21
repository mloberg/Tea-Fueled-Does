<?php namespace TFD\Core;

	class Router{
	
		private static $routes = array();
		private static $request;
		
		function __construct($request){
			self::$request = $request;
			self::load_routes();
		}
		
		public function get(){
			return self::run_route();
		}
		
		private static function load_routes(){
			self::$routes = include_once(CONTENT_DIR.'routes'.EXT);
		}
		
		private static function run_route(){
			$request =& self::$request;
			$routes =& self::$routes;
			if(empty($routes)) return false;
			
			if(isset($routes[$request])){
				return $routes[$request]();
			}else{
				foreach($routes as $route => $function){
					if(preg_match('/^'.self::replace_wildcards($route).'$/', $request, $match)){
						return $function($match);
					}
				}
			}
			return false;
		}
		
		private static function replace_wildcards($key){
			return str_replace(array('/', '[:any]', '[:num]'), array('\/', '([\w\?\.\#_\-\+\*\^\/]+)?', '(\d+)'), $key);
		}
	
	}