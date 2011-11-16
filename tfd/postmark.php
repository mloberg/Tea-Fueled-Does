<?php namespace TFD;

	use TFD\Config;
	use TFD\File;
	
	class Postmark{
	
		private $api_key;
		protected $from;
		protected $reply;
		private $data = array();
		private $attachment_size = 0;
		
		const API_ENDPOINT = 'http://api.postmarkapp.com/email';
		const BATCH_ENDPOINT = 'http://api.postmarkapp.com/email/batch';
		const BOUNCES_ENDPOINT = 'http://api.postmarkapp.com';
		
		public function __construct($api = null, $from = null, $reply = null){
			$this->api_key = (!is_null($api)) ? $api : Config::get('postmark.api_key');
			$this->from = (!is_null($from)) ? $from : Config::get('postmark.from');
			$this->reply = (!is_null($reply)) ? $reply : Config::get('postmark.reply_to');
		}
		
		public static function make($api = null, $from = null, $reply = null){
			return new self($api, $from, $reply);
		}
		
		protected function __send($data, $endpoint = null){
			if(!is_array($data) || empty($data)){
				throw new \Exception('Email data is empty. Cannot send email');
			}else{
				if(is_null($endpoint) || empty($endpoint)) $endpoint = self::API_ENDPOINT;
				$headers = array(
					'Accept: application/json',
					'Content-Type: application/json',
					"X-Postmark-Server-Token: {$this->api_key}"
				);
				$ch = curl_init($endpoint);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$response = curl_exec($ch);
				$error = curl_error($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				$resp = new PostmarkResponse($http_code, $error, $response);
				return $resp;
			}
		}
		
		protected function data($data = null){
			if(!is_null($data)){
				$this->data = $data;
			}else{
				return $this->data;
			}
		}
		
		public function send($info = array(), $headers = array()){
			$this->data = $info + $this->data;
			if(empty($this->data['To']) || empty($this->data['Subject']) || (empty($this->data['HtmlBody']) && empty($this->data['TextBody']))){
				throw new \LogicException('Cannot send email. Missing information');
			}else{
				$this->data['From'] = $this->from;
				$this->data['ReplyTo'] = $this->reply_to;
				$this->data['Headers'] = json_encode($headers);
				// send api request
				$resp = $this->__send($this->data);
				// clear data
				$this->data = array();
				return $resp;
			}
		}
		
		public function to($to){
			$this->data['To'] = $to;
			return $this;
		}
		
		public function cc($cc){
			$this->data['Cc'] = $cc;
			return $this;
		}
		
		public function bcc($bcc){
			$this->data['Bcc'] = $bcc;
			return $this;
		}
		
		public function subject($subject){
			$this->data['Subject'] = $subject;
			return $this;
		}
		
		public function message($message, $type = 'text'){
			if($type == 'html'){
				$this->data['HtmlBody'] = '<html><body>'.$message.'</body></html>';
			}else{
				$this->data['TextBody'] = $message;
			}
			return $this;
		}
		
		public function tag($tag){
			$this->data['Tag'] = $tag;
			return $this;
		}
		
		public function attachment($file, $name = null){
			$this->attachment_size = $this->attachment_size + filesize($file);
			if($this->attachment_size > 102400000){ // Postmark attachment cap is 10MB
				throw new \Exception('You have exceeded Postmark\'s attachment cap (10MB)');
			}elseif(!function_exists('finfo_file')){
				throw new \Exception('finfo_file function is required, but is not available on this system');
			}else{
				$finfo = finfo_open(FILEINFO_MIME);
				$mime = finfo_file($finfo, $file);
				finfo_close($finfo);
				$this->data['Attachments'][] = array(
					'Name' => (is_null($name)) ? basename($file) : $name,
					'Content' => base64_encode(File::get($file)),
					'ContentType' => $mime
				);
			}
			return $this;
		}
	
	}
	
	class PostmarkBatch extends Postmark{
	
		private $batch = array();
		private $info = array();
		
		public function __construct($api = null, $from = null, $reply = null){
			parent::__construct($api, $from, $reply);
		}
		
		public static function make($api = null, $from = null, $reply = null){
			return new self($api, $from, $reply);
		}
		
		public function send(){
			if(empty($this->batch)){
				throw new \Exception('No information has been set');
			}else{
				$resp = $this->__send($this->batch, self::BATCH_ENDPOINT);
				$this->batch = array();
				return $resp;
			}
		}
		
		public function add($info = array(), $headers = array()){
			$email = $this->data();
			$email = $info + $email;
			if(empty($email['To']) || empty($email['Subject']) || (empty($email['HtmlBody']) || empty($email['TextBody']))){
				throw new \LogicException('Cannot send email. Missing information');
			}else{
				$email['From'] = $this->from;
				$email['ReplyTo'] = $this->reply;
				$email['Headers'] = json_encode($headers);
				array_push($this->batch, $email);
				$this->data(array());
			}
		}
	
	}
	
	class PostmarkBounces extends Postmark{
	
		private $api_key;
		
		public function __construct($api = null){
			$this->api_key = (is_null($api)) ? Config::get('postmark.api_key') : $api;
		}
		
		public static function make($api = null){
			return new self($api);
		}
		
		private function req($uri, $params = array()){
			$headers = array(
				"Accept: application/json",
				"X-Postmark-Server-Token: {$this->api_key}"
			);
			$url = self::BOUNCES_ENDPOINT . $uri;
			if(!empty($params)) $url .= '?' . http_build_query($params);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			$error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return new PostmarkResponse($http_code, $error, $response);
		}
		
		public function stats(){
			return $this->req('/deliverystats');
		}
		
		public function get($options = array()){
			if(is_array($options)){
				$params = $options + array('count' => 50, 'offset' => 0);
				return $this->req('/bounces', $params);
			}else{
				return $this->req('/bounces/' . $options);
			}
		}
	
	}
	
	class PostmarkResponse{
	
		private $code;
		private $error;
		private $response;
		
		function __construct($code, $error, $response){
			$this->code = $code;
			$this->error = $error;
			$this->response = $response;
		}
		
		public function sent(){
			return ($this->code !== 200) ? false : true;
		}
		
		public function http_code(){
			return $this->code;
		}
		
		public function error(){
			return $this->error;
		}
		
		public function response(){
			return json_decode($this->response, true);
		}
	
	}