<?php namespace Content;

/**
 * TFD allows for different environments such as development, testing, and production.
 * Each environment can have a different set of options like MySQL host, error reporting, or anything you want or need.
 * Then to define the environment you are using, you must change line 2 of public/.htaccess from DEVELOPMENT to whatever environment you need
 *
 * You can read more about this file at http://teafueleddoes.com/v2/config
 */

	use TFD\Config;
	
	class Environment{
	
		function __construct($env){
			// load some global config options
			$this->general_config();
			$this->api_keys();
			
			// call specific environment settings
			$env = strtolower($env);
			if(!method_exists(__CLASS__, $env)){
				throw new \Exception("{$env} is not a valid environment setting");
			}
			$this->$env();
			Config::set('application.environment', $env);
		}
		
		function general_config(){
			Config::load(array(
				'site.maintenance' => false,
				'site.title' => 'Tea-Fueled Does',
				
				'admin.login' => '/login',
				'admin.logout' => '/logout',
				'admin.path' => '/admin',
				'admin.table' => 'users',
				'admin.auth_key' => '4f0712cd96a93',
				'admin.login_time' => 3600,
				'admin.cost' => 12, // rounds for hashing passwords
				
				'crypter.rounds' => 10, // default rounds for the crypter class
				
				'ajax.path' => '/ajax',
				'ajax.parameter' => 'method',
				'ajax.debug' => false,
				
				'cache.key' => ''
			));
		}
		
		function api_keys(){
			Config::load(array(
				// ReCAPTCHA - http://www.google.com/recaptcha
				'recaptcha.public_key' => '',
				'recaptcha.private_key' => '',
				
				// Postmark - http://postmarkapp.com/
				'postmark.api_key' => '',
				'postmark.from' => '',
				'postmark.reply_to' => '',
				
				// Amazon S3 - http://aws.amazon.com/s3/
				's3.access_key' => '',
				's3.secret_key' => '',
				's3.bucket' => '',
				's3.acl' => 'private'
			));
		}
		
		/**
		 * ENVIRONMENTS
		 */
		
		function development(){
			// php error reporting
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
			Config::load(array(
				'site.url' => 'http://tdd.local', // without trailing slash
				
				'error.log' => false,
				'error.detailed' => true,
				
				'mysql.host' => '127.0.0.1', // do not use "localhost" (use 127.0.0.1 instead)
				'mysql.port' => 3306, // MySQL default is 3306
				'mysql.user' => 'root',
				'mysql.pass' => 'root',
				'mysql.db' => 'tfd',
				
				'redis.host' => '',
				'redis.port' => 6379,
				'redis.pass' => '', // blank for none
				
				'cache.driver' => 'file',
				'cache.dir' => BASE_DIR.'cache/',
				
				'memcached.class' => '', // defaults to memcache
				'memcached.servers' => array(
					array(
						'host' => '',
						'port' => 11211,
						'weight' => 100,
					)
				)
			));
		}
		
		function testing(){
			// php error reporting
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
			Config::load(array(
				'site.url' => '', // without trailing slash
				
				'error.log' => BASE_DIR.'error.log',
				'error.detailed' => true,
				
				'mysql.host' => '',
				'mysql.port' => 3306,
				'mysql.user' => '',
				'mysql.pass' => '',
				'mysql.db' => '',
				
				'redis.host' => '',
				'redis.port' => 6379,
				'redis.pass' => '', // blank for none
				
				'cache.driver' => 'file',
				'cache.dir' => BASE_DIR.'cache/',
				
				'memcached.class' => '', // defaults to memcache
				'memcached.servers' => array(
					array(
						'host' => '',
						'port' => 11211,
						'weight' => 100,
					)
				)
			));
		}
		
		function production(){
			// php error reporting
			error_reporting(0); // no reporting
			Config::load(array(
				'site.url' => '', // without trailing slash
				
				'error.log' => BASE_DIR.'error.log',
				'error.detailed' => false,
				
				'mysql.host' => '',
				'mysql.port' => 3306,
				'mysql.user' => '',
				'mysql.pass' => '',
				'mysql.db' => '',
				
				'redis.host' => '',
				'redis.port' => 6379,
				'redis.pass' => '', // blank for none
				
				'cache.driver' => 'file',
				'cache.dir' => BASE_DIR.'cache/',
				
				'memcached.class' => '', // defaults to memcache
				'memcached.servers' => array(
					array(
						'host' => '',
						'port' => 11211,
						'weight' => 100,
					)
				)
			));
		}
	
	}