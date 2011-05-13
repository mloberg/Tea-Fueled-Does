<?php

	if(function_exists($_GET['ajax'])){
		echo $_GET['ajax']();
	}elseif(file_exists(AJAX_DIR.$_GET['ajax'].EXT)){
		include AJAX_DIR.$_GET['ajax'].EXT;
	}
	
	/**
	 * Add your calls below this line.
	 */
	
	function ajax(){
		return 'call from ajax';
	}