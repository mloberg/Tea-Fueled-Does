<?php namespace TFD\Exception;

	use TFD\Response;
	use TFD\Render;
	use TFD\Config;
	
	class Handler{
	
		public $exception;
		
		public function __construct($e){
			$this->exception = new Examiner($e);
		}
		
		public static function make($e){
			return new self($e);
		}
		
		public function handle($detailed = null){
			// if output buffering is still on, turn it off
			if(ob_get_level() > 0) ob_end_clean();
			// log the error if enabled
			if(Config::get('error.log')) $this->log();
			
			$this->get_response(Config::get('error.detailed'));
			
			// exit with error code
			exit(1);
		}
		
		private function log(){
			$error = Config::get('error.log');
			$time = date("m d, Y H:i:s");
			if(is_callable($error)){
				call_user_func($error, $this->exception);
			}elseif(preg_match('/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/', $error)){
				// email message
				error_log("Error occurred at {$time}\nMessage: {$this->exception->message()}", 1, $error);
			}elseif(is_string($error)){
				error_log("{$time} - {$this->exception->message()}\n", 3, $error);
			}else{
				// log the error
				error_log("{$time} - {$this->exception->message()}\n");
			}
		}
		
		private function get_response($detailed = false){
			echo ($detailed) ? $this->detailed_response() : Response::error('500');
		}
		
		private function detailed_response(){
			$data = array(
				'severity' => $this->exception->severity(),
				'message' => $this->exception->message(),
				'line' => $this->exception->getLine(),
				'trace' => $this->exception->getTraceAsString(),
				'contexts' => $this->exception->context()
			);
			return Response::make(Render::error('exception', $data)->render(), 500)->send();
		}
	
	}