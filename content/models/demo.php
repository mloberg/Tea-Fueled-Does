<?php

	class Demo extends Model{
	
		function __construct(){
			parent::__construct();
		}
	
		function foo(){
			$data = $this->mysql->get("users");
			return $data;
		}
		
		function bar(){
			return "foo";
		}
	
	}