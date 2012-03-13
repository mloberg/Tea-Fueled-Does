<?php namespace TFD;
	
	use Content\Hooks;
	use TFD\Core\Request;
	use TFD\Core\Router;
	use TFD\Core\Render;
	use TFD\Core\Response;
		
	class App{
	
		private static $request;
		
		/**
		 * Magic Methods
		 */
		
		function __construct(){
			session_start();
			Hooks::spinup();
			self::bootstrap($_GET['tfd_request']);
		}
		
		function __destruct(){
			Hooks::spindown();
		}
		
		/**
		 * Accessors
		 */
		
		public function request(){
			return (string)self::$request;
		}
		
		public function url($segment = null){
			if($segment == null){
				return self::request();
			}else{
				$segments = explode('/', self::request());
				return $segments[$segment];
			}
		}
		
		/**
		 * Class methods
		 */
		
		private static function bootstrap($request){
			Config::set('site.url', preg_replace('/\/$/', '', Config::get('site.url')));
			if(!preg_match('/^\//', $request)) $request = '/' . $request;
			self::$request = new Request($request);
			Flash::bootstrap();
		}
		
		public function site(){			
			$router = new Router($this->request()); // create a router object
			$route = $router->get(); // get the matching route
			
			if(Request::is_maintenance()){ // maintance mode is on
				return Response::make(Render::error('maintenance'));
			}elseif(is_array($route)){ // route render options
				if(($route['auth'] || $route['admin']) && !Admin::loggedin()){
					// need to login
					setcookie('redirect', $this->request(), time() + 3600);
					redirect(Config::get('admin.login'));
					exit;
				}
				if($route['admin']){
					return Admin::dashboard($route);
				}
				$render_info = $route;
			}elseif(!is_null($route)){ // route is string
				return $route;
			}elseif(($do = self::$request->run()) !== false){ // other requests (ajax, etc)
				return $do;
			}else{ // normal request
				$render_info = array('view' => $this->request());
			}
			
			
			Hooks::www();
			
			$render = Render::page($render_info);
			
			return Response::make($render->render(), $render->status());
		}
	
	}