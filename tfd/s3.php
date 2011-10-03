<?php namespace TFD;

	class S3{
	
		public static function list_buckets(){
			$rest = new S3Request('GET', '', '');
			$rest = $rest->getResponse();
			if($rest->error === false && $rest->code !== 200){
				$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
			}
			if($rest->error !== false){
				trigger_error(sprintf("S3::list_buckets(): [%s] %s", $rest->error['code'], $rest->error['message']), E_USER_WARNING);
				return false;
			}
			$results = array();
			
			if(!isset($rest->body->Buckets)) return $results;
			
			foreach($rest->body->Buckets->Bucket as $b){
				$results[] = (string) $b->Name;
			}
			
			return $results;
		}
		
		protected static function __getSignature($string){
			return 'AWS '.Config::get('s3.access_key').':'.self::__getHash($string);
		}
		
		private static function __getHash($string){
			return base64_encode(hash_hmac('sha1', $string, Config::get('s3.secret_key'), true));
		}
	
	}
	
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
	
			$this->response = new \STDClass;
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