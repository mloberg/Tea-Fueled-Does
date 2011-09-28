<?php namespace TFD;

	use TFD\Library\CSS;
	use TFD\Library\JavaScript;
	
	class Flash{
		
		static private $flash = array(
			'options' => array(
				'time' => '2',
				'sticky' => false
			)
		);
		static private $valid_types = array('message', 'error', 'warning', 'success');
		
		function __toString(){
			return self::render();
		}
		
		public static function bootstrap(){
			if(!empty($_SESSION['flash']['message'])) self::_redirect();
		}
		
		public function message($message, $type = 'message', $options = array()){
			self::$flash['message'] = $message;
			self::$flash['type'] = (array_search($type, self::$valid_types)) ? $type : 'message';
			self::$flash['options'] = $options + self::$flash['options'];
			self::css();
		}
		
		public static function redirect($message, $type = 'message', $options = array()){
			$_SESSION['flash'] = array('message' => $message, 'type' => $type, 'options' => $options);
		}
		
		private static function css(){
			$styles = array(
				'body' => array(
					'margin-top' => '40px'
				),
				'clear' => array(
					'clear' => 'both'
				),
				'#message-flash p' => array(
					'margin' => 0,
					'padding' => 0
				),
				'#message-flash' => array(
					'width' => '100%',
					'position' => 'absolute',
					'top' => 0,
					'left' => 0,
					'padding' => '5px 0',
					'text-align' => 'center',
					'margin-bottom' => '30px'
				),
				'.message-message' => array(
					'background-color' => '#dcdcdc',
					'color' => '#000'
				),
				'.message-success' => array(
					'background-color' => '#008000',
					'color' => '#fff'
				),
				'.message-error' => array(
					'background-color' => '#b22222',
					'color' => '#fff'
				),
				'.message-warning' => array(
					'background-color' => '#ffd700',
					'color' => '#000'
				)
			);
			CSS::style($styles);
		}
		
		private static function js(){
			$time = self::$flash['options']['time'] * 1000;
			$js = <<<SCRIPT
	if(document.all){var marginTop = parseInt(document.body.currentStyle.marginTop) - 40 + "px";}else{var marginTop = parseInt(document.defaultView.getComputedStyle(document.body, '').getPropertyValue('margin-top')) - 40 + "px";}
	setTimeout(function(){document.getElementById("message-flash").style.display = "none";document.body.style.marginTop = marginTop;},{$time});
SCRIPT;
			JavaScript::script($js);
		}
		
		private static function _redirect(){
			if(is_array($_SESSION['flash']['options'])){
				self::$flash['options'] = $_SESSION['flash']['options'] + self::$flash['options'];
			}
			self::$flash['message'] = $_SESSION['flash']['message'];
			self::$flash['type'] = (array_search($_SESSION['flash']['type'], self::$valid_types)) ? $_SESSION['flash']['type'] : 'message';
			self::css();
		}
		
		public static function render(){
			if(empty(self::$flash['message'])){
				return;
			}
			
			if(self::$flash['options']['sticky'] !== true) self::js();
			$type = self::$flash['type'];
			$message = self::$flash['message'];
			
			// cleanup
			self::$flash = array();
			unset($_SESSION['flash']);
			
			return <<<FLASH
<div id="message-flash" class="message-$type"><p>$message</p></div><div class="clear"></div>
FLASH;
		}
		
	}