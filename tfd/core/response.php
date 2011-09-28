<?php namespace TFD\Core;

	class Reponse{
	
		public static function send_header_code($code){
			switch($code){
				case 404:
					header('HTTP/1.1 404 Not Found');
					break;
			}
		}
		
		public static function redirect($location){
			$redirect = (!preg_match('/^http(s?):\/\//', $location)) ? BASE_URL.$location : $location;
			header("Location: {$redirect}");
			exit;
		}
	
	}