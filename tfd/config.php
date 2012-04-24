<?php namespace TFD;

	/**
	 * Used to store variables.
	 *
	 * Variables stored through Config are not persistent.
	 * Keys must be called in by "category.item".
	 * If you attempt to store a key that doesn't follow this sytax,
	 * an exception will be thrown.
	 */
	
	class Config {
	
		private static $keys = array();
		private static $group = array();
		
		/**
		 * Check for the existance of a config key.
		 *
		 * @param string $key Key to check for
		 * @return boolean True if key exists
		 */

		public static function is_set($key) {
			return (($key = static::parse($key)) !== false && (array_key_exists($key, static::$keys))) ? true : false;
		}

		/**
		 * Set a single config key.
		 *
		 * @param string $key Name of key
		 * @param mixed $value Value of key
		 * @return void
		 */
		
		public static function set($key, $value) {
			if (($key = static::parse($key)) !== false) {
				static::$keys[$key] = $value;
			} else {
				throw new \Exception($key . ' is not a valid config key. Must be in the format of category.item');
			}
		}

		/**
		 * Set a group of config keys
		 *  
		 * @param string|array $name Name of group or array of keys to load
		 * @param array|null $keys Group keys
		 */

		public static function group($name, $keys = null) {
			if (is_array($name)) {
				foreach ($name as $key => $value) {
					static::set($key, $value);
				}
			} elseif (array_key_exists($name, static::$group)) {
				static::$group[$name] = $keys + static::$group[$name];
			} else {
				static::$group[$name] = $keys;
			}
		}

		/**
		 * Load a config group
		 *
		 * @param string|array $group Config group to load
		 */
		
		public static function load($group) {
			if (is_array($group)) {
				static::group($group);
			} elseif (array_key_exists($group, static::$group)) {
				foreach (static::$group[$group] as $key => $value) {
					static::set($key, $value);
				}
			}
		}

		/**
		 * Get the value of a config key.
		 *
		 * @since 2.0a
		 * 
		 * @param string $key Key name
		 * @param mixed $default Value if key doesn't exist
		 */
		
		public static function get($key, $default = null) {
			if (($key = static::parse($key)) !== false && (array_key_exists($key, static::$keys))) {
				return static::$keys[$key];
			}
			return false;
		}

		/**
		 * Parse the key into a valid format
		 *
		 * @since 2.0a
		 * 
		 * @param string $key Key to format
		 * @return string Formatted string
		 */
		
		private static function parse($key) {
			$segments = explode('.', $key);
			if (count($segments) > 2) {
				return false;
			} elseif (count($segments) === 1) {
				return 'application.'.$segments[0];
			} else {
				return $key;
			}
		}
	
	}