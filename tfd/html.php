<?php namespace TFD;

	class HTML{
	
		private static function __build_tag($tag, $value, $attributes, $entities = true){
			$value = ($entities) ? self::entities($value) : $value;
			return '<'.$tag.self::attributes($attributes).'>'.$value.'<'.$tag.'>';
		}
		
		private static function __build_list($type, $list, $attributes, $entities = true){
			$html = '';
			foreach($list as $value){
				$html .= (is_array($value)) ? self::__build_list($type, $value) : '<li>'. (($entites) ? self::entities($value) : $value).'</li>';
			}
			return '<'.$type.self::attributes($attributes).'>'.$html.'</'.$type.'>';
		}
		
		public static function obfuscate($value){
			$safe = '';
			foreach(str_split($value) as $letter){
				switch(rand(1, 3)){
					case 1:
						$safe .= '&#'.ord($letter).';';
						break;
					case 2:
						$safe .= '&#x'.dechex(ord($letter)).';';
						break;
					case 3:
						$safe .= $letter;
				}
			}
			return $safe;
		}
		
		public static function entities($value){
			return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
		}
		
		public static function attributes($attributes){
			$html = array();
			foreach($attributes as $key => $value){
				if(is_numeric($key)) $key = $value;
				if(!is_null($value)){
					$html[] = $key.'="'.self::entities($value).'"';
				}
			}
			return (count($html) > 0) ? ' '.implode(' ', $html) : '';
		}
		
		public static function div($value, $attributes = array(), $entities = true){
			return self::__build_tag('div', $value, $attributes, $entities);
		}
		
		public static function span($value, $attributes = array(), $entities = true){
			return self::__build_tag('span', $value, $attributes, $entities);
		}
		
		public static function p($value, $attributes = array(), $entities = true){
			return self::__build_tag('p', $value, $attributes, $entities);
		}
		
		public static function h1($value, $attributes = array(), $entities = true){
			return self::__build_tag('h1', $value, $attributes, $entities);
		}
		
		public static function h2($value, $attributes = array(), $entities = true){
			return self::__build_tag('h2', $value, $attributes, $entities);
		}
		
		public static function h3($value, $attributes = array(), $entities = true){
			return self::__build_tag('h3', $value, $attributes, $entities);
		}
		
		public static function h4($value, $attributes = array(), $entities = true){
			return self::__build_tag('h4', $value, $attributes, $entities);
		}
		
		public static function h5($value, $attributes = array(), $entities = true){
			return self::__build_tag('h5', $value, $attributes, $entities);
		}
		
		public static function h6($value, $attributes = array(), $entities = true){
			return self::__build_tag('h6', $value, $attributes, $entities);
		}
		
		public static function link($url, $title, $attributes = array()){
			if(filter_var($url, FILTER_VALIDATE_URL) === false) $url = Config::get('site.url').$url;
			$attributes['href'] = $url;
			return self::__build_tag('a', $title, $attributes);
		}
		
		public static function mailto($email, $title = null, $attributes = array()){
			$email = str_replace('@', '&#64;', self::obfuscate($email));
			if(is_null($title)) $title = $email;
			return self::link('&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email, $title, $attributes);
		}
		
		public static function image($url, $alt = '', $attributes = array()){
			if(filter_var($url, FILTER_VALIDATE_URL) === false){
				$remote = false;
				$image = PUBLIC_DIR.$url;
				$url = Config::get('site.url').$url;
			}
			if((!isset($attributes['width']) || !isset($attributes['height'])) && $remote === false){
				list($attributes['width'], $attributes['height'], $type, $attr) = getimagesize($image);
			}
			$attributes['src'] = $url;
			$attributes['alt'] = $alt;
			return '<img'.self::attributes($attributes).' />';
		}
		
		public static function ul($list, $attributes = array(), $entities = true){
			if(!is_array($list)){
				$type = gettype($list);
				throw new \LogicException("HTML::ul() expects an array, {$type} sent.");
				return '';
			}else{
				return self::__build_list('ul', $list, $attributes, $entities);
			}
		}
		
		public static function ol($list, $attributes = array(), $entities = true){
			if(!is_array($list)){
				$type = gettype($list);
				throw new \LogicException("HTML::ol() expects an array, {$type} sent.");
				return '';
			}else{
				return self::__build_list('ol', $list, $attributes, $entities);
			}
		}
	
	}