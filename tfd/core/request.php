<?php

	class Request{
	
		private static $request;
		
		function __construct($request){
			self::$request = self::parse_request($request);
		}
		
		function __toString(){
			return self::$request;
		}
		
		private static function parse_request($req){
			if(empty($req)){
				return 'index';
			}elseif(preg_match('/\/$/', $req)){
				return $req.'index';
			}else{
				return $req;
			}
		}
	
	}