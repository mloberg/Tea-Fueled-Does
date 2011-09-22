<?php namespace Content;

	use \TFD\Library\CSS;
	use \TFD\Library\JavaScript;
	
	abstract class Hooks{
		
		static function spinup(){
			
		}
		
		static function admin(){
			
		}
		
		static function www(){
			
		}
		
		static function pre_render(){
			CSS::load('reset');
			JavaScript::library('mootools');
		}
		
		static function post_render(){
			
		}
		
		static function spindown(){
			
		}
		
		static function login($user){
			
		}
		
		static function logout(){
			
		}
	
	}