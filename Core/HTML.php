<?php namespace TFD\Core;

	use TFD\Core\Config;

	class HTML {
	
		/**
		 * Build an HTML tag.
		 *
		 * @param string $tag Tag name
		 * @param string $value Tag value
		 * @param array $attributes An array of HTML tag attributes
		 * @param boolean $entities Escape HTML entities
		 * @return string HTML tag
		 */

		public static function build_tag($tag, $value = null, $attributes = array(), $entities = true) {
			$attributes = static::attributes($attributes);
			if (is_null($value)) {
				return sprintf("<%s%s />", $tag, $attributes);
			}
			$value = $entities ? static::entities($value) : $value;
			return sprintf("<%s%s>%s</%s>", $tag, $attributes, $value, $tag);
		}

		/**
		 * Escape HTML entities.
		 *
		 * @param string $value Value to escape
		 * @return string Value with HTML entities escaped
		 */

		public static function entities($value) {
			return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
		}

		/**
		 * Parse HTML attribute values.
		 *
		 * @param array $attr HTML arributes
		 * @return string HTML arribute string
		 */
		
		public static function attributes($attr) {
			if (empty($attr)) return;
			$html = array();
			foreach ($attr as $key => $value) {
				if (is_int($key)) {
					$html[] = $value;
				} else {
					$html[] = $key.'="'.static::entities($value).'"';
				}
			}
			return ' '.implode(' ', $html);
		}

		/**
		 * Obfuscate a string.
		 *
		 * @param string $value String to obfuscate
		 * @return string Obfuscated value
		 */

		public static function obfuscate($value) {
			$safe = '';
			foreach (str_split($value) as $letter) {
				switch (rand(1, 3)) {
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

		/**
		 * Build an HTML tag.
		 *
		 * @param string $value Tag value
		 * @param array $attributes Tag attributes
		 * @param boolean $entities Escape HTML entities
		 * @return string HTML tag
		 */

		public static function __callStatic($method, $args) {
			array_unshift($args, $method);
			return call_user_func_array(array('static', 'build_tag'), $args);
		}

		/**
		 * Build a link (a) tag.
		 *
		 * @param string $url Link
		 * @param string $title Link title
		 * @param array $attributes Tag attributes
		 * @return string Link tag
		 */
		
		public static function link($url, $title, $attributes = array()) {
			if (filter_var($url, FILTER_VALIDATE_URL) === false) {
				if(!preg_match('/^\//', $url)) $url = '/' . $url;
				$url = Config::get('site.url').$url;
			}
			$attributes['href'] = $url;
			return static::build_tag('a', $title, $attributes);
		}

		/**
		 * Build a mailto link.
		 *
		 * @param string $email Email
		 * @param sting $title Link title
		 * @param array $attributes Tag attributes
		 * @return string Mailto link tag
		 */
		
		public static function mailto($email, $title = null, $attributes = array()) {
			$email = str_replace('@', '&#64;', static::obfuscate($email));
			if (is_null($title)) $title = $email;
			return static::link('&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email, $title, $attributes);
		}

		/**
		 * Build a RSS link tag.
		 * 
		 * @param string $feed RSS feed URL
		 * @param string $title RSS feed title
		 * @param array $attributes Tag attributes
		 * @return string RSS link tag
		 */
		
		public static function rss($feed, $title = 'RSS Feed', $attributes = array()) {
			if (filter_var($feed, FILTER_VALIDATE_URL) === false) {
				if(!preg_match('/^\//', $feed)) $feed = '/' . $feed;
				$feed = Config::get('site.url').$feed;
			}
			$attributes['rel'] = 'alternate';
			$attributes['type'] = 'application/rss+xml';
			$attributes['title'] = $title;
			$attributes['href'] = $feed;
			return static::build_tag('link', null, $attributes);
		}

		/**
		 * Build an image tag.
		 * 
		 * @param string $url Image location
		 * @param string $alt Image alt
		 * @param array $attributes Tag attributes
		 * @return string Image tag
		 */
		
		public static function image($url, $alt = '', $attributes = array()) {
			if (filter_var($url, FILTER_VALIDATE_URL) === false) {
				$remote = false;
				$image = PUBLIC_DIR.$url;
				if(!preg_match('/^\//', $url)) $url = '/' . $url;
				$url = Config::get('site.url').$url;
			}
			$attributes['src'] = $url;
			$attributes['alt'] = $alt;
			return static::build_tag('img', null, $attributes);
		}

		/**
		 * Build an HTML list.
		 *
		 * @param string $type List type
		 * @param array $list List items
		 * @param array $attributes Tag attributes
		 * @param boolean $entities Escape HTML entities
		 * @return string List tag
		 */

		public static function build_list($type, $list, $attributes = array(), $entities = true) {
			$html = '';
			foreach ($list as $value) {
				if (is_array($value)) {
					$html .= static::build_tag('li', static::build_list($type, $value), array(), false);
				} else {
					$html .= static::build_tag('li', $value, array(), $entities);
				}
			}
			return static::build_tag($type, $html, $attributes, false);
		}

		/**
		 * Build an HTML unorder list.
		 * 
		 * @param array $list List items
		 * @param array $attributes Tag attributes
		 * @param boolean $entities Escape HTML entities
		 * @return string List tag
		 */
		
		public static function ul($list, $attributes = array(), $entities = true) {
			return static::build_list('ul', $list, $attributes, $entities);
		}

		/**
		 * Build an HTML order list.
		 * 
		 * @param array $list List items
		 * @param array $attributes Tag attributes
		 * @param boolean $entities Escape HTML entities
		 * @return string List tag
		 */
		
		public static function ol($list, $attributes = array(), $entities = true) {
			return static::build_list('ol', $list, $attributes, $entities);
		}
	
	}
