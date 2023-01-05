<?php

namespace SiteSystemSynchronizer;


class HttpRequest {
	
	private $base_url = '';
	private $headers = [];
	
	public function __construct($params){
		Helper::_log(__METHOD__);
		
		$this->base_url = $params['base_uri'];
		if(!empty($params['headers'])){
			$headers = array_map(function($k, $v){return "$k: $v";}, array_keys($params['headers']), $params['headers']);
			$this->headers = $headers;
		}
		#Helper::_log($this->base_url);
		#Helper::_log($this->headers);
	}
	
	private function create_url($url){
		Helper::_log(__METHOD__);
		
		$url_fragment = $this->base_url.'/'.$url;

		#Helper::_log('API URL: '.$url_fragment);
		
		return $url_fragment;
	}
	
	public function call($params){
		Helper::_log(__METHOD__);
		
		$curl = curl_init();
		
		curl_setopt_array($curl, [
			CURLOPT_URL => $params['URL'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $params['METHOD'],
			CURLOPT_POSTFIELDS => $params['DATA'],
			CURLOPT_HTTPHEADER => $this->headers,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
		]);
		
		$response = curl_exec($curl);
		
		curl_close($curl);
		
		return $response;
	}
	
	public function get($url, $data){
		Helper::_log(__METHOD__);
		
		$response = $this->call([
			'METHOD' => 'GET',
			'URL'    => $this->create_url($url),
			'DATA'   => $data
		]);
		
		return json_decode($response, true);
	}
	
	public function post($url, $data){
		Helper::_log(__METHOD__);
		
		$response = $this->call([
			'METHOD' => 'POST',
			'URL'    => $this->create_url($url),
			'DATA'   => $data
		]);
		
		return json_decode($response, true);
	}
	
}



