<?php

	class Upload extends App{
		
		function save($file,$path,$name=''){
			if($name == ''){
				$name = basename($_FILES[$file]['name']);
			}
			$target = './'.$path.'/'.$name;
			if(move_uploaded_file($_FILES[$file]['tmp_name'], $target)){
				return true;
			}else{
				return false;
			}
		}
		
		function type($file,$mimes){
			if(is_array($mimes)){
				foreach($mimes as $m){
					$mime .= $m.'|';
				}
				$mimes = preg_replace('/\|$/','',$mime);
			}
			if(preg_match("/({$mimes})$/", $_FILES[$file]['name'])){
				return true;
			}else{
				return false;
			}
		}
		
		function is_image($file){
			$mimes = 'gif|jpg|jpeg|png|tiff|tif';
			if($this->type($file,$mimes)){
				return true;
			}else{
				return false;
			}
		}
		
		function exists($file,$path){
			$full_path = './'.$path.'/'.basename($_FILES[$file]['name']);
			if(file_exists($full_path)){
				return true;
			}else{
				return false;
			}
		}
		
		function size($file,$return=''){
			$bytes = $_FILES[$file]['size'];
			switch($return){
				case "kb":
					$size = round($bytes / 1024);
					break;
				case "mb":
					$size = $bytes / 1048576;
					if($size < 1){
						$size = round($size,2);
					}else{
						$size = round($size,1);
					}
					break;
				case "gb":
					$size = round(($bytes / 1073741824),1);
					break;
				default:
					$size = $bytes;
					break;
			}
			return $size;
		}
	
	}