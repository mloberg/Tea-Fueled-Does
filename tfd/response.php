<?php namespace TFD;

	use TFD\Config;
	
	class Response{
	
		private static $statuses = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Move Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Request Range Not Satisfiable',
			517 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependancy',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded'
		);
		private static $headers = array();
		private static $content;
		private static $status;
		
		function __construct($content = null, $status = 200){
			self::$content = $content;
			self::$status = $status;
		}
		
		public function __toString(){
			return $this->send();
		}
		
		/**
		 * Setters
		 */
		
		public function header($name, $value){
			self::$headers[$name] = $value;
			return $this;
		}
		
		/**
		 * Class methods
		 */
		
		public function send(){
			if(!array_key_exists('Content-Type', self::$headers)){
				$this->header('Content-Type', 'text/html; charset=utf-8');
			}
			
			if(!headers_sent()) $this->send_headers();
			
			return (string)self::$content;
		}
		
		private function send_headers(){
			$protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			
			header($protocol.' '.self::$status.' '.self::$statuses[self::$status]);
			
			foreach(self::$headers as $name => $value){
				header($name.': '.$value, true);
			}
		}
		
		/**
		 * Static methods
		 */
		
		public static function make($content = null, $status = 200){
			return new self($content, $status);
		}
		
		public static function error($code, $data = array()){
			return new self(Render::error($code, $data)->render(), $code);
		}
	
	}