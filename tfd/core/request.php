<?php namespace TFD\Core;

	use TFD\Admin;
	use Content\Ajax;
	use TFD\Config;
	
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
			}elseif(self::is_logout()){
				return Admin::logout();
			}elseif(self::is_login()){
				return Admin::login();
			}elseif(self::is_admin()){
				return Admin::dashboard();
			}
			
			return false;
		}
		
		private static function is_maintenance(){
			return (Config::is_set('application.maintenance') && Config::get('application.maintenance') === true) ? true : false;
		}
		
		private static function is_ajax_request(){
			if(Config::is_set('ajax.path') && preg_match('/^'.preg_quote(Config::get('ajax.path')).'\/?(.*)?$/', self::$request, $match) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
				if(empty($match[1]) && Config::is_set('ajax.parameter') && isset($_REQUEST[Config::get('ajax.parameter')])) return $_REQUEST[Config::get('ajax.parameter')];
				return $match[1];
			}
			return false;
		}
		
		private static function is_logout(){
			return (Config::is_set('admin.logout') && preg_match('/^(admin\/)?'.preg_quote(Config::get('admin.logout')).'$/', self::$request)) ? true : false;
		}
		
		private static function is_login(){
			return (Config::is_set('admin.login') && preg_match('/^'.preg_quote(Config::get('admin.login')).'\/?$/', self::$request)) ? true : false;
		}
		
		private static function is_admin(){
			return (Config::is_set('admin.path') && preg_match('/^'.preg_quote(Config::get('admin.path')).'\/?(.*)?$/', self::$request)) ? true : false;
		}
	
	}