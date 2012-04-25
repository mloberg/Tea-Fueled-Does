<?php namespace TFD;

	class Event {

		private static $events = array();

		/**
		 * Add an event listener.
		 *
		 * @param string $event Event name
		 * @param function $callback Event callback
		 */

		public static function listen($event, $callback) {
			static::$events[$event] = $callback;
		}

		/**
		 * Fire an event.
		 *
		 * @param string $event Event name
		 * @param mixed Callbakc variables
		 * @return mixed Callback return
		 */

		public static function fire() {
			$args = func_get_args();
			$event = array_shift($args);
			if (array_key_exists($event, static::$events)) {
				return call_user_func_array(static::$events[$event], $args);
			}
		}

	}