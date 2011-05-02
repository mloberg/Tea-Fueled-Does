<?php

	class Module extends App{
	
		function __construct(){
			parent::__construct();
		}
		
		function load_module($name){
			include_once(MODULE_DIR.$name.EXT);
			$module = new $name();
			return $module->index();
		}
		
		protected function request($replace){
			$replace = str_replace('/','\/', $replace);
			$req = preg_replace("/^{$replace}\//", "", $this->request);
			if($req == "") $req = "home";
			return $req;
		}
		
		protected function is_admin(){
			if(preg_match('/^'.ADMIN_PATH.'/', $this->request)){
				return true;
			}
		}
	
	}