<?php namespace TFD\Tea;

	use TFD\File;
	
	class Update{
	
		public static function action($arg){
			$latest = file_get_contents('http://get.teafueleddoes.com/update.php?latest');
			$current = File::get(BASE_DIR.'.tfdrevision');
			if($latest == $current){
				echo "You are up to date!\n";
				exit(0);
			}else{
				$update = json_decode(self::post_request('changes', array('from' => $current)), true);
				if($arg == 'list'){
					if(!empty($update['delete'])){
						echo "These files have been deleted:\n";
						foreach($update['delete'] as $file => $content){
							echo "    * {$file}\n";
						}
						echo "\n";
					}
					if(!empty($update['update'])){
						echo "These files have been changed:\n";
						foreach($update['update'] as $file => $content){
							echo "    * {$file}\n";
						}
						echo "\n";
					}
					echo $update['message'];
				}elseif(Tea::yes_no('Would you like to update TFD?')){
					if(!empty($update['delete'])){
						foreach($update['delete'] as $delete){
							@unlink($delete);
							if(file_exists($delete)) echo "\nCould not delete {$delete}!";
						}
					}
					if(!empty($update['update'])){
						foreach($update['update'] as $file => $content){
							$checksum = md5_file($file);
							@unlink($file);
							File::put($file, base64_decode($content));
							if(md5_file($file) == $checksum) echo "\nCould not update {$file}!";
						}
					}
					File::put(BASE_DIR.'.tfdrevision', $update['sha']);
					echo $update['message'];
					echo "TFD updated!\n";
				}
			}
		}
		
		private static function post_request($method, $params){
			$post = array(
				'time' => md5(time()),
				'method' => $method
			) + $params;
			$ch = curl_init('http://get.teafueleddoes.com/update.php');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resp = curl_exec($ch);
			curl_close($ch);
			return $resp;
		}
	
	}