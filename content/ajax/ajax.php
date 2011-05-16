<?php

	class Ajax extends TFD{
	
		function call(){
			if(method_exists(__CLASS__, $_GET['ajax'])){
				return $this->ajax->$_GET['ajax']();
			}elseif(file_exists(AJAX_DIR.$_GET['ajax'].EXT)){
				include AJAX_DIR.$_GET['ajax'].EXT;
			}
		}
		
		function test(){
			return 'foobar';
		}
	
	}