<?php namespace TFD\Library;

	class Image{
	
		private $image = null;
		private $info = array();
		
		function __construct($image){
			if(!file_exists($image)) $image = PUBLIC_DIR.$image;
			$this->__open($image);
		}
		
		function __destruct(){
			// doing some cleanup
			if(is_resource($this->image)) imagedestroy($this->image);
		}
		
		private function __open($file){
			list($this->info['width'], $this->info['height'], $this->info['type'], $this->info['attr']) = getimagesize($file);
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
		
		private function __save($path, $name, $type = null, $quality = 80){
			if(!is_null($type) && !preg_match('/^(gif|jpg|jpeg|png)$/', $type)){
				throw new \TFD\Exception('Not a valid save type!');
			}else{
				if(!is_dir($path)) $path = PUBLIC_DIR.$path;
				if(!preg_match('/\/$/', $path)) $path .= '/';
				$output = $path.$name;
				// figure out the extension and type to save it as
				if(is_null($type)){
					$type = $this->info['type'];
				}
				$output .= '.'.$type;
				switch($type){
					case 'jpg':
					case 'jpeg':
						imagejpeg($this->image, $output, $quality);
						break;
					case 'gif':
						imagegif($this->image, $output);
						break;
					case 'png':
						imagepng($this->image, $output);
						break;
					default:
						throw new \TFD\Exception('Not a valid image type!');
				}
				// check to see if the image was saved
				return (file_exists($output)) ? true : false;
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
					imagedestroy($this->image);
					$this->image = $new_image;
				}
			}
		}
		
		public function save($path, $name, $type = null, $quality = 80){
			return $this->__save($path, $name, $type, $quality);
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
			}
			return $this;
		}
		
		public function resize($width, $height){
			if(!is_int($width) || !is_int($height)){
				throw new \LogicException('Image::resize, expects width and height to be integers.');
			}else{
				$options = array(
					'x' => 0,
					'y' => 0,
					'width' => $width,
					'height' => $height
				);
				$this->__crop($options);
			}
			return $this;
		}
		
		public function scale($options){
			if(!is_array($options)){
				throw new \LogicException('Image::crop() expects an array, '.gettype($options).' given.');
			}else{
				if($options['percent']){
					$scale = '.'.$options['percent'];
					$options['width'] = round($this->info['width'] * $scale);
					$options['height'] = round($this->info['height'] * $scale);
				}elseif((!isset($options['height']) && isset($options['width'])) || ($this->info['width'] > $this->info['height'])){
					$options['height'] = round($this->info['height'] / ($this->info['width'] / $options['width']));
				}elseif((!isset($options['width']) && isset($options['height'])) || ($this->info['height'] > $this->info['width'])){
					$options['width'] = round($this->info['width'] / ($this->info['height'] / $options['height']));
				}else{
					throw new \LogicException('Width, height, or percent not set!');
					return;
				}
				$options['x'] = 0;
				$options['y'] = 0;
				$this->__crop($options);
			}
			return $this;
		}
		
		function crop($width, $height, $x = 0, $y = 0){
			if(!is_int($width) || !is_int($height)){
				throw new \LogicException('Image::crop, expects width and height to be integers.');
			}else{
				$options = array(
					'width' => $width,
					'height' => $height,
					'x' => $x,
					'y' => $y
				);
				$this->__crop($options);
			}
			return $this;
		}
		
		function watermark($watermark, $options = array()){
			$defaults = array(
				'x' => 0,
				'y' => 0
			);
			$options = $options + $defaults;
			
			$image = $type = $new_image = null;
			$info = array();
			list($info['width'], $info['height'], $info['type'], $info['attr']) = getimagesize($watermark);
			switch($info['type']){
				case 1:
					$image = imagecreatefromgif($watermark);
					$type = 'gif';
					break;
				case 2:
					$image = imagecreatefromjpeg($watermark);
					$type = 'jpg';
					break;
				case 3:
					$image = imagecreatefrompng($watermark);
					$type = 'png';
					break;
				default:
					throw new \TFD\Exception('Not a valid image type!');
			}
			
			switch($options['x']){
				case 'left':
					$x = 0;
					break;
				case 'center':
					$x = (($this->info['width'] - $info['width']) / 2);
					echo $x;
					break;
				case 'right':
					$x = ($this->info['width'] - $info['width']);
				default:
					$x = $options['x'];
			}
			switch($options['y']){
				case 'top':
					$y = 0;
					break;
				case 'center':
					$y = (($this->info['height'] - $info['height']) / 2);
					break;
				case 'bottom':
					$y = ($this->info['height'] - $info['height']);
				default:
					$y = $options['y'];
			}
			
			imagecopy($this->image, $image, $x, $y, 0, 0, $info['width'], $info['height']);
			imagedestroy($image);
			return $this;
		}
	
	}