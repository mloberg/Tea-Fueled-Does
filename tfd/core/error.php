<?php

	class Error extends App{
	
		function log_error($error,$die='',$die_msg='Something went wrong.'){
			$time = date("m d, Y H:i:s");
			error_log("{$time} - {$error}\n", 3, './error.log');
			if($die){
				die($die_msg);
			}elseif($this->testing && $die == ''){
				die($die_msg);
			}
		}
		
		function report($message,$die=false){
			if($die || $this->testing){
				die($message);
			}else{
				echo $message;
			}
		}
		
		function email($error,$report=false){
			error_log($error, 1, ADMIN_EMAIL);
			if($report && $this->testing){
				echo $error;
			}elseif($report){
				die($error);
			}
		}
	
	}