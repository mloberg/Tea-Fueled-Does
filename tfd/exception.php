<?php namespace TFD;

	class Exception extends \Exception{
	
		public function __construct($message, $code = 0, Exception $previous = null){
			parent::__construct($message, $code, $previous);
			$this->bootstrap($message, $code);
		}
		
		public function __toString(){
			return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		}
		
		private function bootstrap($message, $code){
			switch($code){
				case 1:
					$this->log_error($message);
					break;
				case 2:
					$this->email_error($message);
					break;
				case 3:
					$this->log_error($message);
					$this->email_error($message);
					break;
			}
		}
		
		private function log_error($message){
			$time = date("m d, Y H:i:s");
			error_log("{$time} - {$message}\n", 3, ERROR_LOG_LOCATION);
		}
		
		private function email_error($message){
			error_log($message, 1, Config::get('application.admin_email'));
		}
	
	}