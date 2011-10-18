<?php namespace TFD;

	class Crypter{
	
		private static $random;
		
		private static function get_salt($rounds = null){
			if(is_null($rounds)) $rounds = Config::get('crypter.rounds');
			$salt = '$2a$' . str_pad($rounds, 2, '0', STR_PAD_LEFT) . '$';
			$salt .= substr(strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 0, 22);
			return $salt;
		}
		
		public static function hash($input, $rounds = null){
			$hash = crypt($input, self::get_salt($rounds));
			if(strlen($hash) > 13) return $hash;
			return false;
		}
		
		public static function verify($input, $existing){
			$hash = crypt($input, $existing);
			return $hash === $existing;
		}
	
	}