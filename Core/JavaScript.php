<?php namespace TFD\Core;

	use TFD\Core\Config;
	
	class JavaScript {

		protected static $library = array();
		protected static $scripts = array();
		protected static $script = array();
		protected static $ready = array();
		protected static $ready_string = '(function(){ ++SOURCE++ })';
		
		/**
		 * Prepare a external JavaScript asset.
		 * 
		 * @param string $src Script location
		 * @return string Prepared script
		 */

		protected static function prepare($src) {
			if (!preg_match('/^http(s*)\:\/\//', $src)) {
				if (!preg_match('/^\//', $src)) $src = '/' . $src;
				$src = Config::get('site.url').$src;
			}
			return '<script src="'.$src.'"></script>';
		}

		/**
		 * Render the JavaScript for the page.
		 *
		 * @return string Rendered JavaScript
		 */
		
		public static function render() {
			$return = implode('', static::$scripts);
			if(!empty(static::$script) || !empty(static::$ready)) {
				$return .= '<script>';
				$return .= implode(';', static::$script);
				if (!empty(static::$ready)) {
					$return .= str_replace('++SOURCE++', implode(';', static::$ready), static::$ready_string);
				}
				$return .= '</script>';
			}
			return $return;
		}

		/**
		 * Clear out loaded data.
		 * 
		 * @return boolean True
		 */

		public static function reset() {
			static::$scripts = array();
			static::$script = array();
			static::$ready = array();
			static::$ready_string = '(function(){ ++SOURCE++ })';
			return true;
		}

		/**
		 * Load a JavaScript to the page.
		 *
		 * @param string $src JavaScript location or library name
		 * @param integer $order Load order
		 */
		
		public static function load($src, $order = null) {
			// because people like to start counting at 1
			if (is_int($order)) $order--;
			if (is_array($src)) {
				foreach ($src as $script) {
					$script = static::parse($script);
					if (!in_array($script, static::$scripts)) {
						static::$scripts[] = $script;
					}
				}
			} else {
				$script = static::parse($src);
				if (isset(static::$scripts[$order])) {
					array_splice(static::$scripts, $order, 0, $script);
				} else {
					static::$scripts[] = $script;
				}
			}
		}

		/**
		 * Parse a JavaScript source.
		 *
		 * @param string $name JavaScript source
		 * @return string Parsed JavaScript
		 */

		private static function parse($name) {
			if (isset(static::$library[$name])) {
				$script = static::$library[$name];
				if (isset($script['depends'])) {
					static::load($script['depends']);
				}
				if (isset($script['ready'])) {
					static::$ready_string = $script['ready'];
				}
				return static::prepare($script['source']);
			}
			return static::prepare($name);
		}

		/**
		 * Load JavaScript libraries.
		 *
		 * @param Array $lib JavaScript libraries
		 */

		public static function library($lib) {
			static::$library = $lib + static::$library;
		}
		
		/**
		 * Functions, vars, etc. that go outside of the ready function.
		 *
		 * @param string $script Script
		 * @param integer $order Script order
		 */
		
		public static function script($script, $order = null) {
			if (isset(static::$script[$order])) {
				array_splice(static::$script, $order, 0, $script);
			} else {
				static::$script[] = $script;
			}
		}
		
		/**
		 * Function, vars, etc. that go inside of the read function.
		 *
		 * @param string $script Script
		 * @param integer $order Script order
		 */
		
		public static function ready($script, $order = null) {
			if (isset(static::$ready[$order])) {
				array_splice(static::$ready, $order, 0, $script);
			} else {
				static::$ready[] = $script;
			}
		}
	
	}
