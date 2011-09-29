<?php namespace TFD\Form;

	use TFD\App;
	use TFD\Config;
	use TFD\HTML as h;
	
	class HTML{
	
		public static function open($action = null, $method = 'POST', $attributes = array()){
			if(is_null($action)){
				$action = Config::get('site.url').App::request();
			}elseif(filter_var($action, FILTER_VALIDATE_URL) === false){
				$action = Config::get('site.url').$action;
			}
			$attributes['action'] = h::entities($action);
			
			// PUT and DELETE methods are spoofed using a hidden field
			$attributes['method'] = ($method == 'PUT' || $method == 'DELETE') ? 'POST' : $method;
			
			$html = '<form'.h::attributes($attributes).'>';
			
			if($method == 'PUT' || $method == 'DELETE'){
				$html .= self::input('hidden', 'REQUEST_METHOD', $method);
			}
			
			return $html;
		}
		
		public static function open_upload($action = null, $method = 'POST', $attributes = array()){
			$attributes['enctype'] = 'multipart/form-data';
			return self::open($action, $method, $attributes);
		}
		
		public static function close(){
			return '</form>';
		}
	
	}