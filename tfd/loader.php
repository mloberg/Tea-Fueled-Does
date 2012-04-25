<?php namespace TFD;

	class LoaderException extends \Exception { }

	class Loader {
	
		private static $alias = array();
		
		/**
		 * Autoloader
		 * 
		 * @param string $name Class name
		 */

		public static function autoload($name) {
			$file = BASE_DIR.strtolower(str_replace('\\', '/', $name)).EXT;
			if (array_key_exists($name, static::$alias)) {
				$alias = static::$alias[$name];
				$file = $alias['file'] ?: BASE_DIR.strtolower(str_replace('\\', '/', $alias['class'])).EXT;
				if (file_exists($file)) {
					include_once($file);
					class_alias(self::$alias[$name]['class'], $name);
				} else {
					throw new LoaderException("Could not load class {$name}. No file found at {$file}");
				}
			} elseif (file_exists($file)) {
				include_once($file);
			} elseif (file_exists(LIBRARY_DIR.$name.EXT)) {
				include_once(LIBRARY_DIR.$name.EXT);
			} else {
				throw new LoaderException("Could not load class {$name}. No file found at {$file}");
			}
		}

		/**
		 * Add a class alias.
		 *
		 * @param string|array $name Name of alias or array of aliases
		 * @param string $class Class to alias to
		 * @param string $file Class file (if it does not match Namespace to file convention)
		 */

		public static function alias($name, $class = null, $file = null) {
			if (is_array($name)) {
				foreach ($name as $alias => $class) {
					static::$alias[$alias] = array('class' => $class);
				}
			} else {
				static::$alias[$name] = array('class' => $class, 'file' => $file);
			}
		}
	
	}
