<?php

	class Flash extends App{
		
		static private $flash = array();
		
		function message($message, $type, $options){
			self::$flash['message'] = $message;
			$default_options = array(
				'time' => '2',
				'sticky' => false
			);
			self::$flash['options'] = $options + $default_options;
			$valid_types = array('message', 'error', 'warning', 'success');
			self::$flash['type'] = $type;
			if(!array_search($type, $valid_types)) self::$flash['type'] = 'message';
			$this->css();
		}
		
		private function css(){
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
			$this->css->style($styles);
		}
		
		private function js(){
			$time = self::$flash['options']['time'] * 1000;
			$js = <<<SCRIPT
	if(document.all){var marginTop = parseInt(document.body.currentStyle.marginTop) - 40 + "px";}else{var marginTop = parseInt(document.defaultView.getComputedStyle(document.body, '').getPropertyValue('margin-top')) - 40 + "px";}
	setTimeout(function(){document.getElementById("message-flash").style.display = "none";document.body.style.marginTop = marginTop;},{$time});
SCRIPT;
			$this->javascript->script($js);
		}
		
		function render(){
			if(self::$flash['options']['sticky'] === false){
				$this->js();
			}
			return '<div id="message-flash" class="message-'.self::$flash['type'].'"><p>'.self::$flash['message'].'</p></div><div class="clear"></div>';
		}
		
	}