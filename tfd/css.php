<?php namespace TFD;

	use TFD\Config;
	
	class CSS {
	
		private static $stylesheets = array();
		private static $styles = null;

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
			ksort(self::$stylesheets);
			$render = implode('', self::$stylesheets);
			if (!is_null(self::$styles)) {
				$render .= '<style>'.self::$styles.'</style>';
			}
			return $render;
		}

		/**
		 * Clear loaded styles and stylesheets.
		 *
		 * @return boolean True
		 */

		public static function reset() {
			self::$stylesheets = array();
			self::$styles = null;
			return true;
		}

		/**
		 * Load a stylesheet.
		 *
		 * @param string|array $src Stylesheet location or name
		 * @param integer $order Load order (does not apply to arrays)
		 */
		
		public static function load($src, $order = null) {
			$preloaded = Config::get('css.stylesheets');
			// because people like to start counting at 1
			if (is_int($order)) $order--;
			if (is_array($src)) {
				foreach ($src as $stylesheet) {
					// check if it's a preloaded stylesheet
					if (isset($preloaded[$stylesheet])) {
						$stylesheet = $preloaded[$stylesheet];
					}
					self::$stylesheets[] = static::prepare($stylesheet);
				}
			} else {
				// check if it's a preloaded stylesheet
				if (isset($preloaded[$src])) {
					$src = $preloaded[$src];
				}
				$src = static::prepare($src);
				if (is_null($order)) {
					self::$stylesheets[] = $src;
				} elseif (isset(self::$stylesheets[$order])) {
					array_splice(self::$stylesheets, $order, 0, $src);
				} else {
					self::$stylesheets[$order] = $src;
				}
			}
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
			self::$styles .= $sheet;
		}
	
	}
