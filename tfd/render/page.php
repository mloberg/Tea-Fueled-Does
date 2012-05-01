<?php namespace TFD\Render;

	use Content\Hooks;
	use TFD\Config;
	use TFD\Event;
	use TFD\Render;
	use TFD\RenderException;

	class Page extends View {
		
		private $options = array();
		
		/**
		 * Constructor method
		 *
		 * @param array $options Render options
		 * @return object this
		 */

		public function __construct($options) {
			Hooks::pre_render();
			$options = $options + array('title' => Config::get('site.title'), 'master' => Config::get('render.master'), 'status' => 200);
			$this->options = $options;
			return $this;
		}
		
		public function __destruct(){
			Hooks::post_render();
		}

		public function __toString() {
			return $this->render();
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
			Hooks::render();
			$status = $this->options['status'];
			if ($status !== 200) {
				return Event::fire($this->options['status']);
			} else {
				$options = $this->options;
				$master = MASTERS_DIR.$options['master'].EXT;
				if (!file_exists($master)) {
					throw new RenderException("Master {$master} does not exist");
				}
				unset($options['master'], $options['status']);

				// render view
				$options['content'] = parent::render_view($options);
				if ($options['content'] === false) return Event::fire('404');
				
				if ($master === false) return self::$options['content'];
				
				return parent::render_file($master, $options);
			}
		}

	}
