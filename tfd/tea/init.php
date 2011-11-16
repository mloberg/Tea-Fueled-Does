<?php namespace TFD\Tea;

	/**
	 * Get TFD ready to go
	 */
	
	use TFD\Config as C;
	use TFD\File;
	
	class Init{
	
		public static function action($args){
			// get current commit
			if(!file_exists(BASE_DIR.'.tfdrevision')){
				$commits = json_decode(file_get_contents('https://api.github.com/repos/mloberg/Tea-Fueled-Does/commits'), true);
				File::put(BASE_DIR.'.tfdrevision', $commits[0]['sha']);
			}
			
			// remove git related files
			self::recursive_rm(BASE_DIR.'.git');
			@unlink(BASE_DIR.'.gitignore');
			@unlink(BASE_DIR.'cache/.gitignore');
			
			// include our config file
			include_once(CONTENT_DIR.'config'.EXT);
			// environment
			echo 'Current environment is '.C::get('application.environment')."\n";
			if(Tea::yes_no('Change environment?')){
				$methods = get_class_methods('Content\Environment');
				$methods = array_values(array_filter($methods, function($var){return (!preg_match('/(__construct|general_config|api_keys)/', $var));}));
				$resp = Tea::multiple($methods, "Which environment would you like to switch to?");
				if($resp != C::get('application.environment')){
					$htaccess = File::get(PUBLIC_DIR.'.htaccess');
					$new_htaccess = preg_replace('/(SetEnv ENV )'.strtoupper(C::get('application.environment')).'/', '${1}'.strtoupper($resp), $htaccess);
					File::put(PUBLIC_DIR.'.htaccess', $new_htaccess);
					// reload config options
					new \Content\Environment($resp);
				}
			}
			
			$conf_file = File::get(CONTENT_DIR.'config'.EXT);
			$conf = explode('function '.C::get('application.environment').'(){', $conf_file);
			
			// site url
			echo 'Site URL (with trailing slash) ['.C::get('site.url').']: ';
			$url = Tea::response(C::get('site.url'));
			$conf[1] = preg_replace("/('site\.url' => ')".preg_quote(C::get('site.url'), '/')."(')/", '${1}'.$url.'$2', $conf[1], 1);
			C::set('site.url', $url);
			
			// mysql host
			echo 'MySQL host (do not use localhost!) ['.C::get('mysql.host').']: ';
			$mysql_host = Tea::response(C::get('mysql.host'));
			$conf[1] = preg_replace("/('mysql\.host' => ')".preg_quote(C::get('mysql.host'))."(')/", '${1}'.$mysql_host.'$2', $conf[1], 1);
			C::set('mysql.host', $mysql_host);
			
			// mysql port
			echo 'MySQL port ['.C::get('mysql.port').']: ';
			$mysql_port = Tea::response(C::get('mysql.port'));
			$conf[1] = preg_replace("/('mysq\.port' => )".C::get('mysql.port')."/", '${1}'.$mysql_port, $conf[1], 1);
			C::set('mysql.port', $mysql_port);
			
			// mysql user
			echo 'MySQL user ['.C::get('mysql.user').']: ';
			$mysql_user = Tea::response(C::get('mysql.user'));
			$conf[1] = preg_replace("/('mysql\.user' => ')".preg_quote(C::get('mysql.user'))."(')/", '${1}'.$mysql_user.'$2', $conf[1], 1);
			C::set('mysql.user', $mysql_user);
			
			// mysq pass
			echo 'MySQL pass ['.C::get('mysql.pass').']: ';
			$mysql_pass = Tea::response(C::get('mysql.pass'));
			$conf[1] = preg_replace("/('mysql\.pass' => ')".preg_quote(C::get('mysql.pass'))."(')/", '${1}'.$mysql_pass.'$2', $conf[1], 1);
			C::set('mysql.pass', $mysql_pass);
			
			// mysql db
			echo 'MySQL Database ['.C::get('mysql.db').']: ';
			$mysql_db = Tea::response(C::get('mysql.db'));
			$conf[1] = preg_replace("/('mysql.db' => ')".preg_quote(C::get('mysql.db'))."(')/", '${1}'.$mysql_db.'$2', $conf[1], 1);
			C::set('mysql.db', $mysql_db);
			
			// write new config file
			File::put(CONTENT_DIR.'config'.EXT, $conf[0].'function '.C::get('application.environment').'(){'.$conf[1]);
			
			Database::init();
			
			if(Tea::yes_no('Setup Migrations?')){
				Migrations::init();
				echo "\n";
			}
			
			// Add a user
			if(Tea::yes_no("Add a user?")){
				User::add();
			}
		}
		
		private static function recursive_rm($dir){
			if(!is_dir($dir) || is_link($dir)) return unlink($dir);
			foreach(scandir($dir) as $file){
				if($file == '.' || $file == '..') continue;
				if(!self::recursive_rm($dir.'/'.$file)){
					chmod($dir.'/'.$file, 0777);
					if(!self::recursive_rm($dir.'/'.$file)) return false;
				}
			}
			return rmdir($dir);
		}
	
	}