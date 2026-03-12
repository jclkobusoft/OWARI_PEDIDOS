<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	 <meta http-equiv="refresh" content="30">
	<title>LIQUIDACIONES</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Karma:wght@300;600&display=swap');
    </style>

	<style type="text/css">
		/* ====== TUS ESTILOS ORIGINALES ====== */
		label{
			font-family: 'Karma', serif;
			font-size: 50px;
			background-color: white;
			color: black;
			line-height: 54px;
		}
		#loading {
		    transition: opacity 1.5s ease-in;
		    opacity: 1;
		    z-index: 2;
		}
		.ocultar-loading {
		    transition: opacity 1.5s ease-out;
		    opacity: 0 !important;
		    z-index: 0 !important;
		}
		.ver-imagen {
		    transition: opacity 1.5s ease-in;
		    opacity: 1 !important;
		    z-index: 3 !important;
		}
		#imagen_dos, #textos, #textos_dos {
		    transition: opacity 1.5s ease-out;
		    opacity: 1;
		    z-index: 1;
		}
		.capa{
		    width:100%;
		    height:100vh;
		    z-index:4;
		    opacity:0.07;
		    position:absolute;
		    top:0;
		    left:0;
		    background-image:url('https://owari.com.mx/fotos/logo.png');
		}

		/* ====== ANIMACIONES DE ENTRADA ====== */

		/* Imagen: arranca invisible para animar al cargar */
		#imagen{
			opacity: 1;              /* anula la opacidad 1 original */
			will-change: transform, opacity, filter;
		}
		

		/* Labels: fade + slide up con escalonamiento */
		#textos label{
			opacity: 0;
			transform: translateY(8px);
			will-change: transform, opacity, filter;
			animation-fill-mode: forwards;
		}
		#textos label.show{
			animation-name: label-in;
			animation-duration: 1000ms;
			animation-timing-function: cubic-bezier(.2,.7,.3,1);
		}
		@keyframes label-in{
			from{ opacity:0; transform: translateY(8px); filter: blur(2px); }
			to{ opacity:1; transform: translateY(0); filter: blur(0); }
		}

		/* Accesibilidad: respeta usuarios con reduced motion */
		@media (prefers-reduced-motion: reduce){
			#imagen, #textos label{
				animation: none !important;
				opacity: 1 !important;
				transform: none !important;
				filter: none !important;
			}
		}
	</style>
</head>
<body>

    <div class="capa"></div>
	<img src="https://pedidos.owari.com.mx/images/liquidacion.png" style="position:absolute;right:0;top:0;width:20%;">
     <div style="width: 49%; height:100vh ; display: inline-block;position: relative;">
		<?php
				$codigo_nikko = $producto->codigo_nikko;
				if(str_contains($producto->codigo_nikko, '/'))
				$codigo_nikko = str_replace("/", "_", $producto->codigo_nikko);
			
				if(str_contains($producto->codigo_nikko, '#'))
				$codigo_nikko = str_replace("#", "+", $producto->codigo_nikko);

				$directory = '/var/www/vhosts/owari.com.mx/laravel/cms/storage/app/public/productos/'.$codigo_nikko;
			
				if(is_dir($directory))
					$files = \Storage::disk('cms')->allFiles('productos/'.$codigo_nikko."/");
				else
					$files = [];
				arsort($files);
          ?>
		@if(count($files) > 0)
			<img id="imagen"  src="{{ "https://owari.com.mx/storage/productos/".$codigo_nikko."/".basename($files[array_key_first($files)],PHP_EOL) }}" alt="Product Image" style="position:absolute;top:50%; left:50%; transform: translate(-50%,-50%);width:75%;">
		@else
			<img  id="imagen" src="{{ 'https://owari.com.mx/images/mascota.png' }}" alt="Product Image" style="position:absolute;top:50%; left:50%; transform: translate(-50%,-50%);width:75%;">
		@endif
	</div>

	<div style="width: 49%; height:100vh ; display: inline-block;position: relative;">
		<div  style="position:absolute;left:0; top:50%; width:80%;transform:translate(0,-50%)">
			<label style="font-weight:300;" id="clave">{{ $producto->codigo_nikko }}</label><br>
			<label style="font-weight:600;" id="descripcion">{{ $producto->descripcion_1 }}</label><br><br>
            <label style="font-weight:600;color:red;">VISITA TU TIENDA EN LINEA PARA CONOCER MAS</label><br>
		</div>
	</div>
	


	

</body>
</html>
