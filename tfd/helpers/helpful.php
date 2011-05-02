<?php
	
	function stylesheet_link_tag($style,$ext='css'){
		$css;
		if(is_array($style)){
			foreach($style as $s){
				$css .= "<link rel=\"stylesheet\" href=\"css/{$s}.{$ext}\" />\n";
			}
		}else{
			$css = "<link rel=\"stylesheet\" href=\"css/{$style}.{$ext}\" />\n";
		}
		return $css;
	}
	
	function redirect($location){
		// check if external link
		if(preg_match('/^http/',$location)){
			header("Location: $location");
			exit();
		}else{
			header("Location: ".BASE_URL.$location);
			exit();
		}
	}
	
	function image_tag($src,$alt){
		$img = BASE_URL.'img/'.$src;
		list($w,$h,$type,$attr) = getimagesize($img);
		return '<img src="'.$img.'" alt="'.$alt.'" '.$attr.' />'."\n";
	}
	
	function mootools($more=false){
		$mootools = "<script src=\"js/mootools-core-1.3.1.min.js\"></script>\n";
		if($more){
			$mootools .= "<script src=\"js/mootools-more-1.3.1.1.min\"></script>\n";
		}
		return $mootools;
	}
	
	function script($script){
		return "<script src=\"js/{$script}.js\"></script>\n";
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