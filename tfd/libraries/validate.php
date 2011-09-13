<?php

	class Validate{
	
		private static $passed = true;
		private static $text;
		
		function __construct($text){
			self::$text = trim($text);
		}
		
		function __toString(){
			return self::$passed;
		}
		
		function text($text){
			self::$text = trim($text);
			return $this;
		}
		
		function clear($text = null){
			if(!is_null($text)) self::$text = trim($text);
			self::$passed = true;
			return $this;
		}
		
		function email(){
			self::$passed = (preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/', self::$text) && self::$passed !== false) ? true : false;
			return $this;
		}
		
		function max_len($int){
			self::$passed = (strlen(self::$text) <= $int && self::$passed !== false) ? true : false;
			return $this;
		}
		
		function min_len($int){
			self::$passed = (strlen(self::$text) >= $int && self::$passed !== false) ? true : false;
			return $this;
		}
		
		function length($int){
			self::$passed = (strlen(self::$text) === $int && self::$passed !== false) ? true : false;
			return $this;
		}
		
		function req(){
			return $this->required();
		}
		
		function required(){
			$text = preg_replace('/\s\s+/', '', self::$text);
			self::$passed = ((!empty($text) || $text !== ' ') && self::$passed !== false) ? true : false;
			return $this;
		}
		
		function match($match){
			self::$passed = (self::$text === trim($match) && self::$passed !== false) ? true : false;
			return $this; 
		}
		
		function number(){
			self::$passed = (preg_match('/\d+/', self::$text) && self::$passed !== false) ? true : false;
			return $this;
		}
	
	}