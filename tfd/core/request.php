<?php namespace TFD\Core;

	use TFD\Admin;
	use Content\Ajax;
	
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
			if(self::is_maintenance()){
				
			}elseif(($call = self::is_ajax_request()) !== false){
				return (string) new Ajax($call);
			}elseif(self::is_login()){
				return Admin::login();
			}elseif(self::is_admin()){
				return Admin::dashboard();
			}elseif(self::is_add_user()){
				
			}
			
			return false;
		}
		
		private static function is_maintenance(){
			return (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) ? true : false;
		}
		
		private static function is_ajax_request(){
			if(defined('AJAX_PATH') && preg_match('/^'.preg_quote(AJAX_PATH).'\/?(.*)?$/', self::$request, $match) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
				if(empty($match[1]) && defined('AJAX_REQUEST_PARAM') && isset($_REQUEST[AJAX_REQUEST_PARAM])) return $_REQUEST[AJAX_REQUEST_PARAM];
				return $match[1];
			}
			return false;
		}
		
		private static function is_login(){
			return (defined('LOGIN_PATH') && preg_match('/^'.preg_quote(LOGIN_PATH).'\/?$/', self::$request)) ? true : false;
		}
		
		private static function is_admin(){
			return (defined('ADMIN_PATH') && preg_match('/^'.preg_quote(ADMIN_PATH).'\/?(.*)?$/', self::$request)) ? true : false;
		}
		
		private static function is_add_user(){
			return (defined(ADD_USER) && self::$request === 'index' && isset($_GET['add_user']) && !empty($_GET['username']) && $_GET['password']) ? true : false;
		}
	
	}