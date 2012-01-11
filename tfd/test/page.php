<?php namespace TFD\Test;

	use TFD\Config;

	class Page{
		
		private $headers = array();
		private $content = null;
		private $info = array();

		public function __construct($page){
			$this->load_page($page);
		}

		// need to support POST requests
		// need to support admin views (add a way to view even if no db connection)
		private function load_page($page){
			if(filter_var($page, FILTER_VALIDATE_URL) !== false){
				throw new \Exception('You can only test local urls');
			}
			if(!preg_match('/^\//', $page)) $page = '/' . $page;
			$page = Config::get('site.url').$page;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $page);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'TFD-Test/'.Config::get('application.version'));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, 1);

			$content = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			
			foreach(explode("\n", substr($content, 0, $info['header_size'])) as $header){
				$parts = explode(': ', $header);
				if(count($parts) >= 2){
					$key = $parts[0];
					unset($parts[0]);
					$this->headers[$key] = trim(implode(': ', $parts));
				}
			}

			$this->content = substr($content, $info['header_size'], (strlen($content) - $info['header_size']));
			$this->info = $info;
		}

		public function statusIs($expected, $message = null){
			Results::add(((integer)$expected === (integer)$this->info['http_code']), $message);
		}

		public function statusIsNot($expected, $message = null){
			Results::add(!((integer)$expected === (integer)$this->info['http_code']), $message);
		}

		public function contentNotEmpty(){
			
		}

		public function contentEmpty(){
			
		}

		public function headerExists($header){
			
		}

		public function headerNotExists($header){
			
		}

		public function contentTypeIs(){
			
		}

		public function contentTypeNot(){
			
		}

		public function isRedirect(){
			
		}

		public function notRedirect(){
			
		}

	}

	// class GetPage{
		
	// 	private static $options = array(
	// 		'method' => 'get',
	// 		'post_data' => false,
	// 		'return_info' => false,
	// 		'return_body' => true,
	// 		'referer' => '',
	// 		'headers' => array(),
	// 		'session' => false,
	// 		'session_close'
	// 	);

	// 	public function __construct($url, $options = array){
	// 		self::$options = $options + self::$options;
	// 		$url_parts = parse_url($url);
	// 		$ch = false;
	// 		$info = array(
	// 			'http_code' => 200
	// 		);
	// 		$response = '';
	// 		$send_header = array(
	// 			'Accept' => 'text/*',
	// 			'User-Agent' => 'TFD-Test/'.Config::get('application.version')
	// 		) + self::$options['headers'];
	// 		if(isset(self::$options['post_data'])){
	// 			self::$options['method'] = 'post';
	// 			if(is_array(self::$options['post_data'])){
	// 				$post_data = array();
	// 				foreach(self::$options['post_data'] as $key => $value){
	// 					$post_data[] = "{$key}=".urlencode($value);
	// 				}
	// 				$url_parts['query'] = implode('&', $post_data);
	// 			}else{
	// 				$url_parts['query'] = self::$options['post_data'];
	// 			}
	// 		}elseif(isset(self::$options['multipart_data'])){
	// 			self::$options['method'] = 'post';
	// 			$url_parts['query'] = $options['multipart_data'];
	// 		}

			
	// 	}

	// }