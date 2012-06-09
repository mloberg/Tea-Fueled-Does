<?php namespace TFD\Core;

	class Form{
	
		private static $labels = array();
		
		private static function id($name, $attributes){
			if(array_key_exists('id', $attributes)) return $attributes['id'];
			if(in_array($name, self::$labels)) return $name;
		}
		
		public static function open($action = null, $method = 'POST', $attributes = array()){
			if(is_null($action)){
				$action = Config::get('site.url').App::request();
			}elseif(filter_var($action, FILTER_VALIDATE_URL) === false){
				if(!preg_match('/^\//', $action)) $action = '/' . $action;
				$action = Config::get('site.url').$action;
			}
			$attributes['action'] = HTML::entities($action);
			
			// PUT and DELETE methods are spoofed using a hidden field
			$attributes['method'] = ($method == 'PUT' || $method == 'DELETE') ? 'POST' : $method;
			
			$html = '<form'.HTML::attributes($attributes).'>';
			
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
		
		public static function label($name, $value, $attributes = array()){
			self::$labels[] = $name;
			
			return '<label for="'.$name.'"'.HTML::attributes($attributes).'>'.HTML::entities($value).'</label>';
		}
		
		public static function input($type, $name, $value = null, $attributes = array()){
			$name = (isset($attributes['name'])) ? $attributes['name'] : $name;
			
			$id = self::id($name, $attributes);
			
			return '<input'.HTML::attributes(array_merge($attributes, compact('type', 'name', 'value', 'id'))).' />';
		}
		
		public static function text($name, $value = null, $attributes = array()){
			return self::input('text', $name, $value, $attributes);
		}
		
		public static function password($name, $value = null, $attributes = array()){
			return self::input('password', $name, $value, $attributes);
		}
		
		public static function hidden($name, $value = null, $attributes = array()){
			return self::input('hidden', $name, $value, $attributes);
		}
		
		public static function file($name, $attributes = array()){
			return self::input('file', $name, null, $attributes);
		}
		
		public static function textarea($name, $value = '', $attributes = array()){
			$attributes = array_merge($attributes, array('id' => self::id($name, $attributes), 'name' => $name));
			if(!isset($attributes['rows'])) $attributes['rows'] = 10;
			if(!isset($attributes['cols'])) $attributes['cols'] = 50;
			
			return '<textarea'.HTML::attributes($attributes).'>'.HTML::entities($value).'</textarea>';
		}
		
		public static function select($name, $options = array(), $selected = null, $attributes = array()){
			$attributes = array_merge($attributes, array('id' => self::id($name, $attributes), 'name' => $name));
			
			$html = array();
			
			foreach($options as $value => $display){
				$option_attributes = array('value' => HTML::entities($value), 'selected' => ($value == $selected) ? 'selected' : null);
				$html[] = '<option'.HTML::attributes($option_attributes).'>'.HTML::entities($display).'</option>';
			}
			
			return '<select'.HTML::attributes($attributes).'>'.implode('', $html).'</select>';
		}
		
		public static function checkable($type, $name, $value = null, $checked = false, $attributes = array()){
			$attributes = array_merge($attributes, array('id' => self::id($name, $attributes), 'checked' => ($checked) ? 'checked' : null));
			return self::input($type, $name, $value, $attributes);
		}
		
		public static function checkbox($name, $value = null, $checked = false, $attributes = array()){
			return self::checkable('checkbox', $name, $value, $checked, $attributes);
		}
		
		public static function radio($name, $value = null, $checked = false, $attributes = array()){
			return self::checkable('radio', $name, $value, $checked, $attributes);
		}
		
		public static function submit($value, $attributes = array()){
			return self::input('submit', null, $value, $attributes);
		}
	
	}