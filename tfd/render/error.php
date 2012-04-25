<?php namespace TFD\Render;

	use TFD\Config;
	use TFD\Render;

	class Error extends Render {
	
		private $page;
		private $data;
		
		public function __construct($type, $data = array()){
			$this->page = VIEWS_DIR.Config::get('views.error').'/'.$type.EXT;
			$this->data = $data;
		}
		
		function __toString(){
			return $this->render();
		}
		
		public function render(){
			return parent::render_file($this->page, $this->data);
		}
	
	}
