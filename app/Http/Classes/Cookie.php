<?php
namespace App\Http\Classes;

class Cookie
{
	public	$key;
	public	$value;
	public	$domain;
	public	$expires;
	public	$path;
	public	$secure 	= false;
	public	$httponly 	= false;
	
	public function __construct($cookie = null)
	{
		if( $cookie )
			$this->parse($cookie);
	}
	public function parse($cookie)
	{
		$parts = explode(';', $cookie);
		list($this->key, $this->value) = array_map('trim', explode('=', $parts[0]));
		foreach($parts as $i => $part)
		{
			//skyp first part (key=value)
			if( $i == 0 ) continue;
			$vals 	= array_map('trim', explode('=', $part));
			$key 	= $vals[0];
			$value 	= isset($vals[1]) ? $vals[1] : null;
			
			if( property_exists($this, strtolower($key)) )
				$this->$key = $value;
		}
	}
	public function buildString()
	{
		$str = sprintf("%s;", $this->getKeyValue());
		foreach(get_object_vars($this) as $prop)
		{
			if( $prop == 'key' || $prop == 'value' ) continue;
			if( $this->$prop === null )
				$str .= $prop . ';';
			else
				$str .= sprintf("%s=%s;", $prop, $this->$prop);
		}
		
		return rtrim($str, ';');
	}
	public function getKeyValue()
	{
		//if( $this->value === null )
		//	return $this->key . ';';
		return sprintf("%s=%s", $this->key, $this->value);
	}
	public function __toString()
	{
		return $this->buildString();
	}
}