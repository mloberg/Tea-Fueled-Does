<?php

	class Hooks extends App{
	
		function __construct(){
			parent::__construct();
		}
		
		function initialize(){
			
		}
		
		function render(){
			$this->css->load('reset');
		}
		
		function login($user){
			
		}
		
		function logout(){
			
		}
	
	}