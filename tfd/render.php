<?php namespace TFD;

	use TFD\Render\View;
	use TFD\Render\Page;
	use TFD\Render\Error;
	
	class RenderException extends \Exception { }

	class Render {
	
		/**
		 * Render a file using output buffering.
		 *
		 * @param string $file File to render
		 * @param array $extra Variables to use in render
		 * @return string Rendered page
		 */

		protected function render_file($file, $extra = array()) {
			ob_start();
			extract($extra, EXTR_SKIP);
			include($file);
			$render = ob_get_contents();
			ob_end_clean();
			return $render;
		}

		/**
		 * Render a view.
		 *
		 * @param array $options Render options
		 */

		protected static function render_view($options) {
			if (isset($options['dir'])) {
				$dir = VIEWS_DIR.$options['dir'].'/';
			} else {
				$dir = VIEWS_DIR.Config::get('views.public').'/';
			}
			$view = $dir.$options['view'].EXT;
			if (!file_exists($view)) {
				return false;
			} else {
				unset($options['view']);
				return static::render_file($view, $options);
			}
		}
		
		/**
		 * Render a page.
		 *
		 * @param array $options Render options
		 * @return object Page render object
		 */

		public static function page($options) {
			return new Page($options);
		}

		/**
		 * Render a view.
		 *
		 * @param array $options Render options
		 * @return object View render object
		 */
		
		public static function view($options) {
			return new View($options);
		}
		
		/**
		 * Render a partial.
		 *
		 * @param string $file Partial
		 * @param array $options Partial variables
		 * @return object View render object
		 */

		public static function partial($file, $options = array()) {
			Event::fire('partial');
			$options['dir'] = Config::get('views.partials');
			$options['view'] = $file;
			return new View($options);
		}
		
		/**
		 * Render an error page.
		 *
		 * @param integer|string $type Error type (exception, 404, 500)
		 * @param array $data Page variables
		 * @return object Error page render object
		 */

		public static function error($type, $data = array()) {
			return new Error($type, $data);
		}
	
	}
