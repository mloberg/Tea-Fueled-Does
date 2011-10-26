<?php namespace TFD\Core;

	class Router{
	
		private static $routes = array();
		private static $request;
		private static $method;
		
		function __construct($request){
			self::$method = (isset($_REQUEST['REQUEST_METHOD'])) ? $_REQUEST['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
			self::$request = $request;
			self::load_routes();
		}
		
		/**
		 * Public accessor for run_routes
		 */
		
		public function get(){
			return self::run_route();
		}
		
		/**
		 * Load routes file
		 */
		
		private static function load_routes(){
			self::$routes = include_once(CONTENT_DIR.'routes'.EXT);
		}
		
		/**
		 * Parses routes and looks for match
		 */
		
		private static function run_route(){
			$request = self::$method.' '.self::$request;
			$routes =& self::$routes;
			
			if(empty($routes)) return null;
			
			if(isset($routes[$request])){
				return $routes[$request]();
			}else{
				foreach($routes as $route => $function){
					$method = reset(explode(' ', $route));
					$req = end(explode(' ', $route));
					if($method === $req) $method = '';
					if(preg_match('/^'.self::replace_wildcards($req).'$/', self::$request, $match) && (empty($method) || $method == self::$method)){
						return $function($match);
					}
				}
			}
			return null;
		}
		
		/**
		 * Replace wildcards in route
		 */
		
		private static function replace_wildcards($key){
			return str_replace(array('/', '[:any]', '[:num]'), array('\/', '([\w\?\.\#\-\+\*\^\/]+)?', '(\d+)'), $key);
		}
	
	}