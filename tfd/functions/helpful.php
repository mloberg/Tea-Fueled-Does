<?php

	/**
	 * Redirect to another internal or external website
	 */
	
	function redirect($location){
		// check if external link
		if(preg_match('/^https?:\/\//',$location)){
			header('Location: ' . $location);
			exit();
		}else{
			header('Location: '.Config::get('site.url').$location);
			exit();
		}
	}
	
	/**
	 * Get a POST request variable
	 */
	
	function post($name){
		return $_POST[$name];
	}
	
	/**
	 * Get a GET request variable
	 */
	
	function get($name){
		return $_GET[$name];
	}
	
	/**
	 * Print pretty (print_r wrapped with <pre> tags
	 */
	
	function print_p($print){
		echo '<pre>';
		print_r($print);
		echo '</pre>';
	}
	
	/**
	 * Evaluate a math function stored as a string
	 */
	
	function matheval($equation){
		$equation = preg_replace('/[^0-9+\-.*\/()%]/', '', $equation);
		$equation = preg_replace('/([+-])([0-9]{1})(%)/', '*(1\$1.0\$2)', $equation);
		$equation = preg_replace('/([+-])([0-9]+)(%)/', '*(1\$1.\$2)', $equation);
		$equation = preg_replace('/([0-9]+)(%)/', '.\$1', $equation);
		if(empty($equation)) return 0;
		eval("\$return=" . $equation . ";" );
		return $return;
	}
	
	/**
	 * Parse a User Agent string into it's different parts
	 * Original script by donatj (https://github.com/donatj/)
	 */
	
	function parse_user_agent($u_agent = null){ 
		if(is_null($u_agent)) $u_agent = $_SERVER['HTTP_USER_AGENT'];
		
		$data = array();
		
		# ^.+?(?<platform>Android|iPhone|iPad|Windows|Macintosh|Windows Phone OS)(?: NT)*(?: [0-9.]+)*(;|\))
		if(preg_match('/^.+?(?P<platform>Android|iPhone|iPad|Windows|Macintosh|Windows Phone OS)(?: NT)*(?: [0-9.]+)*(;|\))/im', $u_agent, $regs)){
			$data['platform'] = $regs['platform'];
		}else{
			$result = '';
		}
		
		# (?<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera)(?:[/ ])(?<version>[0-9.]+)
		preg_match_all('%(?P<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera)(?:[/ ])(?P<version>[0-9.]+)%im', $u_agent, $result, PREG_PATTERN_ORDER);
		
		if($result['browser'][0] == 'AppleWebKit'){
			if(($data['platform'] == 'Android' && !($key = 0)) || $key = array_search('Chrome', $result['browser'])){
				$data['browser'] = 'Chrome';
			}elseif($key = array_search( 'Kindle', $result['browser'])){
				$data['browser'] = 'Kindle';
			}elseif($key = array_search('Safari', $result['browser'])){
				$data['browser'] = 'Safari';
			}else{
				$key = 0;
				$data['browser'] = 'webkit';
			}
			$data['version'] = $result['version'][$key];
		}elseif($key = array_search('Opera', $result['browser'])){
			$data['browser'] = $result['browser'][$key];
			$data['version'] = $result['version'][$key];
		}elseif($result['browser'][0] == 'MSIE'){
			if($key = array_search('IEMobile', $result['browser'])){
				$data['browser'] = 'IEMobile';
			}else{
				$data['browser'] = 'MSIE';
				$key = 0;
			}
			$data['version'] = $result['version'][$key];
		}else{
			$data['browser'] = $result['browser'][0];
			$data['version'] = $result['version'][0];
		}
		
		if($data['browser'] == 'Kindle'){
			$data['platform'] = 'Kindle';
		}
		
		return $data;
	}
	
	/**
	 * Check for a multidimensional array
	 */
	
	function is_multi($array){
		if(!is_array($array)) throw new LogicException('is_multi expects an array.');
		$rv = array_filter($array, 'is_array');
		if(count($rv) > 0) return true;
		return false;
	}