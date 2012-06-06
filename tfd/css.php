<?php namespace TFD;

	use TFD\Config;
	
	class CSS {
	
		protected static $stylesheets = array();
		protected static $styles = null;
		protected static $library = array();

		/**
		 * Prepare a stylesheet.
		 *
		 * @param string $src Stylesheet
		 * @return string Prepared stylesheet
		 */
		
		protected static function prepare($src) {
			if (!preg_match('/^http(s*)\:\/\//', $src)) {
				if (!preg_match('/^\//', $src)) {
					$src = '/' . $src;
				}
				$src = Config::get('site.url').$src;
			}
			return '<link rel="stylesheet" href="'.$src.'" />';
		}

		/**
		 * Return the render CSS styles.
		 *
		 * @return string CSS tags
		 */
				
		public static function render() {
			ksort(static::$stylesheets);
			$render = implode('', static::$stylesheets);
			if (!is_null(static::$styles)) {
				$render .= '<style>'.static::$styles.'</style>';
			}
			return $render;
		}

		/**
		 * Clear loaded styles and stylesheets.
		 *
		 * @return boolean True
		 */

		public static function reset() {
			static::$stylesheets = array();
			static::$styles = null;
			return true;
		}

		/**
		 * 
		 */

		public static function library($lib) {
			static::$library = $lib + static::$library;
		}

		/**
		 * Load a stylesheet.
		 *
		 * @param string|array $src Stylesheet location or name
		 * @param integer $order Load order (does not apply to arrays)
		 */
		
		public static function load($src, $order = null) {
			// because people like to start counting at 1
			if (is_int($order)) $order--;
			if (is_array($src)) {
				foreach ($src as $stylesheet) {
					static::$stylesheets[] = static::parse($stylesheet);
				}
			} else {
				$src = static::parse($src);
				if (isset(static::$stylesheets[$order])) {
					array_splice(static::$stylesheets, $order, 0, $src);
				} else {
					static::$stylesheets[$order] = $src;
				}
			}
		}

		/**
		 * Parse a CSS source.
		 * 
		 * @param string $name CSS source
		 * @return string Parsed CSS
		 */

		private static function parse($name) {
			if (isset(static::$library[$name])) {
				return static::prepare(static::$library[$name]);
			}
			return static::prepare($name);
		}

		/**
		 * Add inline CSS styling.
		 *
		 * @param array $styles An array of elements => style => value
		 */
		
		public static function style($styles) {
			$sheet = '';
			foreach ($styles as $element => $style){
				$sheet .= $element.'{';
				foreach ($style as $key => $value) {
					$sheet .= $key.':'.$value.';';
				}
				$sheet .= '}';
			}
			static::$styles .= $sheet;
		}
	
	}
