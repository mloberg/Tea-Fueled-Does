<?php namespace TFD\Core;

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
		 */

		public static function fire() {
			// get args
			$args = func_get_args();
			$event = array_shift($args);
			if (array_key_exists($event, static::$events)) {
				call_user_func_array(static::$events[$event], $args);
			}
		}

	}