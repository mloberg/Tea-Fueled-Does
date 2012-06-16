<?php namespace TFD\Core;
	
	use TFD\Core\Event;
	use TFD\Core\Render;
	use TFD\Core\Response;
	use TFD\Core\Request;

	class App {
		
		/**
		 * @param string $request Request string
		 */

		public function __construct() {
			Event::fire('spinup');
		}
		
		public function __destruct() {
			Event::fire('spindown');
		}
		
		/**
		 * The main method.
		 *
		 * @return string Site Page
		 */

		public function site() {
			$route = Route::run(Request::get(), Request::method());
			
			if ($route === false) $route = array();
			if (!is_array($route)) return $route;
			if (!isset($route['view'])) $route['view'] = Request::get();
			if (!isset($route['master'])) $route['master'] = 'master';
			
			$render = Render::page($route);
			return (string)Response::make($render->render(), $render->status);
		}
	
	}
