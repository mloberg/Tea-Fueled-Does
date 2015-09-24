<?php namespace TFD;

/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *   Matt Loberg (class)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

	class ReCAPTCHA{
	
		/**
		 * The reCAPTCHA server URL's
		 */
		
		const API_SERVER = 'http://www.google.com/recaptcha/api';
		const API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';
		const VERIFY_SERVER = 'www.google.com';
		
		/**
		 * Encodes the given data into a query string format
		 * @param $data - array of string elements to be encoded
		 * @return string - encoded request
		 */
		
		private static function __qsencode($data){
			$req = '';
			foreach($data as $key => $value){
				$req .= $key . '=' . urlencode(stripslashes($value)) .'&';
			}
			
			$req = substr($req, 0, -1);
			
			return $req;
		}
		
		/**
		 * Submits an HTTP POST to a reCAPTCHA server
		 * @param string $host
		 * @param string $path
		 * @param array $data
		 * @param int port
		 * @return array response
		 */
		
		private static function __http_post($host, $path, $data, $port = 80){
			$req = self::__qsencode($data);
			
			$http_request = "POST {$path} HTTP/1.0\r\n" .
							"Host: {$host}\r\n" .
							"Content-Type: application/x-www-form-urlencoded;\r\n" .
							"Content-Length: ".strlen($req)."\r\n" .
							"User-Agent: reCAPTCHA/PHP\r\n" .
							"\r\n" .
							$req;
			
			$response = '';
			if(false == ($fs = @fsockopen($host, $port, $errno, $errstr, 10))){
				die('Could not open socket!');
			}
			
			fwrite($fs, $http_request);
			
			while(!feof($fs)){
				$response .= fgets($fs, 1160); // One TCP-IP packet
			}
			
			fclose($fs);
			
			$response = explode("\r\n\r\n", $response, 2);
			
			return $response;
		}
		
		/**
		 * Gets the challenge HTML (javascript and non-javascript version).
		 * This is called from the browser, and the resulting reCAPTCHA HTML widget
		 * is embedded within the HTML form it was called from.
		 * @param string $pubkey A public key for reCAPTCHA
		 * @param string $error The error given by reCAPTCHA (optional, default is null)
		 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
		
		 * @return string - The HTML to be embedded in the user's form.
		 */
 		
		public static function get_html($pubkey = null, $error = null, $use_ssl = false){
			if(is_null($pubkey)) $pubkey = Config::get('recaptcha.public_key');
			if(empty($pubkey)){
				throw new \Exception('To use reCAPTCHA you must get an API key from <a href="https://www.google.com/recaptcha/admin/create">https://www.google.com/recaptcha/admin/create</a>');
			}
			
			$server = ($use_ssl) ? self::API_SECURE_SERVER : self::API_SERVER;
			
			$errorpart = '';
			if($error) $errorpart = "&amp;error=".$error;
			
			return <<<RECAPTCHA
<script type="text/javascript" src="$server/challenge?k=$pubkey$errorpart"></script>
<noscript>
	<iframe src="$server/noscript?k=$pubkey$errorpart" height="300" width="500" frameborder="0"></iframe><br />
	<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
	<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
</noscript>
RECAPTCHA;
		}
		
		/**
		  * Calls an HTTP POST function to verify if the user's guess was correct
		  * @param string $privkey
		  * @param string $remoteip
		  * @param string $challenge
		  * @param string $response
		  * @param array $extra_params an array of extra variables to post to the server
		  * @return ReCaptchaResponse
		  */
		
		public static function check_answer($remoteip = null, $challenge = null, $response = null, $privkey = null, $extra_params = array()){
			if(is_null($privkey)) $privkey = Config::get('recaptcha.private_key');
			if(empty($privkey)){
				throw new \Exception('To use reCAPTCHA you must get an API key from <a href="https://www.google.com/recaptcha/admin/create">https://www.google.com/recaptcha/admin/create</a>.');
			}
			
			if(is_null($remoteip)) $remoteip = $_SERVER['REMOTE_ADDR'];
			if(is_null($challenge)) $challenge = $_REQUEST['recaptcha_challenge_field'];
			if(is_null($response)) $response = $_REQUEST['recaptcha_response_field'];
			
			if(strlen($challenge) == 0 || strlen($response) == 0){
				$response = new ReCaptchaResponse();
				$response->set(false, 'incorrect-captcha-sol');
				return $response;
			}
			
			$response = self::__http_post(self::VERIFY_SERVER, '/recaptcha/api/verify',
				array(
					'privatekey' => $privkey,
					'remoteip' => $remoteip,
					'challenge' => $challenge,
					'response' => $response
				) + $extra_params
			);
			
			$answers = explode("\n", $response[1]);
			
			$resp = new ReCaptchaResponse();
			
			if(trim($answers[0]) == 'true'){
				$resp->set(true);
			}else{
				$resp->set(false, $answers[1]);
			}
			
			return $resp;
		}
	
	}
	
	/**
	 * A ReCaptchaResponse is returned from recaptcha_check_answer()
	 */
	
	class ReCaptchaResponse extends ReCAPTCHA{
	
		private static $is_valid;
		private static $error;
		
		public function set($valid, $error = ''){
			self::$is_valid = $valid;
			self::$error = $error;
		}
		
		public function is_valid(){
			return self::$is_valid;
		}
		
		public function error(){
			return self::$error;
		}
	
	}

function _recaptcha_aes_pad($val) {
	$block_size = 16;
	$numpad = $block_size - (strlen ($val) % $block_size);
	return str_pad($val, strlen ($val) + $numpad, chr($numpad));
}

/* Mailhide related code */

function _recaptcha_aes_encrypt($val,$ky) {
	if (! function_exists ("mcrypt_encrypt")) {
		die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
	}
	$mode = MCRYPT_MODE_CBC;   
	$enc = MCRYPT_RIJNDAEL_128;
	$val = _recaptcha_aes_pad($val);
	return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
}


function _recaptcha_mailhide_urlbase64 ($x) {
	return strtr(base64_encode ($x), '+/', '-_');
}

/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
function recaptcha_mailhide_url($pubkey, $privkey, $email) {
	if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
		die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
		     "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>");
	}
	

	$ky = pack('H*', $privkey);
	$cryptmail = _recaptcha_aes_encrypt ($email, $ky);
	
	return "http://www.google.com/recaptcha/mailhide/d?k=" . $pubkey . "&c=" . _recaptcha_mailhide_urlbase64 ($cryptmail);
}

/**
 * gets the parts of the email to expose to the user.
 * eg, given johndoe@example,com return ["john", "example.com"].
 * the email is then displayed as john...@example.com
 */
function _recaptcha_mailhide_email_parts ($email) {
	$arr = preg_split("/@/", $email );

	if (strlen ($arr[0]) <= 4) {
		$arr[0] = substr ($arr[0], 0, 1);
	} else if (strlen ($arr[0]) <= 6) {
		$arr[0] = substr ($arr[0], 0, 3);
	} else {
		$arr[0] = substr ($arr[0], 0, 4);
	}
	return $arr;
}

/**
 * Gets html to display an email address given a public an private key.
 * to get a key, go to:
 *
 * http://www.google.com/recaptcha/mailhide/apikey
 */
function recaptcha_mailhide_html($pubkey, $privkey, $email) {
	$emailparts = _recaptcha_mailhide_email_parts ($email);
	$url = recaptcha_mailhide_url ($pubkey, $privkey, $email);
	
	return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
		"' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);

}


?>