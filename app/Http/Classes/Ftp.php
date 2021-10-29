<?php
namespace App\Http\Classes;

class Ftp
{
	protected	$conn;
	
	public function __construct($ip = null)
	{
		if( $ip )
			$this->open($ip);
	}
	public function open($ip)
	{
		$this->conn = ftp_connect($ip);
	}
	public function close()
	{
		if( !$this->conn )
			return true;
		ftp_close($this->conn);
	}
	public function __call($func, array $a)
	{
		if( /*strstr($func,'ftp_') !== false &&*/ function_exists('ftp_' . $func) )
		{
			array_unshift($a, $this->conn);
			//print 'ftp_'.$func;
			//print_r($a);
			return call_user_func_array('ftp_' . $func, $a);
		}
		// replace with your own error handler.
		die("$func is not a valid FTP function");
	}
}