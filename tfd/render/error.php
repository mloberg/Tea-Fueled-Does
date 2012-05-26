<?php namespace TFD\Render;

	use TFD\Config;
	use TFD\Event;
	use TFD\Render;
	use TFD\RenderException;

	class Error extends Page {

		/**
		 * Render the error page.
		 *
		 * @return string Rendered page
		 */

		public function render(){
			Event::fire('render');
			$master = MASTERS_DIR.$this->options['master'].EXT;
			if (!file_exists($master)) {
				throw new RenderException("Master {$master} does not exist");
			}

			// render view
			$this->options['content'] = parent::render_view($this->options, $this);
			if ($this->options['content'] === false) {
				$this->options['view'] = 'general';
				unset($this->options['content']);
				$this->options['content'] = parent::render_view($this->options, $this);
			}
			
			if ($master === false) return $this->options['content'];
			
			return parent::render_file($master, $this, $this->options);
		}
	
	}
