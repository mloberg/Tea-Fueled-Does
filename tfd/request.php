<?php namespace TFD;

	use TFD\Config;
	
	class Request {

		public static function make($request) {
			if (!preg_match('/^\//', $request)) $request = '/' . $request;
			if (preg_match('/\/$/', $request)) $request .= 'index';
			Config::set('request', $request);
		}

		public static function get() {
			return Config::get('request');
		}
		
		public static function is_ajax() {
			return ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
		}
		
		public static function uri() {
			return Config::get('request');
		}
		
		public static function segment($segment = null) {
			if ($segment == null) return Config::get('request');
			$segments = explode('/', Config::get('request'));
			return $segments[$segment];
		}
		
		public static function method() {
			return (static::spoofed()) ? $_POST['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
		}
		
		public static function spoofed() {
			return is_array($_POST) && array_key_exists('REQUEST_METHOD', $_POST);
		}
		
		public static function ip() {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (isset($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
			if (isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
		}
		
		public static function protocol(){
			return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		}
		
		public static function is_secure() {
			return (static::protocol() == 'https');
		}
	
	}
