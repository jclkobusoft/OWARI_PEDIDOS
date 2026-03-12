<html>
    <head>
        <style>
            /** Define the margins of your page **/
            @page {
                margin: 120px 25px;
                font-family: courier;
            }

            header {
                position: fixed;
                top: -90px;
                left: 0px;
                right: 0px;
                height: 80px;

                /** Extra personal styles **/
                color: black;
                text-align: center;
            }

            footer {
                position: fixed; 
                bottom: -60px; 
                left: 0px; 
                right: 0px;
                height: 50px; 

                /** Extra personal styles **/
                color: black;
                text-align: center;
                line-height: 35px;
            }
            p{
                line-height:15px;
                margin:0;
            }
            br{
                height:5px;
            }
            td{
                vertical-align: top;
            }
            main table tr:nth-child(odd) td{
              background-color: #eee;
            }
            main table tr:nth-child(even) td{
              background-color: white;
            }
        </style>
    </head>
    <body>
        <!-- Define header and footer blocks before your content -->
        <header>
             <table width="100%">
                <tr>
                    <td width="20%">
                        <img src="https://owari.com.mx/upload/gral/general-Owari_007.png" width="100">
                    </td>
                    <td width="40%">
                        <p>
                            <b>Cliente:&nbsp;&nbsp;&nbsp; </b>
                            <br>{{ $pedido->cliente }}
                        </p>

                    </td>
                    <td width="40%">
                        <p>
                            <b style="font-size:15px;">PEDIDO ESPECIAL:</b>&nbsp;&nbsp;{{ $pedido->id }}
                            <br>
                            <b>Capturo:</b><br>{{ $pedido->creador->name }}<br>
                            <b>Fecha captura:</b><br>{{ \Carbon::createFromFormat('Y-m-d H:i:s', $pedido->created_at)->format('d/m/Y h:i A') }}
                        </p>
                    </td>
                </tr>
            </table>
        </header>

        <footer>
            <table width="100%">
                <tr>
                    <td width="55%">
                        <p>Total de partidas: <b>{{ count($pedido->partidas) }}</b></p>
                    </td>
                    <td width="15%" align="right">

                    </td>
                    <td width="15%" align="right">

                    </td>
                    <td width="15%" align="right">
                        <p>
                            <b style="font-size:20px;">TOTAL:<br><br>$&nbsp;&nbsp;{{ number_format($pedido->gran_total,2,'.',',') }}</b>
                        </p>
                    </td>
                </tr>
            </table>
        </footer>

        <!-- Wrap the content of your PDF inside a main tag -->
        <main>
            <table width="100%">
                <thead>
                    <tr>
                        <th class="pequena" align="center">Cantidad</th>
                        <th class="pequena" align="left">Clave</th>
                        <th class="pequena" align="left">Descripcion</th>
                        <th class="pequena" align="right">P.U.</th>
                        <th class="pequena" align="right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->partidas as $partida)
                    <tr>
                        <td class="pequena" align="center">{{ number_format($partida->cantidad,0,',','.') }}</td>
                        <td class="pequena" align="left">{{ $partida->clave}}</td>
                        <?php

                            $descripcion = \DB::connection('mysql')->select("SELECT descripcion_1 FROM productos_busqueda WHERE codigo_nikko = '".$partida->clave."' LIMIT 1");
                        ?>
                        <td class="pequena" align="left">{{ substr($descripcion[0]->descripcion_1,0,30) }}</td>
                        <td class="pequena" align="right">${{ number_format($partida->precio_unitario,2,'.',',') }}</td>
                        <td class="pequena" align="right">${{ number_format($partida->gran_total,2,'.',',') }}</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </main>
    </body>
</html>