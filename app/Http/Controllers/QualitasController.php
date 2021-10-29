<?php
namespace App\Http\Controllers;

use App\Http\Classes\Qualitas;
use Illuminate\Http\Request;

class QualitasController extends Controller
{
	public function default()
	{
		$qualitas = $this->getInstance();
		//$qualitas->getSystemId();
		$data = $qualitas->getEdosCuenta();
		$polizas = $data->responseData->edosCuenta;
		//$filename = $qualitas->downloadExcel($data->responseData->edosCuenta[0]);
		//$qualitas->uploadToFtp($filename);
		return view('polizas', get_defined_vars());
	}
	/**
	 * 
	 * @return \App\Http\Classes\Qualitas
	 */
	protected function getInstance()
	{
		static $obj;
		if( $obj )
			return $obj;
		$obj = new Qualitas();
		$obj->setCrendentials('78595', 'MAESTRA', 'PRO4508');
		
		return $obj;
	}
	protected function downloadFile($clave, $fecha)
	{
		$qualitas = $this->getInstance();
		$obj = (object)[
			'clave'	=> $clave,
			'fecha'	=> $fecha
		];
		$filename = $qualitas->downloadExcel($obj);
		return $filename;
	}
	public function download(Request $req)
	{
		list($clave, $fecha) = explode(',', $req->get('data'));
		$filename = $this->downloadFile($clave, $fecha);
		header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition: attachment; filename=". basename($filename));  //File name extension was wrong
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		readfile($filename);
		die();
	}
	public function upload(Request $req)
	{
		list($clave, $fecha) = explode(',', $req->get('data'));
		$filename = $this->downloadFile($clave, $fecha);
		$qualitas = $this->getInstance();
		$qualitas->uploadToFtp($filename);
		return redirect(route('qualitas'));
	}
}