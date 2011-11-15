<?php namespace TFD;

	class File{
	
		public static function get($path){
			return file_get_contents($path);
		}
		
		public static function put($path, $data){
			return file_put_contents($path, $data, LOCK_EX);
		}
		
		public static function append($path, $data){
			return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
		}
		
		public static function snapshot($path, $line, $padding = 5){
			if(!file_exists($path)) return array();
			$file = file($path, FILE_IGNORE_NEW_LINES);
			
			array_unshift($file, '');
			
			if(($start = $line - $padding) < 0) $start = 0;
			if(($length = ($line - $start) + $padding + 1) < 0) $length = 0;
			return array_slice($file, $start, $length, true);
		}
	
	}