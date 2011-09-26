<?php namespace TFD\Library;

	abstract class HTML{
	
		private static function text($text,$size,$extra){
			$string = "<{$size}";
			if($extra !== ''){
				$string .= " {$extra}";
			}
			$string .= ">{$text}</{$size}>\n";
			return $string;
		}
		
		public static function p($text,$e=''){return self::text($text,'p',$e);}
		public static function h1($text,$e=''){return self::text($text,'h1',$e);}
		public static function h2($text,$e=''){return self::text($text,'h2',$e);}
		public static function h3($text,$e=''){return self::text($text,'h3',$e);}
		public static function h4($text,$e=''){return self::text($text,'h4',$e);}
		public static function h5($text,$e=''){return self::text($text,'h5',$e);}
		public static function h6($text,$e=''){return self::text($text,'h6',$e);}
		
		private static function html_list($type,$list_items,$extra){
			$string = "<{$type}";
			if($extra !== ''){
				$string .= " {$extra}";
			}
			$string .= ">\n";
			foreach($list_items as $li){
				$string .= "<li>{$li}</li>\n";
			}
			$string .= "</{$type}>\n";
			return $string;
		}
		
		public static function br(){return "<br />\n";}
		
		public static function ul($li,$e=''){return self::html_list('ul',$li,$e);}
		public static function ol($li,$e=''){return self::html_list('ol',$li,$e);}
		
		public static function img(){
		
		}
	
	}
	
	abstract class Form{
	
		public static function open($action,$method='post',$e=''){
			if(is_array($e)){
				foreach($e as $k => $v){
					$extra .= " {$k}=\"{$v}\"";
				}
			}elseif($e !== ''){
				$extra = ' '.$e;
			}
			return "<form action=\"{$action}\" method=\"{$method}\"{$extra}>\n";
		}
		
		public static function open_upload($action,$extra=''){
			if($extra !== ''){
				$extra = $extra . 'enctype="multipart/form-data"';
			}else{
				$extra = 'enctype="multipart/form-data"';
			}
			return self::open($action,'post',$extra);
		}
		
		public static function label($for,$text){return "<label for=\"{$for}\">{$text}</label>";}
		
		private static function form_input($type,$name,$value,$extra){
			if($_POST[$name] && $_POST[$name] !== 'submit'){
				$value = $_POST[$name];
			}elseif($_GET[$name] && $_GET[$name] !== 'submit'){
				$value = $_GET[$name];
			}
			$string = "<input type=\"{$type}\" name=\"{$name}\"";
			if($type !== 'file'){
				$string .= " value=\"{$value}\"";
			}
			if(is_array($extra)){
				foreach($extra as $k => $v){
					$string .= " {$k}=\"{$v}\"";
				}
			}elseif($extra !== ""){
				$string .= " {$extra}";
			}
			$string .= " />\n";
			return $string;
		}
		
		public static function input($name,$value='',$opts=''){return self::form_input('text',$name,$value,$opts);}
		public static function password($name,$value='',$opts=''){return self::form_input('password',$name,$value,$opts);}
		public static function file_upload($name,$opts=''){return self::form_input('file',$name,'',$opts);}
		public static function submit($value,$opts=''){return self::form_input("submit","submit",$value,$opts);}
		public static function textarea($name,$value='',$opts=''){
			$string = "<textarea name=\"{$name}\"";
			if(is_array($opts)){
				foreach($opts as $k => $v){
					$string .= " {$k}=\"{$v}\"";
				}
			}elseif($opts !== ''){
				$string .= " {$opts}";
			}
			if($_POST[$name]){
				$value = $_POST[$name];	
			}elseif($_GET[$name]){
				$value = $_GET[$name];
			}
			$string .= ">{$value}</textarea>\n";
			return $string;
		}
		
		public static function dropdown($name,$values,$selected='',$opts=''){
			$string = "<select name=\"{$name}\"";
			if(is_array($opts)){
				foreach($opts as $k => $v){
					$string .= " {$k}=\"{$v}\"";
				}
			}elseif($opts !== ''){
				$string .= ' '.$opts;
			}
			$string .= ">\n";
			foreach($values as $key => $value){
				$string .= "<option value=\"{$key}\"";
				if($key == $selected){
					$string .= " selected=\"selected\"";
				}
				$string .= ">{$value}</option>\n";
			}
			$string .= "</select>\n";
			return $string;
		}
		
		public static function multi($type,$name,$values,$extra){
			$template = "<input type=\"{$type}\" name=\"{$name}\"";
			foreach($values as $k => $v){
				if($v !== '') $v = ' '.$v;
				$string .= "{$template} value=\"{$k}\"{$v}";
				if(is_array($extra)){
					foreach($extra as $k => $v){
						$string .= " {$k}=\"{$v}\"";
					}
				}elseif($extra !== ""){
					$string .= " {$extra}";
				}
				$string .= " />{$k}<br />\n";
			}
			return $string;
		}
		
		public static function radio($name,$values,$opts=''){return self::multi('radio',$name,$values,$opts);}
		public static function checkbox($name,$values,$opts=''){return self::multi("checkbox",$name,$values,$opts);}
		
		public static function close(){return "</form>\n";}
	
	}