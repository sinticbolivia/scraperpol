<?php
namespace App\Http\Classes;
use Exception;

class Qualitas extends Scraper implements IScraper
{
	protected	$baseUrl = 'https://agentes360.qualitas.com.mx';
	protected	$homeUrl = 'https://agentes360.qualitas.com.mx/web/guest/home';
	protected	$systemId;
	protected	$pageId;
	protected	$login;
	protected	$account;
	protected	$pwd;
	protected	$dataFile;
	protected	$hasSession = false;
	
	public function __construct()
	{
		$this->useProxy 	= true;
		$this->cookiesFile 	= storage_path('logs') . '/qualitas-cookies.txt';
		$this->dataFile 	= storage_path('logs') . '/qualitas-data.json';
		$this->saveCookies = true;
		if( is_file($this->cookiesFile) )
			unlink($this->cookiesFile);
	}
	protected function getLoginUrl()
	{
		$headers = [
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
			//'Accept-Encoding: gzip, deflate, br',
			'Connection: keep-alive',
			'Host: agentes360.qualitas.com.mx',
			'Sec-Fetch-Dest: document',
			'Sec-Fetch-Mode: navigate',
			'Sec-Fetch-Site: none',
			'Upgrade-Insecure-Requests: 1'
		];
		$response = $this->request($this->homeUrl, null, 'GET', $headers);
		//echo '<pre>', print_r($response, 1), '</pre>';
		if( empty($response->body) )
			throw new Exception('REQUEST ERROR: ' . $this->homeUrl . ' => EMPTY RESPONSE');
		
		if( $this->isLoggedIn($response->body) )
		{
			var_dump('HAS_SESSION');
			return 'HAS_SESSION';
		}
		$dom = new \DOMDocument();
		$dom->loadHTML($response->body, LIBXML_NOWARNING | LIBXML_NOERROR);
		$form = $dom->getElementsByTagName('form')->item(0);
		//print $dom->saveHTML($form);
		return $form->getAttribute('action');
		//die(__METHOD__);
	}
	public function setCrendentials($login, $account, $pwd)
	{
		$this->login 	= $login;
		$this->account	= $account;
		$this->pwd		= $pwd;
	}
	public function login($login = null, $account = null, $pwd = null)
	{
		if( $this->hasSession )
			return true;
		
		if( $login && $account && $pwd )
			$this->setCrendentials($login, $account, $pwd);
		
		$url = $this->getLoginUrl();
		if( $url == 'HAS_SESSION' )
		{
			$this->hasSession = true;
			$this->setCookie('LFR_SESSION_STATE_20105', $this->getData()['LFR_SESSION_STATE_20105']);
			$this->setCookie('LFR_SESSION_STATE_972826', $this->getData()['LFR_SESSION_STATE_972826']);
			return true;
		}
		
		//var_dump("LOGIN URL: $url");
		$blogin = $this->login . '-' . $this->account;
		$args = [
			'_com_liferay_login_web_portlet_LoginPortlet_redirect'				=> '',
			'_com_liferay_login_web_portlet_LoginPortlet_doActionAfterLogin'	=> 'false',
			'_com_liferay_login_web_portlet_LoginPortlet_login'					=> $blogin,
			'_com_liferay_login_web_portlet_LoginPortlet_account'				=> $this->account,
			'_com_liferay_login_web_portlet_LoginPortlet_password'				=> $this->pwd
		];
		try
		{
			$response = $this->request($url, $args, 'POST');
			$mtime = $this->TimeMilliseconds();
			$this->setCookie('LFR_SESSION_STATE_20105', $mtime);
			$this->setCookie('LFR_SESSION_STATE_972826', $mtime); //js -> getUsername
			$this->saveData('LFR_SESSION_STATE_20105', $mtime);
			$this->saveData('LFR_SESSION_STATE_972826', $mtime);
			//echo '<pre>', print_r($response, 1), '</pre>';
			$this->hasSession = true;
			
		}
		catch(Exception $e)
		{
			die($e->getMessage());
		}
		
	}
	public function isLoggedIn($html)
	{
		return stristr($html, 'conectado como') ? true : false;
	}
	public function getSystemId()
	{
		$url = $this->baseUrl . '/o/Dashboard-portlet/dwr/call/plaincall/__System.generateId.dwr';
		$data = [
			'callCount' 		=> 1,
			'c0-scriptName' 	=> '__System',
			'c0-methodName' 	=> 'generateId',
			'c0-id' 			=> 0,
			'batchId' 			=> 0,
			'instanceId' 		=> 0,
			'page' 				=> '/group/guest/inicio',
			'scriptSessionId' 	=> ''
		];
		$dataStr = '';
		foreach($data as $key => $val)
		{
			$dataStr .= sprintf("%s=%s\r\n", $key, $val);
		}
		$headers = [
			'Accept: */*',
			'Host: agentes360.qualitas.com.mx',
			'Origin: https://agentes360.qualitas.com.mx',
			'Content-Type: text/plain',
			'Connection: keep-alive',
			'Sec-Fetch-Dest: empty',
			'Sec-Fetch-Mode: cors',
			'Sec-Fetch-Site: same-origin',
			'Referer: https://agentes360.qualitas.com.mx/group/guest/inicio',
		];
		$response = $this->request($url, $dataStr, 'POST', $headers);
		//echo '<pre>', print_r($response, 1), '</pre>';
		if( !preg_match('/\(.*,.*,"(.*)"\)/', $response->body, $matches) )
			throw new Exception('Unable to get system id');
		$this->systemId = $matches[1];
		$this->pageId	= $this->tokenify($this->TimeMilliseconds()) . '-' . $this->tokenify((int)rand() * 1E16);
		$this->setCookie('DWRSESSIONID', $this->systemId);
		//##pageid -> dwr.engine._pageId = dwr.engine.util.tokenify(new Date().getTime()) + "-" + dwr.engine.util.tokenify(Math.random() * 1E16);
		
	}
	public function getEdosCuenta()
	{
		$cacheFile = storage_path('app') . '/qualitas-edoscuenta.json';
		if( is_file($cacheFile) )
		{
			$mtime 	= filemtime($cacheFile);
			$diff	= time() - $mtime;
			$cacheTimeSecs = 60 * 10; //10 mins
			if( $diff <= $cacheTimeSecs )
				return json_decode(file_get_contents($cacheFile));
		}
		$this->login();
		$this->getSystemId();
		$this->request('https://agentes360.qualitas.com.mx/group/guest/comisiones-y-bonos');
		
		$url = $this->baseUrl . '/o/Comisiones-Bonos-portlet/dwr/call/plaincall/ReportEdosCuentaService.getEdosCuenta.dwr';
		$headers = [
			'Accept: */*',
			//'Accept-Encoding: gzip, deflate, br',
			'Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3',
			'Connection: keep-alive',
			'Content-Type: text/plain',
			'Host: agentes360.qualitas.com.mx',
			'Origin: https://agentes360.qualitas.com.mx',
			'Referer: https://agentes360.qualitas.com.mx/group/guest/comisiones-y-bonos',
			'Sec-Fetch-Dest: empty',
			'Sec-Fetch-Mode: cors',
			'Sec-Fetch-Site: same-origin',
		];
		$data = [
			'callCount' 			=> 1,
			'nextReverseAjaxIndex'	=> 0,
			'c0-scriptName' 		=> 'ReportEdosCuentaService',
			'c0-methodName' 		=> 'getEdosCuenta',
			'c0-id' 				=> 0,
			'c0-param0'				=> 'string:' . $this->login,
			'batchId' 				=> 1,
			'instanceId' 			=> 0,
			'page' 					=> '%2Fgroup%2Fguest%2Fcomisiones-y-bonos',
			'scriptSessionId' 		=> sprintf("%s/%s", $this->systemId, $this->pageId),
		];
		$dataStr = '';
		foreach($data as $key => $val)
		{
			$dataStr .= sprintf("%s=%s\r\n", $key, $val);
		}
		$response = $this->request($url, $dataStr, 'POST', $headers);
		//echo '<pre>', print_r($response, 1), '</pre>';
		$json = $this->getDwrResponse($response->body);
		//echo '<pre>', print_r($json, 1), '</pre>', "\n";
		$json = $this->fixJson($json);
		//##save cache
		file_put_contents($cacheFile, $json);
		//echo '<pre>', print_r($json, 1), '</pre>', "\n";
		$data = json_decode($json);
		//print_r($data);
		return $data;
	}
	protected function TimeMilliseconds() 
	{
		$mt = explode(' ', microtime());
		return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
	}
	protected function tokenify($number)
	{
		$tokenbuf = [];
		$charmap = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*$";
		$remainder = $number;
		while ($remainder > 0)
		{
			$tokenbuf[] = $charmap[$remainder & 0x3F];
			$remainder	= floor($remainder / 64);
		}
		return implode('', $tokenbuf);
	}
	protected function saveData($key, $val)
	{
		if( !is_file($this->dataFile) )
		{
			file_put_contents($this->dataFile, json_encode([$key => $val]));
			return true;
		}
		$obj = json_decode(file_get_contents($this->dataFile));
		if( !$obj || !is_object($obj))
			$obj = (object)[];
		
		$obj->$key = $val;
		file_put_contents($this->dataFile, json_encode($obj));
		return true;
	}
	protected function getData()
	{
		if( !is_file($this->dataFile) )
			return [];
		$obj = json_decode(file_get_contents($this->dataFile));
		if( !$obj || !is_object($obj))
			return [];
		return (array)$obj;
	}
	protected function getDwrResponse($str)
	{
		if( !preg_match('/\("\d+","\d+",(.*)\)/', $str, $matches) )
			return null;
		return $matches[1];
	}
	public function fixJson($json)
	{
		return preg_replace("/([{|,])\s*([a-zA-Z0-9_]+):/", '$1"$2":', $json);
	}
	public function downloadAttachment($obj, $type = 'xlsEdoCuenta')
	{
		set_time_limit(0);
		$this->login();
		$url = 'https://agentes360.qualitas.com.mx/group/guest/comisiones-y-bonos?'.
			'p_p_id=comisionesbonos_WAR_ComisionesBonosportlet'.
			'&p_p_lifecycle=2'.
			'&p_p_state=normal'.
			'&p_p_mode=view'.
			'&p_p_resource_id=verExcelEdoCuenta'.
			'&p_p_cacheability=cacheLevelPage'.
			'&_comisionesbonos_WAR_ComisionesBonosportlet_myaction=' . $type;
		$data = [
			'agent'	=> $this->login,
			'num'	=> $obj->clave,
			'desc'	=> $obj->fecha
		];
		$response = $this->request($url, $data, 'POST');
		//echo '<pre>', print_r($response, 1), '</pre>', "\n";
		$disposition = $response->getHeader('Content-Disposition');
		list(, $fileData) = array_map('trim', explode(';', $disposition));
		list(, $filename) = array_map('trim', explode('=', $fileData));
		$filename = trim($filename, '"');
		$xlsFile = storage_path('logs') . '/' . $filename;
		file_put_contents($xlsFile, $response->body);
		
		return $xlsFile;
	}
	public function downloadExcel($obj)
	{
		return $this->downloadAttachment($obj, 'xlsEdoCuenta');
	}
	public function downloadPdf($obj)
	{
		return $this->downloadAttachment($obj, 'pdfEdoCuenta');
	}
	public function uploadToFtp($filename)
	{
		set_time_limit(0);
		$ftp_server = 'plataformacreatsol.com';
		$ftp_user	= 'docsconsiliaciones@plataformacreatsol.com';
		$ftp_pass	= '+4ilZKCVg3C~';
		
		$ftp = new Ftp($ftp_server);
		if( !$ftp->login($ftp_user, $ftp_pass) )
			throw new Exception('FTP login error');
		
		$dir = 'Q5747';
		try
		{
			$ftp->mkdir($dir);
		}
		catch(Exception $e)
		{
			
		}
		$ftp->chdir($dir);
		$ftp->put(basename($filename), $filename, FTP_BINARY);
		$ftp->close();
		
	}
}