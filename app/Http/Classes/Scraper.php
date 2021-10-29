<?php
namespace App\Http\Classes;

abstract class Scraper
{
	protected	$proxyIp 	= '187.130.139.197';
	protected	$proxyPort 	= 37812;
	protected	$proxyType 	= 'socks4';
	
	protected	$useProxy	= false;
	protected	$cookiesFile = null;
	protected	$timeout		= 20;
	/**
	 * @var Cookie[]
	 */
	protected	$cookies		= [];
	protected	$saveCookies	= false;
	
	/**
	 * 
	 * @param string $url
	 * @param mixed $data
	 * @param string $method
	 * @param array $headers
	 * @return \App\Http\Classes\RequestResponse
	 */
	public function request(string $url, $data = null, $method = 'GET', $headers = null)
	{
		$options = [
			CURLOPT_HEADER			=> 1,
			CURLOPT_RETURNTRANSFER 	=> 1,
			CURLOPT_FOLLOWLOCATION	=> 1,
			CURLOPT_MAXREDIRS		=> 100,
			CURLOPT_USERAGENT		=> 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:93.0) Gecko/20100101 Firefox/93.0',
			CURLOPT_SSL_VERIFYHOST	=> 0,
			CURLOPT_TIMEOUT			=> $this->timeout,
			CURLOPT_VERBOSE			=> true,
			CURLINFO_HEADER_OUT		=> true,
			//CURLOPT_SSL_VERIFYPEER	=> 0
		];
		if( $this->useProxy )
			$options[CURLOPT_PROXY]	= sprintf("%s://%s:%d", $this->proxyType, $this->proxyIp, $this->proxyPort);
		
		if( $method == 'POST' )
			$options[CURLOPT_POST] = 1;
		if( in_array($method, ['PUT', 'POST']) )
		{
			$options[CURLOPT_POSTFIELDS] = is_array($data) || is_object($data) ? http_build_query($data) : $data;
			//echo '<pre>', print_r($options[CURLOPT_POSTFIELDS], 1), '</pre>';
		}
		if( in_array($method, ['PUT', 'DELETE', 'PATCH']) )
			$options[CURLOPT_CUSTOMREQUEST] = $method;
		$rHeaders = [];
		if( $headers && is_array($headers) )
		{
			$rHeaders = array_merge($rHeaders, $headers);
		}
		if( count($rHeaders) )
		{
			//print_r($rHeaders);
			$options[CURLOPT_HTTPHEADER] = $rHeaders;
		}
		//##store cookies
		//Create And Save Cookies
		if( $this->cookiesFile )
		{
			$options[CURLOPT_COOKIEJAR]		= $this->cookiesFile;
			$options[CURLOPT_COOKIEFILE] 	= $this->cookiesFile;
		}
		if( $this->saveCookies )
		{
			$this->restoreCookies($options);
			//echo 'SENDING COOKIES: ', $options[CURLOPT_COOKIE], "\n";
		}
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$res = curl_exec($ch);
		//print $res;
		//var_dump($options);die(__METHOD__);
		$info = curl_getinfo($ch);
		curl_close($ch);
		$response = new RequestResponse($res, $info);
		//##save cookies
		//if( $this->saveCookies )
		//	$this->cookies = array_merge($this->cookies, $response->cookies);
		
		return $response;
	}
	protected function restoreCookies(&$options)
	{
		//if( !count($this->cookies) )
		//	return false;
		$cookies = '';
		foreach($this->cookies as $key => $cookie)
		{
			$cookies .= $cookie->getKeyValue() . ';';
		}
		$options[CURLOPT_COOKIE] = rtrim($cookies, ';');
		
	}
	public function setCookie($key, $val)
	{
		$this->cookies[$key] = new Cookie("$key=$val");
	}
}