<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	
	
	@if(isset($pedido) && $pedido->clave_proveedor == 'S227')
		<div style="background-color:#c62828; color:#fff; padding:15px; text-align:center; font-size:20px; font-weight:bold; border-radius:4px;">
			SYD - PEDIDO URGENTE (SURTIR EN 20 MIN)
		</div>
		<h2 style="color:#c62828;">Hay un nuevo pedido especial de SYD (proveedor S227).</h2>
		<p>Este pedido debe surtirse con el proveedor cercano <b>Suspension Y Direccion (S227)</b> inmediatamente.</p>
	@elseif($info_cliente['CAMPLIB14'] == "SI")
		<h2 style="color:red">Hay un nuevo pedido especial QUE SE DEBE COBRAR PRIMERO.</h2>
	@else
		<h2>Hay un nuevo pedido especial.</h2>
	@endif
	<p>Adjuntamos un pedido especial para que le des seguimiento.</p>
	<p>Reciba un cordial saludo.</p>
</body>
</html>