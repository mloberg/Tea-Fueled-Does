<?php namespace TFD\Test;

	use TFD\Config;

	class Page{
		
		private $page = null;
		private $headers = array();
		private $content = null;
		private $info = array();

		public function __construct($page){
			$this->page = $page;
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

		public function assertStatusIs($expected, $message = null){
			if(is_null($message)) $message = sprintf("Expected [%s] to have HTTP Status code of [%s]", $this->page, $expected);
			Results::add(((integer)$expected === (integer)$this->info['http_code']), $message);
		}

		public function assertStatusNot($expected, $message = null){
			if(is_null($message)) $message = sprintf("Expected [%s] to not have HTTP Status code of [%s]", $this->page, $expected);
			Results::add(!((integer)$expected === (integer)$this->info['http_code']), $message);
		}

		public function assertContent($message = null){
			if(is_null($message)) $message = sprintf("Page [%s] content is empty", $this->page);
			Results::add(!empty($this->content), $message);
		}

		public function assertContentEmpty($message = null){
			if(is_null($message)) $message = sprintf("Page [%s] content is not empty", $this->page);
			Results::add(empty($this->content), $message);
		}

		public function assertInContent($search, $message = null){
			if(is_null($message)) $message = sprintf("Expected [%s] in page content", $search);
			Results::add((strpos($this->content, $search) !== false), $message);
		}

		public function assertNotInContent($search, $message = null){
			if(is_null($message)) $message = sprintf("Did not expect [%s] in page content", $search);
			Results::add((strpos($this->content, $search) === false), $message);
		}

		public function assertHeaderExists($header, $message = null){
			if(is_null($message)) $message = sprintf("Expected header [%s]", $header);
			Results::add(array_key_exists($header, $this->headers), $message);
		}

		public function assertHeaderNotExists($header, $message = null){
			if(is_null($message)) $message = sprintf("Did not expect header [%s]", $header);
			Results::add(!array_key_exists($header, $this->headers), $message);
		}

		public function assertHeaderIs($header, $expected, $message = null){
			if(is_null($message)) $message = sprintf("Header [%s] should be [%s]", $header, $expected);
			Results::add(($this->headers[$header] == $expected), $message);
		}

		public function assertHeaderNot($header, $expected, $message = null){
			if(is_null($message)) $message = sprintf("Header [%s] should not be [%s]", $header, $expected);
			Results::add(($this->headers[$header] != $expected), $message);
		}

		public function assertContentTypeIs($expected, $message = null){
			
		}

		public function assertContentTypeNot($expected, $message = null){
			
		}

		public function assertIsRedirect($message = null){
			
		}

		public function assertNotRedirect($message = null){
			
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