<?php

	class Render extends App{
	
		static private $replace = array();
		
		function original($options){
			if(!$this->is_admin) $this->hooks->front();
			$this->hooks->render();
			extract($options);
			// get full path of the file
			if($dir){
				if($dir == 'admin-dashboard' && !$this->admin->loggedin()){
					$this->send_404();
					$master = '404';
				}else{
					$file = CONTENT_DIR . $dir.'/'.$file.EXT;
				}
			}else{
				$file = WEB_DIR . $file . EXT;
			}
			// start the output buffer
			ob_start();
			if(file_exists($file)){
				// include the file
				include($file);
				// save file contents to var
				$content = ob_get_contents();
				// clean the output buffer
				ob_clean();
			}elseif($this->testing && $this->request !== '404'){
				$this->send_404();
				$this->error->report($file.' not found!');
			}else{
				// if the file wasn't found, 404
				$this->send_404();
				$master = '404';
			}
			// figure out the title
			if(!$options['title'] && $title == ''){
				$title = SITE_TITLE;
			}elseif($options['title']){
				$title = $options['title'];
			}
			if(!empty($replace)){
				foreach($replace as $item => $value){
					$content = str_replace($item, $value, $content);
				}
			}
			// get the full path to the master
			$master = MASTERS_DIR . $master . EXT;
			if(!file_exists($master)){
				// if the master doesn't exist, use the default one
				$master = DEFAULT_MASTER;
			}
			// include master
			include($master);
			// save it to a var
			$page = ob_get_contents();
			// end the output buffer
			ob_end_clean();
			
			// return the page
			return $page;
		}
		
		function replace($text, $replace){
			self::$replace[$text] = $replace;
		}
	
	}