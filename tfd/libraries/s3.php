<?php
/**
* $Id: S3.php 47 2009-07-20 01:25:40Z don.schonknecht $
*
* Copyright (c) 2008, Donovan Sch?nknecht.  All rights reserved.
* Copyright (c) 2010, Nick Welch
* Copyright (c) 2010, Parthenon Software
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*   this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*   notice, this list of conditions and the following disclaimer in the
*   documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* Amazon S3 is a trademark of Amazon.com, Inc. or its affiliates.
*
*
* This class has been modified by Matthew Loberg for use with Tea-Fueled Does.
*
*/

class S3Exception extends Exception {
	// eventually extend to use my errors class
}

/**
* Amazon S3 PHP class
*
* original class - http://undesigned.org.za/2007/10/22/amazon-s3-php-class
* modified by Matthew Loberg (http://mloberg.com) for Tea-Fueled Does
*/
	class S3{
	
		private static $__accessKey = S3_ACCESS_KEY;
		private static $__secretKey = S3_SECRET_KEY;
		private static $bucket;
		private static $acl;
	
	
		/********************
		 SETTERS AND GETTERS
		********************/
		
		public static function set_auth($accessKey, $secretKey){
			self::$__accessKey = $accessKey;
			self::$__secretKey = $secretKey;
		}
		
		public static function set_acl($acl){
			if(preg_match('/(private|public-read|public-read-write|authenticated-read)/',$acl)){
				self::$acl = $acl;
			}else{
				self::$acl = S3_DEFAULT_ACL;
			}
		}
		
		public static function get_acl(){
			if(self::$acl){
				return self::$acl;
			}else{
				return S3_DEFAULT_ACL;
			}
		}
		
		public static function set_bucket($bucket){
			self::$bucket = $bucket;
		}
		
		public static function get_bucket(){
			if(self::$bucket){
				return self::$bucket;
			}else{
				return S3_DEFAULT_BUCKET;
			}
		}
		
	
		/********************
		 BUCKET METHODS
		********************/
		
		public static function listBuckets($detailed = false) {
			$rest = new S3Request('GET', '', '');
			$rest = $rest->getResponse();
			if ($rest->error === false && $rest->code !== 200)
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			if ($rest->error !== false) {
				trigger_error(sprintf("S3::listBuckets(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			$results = array();
			if (!isset($rest->body->Buckets)) return $results;
	
			if ($detailed) {
				if (isset($rest->body->Owner, $rest->body->Owner->ID, $rest->body->Owner->DisplayName))
				$results['owner'] = array(
					'id' => (string)$rest->body->Owner->ID, 'name' => (string)$rest->body->Owner->ID
				);
				$results['buckets'] = array();
				foreach ($rest->body->Buckets->Bucket as $b)
					$results['buckets'][] = array(
						'name' => (string)$b->Name, 'time' => strtotime((string)$b->CreationDate)
					);
			} else
				foreach ($rest->body->Buckets->Bucket as $b) $results[] = (string)$b->Name;
	
			return $results;
		}

		public static function getBucket($prefix = null, $marker = null, $maxKeys = null, $delimiter = null, $returnCommonPrefixes = false){
			$bucket = self::get_bucket();
			
			$rest = new S3Request('GET', $bucket, '');
			if ($prefix !== null && $prefix !== '') $rest->setParameter('prefix', $prefix);
			if ($marker !== null && $marker !== '') $rest->setParameter('marker', $marker);
			if ($maxKeys !== null && $maxKeys !== '') $rest->setParameter('max-keys', $maxKeys);
			if ($delimiter !== null && $delimiter !== '') $rest->setParameter('delimiter', $delimiter);
			$response = $rest->getResponse();
			if ($response->error === false && $response->code !== 200)
				$response->error = array('code' => $response->code, 'message' => 'Unexpected HTTP status');
			if ($response->error !== false) {
				trigger_error(sprintf("S3::getBucket(): [%s] %s", $response->error['code'], $response->error['message']), E_USER_WARNING);
				return false;
			}
	
			$results = array();
	
			$nextMarker = null;
			if (isset($response->body, $response->body->Contents))
			foreach ($response->body->Contents as $c) {
				$results[(string)$c->Key] = array(
					'name' => (string)$c->Key,
					'time' => strtotime((string)$c->LastModified),
					'size' => (int)$c->Size,
					'hash' => substr((string)$c->ETag, 1, -1)
				);
				$nextMarker = (string)$c->Key;
			}
	
			if ($returnCommonPrefixes && isset($response->body, $response->body->CommonPrefixes))
				foreach ($response->body->CommonPrefixes as $c)
					$results[(string)$c->Prefix] = array('prefix' => (string)$c->Prefix);
	
			if (isset($response->body, $response->body->IsTruncated) &&
			(string)$response->body->IsTruncated == 'false') return $results;
	
			if (isset($response->body, $response->body->NextMarker))
				$nextMarker = (string)$response->body->NextMarker;
	
			// Loop through truncated results if maxKeys isn't specified
			if ($maxKeys == null && $nextMarker !== null && (string)$response->body->IsTruncated == 'true')
			do {
				$rest = new S3Request('GET', $bucket, '');
				if ($prefix !== null && $prefix !== '') $rest->setParameter('prefix', $prefix);
				$rest->setParameter('marker', $nextMarker);
				if ($delimiter !== null && $delimiter !== '') $rest->setParameter('delimiter', $delimiter);
	
				if (($response = $rest->getResponse(true)) == false || $response->code !== 200) break;
	
				if (isset($response->body, $response->body->Contents))
				foreach ($response->body->Contents as $c) {
					$results[(string)$c->Key] = array(
						'name' => (string)$c->Key,
						'time' => strtotime((string)$c->LastModified),
						'size' => (int)$c->Size,
						'hash' => substr((string)$c->ETag, 1, -1)
					);
					$nextMarker = (string)$c->Key;
				}
	
				if ($returnCommonPrefixes && isset($response->body, $response->body->CommonPrefixes))
					foreach ($response->body->CommonPrefixes as $c)
						$results[(string)$c->Prefix] = array('prefix' => (string)$c->Prefix);
	
				if (isset($response->body, $response->body->NextMarker))
					$nextMarker = (string)$response->body->NextMarker;
	
			} while ($response !== false && (string)$response->body->IsTruncated == 'true');
	
			return $results;
		}

		public static function putBucket($bucket = null, $location = false) {
			if(!$bucket) $bucket = self::get_bucket();
			$acl = self::get_acl();
			
			$rest = new S3Request('PUT', $bucket, '');
			$rest->setAmzHeader('x-amz-acl', $acl);
	
			if ($location !== false) {
				$dom = new DOMDocument;
				$createBucketConfiguration = $dom->createElement('CreateBucketConfiguration');
				$locationConstraint = $dom->createElement('LocationConstraint', strtoupper($location));
				$createBucketConfiguration->appendChild($locationConstraint);
				$dom->appendChild($createBucketConfiguration);
				$rest->data = $dom->saveXML();
				$rest->size = strlen($rest->data);
				$rest->setHeader('Content-Type', 'application/xml');
			}
			$rest = $rest->getResponse();
	
			if ($rest->error === false && $rest->code !== 200)
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			if ($rest->error !== false) {
				trigger_error(sprintf("S3::putBucket({$bucket}, {$acl}, {$location}): [%s] %s",
				$rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			return true;
		}

		public static function deleteBucket(){
			$bucket = self::get_bucket();
			
			$rest = new S3Request('DELETE', $bucket);
			$rest = $rest->getResponse();
			if ($rest->error === false && $rest->code !== 204)
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			if ($rest->error !== false) {
				trigger_error(sprintf("S3::deleteBucket({$bucket}): [%s] %s",
				$rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			return true;
		}
		
		public static function getBucketLocation(){
			$bucket = self::get_bucket();
			
			$rest = new S3Request('GET', $bucket, '');
			$rest->setParameter('location', null);
			$rest = $rest->getResponse();
			if($rest->error === false && $rest->code !== 200){
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			}
			if($rest->error !== false){
				trigger_error(sprintf("S3::getBucketLocation({$bucket}): [%s] %s",
				$rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			return(isset($rest->body[0]) && (string)$rest->body[0] !== '') ? (string)$rest->body[0] : 'US';
		}
		
		
		/********************
		 OBJECT METHODS
		********************/
		
		public static function putObject($input, $uri, $metaHeaders = array(), $requestHeaders = array()) {
			$bucket = self::get_bucket();
			$acl = self::get_acl();
			
			$rest = new S3Request('PUT', $bucket, $uri);
	
			if (is_string($input)) $input = array(
				'data' => $input, 'size' => strlen($input),
				'md5sum' => base64_encode(md5($input, true))
			);
	
			// Data
			if (isset($input['fp']))
				$rest->fp =& $input['fp'];
			elseif (isset($input['file']))
				$rest->fp = @fopen($input['file'], 'rb');
			elseif (isset($input['data']))
				$rest->data = $input['data'];
	
			// Content-Length (required)
			if (isset($input['size']) && $input['size'] >= 0)
				$rest->size = $input['size'];
			else {
				if (isset($input['file']))
					$rest->size = filesize($input['file']);
				elseif (isset($input['data']))
					$rest->size = strlen($input['data']);
			}
	
			// Custom request headers (Content-Type, Content-Disposition, Content-Encoding)
			if (is_array($requestHeaders))
				foreach ($requestHeaders as $h => $v) $rest->setHeader($h, $v);
			elseif (is_string($requestHeaders)) // Support for legacy contentType parameter
				$input['type'] = $requestHeaders;
	
			// Content-Type
			if (!isset($input['type'])) {
				if (isset($requestHeaders['Content-Type']))
					$input['type'] =& $requestHeaders['Content-Type'];
				elseif (isset($input['file']))
					$input['type'] = self::__getMimeType($input['file']);
				else
					$input['type'] = 'application/octet-stream';
			}
	
			// We need to post with Content-Length and Content-Type, MD5 is optional
			if ($rest->size < 0 || ($rest->fp === false && $rest->data === false))
				throw new S3Exception('Missing input parameters');
	
			$rest->setHeader('Content-Type', $input['type']);
			if (isset($input['md5sum'])) $rest->setHeader('Content-MD5', $input['md5sum']);
	
			$rest->setAmzHeader('x-amz-acl', $acl);
			foreach ($metaHeaders as $h => $v) $rest->setAmzHeader('x-amz-meta-'.$h, $v);
			$rest->getResponse();
	
			if ($rest->response->error === false && $rest->response->code !== 200)
				throw new S3Exception('Unexpected HTTP status', $rest->response->code);
	
			if ($rest->response->error !== false)
				throw new S3Exception($rest->response->error['message'], $rest->response->error['code']);
	
			return true;
		}

		public static function putObjectFile($file, $uri, $metaHeaders = array(), $contentType = null) {
			return self::putObject(self::inputFile($file), $uri, $metaHeaders, $contentType);
		}

		public static function get_object($uri, $saveTo = false){
			$bucket = self::get_bucket();
			
			$rest = new S3Request('GET', $bucket, $uri);
			if ($saveTo !== false) {
				if (is_resource($saveTo))
					$rest->fp =& $saveTo;
				else
					if (($rest->fp = @fopen($saveTo, 'wb')) !== false)
						$rest->file = realpath($saveTo);
					else
						$rest->response->error = array('code' => 0, 'message' => 'Unable to open save file for writing: '.$saveTo);
			}
			if ($rest->response->error === false) $rest->getResponse();
	
			if ($rest->response->error === false && $rest->response->code !== 200)
				$rest->response->error = array('code' => $rest->response->code, 'message' => 'Unexpected HTTP status');
			if ($rest->response->error !== false) {
				trigger_error(sprintf("S3::getObject({$bucket}, {$uri}): [%s] %s",
				$rest->response->error['code'], $rest->response->error['message']), E_USER_WARNING);
				return false;
			}
			return $rest->response;
		}

		public static function getObjectInfo($uri, $returnInfo = true){
			$bucket = self::get_bucket();
			
			$request = new S3Request('HEAD', $bucket, $uri);
			$response = $request->getResponse();
	
			if ($response->error !== false)
				throw new S3Exception($response->error['message'], $response->error['code']);
	
			if ($response->code !== 200 && $response->code !== 404)
				throw new S3Exception('Unexpected HTTP status', $response->code);
	
			if ($response->code == 200)
				return $returnInfo ? $response->headers : true;
	
			return false;
		}

		public static function copyObject($srcBucket, $srcUri, $uri, $metaHeaders = array(), $requestHeaders = array()){
			$bucket = self::get_bucket();
			$acl = self::get_acl();
			
			$rest = new S3Request('PUT', $bucket, $uri);
			$rest->setHeader('Content-Length', 0);
			foreach ($requestHeaders as $h => $v) $rest->setHeader($h, $v);
			foreach ($metaHeaders as $h => $v) $rest->setAmzHeader('x-amz-meta-'.$h, $v);
			$rest->setAmzHeader('x-amz-acl', $acl);
			$rest->setAmzHeader('x-amz-copy-source', sprintf('/%s/%s', $srcBucket, $srcUri));
			if(sizeof($requestHeaders) > 0 || sizeof($metaHeaders) > 0){
				$rest->setAmzHeader('x-amz-metadata-directive', 'REPLACE');
			}
			$rest = $rest->getResponse();
			if($rest->error === false && $rest->code !== 200)
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			if($rest->error !== false){
				trigger_error(sprintf("S3::copyObject({$srcBucket}, {$srcUri}, {$bucket}, {$uri}): [%s] %s",
				$rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			return isset($rest->body->LastModified, $rest->body->ETag) ? array(
				'time' => strtotime((string)$rest->body->LastModified),
				'hash' => substr((string)$rest->body->ETag, 1, -1)
			) : false;
		}

		public static function deleteObject($object) {
			$bucket = self::get_bucket();
			
			$rest = new S3Request('DELETE', $bucket, $object);
			$rest = $rest->getResponse();
			if ($rest->error === false && $rest->code !== 204){
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			}
			if ($rest->error !== false){
				trigger_error(sprintf("S3::deleteObject(): [%s] %s",
				$rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			return true;
		}

		public static function getAuthenticatedURL($uri, $lifetime, $hostBucket = false, $https = false){
			$bucket = self::get_bucket();
			$expires = time() + $lifetime;
			$uri = str_replace('%2F', '/', rawurlencode($uri)); // URI should be encoded (thanks Sean O'Dea)
			return sprintf(($https ? 'https' : 'http').'://%s/%s?AWSAccessKeyId=%s&Expires=%u&Signature=%s',
			$hostBucket ? $bucket : $bucket.'.s3.amazonaws.com', $uri, self::$__accessKey, $expires,
			urlencode(self::__getHash("GET\n\n\n{$expires}\n/{$bucket}/{$uri}")));
		}
		
		
		/********************
		 HELPER METHODS
		********************/
		
		private static function inputFile($file, $md5sum = true) {
			if (!is_file($file) || !is_readable($file))
				throw new S3Exception("Unable to open input file: $file");
			return array('file' => $file, 'size' => filesize($file),
			'md5sum' => $md5sum !== false ? (is_string($md5sum) ? $md5sum :
			base64_encode(md5_file($file, true))) : '');
		}
	
		public static function __getMimeType(&$file) {
			$type = false;
			// Fileinfo documentation says fileinfo_open() will use the
			// MAGIC env var for the magic file
			if (extension_loaded('fileinfo') && isset($_ENV['MAGIC']) &&
			($finfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC'])) !== false) {
				if (($type = finfo_file($finfo, $file)) !== false) {
					// Remove the charset and grab the last content-type
					$type = explode(' ', str_replace('; charset=', ';charset=', $type));
					$type = array_pop($type);
					$type = explode(';', $type);
					$type = trim(array_shift($type));
				}
				finfo_close($finfo);
	
			// If anyone is still using mime_content_type()
			} elseif (function_exists('mime_content_type'))
				$type = trim(mime_content_type($file));
	
			if ($type !== false && strlen($type) > 0) return $type;
	
			// Otherwise do it the old fashioned way
			static $exts = array(
				'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png',
				'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'ico' => 'image/x-icon',
				'swf' => 'application/x-shockwave-flash', 'pdf' => 'application/pdf',
				'zip' => 'application/zip', 'gz' => 'application/x-gzip',
				'tar' => 'application/x-tar', 'bz' => 'application/x-bzip',
				'bz2' => 'application/x-bzip2', 'txt' => 'text/plain',
				'asc' => 'text/plain', 'htm' => 'text/html', 'html' => 'text/html',
				'css' => 'text/css', 'js' => 'text/javascript',
				'xml' => 'text/xml', 'xsl' => 'application/xsl+xml',
				'ogg' => 'application/ogg', 'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav',
				'avi' => 'video/x-msvideo', 'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg',
				'mov' => 'video/quicktime', 'flv' => 'video/x-flv', 'php' => 'text/x-php'
			);
			$ext = strtolower(pathInfo($file, PATHINFO_EXTENSION));
			return isset($exts[$ext]) ? $exts[$ext] : 'application/octet-stream';
		}
		
		
		/********************
		 SIGNATURE METHODS
		********************/
		
		protected static function __getSignature($string){
			return 'AWS '.self::$__accessKey.':'.self::__getHash($string);
		}

		private static function __getHash($string){
			return base64_encode(extension_loaded('hash') ?
			hash_hmac('sha1', $string, self::$__secretKey, true) : pack('H*', sha1(
			(str_pad(self::$__secretKey, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) .
			pack('H*', sha1((str_pad(self::$__secretKey, 64, chr(0x00)) ^
			(str_repeat(chr(0x36), 64))) . $string)))));
		}
	
	}
	
	/********************
	 The Working Class
	********************/
	
	final class S3Request extends S3{
		private $verb, $bucket, $uri, $resource = '', $parameters = array(),
		$amzHeaders = array(), $headers = array(
			'Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => ''
		);
		public $fp = false, $size = 0, $data = false, $response;
	
		function __construct($verb, $bucket = '', $uri = '', $defaultHost = 's3.amazonaws.com') {
			$this->verb = $verb;
			$this->bucket = strtolower($bucket);
			$this->uri = $uri !== '' ? '/'.str_replace('%2F', '/', rawurlencode($uri)) : '/';
	
			if ($this->bucket !== '') {
				$this->headers['Host'] = $this->bucket.'.'.$defaultHost;
				$this->resource = '/'.$this->bucket.$this->uri;
			} else {
				$this->headers['Host'] = $defaultHost;
				$this->resource = $this->uri;
			}
			$this->headers['Date'] = gmdate('D, d M Y H:i:s T');
	
			$this->response = new STDClass;
			$this->response->error = false;
		}
	
		public function setParameter($key, $value) {
			$this->parameters[$key] = $value;
		}
	
		public function setHeader($key, $value) {
			$this->headers[$key] = $value;
		}
	
		public function setAmzHeader($key, $value) {
			$this->amzHeaders[$key] = $value;
		}
	
		public function getResponse() {
			$query = '';
			if (sizeof($this->parameters) > 0) {
				$query = substr($this->uri, -1) !== '?' ? '?' : '&';
				foreach ($this->parameters as $var => $value)
					if ($value == null || $value == '') $query .= $var.'&';
					// Parameters should be encoded (thanks Sean O'Dea)
					else $query .= $var.'='.rawurlencode($value).'&';
				$query = substr($query, 0, -1);
				$this->uri .= $query;
	
				if (array_key_exists('acl', $this->parameters) ||
				array_key_exists('location', $this->parameters) ||
				array_key_exists('torrent', $this->parameters) ||
				array_key_exists('logging', $this->parameters))
					$this->resource .= $query;
			}
			$url = 'http://'.$this->headers['Host'].$this->uri;
	
			// Basic setup
			$curl = curl_init();
	
			curl_setopt($curl, CURLOPT_URL, $url);
	
			// Headers
			$headers = array(); $amz = array();
			foreach ($this->amzHeaders as $header => $value)
				if (strlen($value) > 0) $headers[] = $header.': '.$value;
			foreach ($this->headers as $header => $value)
				if (strlen($value) > 0) $headers[] = $header.': '.$value;
	
			// Collect AMZ headers for signature
			foreach ($this->amzHeaders as $header => $value)
				if (strlen($value) > 0) $amz[] = strtolower($header).':'.$value;
	
			// AMZ headers must be sorted
			if (sizeof($amz) > 0) {
				sort($amz);
				$amz = "\n".implode("\n", $amz);
			} else $amz = '';
	
			// Authorization string
			$headers[] = 'Authorization: ' . S3::__getSignature(
				$this->verb."\n".$this->headers['Content-MD5']."\n".
				$this->headers['Content-Type']."\n".$this->headers['Date'].$amz."\n".$this->resource
			);
	
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
			curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, '__responseWriteCallback'));
			curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, '__responseHeaderCallback'));
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	
			// Request types
			switch ($this->verb) {
				case 'GET': break;
				case 'PUT':
					if ($this->fp !== false) {
						curl_setopt($curl, CURLOPT_PUT, true);
						curl_setopt($curl, CURLOPT_INFILE, $this->fp);
						if ($this->size >= 0)
							curl_setopt($curl, CURLOPT_INFILESIZE, $this->size);
					} elseif ($this->data !== false) {
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
						curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
						if ($this->size >= 0)
							curl_setopt($curl, CURLOPT_BUFFERSIZE, $this->size);
					} else
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
				break;
				case 'HEAD':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
					curl_setopt($curl, CURLOPT_NOBODY, true);
				break;
				case 'DELETE':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
				default: break;
			}
	
			// Execute, grab errors
			if (curl_exec($curl))
				$this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			else
				$this->response->error = array(
					'code' => curl_errno($curl),
					'message' => curl_error($curl),
					'resource' => $this->resource
				);
	
			@curl_close($curl);
	
			// Parse body into XML
			if ($this->response->error === false && isset($this->response->headers['type']) &&
			$this->response->headers['type'] == 'application/xml' && isset($this->response->body)) {
				$this->response->body = simplexml_load_string($this->response->body);
	
				// Grab S3 errors
				if (!in_array($this->response->code, array(200, 204)) &&
				isset($this->response->body->Code, $this->response->body->Message)) {
					$this->response->error = array(
						'code' => (string)$this->response->body->Code,
						'message' => (string)$this->response->body->Message
					);
					if (isset($this->response->body->Resource))
						$this->response->error['resource'] = (string)$this->response->body->Resource;
					unset($this->response->body);
				}
			}
	
			// Clean up file resources
			if ($this->fp !== false && is_resource($this->fp)) fclose($this->fp);
	
			return $this->response;
		}
	
	
		/**
		* CURL write callback
		*/
		private function __responseWriteCallback(&$curl, &$data) {
			if ($this->response->code == 200 && $this->fp !== false)
				return fwrite($this->fp, $data);
			else
				$this->response->body .= $data;
			return strlen($data);
		}
	
	
		/**
		* CURL header callback
		*/
		private function __responseHeaderCallback(&$curl, &$data) {
			if (($strlen = strlen($data)) <= 2) return $strlen;
			if (substr($data, 0, 4) == 'HTTP')
				$this->response->code = (int)substr($data, 9, 3);
			else {
				list($header, $value) = explode(': ', trim($data), 2);
				if ($header == 'Last-Modified')
					$this->response->headers['time'] = strtotime($value);
				elseif ($header == 'Content-Length')
					$this->response->headers['size'] = (int)$value;
				elseif ($header == 'Content-Type')
					$this->response->headers['type'] = $value;
				elseif ($header == 'ETag')
					$this->response->headers['hash'] = $value{0} == '"' ? substr($value, 1, -1) : $value;
				elseif (preg_match('/^x-amz-meta-.*$/', $header))
					$this->response->headers[$header] = is_numeric($value) ? (int)$value : $value;
			}
			return $strlen;
		}
	
	}