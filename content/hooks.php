<?php namespace Content;

	use \TFD\Library\CSS;
	
	abstract class Hooks{
		
		static function spinup(){
			
		}
		
		static function admin(){
			
		}
		
		static function www(){
			
		}
		
		static function pre_render(){
			CSS::load('reset');
		}
		
		static function post_render(){
			
		}
		
		static function login($user){
			
		}
		
		static function logout(){
			
		}
	
	}