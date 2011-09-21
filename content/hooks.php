<?php namespace Content;

	use \TFD\Library\CSS;
	
	abstract class Hooks{
		
		static function initialize(){
			
		}
		
		static function admin(){
			
		}
		
		static function front(){
			
		}
		
		static function render(){
			CSS::load('reset');
		}
		
		static function login($user){
			
		}
		
		static function logout(){
			
		}
	
	}