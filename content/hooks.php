<?php

	abstract class Hooks{
		
		static function initialize(){
			
		}
		
		static function admin(){
			
		}
		
		static function front(){
			
		}
		
		static function render(){
			global $app;
			$app->css->load('reset');
		}
		
		static function login($user){
			
		}
		
		static function logout(){
			
		}
	
	}