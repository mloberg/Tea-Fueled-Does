<?php namespace TFD\Test;

	use TFD\Config;
	use TFD\Admin;
	use TFD\Crypter;
	use TFD\DB\MySQL;

	class Page{
		
		private $page = null;
		private $headers = array();
		private $content = null;
		private $info = array();

		public function __construct($page, $options = array()){
			$this->page = $page;
			$this->load_page($page, $options);
		}

		public static function make($page, $options = array()){
			return new self($page, $options);
		}

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

			$user_agent = (isset($options['headers']['User-Agent']) ? $options['headers']['User-Agent'] : 'TFD-Test/'.Config::get('application.version'));
			unset($options['headers']['User-Agent']);

			if($options['admin'] === true){
				// create a valid admin session if one doesn't exist
				// this will not work in Tea
				if(!Admin::loggedin() && isset($options['username'])){
					if(is_string($options['username'])){
						$user = MySQL::table(Config::get('admin.table'))->where('username', '=', $options['username'])->limit(1)->get(array('id', 'secret'));
						$_SESSION['user_id'] = $user['id'];
					}else{
						$user = MySQL::table(Config::get('admin.table'))->where('id', '=', $options['username'])->limit(1)->get('secret');
						$_SESSION['user_id'] = $options['username'];
					}
					if(empty($user)){
						throw new \Exception('Not a valid username');
					}
					$_SESSION['logged_in'] = true;
					$salt = Config::get('admin.auth_key').$user_agent.session_id();
					$hash = Crypter::hash_with_salt($user['secret'], $salt);
					$_SESSION['fingerprint'] = $hash;
				}else{
					// if the user is logged in, we need to match the User Agent so the session is valid
					$user_agent = $_SERVER['HTTP_USER_AGENT'];
				}
				$cookie = session_name() . '=' . session_id() . '; path=' . session_save_path();
				session_write_close();
				curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			}
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

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
			Results::add(!($this->info['http_code'] === 404), $message);
		}

		public function assertStatusIs($expected, $message = null){
			Results::add(((integer)$expected === $this->info['http_code']), $message);
		}

		public function assertStatusNot($expected, $message = null){
			Results::add(!((integer)$expected === (integer)$this->info['http_code']), $message);
		}

		public function assertContent($message = null){
			Results::add((!empty($this->content) && !($this->info['http_code'] === 404)), $message);
		}

		public function assertContentEmpty($message = null){
			Results::add((empty($this->content) || ($this->info['http_code'] === 404)), $message);
		}

		public function assertInContent($search, $message = null){
			Results::add(((strpos($this->content, $search) !== false)  && !($this->info['http_code'] === 404)), $message);
		}

		public function assertNotInContent($search, $message = null){
			Results::add(((strpos($this->content, $search) === false) || ($this->info['http_code'] === 404)), $message);
		}

		public function assertHeaderExists($header, $message = null){
			Results::add(array_key_exists($header, $this->headers), $message);
		}

		public function assertHeaderNotExists($header, $message = null){
			Results::add(!array_key_exists($header, $this->headers), $message);
		}

		public function assertHeaderIs($header, $expected, $message = null){
			Results::add(($this->headers[$header] == $expected), $message);
		}

		public function assertHeaderNot($header, $expected, $message = null){
			Results::add(($this->headers[$header] != $expected), $message);
		}

		public function assertContentType($expected, $message = null){
			Results::add((strpos(strtolower($this->info['content_type']), strtolower($expected)) !== false), $message);
		}

		public function assertContentTypeNot($expected, $message = null){
			Results::add((strpos(strtolower($this->info['content_type']), strtolower($expected)) === false), $message);	
		}

		public function assertRedirect($message = null){
			Results::add(isset($this->headers['Location']), $message);
		}

		public function assertNotRedirect($message = null){
			Results::add(!isset($this->headers['Location']), $message);
		}

		public function assertRedirectsTo($location, $message = null){
			if(filter_var($location, FILTER_VALIDATE_URL) !== false){
				if(!preg_match('/^\//', $location)) $location = '/' . $location;
				$location = Config::get('site.url') . $location;
			}
			Results::add(($this->headers['Location'] === $location), $message);
		}

	}