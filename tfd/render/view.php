<?php namespace TFD\Render;

	use TFD\Render;
	use TFD\RenderException;

	class View extends Render {
	
		private $options = array();
		
		/**
		 * Constructor
		 *
		 * @param array $options Render options
		 * @return object $this
		 */

		public function __construct($options) {
			$this->options = $options;
			return $this;
		}
		
		public function __toString() {
			return $this->render();
		}

		/**
		 * Set an array of options.
		 *
		 * @param array $options Render options
		 */
		
		public function set($options){
			$this->options = $options + $this->options;
			return $this;
		}
		
		/**
		 * Set a single option.
		 *
		 * @param string $name Option name
		 * @param mixed $value Option value
		 */

		public function __set($name, $value){
			$this->options[$name] = $value;
		}
		
		/**
		 * Get the value of an option item.
		 *
		 * @param string $name Options name
		 */
		
		public function __get($name){
			if (array_key_exists($name, $this->options)) return $this->options[$name];
			return null;
		}

		/**
		 * Render the view.
		 *
		 * @return string Rendered view
		 */

		public function render() {
			$options = $this->options;
			$render = parent::render_view($options, $this);
			if ($render === false) throw new RenderException("View {$options['view']}");
			return $render;
		}
	
	}
