<?php namespace TFD\Core;

	class LoaderException extends \Exception { }

	class Loader {
	
		private static $aliases = array();
		
		/**
		 * Autoloader
		 * 
		 * @param string $name Class name
		 */

		public static function autoload($name) {
			$name = ltrim($name, '\\');
			$alias = false;
			if (array_key_exists($name, static::$aliases)) {
				$file = static::parse(static::$aliases[$name]);
				$alias = self::$aliases[$name];
			} elseif(!preg_match('/^TFD\\\/', $name) && file_exists($file = static::parse($name, LIBRARY_DIR))) {
				if (preg_match('/namespace (.*);/', file_get_contents($file), $match)) {
					$alias = $match[1] . '\\' . end(explode('\\', $name));
				}
			} else {
				$file = static::parse($name);
			}
			if (!file_exists($file)) {
				throw new LoaderException("Could not load class {$name}. No file found at {$file}");
			}
			require_once($file);
			if ($alias) {
				class_alias($alias, $name);
			}
		}

		/**
		 * Parse a class name into a file.
		 *
		 * @param string $name Class name
		 * @param string $prefix File prefix
		 * @return string Filename
		 */

		protected static function parse($name, $prefix = BASE_DIR) {
			return $prefix . preg_replace('/TFD\//', '', str_replace(array('\\', '_'), '/', $name)) . EXT;
		}

		/**
		 * Register the autoloader.
		 */

		public static function register() {
			spl_autoload_register(array('static', 'autoload'));
		}

		/**
		 * Unregister the autoloader.
		 */

		public static function unregister() {
			spl_autoload_unregister(array('static', 'autoload'));
		}

		/**
		 * Add class aliases.
		 *
		 * @param array $aliases Class aliases
		 */

		public static function alias($aliases) {
			static::$aliases = $aliases + static::$aliases;
		}
	
	}
