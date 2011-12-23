<?php namespace TFD\Core;

	use TFD\Admin;
	use Content\Ajax;
	use TFD\Config;
	use TFD\App;
	
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
			}elseif(preg_match('/\/$/', $req)){ // if request ends with / added index
				return $req.'index';
			}else{
				return $req;
			}
		}
		
		public static function run(){
			if(($call = self::is_ajax()) !== false && method_exists('Ajax', $call)){
				return (string) new Ajax($call);
			}elseif(self::is_logout()){
				return Admin::logout();
			}elseif(self::is_login()){
				return Admin::login();
			}elseif(self::is_admin()){
				return Admin::dashboard();
			}
			
			return false;
		}
		
		public static function is_maintenance(){
			return (Config::is_set('application.maintenance') && Config::get('application.maintenance') === true) ? true : false;
		}
		
		public static function is_ajax(){
			if(Config::is_set('ajax.path') && preg_match('/^'.preg_quote(Config::get('ajax.path'), '/').'\/?(.*)?$/', self::$request, $match) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || Config::get('ajax.debug'))){
				if(empty($match[1]) && Config::is_set('ajax.parameter') && isset($_REQUEST[Config::get('ajax.parameter')])) return $_REQUEST[Config::get('ajax.parameter')];
				return $match[1];
			}
			return false;
		}
		
		private static function is_logout(){
			return (Config::is_set('admin.logout') && preg_match('/^(\/admin)?'.preg_quote(Config::get('admin.logout'), '/').'$/', self::$request)) ? true : false;
		}
		
		private static function is_login(){
			return (Config::is_set('admin.login') && preg_match('/^'.preg_quote(Config::get('admin.login'), '/').'\/?$/', self::$request)) ? true : false;
		}
		
		public static function is_admin(){
			return (Config::is_set('admin.path') && preg_match('/^'.preg_quote(Config::get('admin.path'), '/').'\/?(.*)?$/', self::$request)) ? true : false;
		}
		
		public static function uri(){
			return App::request();
		}
		
		public static function segment($segment = null){
			return App::url($segment);
		}
		
		public static function method(){
			return (self::spoofed()) ? $_POST['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
		}
		
		public static function spoofed(){
			return is_array($_POST) && array_key_exists('REQUEST_METHOD', $_POST);
		}
		
		public static function ip(){
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
			if(isset($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
			if(isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
		}
		
		public static function protocol(){
			return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		}
		
		public static function is_secure(){
			return (self::protocol() == 'https');
		}
	
	}