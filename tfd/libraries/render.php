<?php

	class Render{
	
		static private $elements = array();
		
		function __get($name){
			if(array_key_exists($name, self::$elements)){
				return self::$elements[$name];
			}
		}
		
		function __set($name, $value){
			self::$elements[$name] = $value;
		}
		
		function clear(){
			self::$elements = array();
		}
	
	}