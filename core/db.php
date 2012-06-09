<?php namespace TFD\Core;

	use TFD\Core\DB\MySQL;

	class DB {

		private $connector;

		/**
		 * Create a new DB object.
		 *
		 * Create a new DB object that forwards all calls to the default db.class
		 */

		public function __construct() {
			$db = new \ReflectionClass(Config::get('db.class'));
			$this->connector = $db->newInstanceArgs(func_get_args());
			return $this->connector;
		}

		/**
		 * Redirect all static calls to the default db connector.
		 */

		public static function __callStatic($method, $parameters) {
			return call_user_func_array(array(Config::get('db.class'), $method), $parameters);
		}

		/**
		 * Redirect all object calls to $this->connector
		 */

		public function __call($method, $parameters) {
			return call_user_func_array(array($this->connector, $method), $parameters);
		}

	}