@extends('layout')
@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col">
			<h1>Listado de Polizas</h1>
			<table class="table table-sm table-bordered table-striped">
			<thead>
			<tr>
				<td>Nro.</td>
				<th>Clave</th>
				<th>Descripcion</th>
				<th>Fecha</th>
				<th>Accion</th>
			</tr>
			</thead>
			<tbody>
			@foreach($polizas as $i => $p)
			<tr>
				<td>{{ $i + 1 }}</td>
				<td>{{ $p->clave }}</td>
				<td>{{ $p->descripcion }}</td>
				<td>{{ $p->fecha }}</td>
				<td>
					<a href="{{ route('download') }}?data={{ $p->clave }},{{ $p->fecha }}" class="btn btn-primary btn-sm" target="_blank">Descargar</a>
					<a href="{{ route('upload') }}?data={{ $p->clave }},{{ $p->fecha }}" class="btn btn-success btn-sm" >Subir FTP</a>
				</td>
			</tr>
			@endforeach
			</tbody>
			</table>
		</div>
	</div>
</div>
@endsection