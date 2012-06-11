<?php namespace TFD\Core;

	use TFD\Core\Config;
	use TFD\Core\HTML;

	class Form {

		/**
		 * Open a form tag.
		 *
		 * @param string $action Where the form should submit
		 * @param string $method Form method
		 * @param array $attributes Tag attributes
		 * @return string Opening form tag
		 */
		
		public static function open($action, $method = 'POST', $attributes = array()) {
			if (filter_var($action, FILTER_VALIDATE_URL) === false) {
				if(!preg_match('/^\//', $action)) {
					$action = '/' . $action;
				}
				$action = Config::get('site.url').$action;
			}
			$attributes['action'] = $action;
			// PUT and DELETE methods are spoofed using a hidden field
			$attributes['method'] = ($method == 'PUT' || $method == 'DELETE') ? 'POST' : $method;
			$html = '<form'.HTML::attributes($attributes).'>';
			if ($method == 'PUT' || $method == 'DELETE') {
				$html .= static::input('hidden', 'REQUEST_METHOD', $method);
			}
			return $html;
		}

		/**
		 * Open a form tag for uploading.
		 *
		 * @param string $action Where the form should submit
		 * @param string $method Form method
		 * @param array $attributes Tag attributes
		 * @return string Opening form tag
		 */
		
		public static function open_upload($action, $method = 'POST', $attributes = array()) {
			$attributes['enctype'] = 'multipart/form-data';
			return static::open($action, $method, $attributes);
		}

		/**
		 * Closing form tag.
		 *
		 * @return string Closing form tag
		 */
		
		public static function close() {
			return '</form>';
		}

		/** 
		 * Form label.
		 *
		 * @param string $name 
		 * @param string $value
		 * @param array $attributes 
		 */
		
		public static function label($name, $value, $attributes = array()) {
			$attributes['for'] = $name;
			return HTML::build_tag('label', $value, $attributes);
		}

		/**
		 * Form input.
		 *
		 * @param string $type Input type
		 * @param string $name Input name
		 * @param string $value Input default value
		 * @param array $attributes Tag attributes
		 * @return string Form input tag
		 */
		
		public static function input($type, $name, $value = null, $attributes = array()) {
			$attributes['type'] = $type;
			if (!is_null($name)) {
				$attributes['name'] = $name;
			}
			if (!is_null($value)) {
				$attributes['value'] = $value;
			}
			return HTML::build_tag('input', null, $attributes);
		}

		/**
		 * Form input.
		 *
		 * @param string $name Input name
		 * @param string $value Input default value
		 * @param array $attributes Tag attributes
		 * @return string Form input tag
		 */

		public static function __callStatic($method, $args) {
			array_unshift($args, $method);
			return call_user_func_array(array('static', 'input'), $args);
		}

		/**
		 * Form submit input.
		 *
		 * @param string $value Button value
		 * @param array $attributes Tag attributes
		 * @return string Form input submit
		 */

		public static function submit($value, $attributes = array()) {
			return static::input('submit', null, $value, $attributes);
		}

		/**
		 * File input.
		 *
		 * @param string $name Input name
		 * @param array $attributes Tag attributes
		 * @return string Form input tag
		 */
		
		public static function file($name, $attributes = array()) {
			return static::input('file', $name, null, $attributes);
		}

		/**
		 * Textarea input.
		 *
		 * @param string $name Input name
		 * @param string $value Input value
		 * @param array $attributes Tag attributes
		 * @return string Form textarea tag
		 */
		
		public static function textarea($name, $value = '', $attributes = array()) {
			$attributes['name'] = $name;
			return HTML::build_tag('textarea', $value, $attributes);
		}

		/**
		 * Form select box.
		 *
		 * @param string $name Input name
		 * @param array $options Select options
		 * @param string $selected Selected value
		 * @param array $attributes Tag attributes
		 * @return string Form select tag
		 */
		
		public static function select($name, $options = array(), $selected = null, $attributes = array()) {
			$html = array();
			$attributes['name'] = $name;
			foreach ($options as $value => $display) {
				$opt_attr = array('value' => $value);
				if ($value === $selected) {
					$opt_attr[] = 'selected';
				}
				$html[] = HTML::build_tag('option', $display, $opt_attr);
			}
			return HTML::build_tag('select', implode('', $html), $attributes, false);
		}

		/**
		 * Make a checkable form element.
		 * 
		 * @param string $type Checkable type
		 * @param string $name Input name
		 * @param string $value Input value
		 * @param boolean $checked Item check state
		 * @param array $attributes Tag attributes
		 * @return string Form check tag
		 */
		
		public static function checkable($type, $name, $value = null, $checked = false, $attributes = array()){
			if ($checked === true) {
				$attributes[] = 'checked';
			}
			return static::input($type, $name, $value, $attributes);
		}

		/**
		 * Form checkbox.
		 * 
		 * @param string $name Input name
		 * @param string $value Input value
		 * @param boolean $checked Item check state
		 * @param array $attributes Tag attributes
		 * @return string Form check tag
		 */
		
		public static function checkbox($name, $value = null, $checked = false, $attributes = array()){
			return static::checkable('checkbox', $name, $value, $checked, $attributes);
		}

		/**
		 * Form radio button.
		 * 
		 * @param string $name Input name
		 * @param string $value Input value
		 * @param boolean $checked Item check state
		 * @param array $attributes Tag attributes
		 * @return string Form check tag
		 */
		
		public static function radio($name, $value = null, $checked = false, $attributes = array()){
			return static::checkable('radio', $name, $value, $checked, $attributes);
		}
	
	}
