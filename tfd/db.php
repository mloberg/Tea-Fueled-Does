<?php namespace TFD;

	use TFD\DB\MySQL;

	class DB {

		/**
		 * Redirect all static calls to the default db connector.
		 */

		public static function __callStatic($method, $parameters) {
			return call_user_func_array(array(Config::get('db.class'), $method), $parameters);
		}

	}