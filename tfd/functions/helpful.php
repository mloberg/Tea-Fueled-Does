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
			if(!preg_match('/^\//', $location)) $location = '/' . $location;
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
	 * Original script by Jesse Donat <donatj@gmail.com> (https://github.com/donatj/)
	 */
	
	function parse_user_agent($u_agent = null){ 
		if(is_null($u_agent)) $u_agent = $_SERVER['HTTP_USER_AGENT'];

		$data = array();

		if( preg_match('/\((.*?)\)/im', $u_agent, $regs) ) {

			# (?<platform>Android|iPhone|iPad|Windows|Linux|Macintosh|Windows Phone OS|Silk)(?: NT)?(?:[ /][0-9._]+)*(;|$)
			preg_match_all('%(?P<platform>Android|iPhone|iPad|Windows|Linux|Macintosh|Windows Phone OS|Silk)(?: NT)?(?:[ /][0-9._]+)*(;|$)%im', $regs[1], $result, PREG_PATTERN_ORDER);
			$result['platform'] = array_unique($result['platform']);
			if( count($result['platform']) > 1 ) {
				if( ($key = array_search( 'Android', $result['platform'] )) !== false ) {
					$data['platform']  = $result['platform'][$key];
				}
			}else{
				$data['platform'] = $result['platform'][0];
			}

		}

		# (?<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|Silk|Lynx|Version)(?:[/ ])(?<version>[0-9.]+)
		preg_match_all('%(?P<browser>Camino|Kindle|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|Silk|Lynx|Version)(?:[/ ])(?P<version>[0-9.]+)%im', $u_agent, $result, PREG_PATTERN_ORDER);

		//print_r( $result );

		if( ($key = array_search( 'Kindle', $result['browser'] )) !== false || ($key = array_search( 'Silk', $result['browser'] )) !== false ) {
			$data['browser']  = $result['browser'][$key];
			$data['platform'] = 'Kindle';
			$data['version']  = $result['version'][$key];
		}elseif( $result['browser'][0] == 'AppleWebKit' ) {
			if( ( $data['platform'] == 'Android' && !($key = 0) ) || $key = array_search( 'Chrome', $result['browser'] ) ) {
				$data['browser'] = 'Chrome';
				if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
			}elseif( $key = array_search( 'Kindle', $result['browser'] ) ) {
				$data['browser'] = 'Kindle';
			}elseif( $key = array_search( 'Safari', $result['browser'] ) ) {
				$data['browser'] = 'Safari';
				if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
			}else{
				$key = 0;
			}
			
			$data['version'] = $result['version'][$key];
		}elseif( ($key = array_search( 'Opera', $result['browser'] )) !== false ) {
			$data['browser'] = $result['browser'][$key];
			$data['version'] = $result['version'][$key];
			if( ($key = array_search( 'Version', $result['browser'] )) !== false ) { $data['version'] = $result['version'][$key]; }
		}elseif( $result['browser'][0] == 'MSIE' ){
			if( $key = array_search( 'IEMobile', $result['browser'] ) ) {
				$data['browser'] = 'IEMobile';
			}else{
				$data['browser'] = 'MSIE';
				$key = 0;
			}
			$data['version'] = $result['version'][$key];
		}elseif( $key = array_search( 'Kindle', $result['browser'] ) ) {
			$data['browser'] = 'Kindle';
			$data['platform'] = 'Kindle';
		}else{
			$data['browser'] = $result['browser'][0];
			$data['version'] = $result['version'][0];
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