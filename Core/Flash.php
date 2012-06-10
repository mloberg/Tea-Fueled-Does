<?php namespace TFD\Core;
	
	class Flash {
		
		static protected $info = array(
			'type' => 'message',
			'message' => '',
			'time' => 2
		);

		/**
		 * Valid flash types.
		 */

		static protected $types = array(
			'message' => 'background-color:#dcdcdc;color:#000',
			'error' => 'background-color:#b22222;color:#fff',
			'warning' => 'background-color:#ffd700;color:#000',
			'success' => 'background-color:#008000;color:#fff',
		);

		/**
		 * Flash a message.
		 *
		 * This is a catch all for flash message types.
		 * (Flash::message, Flash::error, Flash::success, Flash::warning)
		 *
		 * @param string $message Message to flash
		 * @param integer|boolean $time Flash time (true for sticky)
		 */

		public static function __callStatic($method, $args) {
			static::$info['type'] = $method;
			static::$info['message'] = array_shift($args);
			static::$info['time'] = array_shift($args) ?: 2;
		}

		/**
		 * Flash after redirect.
		 *
		 * Save flash information into the session.
		 * 
		 * @param string $message Flash message
		 * @param string $type Flash type (message, error, warning, success)
		 * @param integer|boolean $time Flash time (true for sticky)
		 */
		
		public static function redirect($message, $type = 'message', $time = 2) {
			$_SESSION['flash'] = array('message' => $message, 'type' => $type, 'time' => $time);
		}

		/**
		 * Render a flash.
		 *
		 * @return string Flash HTML, and JS
		 */

		public static function render() {
			if (!empty($_SESSION['flash'])) {
				static::$info = $_SESSION['flash'];
				unset($_SESSION['flash']);
			}
			if (empty(self::$info['message'])) return;
			$type = self::$info['type'];
			$message = self::$info['message'];
			$style = static::$types[$type] ?: static::$types['message'];
			$html = <<<FLASH
<div id="flash-message-wrapper" style="margin-bottom:40px">
	<div style="$style;font-size:18px;left:0;margin:0;opacity:1;padding:5px;position:absolute;text-align:center;top:0;width:100%">$message</div>
</div>
FLASH;
			if (static::$info['time'] !== true) {
				$time = static::$info['time'] * 1000;
				$html .= <<<JS
<script>function animateFlashFade(){var a=document.getElementById("flash-message-wrapper"),b=a.Visibility-5;a.Visibility=b;a.style.opacity=b/100;a.style.filter="alpha(opacity = "+b+")";if(b<=0){document.body.removeChild(a)}else{setTimeout("animateFlashFade()",33)}}setTimeout(function(){var a=document.getElementById("flash-message-wrapper").Visibility=100;setTimeout("animateFlashFade()",33)}, $time)</script>
JS;
			}
			return $html;
		}
		
	}
