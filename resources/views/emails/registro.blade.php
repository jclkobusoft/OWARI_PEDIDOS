<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<p>Un prospecto de cliente online a sido registrado.</p><br><br>
	<ul>
		<li><b>Nombre:</b> {{ $registrado->nombre }}</li>
		<li><b>E-mail:</b> <a href="mailto:{{$registrado->email}}">{{ $registrado->email }}</a></li>
		<li><b>Telefono:</b> <a href="https://api.whatsapp.com/send?phone=52{{ trim(str_replace(" ", "", $registrado->telefono)) }}&text=Hola, recibimos tu registro para ser cliente online">{{ $registrado->telefono }}</a></li>
		<li><b>Clave de cliente:</b> {{ ($registrado->cliente != "" || $registrado->cliente !== null) ? $registrado->cliente : "Sin clave de cliente" }}</li>
	</ul>
</body>
</html>