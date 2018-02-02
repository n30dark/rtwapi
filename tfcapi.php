<?php
/**
 * the first club
 * 
 * @link    http://www.thefirstclub.com
 * @package framework_library_tfcapi
 */

/**
 * @category framework
 * @package  framework_library_tfcapi
 */

class Library_TfcApi
{

	private $user;

	private $key;

	private $url;

	private $hashAlgorithm = 'sha256';

	private $requestType = 'GET';

	private $data = array();

	public function __construct(){

	}

	public function setUser($user){
		$this->user = $user;
		return $this;
	}

	public function setKey($key){
		$this->key = $key;
		return $this;
	}

	public function setUrl($url){
		$this->url = $url;
		return $this;
	}

	public function setData($data = array()){
		if(!is_array($data)) throw new Exception('Cannot set TFC API data: data is not array');
		$this->data = $data;
		return $this;
	}

	public function setGet(){
		$this->requestType = 'GET';
		return $this;
	}

	public function setPost(){
		$this->requestType = 'POST';
		return $this;
	}

	public function setPut(){
		$this->requestType = 'PUT';
		return $this;
	}

	public function setDelete(){
		$this->requestType = 'DELETE';
		return $this;
	}

	public function makeRequest(){
		$auth = $this->makeAuth();
		$signature = $this->makeSignature($auth);

		$ch = curl_init();

		$headers = array(
			'X-TFC-Auth: ' . $auth,
			'X-TFC-Signature: ' . $signature
		);

		$options = array(
			CURLOPT_URL => $this->url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
		);

		if($this->requestType !== 'GET'){
			$options[CURLOPT_CUSTOMREQUEST] = $this->requestType;
			$options[CURLOPT_POSTFIELDS] = empty($this->data) ? null : json_encode($this->data);
		}
		curl_setopt_array($ch, $options);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	private function makeAuth(){
		$auth = 'timestamp=' . date('c') . '&user_id=' . $this->user;
		return $auth;
	}

	private function makeSignature($auth){
		$stringToHash = $auth;
		if(!empty($this->data)){
			$stringToHash = 'data=' . json_encode($this->data) . '&' . $stringToHash;
		}
		
		$signature = hash_hmac($this->hashAlgorithm, $stringToHash, $this->key);
		return $signature;
	}

}
