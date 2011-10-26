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
				return $this->request();
			}else{
				$segments = explode('/', $this->request());
				return $segments[$segment - 1];
			}
		}
		
		/**
		 * Class methods
		 */
		
		private static function bootstrap($request){
			self::$request = new Request($request);
			Flash::bootstrap();
		}
		
		public function site(){			
			$router = new Router($this->request()); // create a router object
			$route = $router->get(); // get the matching route
			
			if(is_array($route)){
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
			}elseif(!is_null($route)){
				return $route;
			}elseif(($do = self::$request->run()) !== false){
				return $do;
			}else{
				$render_info = array('view' => $this->request());
			}
			
			
			Hooks::www();
			
			$render = Render::page($render_info);
			
			return Response::make($render->render(), $render->status());
		}
	
	}