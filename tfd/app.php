<?php namespace TFD;
	
	class App {
		
		/**
		 * @param string $request Request string
		 */

		public function __construct() {
			Event::fire('spinup');
			Flash::bootstrap();
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
