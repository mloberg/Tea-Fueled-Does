<?php namespace TFD\Core\Exception;

	/**
	 * Take an exception and examine it
	 */
	
	use TFD\Core\File;
	
	class Examiner{
	
		public $exception;
		
		// readable error levels
		
		private $levels = array(
			0 => 'Error',
			E_ERROR => 'Error',
			E_WARNING => 'Warning',
			E_PARSE => 'Parsing Error',
			E_NOTICE => 'Notice',
			E_CORE_ERROR => 'Core Error',
			E_CORE_WARNING => 'Core Warning',
			E_COMPILE_ERROR => 'Compile Error',
			E_COMPILE_WARNING => 'Compile Warning',
			E_USER_ERROR => 'User Error',
			E_USER_WARNING => 'User Warning',
			E_USER_NOTICE => 'User Notice',
			E_STRICT => 'Runtime Notice'
		);
		
		public function __construct($e){
			$this->exception = $e;
		}
		
		public function severity(){
			if(array_key_exists($this->exception->getCode(), $this->levels)){
				return $this->levels[$this->exception->getCode()];
			}
			return $this->exception->getCode();
		}
		
		public function message(){
			$file = 'TFD/'.str_replace(array(CORE_DIR, APP_DIR), array('CORE_DIR/', 'APP_DIR/'), $this->exception->getFile());
			return rtrim($this->exception->getMessage(), '.').' in '.$file.' on line '.$this->exception->getLine().'.';
		}
		
		public function context(){
			return File::snapshot($this->exception->getFile(), $this->exception->getLine());
		}
		
		/**
		 * Pass all other method calls to the exception
		 */
		
		public function __call($method, $parameters){
			return call_user_func_array(array($this->exception, $method), $parameters);
		}
	
	}