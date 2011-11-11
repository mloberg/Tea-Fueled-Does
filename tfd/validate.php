<?php namespace TFD;

	class Validate{
	
		public static function text($text){
			return new TextValidation($text);
		}
	
	}
	
	class TextValidation{
	
		private static $passed = true;
		private static $text;
		
		function __construct($text){
			self::$text = $text;
		}
		
		public function passed(){
			return self::$passed;
		}
		
		public function failed(){
			return (self::$passed === true) ? false : true;
		}
		
		public function email(){
			if(!preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/', self::$text)) self::$passed = false;
			return $this;
		}
		
		public function length($int){
			if(strlen(self::$text) !== (int)$int) self::$passed = false;
			return $this;
		}
		
		public function max_length($int){
			if(strlen(self::$text) >= (int)$int) self::$passed = false;
			return $this;
		}
		
		public function min_length($int){
			if(strlen(self::$text) <= (int)$int) self::$passed = false;
			return $this;
		}
		
		public function required(){
			$text = preg_replace('/\s\s+/', '', self::$text);
			if(empty($text) || $text === ' ') self::$passed = false;
			return $this;
		}
		
		public function match($match){
			if(self::$text !== $match) self::$passed = false;
			return $this;
		}
		
		public function number(){
			if(!preg_match('/\d+/', self::$text)) self::$passed = false;
			return $this;
		}
	
	}