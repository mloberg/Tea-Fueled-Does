<?php

	class Router{
	
		private static $routes = array();
		private static $request;
		
		function __construct($request){
			self::$request = (string)$request;
			self::load_routes();
		}
		
		public function route(){
			return $this->run_route(self::$request, self::$routes);
		}
		
		private function load_routes(){
			self::$routes = include_once(CONTENT_DIR.'routes'.EXT);
		}
		
		private function run_route($request, $routes){
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