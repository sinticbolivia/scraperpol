<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\QualitasController;
use Illuminate\Http\Request;
use Exception;

class ApiQualitasController extends QualitasController
{
	protected function checkCredentials(object $data)
	{
		if( !isset($data->credentials) )
			return false;
		$str = base64_decode($data->credentials);
		if( empty($str) )
			return false;
		list($login, $account, $pwd) = explode(':', $str);
		if( !$login || !$account || !$pwd )
		{
			unset($data->credentials);
			throw new Exception('Credenciales incompletas');
		}
		
		$q = $this->getInstance();
		$q->setCrendentials($login, $account, $pwd);
		unset($data->credentials);
	}
	public function list(Request $request)
	{
		try 
		{
			$data = $request->json()->all();
			$this->checkCredentials($data);
			
			//if()
			$qualitas 	= $this->getInstance();
			$data = $qualitas->getEdosCuenta();
			return response()->json($data->responseData->edosCuenta);
		} 
		catch (Exception $e) 
		{
			response()->json(['error' => $e->getMessage()], 500);
		}
	}
	public function download(Request $req)
	{
		try
		{
			$data = (object)$req->json()->all();
			$this->checkCredentials($data);
			if( empty($data->clave) )
				throw new Exception('Clave de registro invalida');
			if( empty($data->fecha) )
				throw new Exception('Fecha de registro invalida');
			
			$qualitas = $this->getInstance();
			$filename = $qualitas->downloadExcel($data);
			if( isset($data->format) && $data->format == 'bin' )
				return response()->download($filename);
			return response()->json(['filename' => basename($filename), 'buffer' => base64_encode(file_get_contents($filename))]);
		}
		catch(Exception $e)
		{
			response()->json(['error' => $e->getMessage()], 500);
		}
	}
}