<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## API

#### Obtener el listado de los registros

POST /api/qualitas

BODY

<code>
{
	"credentials": "fdfdsfdsdf234f/="
}
</code>


credentials -> son las credenciales de acceso al portal qualitas con el siguiente formato y codificado en base64

	[login]:[account]:[password]

#### Descarga de excel de un registro
	
POST /api/qualitas/download

BODY

<code>
{
	"credentials": "xxxx",
	"clave": "s2443",
	"fecha": "01/07/2021",
	"format": "base64"
}
</code>


clave	-> La clave del registro recuperado

fecha	-> La fecha del registro recuperado

format -> base64 | bin


#### Subir archivo a FTP


POST /api/qualitas/upload-ftp?data=[clave],[fecha]

BODY

<code>
{
	"credentials": "xxxx",
}
</code>


clave	-> La clave del registro recuperado

fecha	-> La fecha del registro recuperado
