<?php namespace TFD\Core;

	use TFD\Core\Render;
	
	class Response {
	
		protected static $statuses = array(
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

		protected $headers = array();
		protected $content;
		protected $status;
		
		/**
		 * Return a new Response object.
		 * 
		 * @param string $content Response content
		 * @param integer $status HTTP status
		 * @return object New Response object
		 */

		function __construct($content = null, $status = 200) {
			$this->content = $content;
			$this->status = $status;
		}
		
		/**
		 * Redirects to send method.
		 */

		public function __toString() {
			return $this->send();
		}
		
		/**
		 * Set an HTTP header.
		 *
		 * @param string $name HTTP Header name
		 * @param string $value HTTP Hader value
		 * @return object Self
		 */
		
		public function header($name, $value) {
			$this->headers[$name] = $value;
			return $this;
		}
		
		/**
		 * Return the response.
		 *
		 * @return string Response
		 */
		
		public function send() {
			// set a Content-Type header if we don't have one already
			if(!array_key_exists('Content-Type', $this->headers)){
				$this->header('Content-Type', 'text/html; charset=utf-8');
			}
			// make sure we haven't sent headers already
			if(!headers_sent()) {
				$this->send_headers();
			}
			return (string)$this->content;
		}

		/**
		 * Send HTTP headers.
		 */
		
		private function send_headers() {
			$protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			header($protocol.' '.$this->status.' '.static::$statuses[$this->status]);
			foreach($this->headers as $name => $value){
				header($name.': '.$value, true);
			}
		}
		
		/**
		 * Return a new Response object.
		 * 
		 * @param string $content Response content
		 * @param integer $status HTTP status
		 * @return object New Response object
		 */
		
		public static function make($content = null, $status = 200) {
			return new static($content, $status);
		}

		/**
		 * Return an error response (non 200)
		 *
		 * @param integer $code HTTP code
		 * @param array $data 
		 */
		
		public static function error($code, $data = array()) {
			return new static(Render::error($code, $data)->render(), $code);
		}
	
	}
