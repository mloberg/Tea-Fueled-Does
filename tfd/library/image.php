<?php namespace TFD\Library;

	class Image{
	
		private $image = null;
		private $new_image = null;
		private $info = array();
		
		function __construct($image){
			$file = (preg_match('/^\//', $image)) ? $image : PUBLIC_DIR.$image;
			$this->__open($file);
		}
		
		function __destruct(){
			// doing some cleanup
			if(is_resource($this->image))imagedestroy($this->image);
			if(is_resource($this->new_image))imagedestroy($this->new_image);
		}
		
		private function __open($file){
			$this->info = getimagesize($file);
			switch($this->info['type']){
				case 1:
					$this->image = imagecreatefromgif($file);
					$this->info['type'] = 'gif';
					break;
				case 2:
					$this->image = imagecreatefromjpeg($file);
					$this->info['type'] = 'jpg';
					break;
				case 3:
					$this->image = imagecreatefrompng($file);
					$this->info['type'] = 'png';
					break;
				default:
					throw new \TFD\Exception('Not a valid image type!');
			}
		}
		
		private function __save($options){
			$output = PUBLIC_DIR;
			if($options['path']){
				$output .= $options['path'].'/';
			}
			$output .= $options['name'];
			// figure out the extension and type to save it as
			if($options['type']){
				$output .= '.'.$options['type'];
				$type = $options['type'];
			}elseif(preg_match('/\.(gif|jpg|jpeg|png)$/', $save, $match)){
				$type = $match[1];
			}else{
				$output .= '.'.$this->info['type'];
				$type = $this->info['type'];
			}
			switch($this->info['type']){
				case 'jpg':
				case 'jpeg':
					$quality = ($options['quality']) ? $options['quality'] : 80;
					imagejpeg($this->new_image,$output,$quality);
					break;
				case 'gif':
					imagegif($this->new_image,$output);
					break;
				case 'png':
					imagepng($this->new_image,$output);
					break;
				default:
					echo 'Not a valid image type';
					exit;
			}
			// check to see if the image was saved
			if(file_exists($output)){
				return true;
			}else{
				return false;
			}
		}
		
		private function __crop($options){
			if(is_resource($this->image)){
				if(!is_array($options)){
					throw new \LogicException('Image::crop() expects an array, '.gettype($options).' given.');
				}elseif(!isset($options['width']) || !isset($options['height'])){
					throw new \LogicException('Width and height are required!');
				}else{
					$default = array(
						'x' => 0,
						'y' => 0
					);
					$options = $options + $default;
					$new_image = imagecreatetruecolor($options['width'], $options['height']);
					imagecopyresampled($new_image, $this->image, 0, 0, $options['x'], $options['y'], $options['width'], $options['height'], $this->info['width'], $this->info['height']);
					$this->image = $new_image;
					imagedestroy($new_image);
				}
			}
		}
		
		public function rotate($rotate){
			if(is_resource($this->image)){
				switch($rotate){
					case 'left':
						$degrees = 270;
						break;
					case 'right':
						$degrees = 90;
						break;
					case 'flip':
						$degrees = 180;
						break;
					default:
						$degrees = $rotate;
						break;
				}
				$new_image = imagerotate($this->image, $degrees, 0);
				$this->image = $new_image;
				imagedestroy($new_image);
			}
			return $this;
		}
		
		public function resize($width, $height){
			if(empty($width) || empty($height)){
				throw new \LogicException('Image::resize, expects width and height.');
			}else{
				$options = array(
					'x' => 0,
					'y' => 0,
					'width' => $width,
					'height' => $height
				);
				$this->_crop($options);
			}
			return $this;
		}
		
		public function scale($o){
			$options = array();
			if(!is_resource($this->image)){
				
			}elseif(!is_array($options)){
				throw new \LogicException('Image::crop() expects an array, '.gettype($options).' given.');
			}else{
				$o = $options;
				if($o['percent']){
					$scale = '.'.$o['percent'];
					$options['width'] = round($this->info['width'] * $scale);
					$options['height'] = round($this->info['height'] * $scale);
				}elseif(!isset($o['height']) || ($this->info['width'] > $this->info['height'])){
					
				}elseif(!isset($o)){
					
				}
			}
			if($options['percent']){
				$scale = '.'.$options['percent'];
				$opts['width'] = round($this->info['width'] * $scale);
				$opts['height'] = round($this->info['height'] * $scale);
			}else{
				if(!$options['height'] || ($this->info['width'] > $this->info['height'])){
					$opts['width'] = $options['width']
					$opts['height'] = round($this->info['height'] / ($this->info['width'] / $options['width']));
				}elseif(!$options['width'] || ($this->info['height'] > $this->info['width'])){
					$opts['height'] = $options['height'];
					$opts['width'] = round($this->info['width'] / ($this->info['height'] / $options['height']));
				}else{
					return false;
				}
			}
			// now crop it
			$opts['x'] = 0;
			$opts['y'] = 0;
			return $this->_crop($file, $opts, $output);
		}
		
		function crop($file, $options, $output){
			$this->open($file);
			extract($options);
			$this->new_image = imagecreatetruecolor($width, $height);
			imagecopy($this->new_image, $this->image, 0, 0, $x, $y, $this->info['width'], $this->info['height']);
			return $this->save($output);
		}
		
		function save_as($file, $output){
			$this->open($file);
			$width = $this->info['width'];
			$height = $this->info['height'];
			$this->new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled($this->new_image, $this->image, 0, 0, 0, 0, $width, $height, $width, $height);
			return $this->save($output);
		}
		
		function watermark($file, $watermark, $output, $options = array()){
			$options = $options + array('x' => 0, 'y' => 0);
			$this->open($watermark);
			switch(end(explode('.', $file))){
				case 'gif':
					$this->new_image = imagecreatefromgif($file);
					break;
				case 'jpg':
				case 'jpeg':
					$this->new_image = imagecreatefromjpeg($file);
					break;
				case 'png':
					$this->new_image = imagecreatefrompng($file);
					break;
				default:
					echo 'Not a valid image type.';
					exit;
			}
			
			$width = imagesx($this->new_image);
			$height = imagesy($this->new_image);
			
			switch($options['x']){
				case 'left':
					$x = 0;
					break;
				case 'center':
					$x = (($width - $this->info['width']) / 2);
					break;
				case 'right':
					$x = ($width - $this->info['width']);
				default:
					$x = $options['x'];
			}
			switch($options['y']){
				case 'top':
					$y = 0;
					break;
				case 'center':
					$y = (($height - $this->info['height']) / 2);
					break;
				case 'bottom':
					$y = ($height - $this->info['height']);
				default:
					$y = $options['y'];
			}
			
			imagecopy($this->new_image, $this->image, $x, $y, 0, 0, $this->info['width'], $this->info['height']);
			
			return $this->save($output);
		}
	
	}