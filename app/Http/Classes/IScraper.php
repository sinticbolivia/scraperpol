<?php
namespace App\Http\Classes;

interface IScraper
{
	public function request(string $url, $data = null, $method = 'GET', $headers = null);
	
}