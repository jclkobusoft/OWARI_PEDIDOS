<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	
	
	@if($info_cliente['CAMPLIB14'] == "SI")
		<h2 style="color:red">Hay un nuevo pedido especial QUE SE DEBE COBRAR PRIMERO.</h2>
	@else
		<h2>Hay un nuevo pedido especial.</h2>
	@endif
	<p>Adjuntamos un pedido especial para que le des seguimiento.</p>
	<p>Reciba un cordial saludo.</p>
</body>
</html>