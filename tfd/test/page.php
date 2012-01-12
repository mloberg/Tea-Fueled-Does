<?php namespace TFD\Test;

	use TFD\Config;
	use TFD\Admin;

	class Page{
		
		private $page = null;
		private $headers = array();
		private $content = null;
		private $info = array();

		public function __construct($page, $options = array()){
			$this->page = $page;
			$this->load_page($page, $options);
		}

		// TODO: support admin views
		// TODO: multipart posts
		private function load_page($page, $options){
			$options = $options + array(
				'method' => 'get',
				'post_data' => false,
				'referer' => '',
				'headers' => array(),
				'admin' => false,
			);
			if(filter_var($page, FILTER_VALIDATE_URL) !== false){
				throw new \Exception('You can only test local urls');
			}
			// make a valid url
			if(!preg_match('/^\//', $page)) $page = '/' . $page;
			$url = Config::get('site.url').$page;
			$url_parts = parse_url($url);

			// post data
			if(!empty($options['post_data'])){
				// method has to be post
				$options['method'] = 'post';
				// if an array, turn it into a query string
				if(is_array($options['post_data'])){
					$options['post_data'] = http_build_query($options['post_data']);
				}
				$url_parts['query'] .= $options['post_data'];
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

			// if the current user is logged in, they will be able to test admin protected pages
			if($options['admin'] === true && Admin::loggedin()){
				$cookie = session_name() . '=' . session_id() . '; path=' . session_save_path();
				session_write_close();
				curl_setopt($ch, CURLOPT_COOKIE, $cookie);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			}elseif($options['admin'] && isset($options['username'])){
				// TDOD: allow to pass a username and create a valid session
			}else{
				curl_setopt($ch, CURLOPT_USERAGENT, (isset($options['headers']['User-Agent']) ? $options['User-Agent'] : 'TFD-Test/'.Config::get('application.version')));
			}
			unset($options['headers']['User-Agent']);

			if($options['method'] == 'post'){
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $url_parts['query']);
			}

			// organize our custom headers
			$custom_headers = array();
			foreach($options['headers'] as $name => $value){
				if(is_array($value)){
					foreach($value as $item){
						$custom_headers[] = "{$name}: {$item}";
					}
				}else{
					$custom_headers[] = "{$name}: {$value}";
				}
			}
			if(isset($url_parts['user']) && isset($url_parts['pass'])){
				$custom_headers[] = 'Authorization: Basic '.base64_encode($url_parts['user'].':'.$url_parts['pass']);
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);

			$content = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			print_p("{$page}: {$content}");
			
			// parse the returned headers
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

		public function assertExists($message = null){
			if(is_null($message)) $message = sprintf("Page does not exist");
			Results::add(!($this->info['http_code'] === 404), $message);
		}

		public function assertStatusIs($expected, $message = null){
			if(is_null($message)) $message = sprintf("Expected to have HTTP Status code of [%s]", $expected);
			Results::add(((integer)$expected === $this->info['http_code']), $message);
		}

		public function assertStatusNot($expected, $message = null){
			if(is_null($message)) $message = sprintf("Expected to not have HTTP Status code of [%s]", $expected);
			Results::add(!((integer)$expected === (integer)$this->info['http_code']), $message);
		}

		public function assertContent($message = null){
			if(is_null($message)) $message = sprintf("Page content is empty");
			Results::add((!empty($this->content) && !($this->info['http_code'] === 404)), $message);
		}

		public function assertContentEmpty($message = null){
			if(is_null($message)) $message = sprintf("Page content is not empty");
			Results::add((empty($this->content) || ($this->info['http_code'] === 404)), $message);
		}

		public function assertInContent($search, $message = null){
			if(is_null($message)) $message = sprintf("Expected [%s] in page content", $search);
			Results::add(((strpos($this->content, $search) !== false)  && !($this->info['http_code'] === 404)), $message);
		}

		public function assertNotInContent($search, $message = null){
			if(is_null($message)) $message = sprintf("Did not expect [%s] in page content", $search);
			Results::add(((strpos($this->content, $search) === false) || ($this->info['http_code'] === 404)), $message);
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

		public function assertContentType($expected, $message = null){
			if(is_null($message)) $message = sprintf("Expected content type to be [%s]", $expected);
			Results::add((strpos(strtolower($this->info['content_type']), strtolower($expected)) !== false), $message);
		}

		public function assertContentTypeNot($expected, $message = null){
			if(is_null($message)) $message = sprintf("Expected content type to not be [%s]", $expected);
			Results::add((strpos(strtolower($this->info['content_type']), strtolower($expected)) === false), $message);	
		}

		public function assertRedirect($message = null){
			if(is_null($message)) $message = sprintf("Expected a redirect", $expected);
			Results::add(isset($this->headers['Location']), $message);
		}

		public function assertNotRedirect($message = null){
			if(is_null($message)) $message = sprintf("Did not expected a redirect", $expected);
			Results::add(!isset($this->headers['Location']), $message);
		}

		public function assertRedirectsTo($location, $message = null){
			if(filter_var($location, FILTER_VALIDATE_URL) !== false){
				if(!preg_match('/^\//', $location)) $location = '/' . $location;
				$location = Config::get('site.url') . $location;
			}
			if(is_null($message)) $message = sprintf("Expected a redirect to [%s]", $location);
			Results::add(($this->headers['Location'] === $location), $message);
		}

	}