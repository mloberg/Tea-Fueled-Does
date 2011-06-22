<?php

	/**
	 * This is a simple library for sending emails with Postmark
	 *
	 * Usage: $this->postmark->to(email)->subject(subject)->message(message, type)->send();
	 */
	
	class Postmark{
	
		private $api_key = POSTMARK_API_KEY;
		private $from = POSTMARK_FROM;
		private $reply = POSTMARK_REPLY_TO;
		private $data = array();
		private static $errors;
		
		function e(){
			return self::$errors;
		}
		
		function send($data = array()){
			$this->data = $this->data + $data;
			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'X-Postmark-Server-Token: '.$this->api_key
			);
			$data = $this->prepare_data();
			$ch = curl_init('http://api.postmarkapp.com/email');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$return = curl_exec($ch);
			$curl_error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			// do some checking to make sure it sent
			if($http_code !== 200){
				$error = json_decode($return, true);
				self::$errors = $error;
				return false;
			}else{
				$this->data = array();
				return true;
			}
		}
		
		function to($to){
			$this->data['To'] = $to;
			return $this;
		}
		
		function subject($subject){
			$this->data['Subject'] = $subject;
			return $this;
		}
		
		function message($message, $type = 'text'){
			if($type == 'html'){
				$this->data['HtmlBody'] = '<html><body>'.$body.'</body></html>';
			}else{
				$this->data['TextBody'] = $message;
			}
			return $this;
		}
		
		function tag($tag){
			$this->data['Tag'] = $tag;
			return $this;
		}
		
		private function prepare_data(){
			$this->data['From'] = $this->from;
			if($this->reply){
				$this->data['ReplyTo'] = $this->reply;
			}
			return $this->data;
		}
	
	}