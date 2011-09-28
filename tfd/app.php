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
			$do = self::$request->run();
			if($do !== false) return $do;
			
			$router = new Router($this->request()); // create a router object
			$route = $router->get();
			
			if(is_array($route)){
				if(($route['auth'] || $route['admin']) && !Admin::loggedin()){
					// need to login
					setcookie('redirect', $this->request(), time() + 3600);
					redirect(LOGIN_PATH);
					exit;
				}
				if($route['admin']){
					return Admin::dashboard($route);
				}
				$render_info = $route;
			}else{
				$render_info = array('view' => $this->request());
			}
			
			Hooks::www();
			
			$render = Render::page($render_info);
			
			return Response::make($render->render());
		}
	
	}