<?php namespace TFD\Core;

	use TFD\Core\Config;

	class CrypterException extends \Exception { }

	class Crypter {

		/**
		 * Generate a random salt.
		 *
		 * @param integer $cost Blowfish cost parameter
		 * @return string Blowfish salt
		 */

		public static function generate_salt($cost = null) {
			if (is_null($cost)) $cost = Config::get('crypter.cost');
			if ($cost < 4 || $cost > 31) throw new CrypterException('Cost must be between 4 and 31');
			$salt = '$2a$' . str_pad($cost, 2, '0', STR_PAD_LEFT) . '$';
			$salt .= substr(str_replace('+', '.', base64_encode(openssl_random_pseudo_bytes(16))), 0, 22);
			return $salt;
		}

		/**
		 * Hash a string value using the Blowfish algorithm.
		 *
		 * @param string $input String to hash
		 * @param integer $cost Blowfish cost parameter
		 * @return string Hashed value
		 */
		
		public static function hash($input, $cost = null) {
			return crypt($input, static::generate_salt($cost));
		}
		
		/**
		 * Verify a hashed value.
		 *
		 * @param string $input String value
		 * @param string $hash Hashed value
		 * @return boolean True if input matches hash
		 */

		public static function verify($input, $hash) {
			return crypt($input, $hash) === $hash;
		}
	
	}
