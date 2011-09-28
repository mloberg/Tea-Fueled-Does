<?php namespace TFD\API;

	class Postmark{
	
		private $api_key = POSTMARK_API_KEY;
		protected $from = POSTMARK_FROM;
		protected $reply = POSTMARK_REPLY_TO;
		private $data = array();
		
		const API_ENDPOINT = 'http://api.postmarkapp.com/email';
		const BATCH_ENDPOINT = 'http://api.postmarkapp.com/email/batch';
		const BOUNCES_ENDPOINT = 'http://api.postmarkapp.com/bounces';
		
		function __construct($api = null, $from = null, $reply = null){
			if(!is_null($api)) $this->api_key = $api;
			if(!is_null($from)) $this->from = $from;
			if(!is_null($reply)) $this->reply = $reply;
		}
		
		protected function __send($data, $endpoint = null){
			if(!is_array($data) || empty($data)){
				throw new \LogicException('');
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
		
		public function get_bounces($count = 50, $offset = 0){
			$headers = array(
				"Accept: application/json",
				"X-Postmark-Server-Token: {$this->api_key}"
			);
			$ch = curl_init(self::BOUNCES_ENDPOINT."?count={$count}&offset={$offset}");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			$error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return new PostmarkResponse($http_code, $error, $response);
		}
		
		public function send(){
			if(empty($this->data['To'])){
				throw new \TFD\Exception('Postmark::to() has not been set!');
			}else{
				$this->data['From'] = $this->from;
				$this->data['ReplyTo'] = $this->reply_to;
				$resp = $this->__send($this->data);
				$this->data = array();
				return $resp;
			}
		}
		
		public function to($to){
			$this->data['To'] = $to;
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
	
	}
	
	class PostmarkBatch extends Postmark{
	
		private $batch = array();
		private $info = array();
		
		function __construct($api = null, $from = null, $reply = null){
			parent::__construct($api, $from, $reply);
		}
		
		public function send(){
			if(empty($this->batch)){
				throw new \TFD\Exception('No information has been set!');
			}else{
				$resp = $this->__send($this->batch, self::BATCH_ENDPOINT);
				$this->batch = array();
				return $resp;
			}
		}
		
		public function add(){
			$email = $this->data();
			if(empty($email['To'])){
				throw new \TFD\Exception('PostmarkBatch::to() has not been set!');
			}else{
				$email['From'] = $this->from;
				$email['ReplyTo'] = $this->reply;
				array_push($this->batch, $email);
				$this->data(array());
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