<?php namespace TFD\Tea;

	class Tea{
	
		/**
		 * Get the user's response
		 */
		
		public static function response($default = null){
			$response = trim(fgets(STDIN));
			if(!is_null($default) && empty($response)){
				return $default;
			}
			return $response;
		}
		
		public static function response_to_lower($default = null){
			return strtolower(self::response($default));
		}
		
		public static function response_to_upper($default = null){
			return strtoupper(self::response($default));
		}
		
		public static function yes_no($question){
			do{
				echo $question.' [y/n]: ';
				$response = self::response_to_lower();
				if($response == 'y'){
					return true;
				}elseif($response == 'n'){
					return false;
				}
			}while(!$exit);
		}
		
		public static function multiple($choice, $text = "Please select an above option: "){
			foreach($choice as $key => $value){
				echo "  {$key}: {$value}\n";
			}
			do{
				echo $text.' ';
				$resp = $choice[self::response()];
			}while(empty($resp));
			return $resp;
		}
	
	}