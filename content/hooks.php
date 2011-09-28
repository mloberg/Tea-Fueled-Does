<?php namespace Content;

	use TFD\CSS;
	use TFD\JavaScript;
	use TFD\Flash;
	use TFD\DB\MySQL;
	
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
		
		static function render(){
			
		}
		
		static function post_render(){
			
		}
		
		static function partial(){
			
		}
		
		static function spindown(){
			
		}
		
		static function login($user){
			
		}
		
		static function logout(){
			
		}
	
	}