<?php namespace TFD\Core\Render;

	use TFD\Core\Config;
	use TFD\Core\Event;
	use TFD\Core\Render;
	use TFD\Core\RenderException;

	class Page extends View {
		
		protected $options = array();
		
		/**
		 * Constructor method
		 *
		 * @param array $options Render options
		 * @return object this
		 */

		public function __construct($options) {
			Event::fire('pre_render');
			$this->options = $options + array('title' => Config::get('site.title'), 'master' => Config::get('render.master'), 'status' => 200);
			return $this;
		}
		
		public function __destruct(){
			Event::fire('post_render');
		}

		public function __toString() {
			return $this->render();
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
		 * Return the HTTP status of the page.
		 *
		 * @return integer HTTP status code
		 */

		public function status() {
			return $this->options['status'];
		}

		/**
		 * Render the full page.
		 *
		 * @return string Rendered page
		 */
		
		public function render(){
			Event::fire('render');
			$status = $this->options['status'];
			if ($status !== 200) {
				return Event::fire($this->options['status']);
			} else {
				$master = MASTERS_DIR.$this->options['master'].EXT;
				if (!file_exists($master)) {
					throw new RenderException("Master {$master} does not exist");
				}

				// render view
				$this->options['content'] = parent::render_view($this->options, $this);
				if ($this->options['content'] === false) return Event::fire('404');
				
				if ($master === false) return $this->options['content'];
				
				return parent::render_file($master, $this, $this->options);
			}
		}

	}
