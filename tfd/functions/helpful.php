<?php

	function redirect($location, $message = null, $type = 'message', $options = array()){
		// check if external link
		if(preg_match('/^http/',$location)){
			header("Location: $location");
			exit();
		}else{
			if(!is_null($message)){
				global $app;
				$app->flash->redirect($message, $type, $options);
			}
			header("Location: ".Config::get('site.url').$location);
			exit();
		}
	}
	
	function image_tag($src,$alt){
		$img = Config::get('site.url').$src;
		list($w,$h,$type,$attr) = getimagesize($img);
		return '<img src="'.$img.'" alt="'.$alt.'" '.$attr.' />'."\n";
	}
	
	function post($name){
		return $_POST[$name];
	}
	
	function get($name){
		return $_GET[$name];
	}
	
	function print_p($print){
		echo '<pre>';
		print_r($print);
		echo '</pre>';
	}
	
	function href($a, $text, $admin=false){
		if(preg_match('/^http/', $a)){
			return '<a href="'.$a.'">'.$text.'</a>';
		}else{
			$href = Config::get('site.url');
			if($admin) $href .= Config::get('admin.path').'/';
			$href .= $a;
			return '<a href="'.$href.'">'.$text.'</a>';
		}
	}
	
	function matheval($equation){
		$equation = preg_replace('/[^0-9+\-.*\/()%]/', '', $equation);
		$equation = preg_replace('/([+-])([0-9]{1})(%)/', '*(1\$1.0\$2)', $equation);
		$equation = preg_replace('/([+-])([0-9]+)(%)/', '*(1\$1.\$2)', $equation);
		$equation = preg_replace('/([0-9]+)(%)/', '.\$1', $equation);
		if($equation == ''){
			$return = 0;
		}else{
			eval("\$return=" . $equation . ";" );
		}
		return $return;
	}
	
	/**
	 * Original script by donatj (https://github.com/donatj/)
	 */
	function parse_user_agent($u_agent = null){ 
		if(is_null($u_agent)) $u_agent = $_SERVER['HTTP_USER_AGENT'];
		
		$data = array();
		
		# ^.+?(?<platform>Android|iPhone|iPad|Windows|Macintosh|Windows Phone OS)(?: NT)*(?: [0-9.]+)*(;|\))
		if(preg_match('/^.+?(?P<platform>Android|iPhone|iPad|Windows|Macintosh|Windows Phone OS)(?: NT)*(?: [0-9.]+)*(;|\))/im', $u_agent, $regs)){
			$data['platform'] = $regs['platform'];
		}else{
			$result = "";
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