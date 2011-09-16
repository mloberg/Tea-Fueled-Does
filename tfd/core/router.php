<?php

	class Router extends App{
	
		private static $routes = array();
		
		function route($request){
			// load the routes
			$routes = include_once(CONTENT_DIR.'routes'.EXT);
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