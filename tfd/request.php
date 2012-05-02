<?php namespace TFD;

	use TFD\Config;
	
	class Request {

		/**
		 * Parse the request string.
		 *
		 * @param string $request Request string
		 * @return string Parsed request string
		 */

		public static function make($request) {
			if (!preg_match('/^\//', $request)) $request = '/' . $request;
			if (preg_match('/\/$/', $request)) $request .= 'index';
			Config::set('request', $request);
			return $request;
		}

		/**
		 * Return request string.
		 * 
		 * @return string Request string.
		 */

		public static function get() {
			return Config::get('request');
		}
		
		/**
		 * Check if the request is an ajax request.
		 *
		 * @return boolean True if Ajax request
		 */

		public static function is_ajax() {
			return ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
		}
		
		/**
		 * Return request string.
		 * 
		 * @return string Request string.
		 */

		public static function uri() {
			return Config::get('request');
		}
		
		/**
		 * Return a segment of the request string.
		 *
		 * @param integer $segment Segment to return
		 * @return string Request segment
		 */

		public static function segment($segment = null) {
			if ($segment == null) return Config::get('request');
			$segments = explode('/', Config::get('request'));
			return $segments[$segment];
		}
		
		/**
		 * Return request method.
		 *
		 * @return string Request method (POST, GET, PUT, DELETE)
		 */

		public static function method() {
			return (static::spoofed()) ? $_POST['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
		}

		/**
		 * Check if method is spoofed (via REQUEST_METHOD).
		 *
		 * @return boolean True if spoofed
		 */
		
		public static function spoofed() {
			return is_array($_POST) && array_key_exists('REQUEST_METHOD', $_POST);
		}

		/**
		 * Return requester's IP address
		 */

		public static function ip() {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (isset($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
			if (isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
		}

		/**
		 * Return request protocol (http, https).
		 *
		 * @return string https or http
		 */

		public static function protocol(){
			return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		}

		/**
		 * Check for HTTPS request.
		 *
		 * @return boolean True if requested with HTTPS
		 */

		public static function is_secure() {
			return (static::protocol() == 'https');
		}

		/**
		 * Set the environment.
		 *
		 * @param string $environment Environment
		 * @return string Environment
		 */

		public static function set_env($environment) {
			Config::set('environment', $environment);
			return $environment;
		}

		/**
		 * Detect the environment from an array.
		 *
		 * @param array $environments Multidimensional array of environments and patterns
		 * @param string $uri Host
		 */

		public static function detect_env($environments, $uri = null) {
			if (is_null($uri)) $uri = $_SERVER['HTTP_HOST'];
			foreach ($environments as $environment => $match) {
				foreach ($match as $pattern) {
					$pattern = '/^'.str_replace('*', '(.*)', $pattern).'$/';
					if (preg_match($pattern, $uri)) {
						return static::set_env($environment);
					}
				}
			}
			return static::set_env('development');
		}

		/**
		 * Get the environment.
		 *
		 * @return string Environment
		 */

		public static function get_env() {
			return Config::get('environment') ?: 'development';
		}
	
	}
