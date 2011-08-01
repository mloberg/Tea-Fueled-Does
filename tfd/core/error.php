<?php

	class Error extends App{
	
		function log_error($error, $die = null, $die_msg = 'Something went wrong.'){
			$time = date("m d, Y H:i:s");
			error_log("{$time} - {$error}\n", 3, './error.log');
			if($die || $this->testing && $die == null){
				echo $die_msg;
				exit;
			}
		}
		
		function report($message, $die = false){
			echo $message;
			if($die || $this->testing) exit;
		}
		
		function email($error, $report = false){
			error_log($error, 1, ADMIN_EMAIL);
			if($report && $this->testing){
				echo $error;
			}elseif($report){
				echo $error;
				exit;
			}
		}
	
	}