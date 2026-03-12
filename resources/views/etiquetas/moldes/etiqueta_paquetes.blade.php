<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
    @page {
        margin: 2px;
        font-family: Courier;
        font-size:30px;
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

    	$contador=0;

    ?>
    @foreach($etiquetas as $key => $value)
	    	
    	@for($i=0;$i<$value;$i++)
	    	 <?php
		    	$contador++;
		    ?>
		    <table width="100%" style=" table-layout: fixed;">
		        <tr style="height: 60px; overflow: hidden">
		        	<td style="font-size: 45px;font-weight: bold;height: 60px;">{{$cliente}}</td>
		        	<td style="text-align: right;font-weight: bold;height: 60px;
		        	@if(strlen($nombre_cliente) > 30) font-size: 18px;
		        	@elseif(strlen($nombre_cliente) > 25 && strlen($nombre_cliente) <= 30) font-size: 20px;
		        	@elseif(strlen($nombre_cliente) > 20 && strlen($nombre_cliente) <= 25) font-size: 22px;
		        	@else 
		        		font-size:25px;
		        	@endif
		        	">{{$nombre_cliente}}</td>
		        </tr>
		        <tr>
		        	<td>Pedido:</td>
		        	<td style="font-weight: bold;">{{ $pedido }}</td>
		        </tr>
		        <tr>
		        	<td style="font-size: 15px;font-weight: bold;">
		        		<br>
		        		@if($etiquetas['caja'] > 0 )
		        			CAJAS: {{ $etiquetas['caja'] }}<br>
		        		@endif
		        		@if($etiquetas['atado'] > 0 )
		        			ATADOS: {{ $etiquetas['atado'] }}<br>
		        		@endif
		        		@if($etiquetas['bolsa'] > 0 )
		        			BOLSAS: {{ $etiquetas['bolsa'] }}<br>
		        		@endif
		        		<br><label style="font-size: 20px;">Empaco:<b>{{ $empaca }}</b></label>
		        	</td>
		        	<td style="font-weight: bold;text-align: right;">
		        		{{ strtoupper($key)}}
		        		<br>{{ $contador }}/{{ $total_etiquetas }}
		        	</td>
		        </tr>
		        <tr>
		        	<td></td>
		        	<td style="font-size: 12px;font-weight: bold;text-align: right;">{{ date("d/m/Y h:i A") }}</td>
		        </tr>

		    </table>
		   
		    @if($contador < $total_etiquetas) 
			    <div style="page-break-before: always;">
			    </div>
		    @endif
	    @endfor

    @endforeach
</body>

</html>