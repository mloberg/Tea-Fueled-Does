<?php namespace TFD;

	class Session {

		/**
		 * Register a custom session handler.
		 */

		public static function register() {
			$handler = Config::get('session.handler');
			// if session.handler is empty, return
			if (empty($handler)) return;
			session_save_path(Config::get('session.save_path'));
			$handler = static::instance(Config::get('session.handler'));
			session_set_save_handler(
				array($handler, 'open'),
				array($handler, 'close'),
				array($handler, 'read'),
				array($handler, 'write'),
				array($handler, 'destroy'),
				array($handler, 'gc')
			);
			register_shutdown_function('session_write_close');
		}

		/**
		 * Get an instance of a session handler.
		 *
		 * @param string $class Session class name
		 * @return object Session handler
		 */

		private static function instance($class) {
			switch (strtolower($class)) {
				case 'redis':
					return new Session\Redis;
					break;
				default:
					throw new \Exception("Unknown session handler '{$class}'");
					break;
			}
		}

	}
