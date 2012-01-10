<?php namespace TFD;

	use TFD\CSS;
	use TFD\JavaScript;
	
	class Flash{
		
		static private $flash = array(
			'options' => array(
				'time' => '2',
				'sticky' => false
			)
		);
		static private $valid_types = array('message', 'error', 'warning', 'success');
		
		public function __toString(){
			return self::render();
		}
		
		private static function __message($message, $type, $options){
			self::$flash['message'] = $message;
			self::$flash['type'] = (array_search($type, self::$valid_types)) ? $type : 'message';
			self::$flash['options'] = $options + self::$flash['options'];
		}
		
		public static function bootstrap(){
			if(!empty($_SESSION['flash']['message'])) self::_redirect();
		}
		
		public static function success($message, $options = array()){
			self::__message($message, 'success', $options);
		}
		
		public static function error($message, $options = array()){
			self::__message($message, 'error', $options);
		}
		
		public static function warning($message, $options = array()){
			self::__message($message, 'warning', $options);
		}
		
		public static function message($message, $type = 'message', $options = array()){
			if(is_array($type)){
				self::__message($message, 'message', $options);
			}else{
				self::__message($message, $type, $options);
			}
		}
		
		public static function redirect($message, $type = 'message', $options = array()){
			$_SESSION['flash'] = array('message' => $message, 'type' => $type, 'options' => $options);
		}
		
		private static function js(){
			if(self::$flash['options']['sticky'] === true) return;
			$time = self::$flash['options']['time'] * 1000;
			$js = <<<SCRIPT
<script>function animateFlashFade(){var a=document.getElementById("flash-message-wrapper"),b=a.Visibility-5;a.Visibility=b;a.style.opacity=b/100;a.style.filter="alpha(opacity = "+b+")";if(b<=0){document.body.removeChild(a)}else{setTimeout("animateFlashFade()",33)}}setTimeout(function(){var a=document.getElementById("flash-message-wrapper").Visibility=100;setTimeout("animateFlashFade()",33)}, $time)</script>
SCRIPT;
			return $js;
		}
		
		private static function _redirect(){
			if(is_array($_SESSION['flash']['options'])){
				self::$flash['options'] = $_SESSION['flash']['options'] + self::$flash['options'];
			}
			self::$flash['message'] = $_SESSION['flash']['message'];
			self::$flash['type'] = (array_search($_SESSION['flash']['type'], self::$valid_types)) ? $_SESSION['flash']['type'] : 'message';
		}
		
		public static function render(){
			if(empty(self::$flash['message'])) return;
			$type = self::$flash['type'];
			$message = self::$flash['message'];
			$styles = array(
				'message' => 'background-color:#dcdcdc;color:#000',
				'success' => 'background-color:#008000;color:#fff',
				'error' => 'background-color:#b22222;color:#fff',
				'warning' => 'background-color:#ffd700;color:#000'
			);
			$style = (isset($styles[$type])) ? $styles[$type] : $styles['message'];
			$html = <<<FLASH
<div id="flash-message-wrapper">
	<div style="$style;font-size:18px;left:0;margin:0;opacity:1;position:absolute;text-align:center;top:0;width:100%"><div style="padding:5px;">$message</div></div>
	<div style="margin-bottom:29px"></div>
</div>
FLASH;
			$js = self::js();
			// cleanup
			self::$flash = array();
			unset($_SESSION['flash']);
			// flash html
			return $html . $js;
		}
		
	}