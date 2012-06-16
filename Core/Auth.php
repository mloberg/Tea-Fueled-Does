<?php namespace TFD\Core;

	use TFD\Core\Config;
	use TFD\Core\Crypter;

	class Auth {

		/**
		 * Validate a user's fingerprint
		 *
		 * @param string $fingerprint Fingerprint to validate
		 * @param string $username Username to validate
		 * @param string $secret User's secret
		 * @return boolean True if the fingerprint is valid
		 */

		public static function valid($fingerprint, $username, $secret) {
			return Crypter::verify(static::fingerprint_string($username, $secret), $fingerprint);
		}

		/**
		 * Create a unique login fingerprint for a user
		 *
		 * @param string $username Username to login
		 * @param string $secret User's secret
		 * @return string Unique login fingerprint
		 */

		public static function login($username, $secret) {
			return static::fingerprint($username, $secret);
		}

		/**
		 * Create a fingerprint and store it to the session.
		 * 
		 * @param string $username Username of logged in user
		 * @param string $secret User's secret
		 * @return string Hashed fingerprint
		 */

		protected static function fingerprint($username, $secret) {
			return Crypter::hash(static::fingerprint_string($username, $secret));
		}

		/**
		 * Return the formatted string fingerprints use.
		 *
		 * @param string $username Username
		 * @param string $secret User secret
		 * @return string Formatted string
		 */

		protected static function fingerprint_string($username, $secret) {
			return sprintf("%s%s%s%s", Config::get('auth.key'), $username, $_SERVER['HTTP_USER_AGENT'], $secret);
		}

	}
