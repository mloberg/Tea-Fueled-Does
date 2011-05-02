<?php

	class Image extends App{
	
		private $image;
		private $info = array();
		private $new_image;
		
		function __destruct(){
			if(is_resource($this->image))imagedestroy($this->image);
			if(is_resource($this->new_image))imagedestroy($this->new_image);
		}
		
		private function open($file){
			$file = './'.$file;
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
					$this->error->report('Not a valid image.',true);
					break;
			}
		}
		
		private function save($output,$quality){
			$output = './'.$output;
			if(!preg_match('/\.(gif|jpg|jpeg|png)$/',$output)){
				$output = $output.'.'.$this->info['type'];
			}else{
				$output = preg_replace('/(gif|jpg|jpeg|png)$/',$this->info['type'],$output);
			}
			switch($this->info['type']){
				case 'jpg':
				case 'jpeg':
					imagejpeg($this->new_image,$output,$quality);
					break;
				case 'gif':
					imagegif($this->new_image,$output);
					break;
				case 'png':
					imagepng($this->new_image,$output);
					break;
			}
			if(file_exists($output)){
				return true;
			}else{
				return false;
			}
		}
		
		function resize($opts){
			$opts['x'] = 0;
			$opts['y'] = 0;
			return $this->crop($opts);
		}
		
		function scale($opts){
			$scale = $opts['scale'];
			$file = './'.$opts['file'];
			list($width,$height,$type,$attr) = getimagesize($file);
			if(preg_match('/%$/',$scale)){
				// scale percentage
				$scale = '.'.preg_replace('/%$/','',$scale);
				$opts['width'] = round($width * $scale);
				$opts['height'] = round($height * $scale);
			}else{
				// scale by width or height
				if($opts['width']){
					$opts['height'] = round($height / ($width / $opts['width']));
				}else{
					$opts['width'] = round($width / ($height / $opts['height']));
				}
			}
			$opts['x'] = 0;
			$opts['y'] = 0;
			return $this->crop($opts);
		}
		
		function crop($opts){
			extract($opts);
			$this->open($file);
			if($type){
				$this->info['type'] = $type;
			}
			if(!$quality){
				$quality = 90;
			}
			if($output == ''){
				$output = $file;
			}
			$this->new_image = imagecreatetruecolor($width,$height);
			imagecopyresampled($this->new_image,$this->image, 0, 0, $x, $y, $width, $height, $this->info['width'], $this->info['height']);
			return $this->save($output,$quality);
		}
		
		function rotate($opts){
			extract($opts);
			$this->open($file);
			if($type){
				$this->info['type'] = $type;
			}
			if(!$quality){
				$quality = 90;
			}
			if($output == ''){
				$output = $file;
			}
			switch($rotate){
				case "left":
					$degrees = 270;
					break;
				case "right":
					$degrees = 90;
					break;
				case "flip":
					$degrees = 180;
					break;
				default:
					$degrees = $rotate;
					break;
			}
			$this->new_image = imagerotate($this->image,$degrees,0);
			return $this->save($output,$quality);
		}
	
	}