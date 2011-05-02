<?php

	/**
	 * A simple input validation class
	 */

	class Validation{
	
		private $rules = array();
		
		function test($text){
			$text = trim($text);
			$test = new ValidationTest($text);
			$return = false;
			foreach($this->rules as $key => $value){
				if(!$test->$key($value)){
					$return = true;
				}
				unset($this->rules[$key]);
			}
			return $return;
		}
		
		function email(){
			$this->rules['run_email'] = 'email';
			return $this;
		}
		
		function max_len($int){
			$this->rules['run_max_len'] = $int;
			return $this;
		}
		
		function min_len($int){
			$this->rules['run_min_len'] = $int;
			return $this;
		}
		
		function req(){
			$this->rules['run_req'] = 'req';
			return $this;
		}
		
		function match($match){
			$this->rules['run_match'] = $match;
			return $this;
		}
	
	}
	
	class ValidationTest extends Validation{
	
		protected $text;
		
		function __construct($text){
			$this->text = $text;
		}
		
		protected function run_email(){
			if(!preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/', $this->text)){
				return false;
			}else{
				return true;
			}
		}
		
		protected function run_max_len($int){
			if(strlen($this->text) > $int){
				return false;
			}else{
				return true;
			}
		}
		
		protected function run_min_len($int){
			if(strlen($this->text) < $int){
				return false;
			}else{
				return true;
			}
		}
		
		protected function run_req(){
			$text = preg_replace('/\s\s+/','',$this->text);
			if(!$text || $text == ' '){
				return false;
			}else{
				return true;
			}
		}
		
		protected function run_match($match){
			if($this->text !== trim($match)){
				return false;
			}else{
				return true;
			}
		}
	
	}