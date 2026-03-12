<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
    @page {
        margin: 2px;
        font-family: Courier;
        font-size:12px;
        text-transform:uppercase;
    }

    table {
        border-collapse: collapse;
    }

    #barras {
        margin: 0;
    }
    </style>
</head>

<body>
    <?php
      $generator = new Picqer\Barcode\BarcodeGeneratorJPG();
    ?>
    <?php
    	$total = count($productos);
    ?>
    @foreach($productos as $x => $producto)
	    @for($i=0;$i<$producto['CANTIDAD'];$i++)
		    <table width="100%">
		        <tr>
		            <td style="border:2px black solid;max-height: 25px; font-size: 13px;" width="70%" align="center">
		               	<b>{{ $producto['clave'] }}</b>
		            </td>
		            <td align="center">
		            	<label style="font-size: 10px">{{ date('d.m.y') }}</label><br>
		            	<label style="font-size: 14px;font-weight: bold; text-transform:uppercase;">{{ $quien }}</label>
		            </td>
		        </tr>
		        <tr>
		            <td style="max-height: 13px;" colspan="2" align="left">
		                <label style="white-space: nowrap;">{{ $producto['descripcion'] }}</label>
		            </td>
		        </tr>
		        <tr>
		            <td style="max-height: 32px;" colspan="2" align="center">
		                <img id="barras" src="data:image/png;base64,{{  base64_encode($generator->getBarcode(($producto['codigo_barras']  != null ? $producto['codigo_barras'] : $producto['clave']), $generator::TYPE_CODE_128)) }}" style="width:90%;height: 32px;"><br>
		            </td>
		        </tr>

		    </table>
		    @if($i+1 < $producto['CANTIDAD']) 
		    <div style="page-break-before: always;">
		    </div>
		    @endif
	    @endfor
	    @if($x+1 < $total) 
		    <div style="page-break-before: always;">
		    </div>
		@endif
	@endforeach
</body>

</html>