<?php

	class Tea{
	
		function __construct(){
			echo "== Tea Fueled Does Version ",TFD_VERSION." ==\n";
			spl_autoload_register('Tea::loader');
		}
		
		static function loader($name){
			include_once(TEA_DIR.'classes'.DIRECTORY_SEPARATOR.$name.EXT);
		}
		
		function command($arg){
			if(empty($arg[1])){
				echo "Looking for help?\n";
			}else{
				$arg[1]::action($arg);
			}
		}
	
	}