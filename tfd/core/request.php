<?php namespace TFD\Core;

	use \TFD\Admin;
	
	class Request{
	
		private static $request;
		
		function __construct($request){
			self::$request = self::parse_request($request);
		}
		
		function __toString(){
			return self::$request;
		}
		
		private static function parse_request($req){
			if(empty($req)){
				return 'index';
			}elseif(preg_match('/\/$/', $req)){
				return $req.'index';
			}else{
				return $req;
			}
		}
		
		public static function run(){
			if(MAINTENANCE_MODE){
				// Render the maintenance page
				
			}elseif(preg_match('/^'.preg_quote(AJAX_PATH).'\/(.*)$/', self::$request)){
				// ajax request
				
			}elseif(preg_match('/^'.preg_quote(LOGIN_PATH).'\/?$/', self::$request)){
				// login page
				return Admin::login();
			}elseif(preg_match('/^'.preg_quote(ADMIN_PATH).'(\/?(.*))$/', self::$request)){
				// admin dahboard
				return Admin::dashboard();
			}elseif(ADD_USER && $this->request === 'index' && array_key_exists('add_user', $_GET) && $_GET['username'] && $_GET['password']){
				// add a user
			}
			
			return false;
/*
			if(MAINTENANCE_MODE){
				// render new maintenance page
				
			}elseif(preg_match('/'.preg_quote(AJAX_PATH).'\/(.*)$/', self::$request)){
				// ajax request
				
				// old ajax stuff
				if(empty($_GET['ajax'])){
					$_GET['ajax'] = preg_replace('/^(.*)'.MAGIC_AJAX_PATH.'\//', '', $this->request());
				}
				return $this->ajax->call();
			}elseif(preg_match('/^'.preg_quote(LOGIN_PATH).'/', self::$request)){
				// login page
				return Admin::login();
			}elseif(preg_match('/^'.preg_quote(ADMIN_PATH).'/', self::$request) && Admin::loggedin()){
				// dashboard page
				return Admin::dashboard();
			}elseif(ADD_USER && self::$request === 'index' && isset($_GET['add_user']) && !empty($_GET['username']) && !empty($_GET['password'])){
				// add a user
				
			}
			
			return false;
*/
		}
	
	}