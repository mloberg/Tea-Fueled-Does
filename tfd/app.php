<?php namespace TFD;
	
	use Content\Hooks;
		
	class App {
	
		private static $request;
		
		/**
		 * @param string $request Request string
		 */

		public function __construct($request) {
			session_start();
			Hooks::spinup();
			Request::make($request);
			Flash::bootstrap();
		}
		
		public function __destruct() {
			Hooks::spindown();
		}
		
		public function site() {
			$route = Route::run(Request::get(), Request::method());
			
			if ($route === false) $route = array();
			if (!is_array($route)) return $route;
			if (!isset($route['view'])) $route['view'] = Request::get();
			if (!isset($route['master'])) $route['master'] = 'master';
			
			$render = Render::page($route);
			return (string)Response::make($render->render(), $render->status());
		}
	
	}
