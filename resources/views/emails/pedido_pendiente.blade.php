<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<h2>Hay un cliente nuevo que ha generado un pedido</h2>
	<h4>Cliente: {{ $registrado->nombre }}</h4>
	<h4>Telefono: {{ $registrado->telefono }}</h4>
	<h4>Email: {{ $registrado->email }}</h4>
	<p>Adjuntamos su pedido para que le des seguimiento.</p>
	<p>Reciba un cordial saludo.</p>
</body>
</html>