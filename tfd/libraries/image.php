<?php

	class Image{
	
		private $image;
		private $info = array();
		private $new_image;
		
		function __destruct(){
			// doing some cleanup
			if(is_resource($this->image))imagedestroy($this->image);
			if(is_resource($this->new_image))imagedestroy($this->new_image);
		}
		
		private function open($file){
			$file = PUBLIC_DIR.$file;
			list($width,$height,$type,$attr) = getimagesize($file);
			$this->info['width'] = $width;
			$this->info['height'] = $height;
			switch($type){
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
					echo 'Not a valid image.';
					exit;
			}
		}
		
		private function save($options){
			$output = PUBLIC_DIR.$options['path'].'/'.$options['name'];
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
		
		private function _crop($file, $options, $output){
			// create the image resource, if already doesn't exist
			if(!is_resource($this->image)){
				$this->open($file);
			}
			extract($options);
			$this->new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled($this->new_image, $this->image, 0, 0, $x, $y, $width, $height, $this->info['width'], $this->info['height']);
			return $this->save($output);
		}
		
		function resize($file, $options, $output){
			$options['x'] = 0;
			$options['y'] = 0;
			return $this->_crop($file, $options, $output);
		}
		
		function scale($file, $options, $output){
			// create image and get info
			$this->open($file);
			// now figure out the scale
			$opts = array();
			if($options['percent']){
				$scale = '.'.$options['percent'];
				$opts['width'] = round($this->info['width'] * $scale);
				$opts['height'] = round($this->info['height'] * $scale);
			}else{
				if($options['width']){
					$opts['width'] = $options['width'];
					$opts['height'] = round($this->info['height'] / ($this->info['width'] / $options['width']));
				}elseif($options['height']){
					$opts['height'] = $options['height'];
					$opts['width'] = round($this->info['width'] / ($this->info['height'] / $options['height']));
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
		
		function rotate($file, $rotate, $output){
			$this->open($file);
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
			$this->new_image = imagerotate($this->image,$degrees,0);
			return $this->save($output);
		}
	
	}