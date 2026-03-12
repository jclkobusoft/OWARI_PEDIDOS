<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Factura</title>
  <style>
    /* Márgenes de página: deja espacio para header y footer */
    @page { margin: 230px 30px 40px 30px; }

    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color:#111; margin:0; }

    /* Header y footer fijos que se repiten en todas las páginas */
    header {
      position: fixed;
      top: -215px;       /* entra en el margen superior */
      left: 0; right: 0;
      height: 200px;
      padding: 8px 0;
    }
    footer {
      position: fixed;
      bottom: -80px;    /* entra en el margen inferior */
      left: 0; right: 0;
      height: 50px;
      padding: 6px 0;
      font-size: 10px;
      color:#555;
    }

    /* Utilidades */
    .row { display: flex; justify-content: space-between; align-items: center; }
    .left { text-align: left; }
    .center { text-align: center; }
    .right { text-align: right; }
    .muted { color:#666; }

    .tabla { display: table; width: 100%; table-layout: fixed; }
    .celda { display: table-cell; vertical-align: top; }
    .c65 { width: 65%; }
    .c35 { width: 35%; }
    .c20 { width: 20%; }
    .c30 { width: 30%; }
    .c40 { width: 40%; }
    .c15 { width: 15%; }


    .t13{ font-size:13px;}
    .t12{ font-size:12px;}

    .bold{font-weight:bold;} 
    .l15{ font-size:15px}
    .l18{ font-size:18px}
    .l7{ font-size:7px}
    .celdav { display: table-cell; vertical-align: middle; }
    

    /* Tabla multipágina */
    table { width:100%; border-collapse: collapse; font-size:9px; padding-bottom: 15px; }
    thead th { border-top:2px solid #000;border-bottom:2px solid #000; padding:2px; vertical-align: top; }
    thead { display: table-header-group; }   /* repite encabezados en cada página */
    tfoot { display: table-row-group; }      /* evita repetir pie de tabla */
    tr { page-break-inside: avoid; }         /* no partir filas */
    tr td {padding:2px; } 
    .nowrap { white-space: nowrap; }
    tfoot tr:last-child { border-top:2px solid #000;border-bottom:2px solid #000; padding:2px; vertical-align: top; }


    table.zebra { width:100%; border-collapse: collapse; }
  table.zebra tbody tr:nth-child(odd)  { background:#f2f2f2; } /* gris */
  table.zebra tbody tr:nth-child(even) { background:#fff; }    /* blanco */
  table.zebra th, table.zebra td { padding:6px 8px; }
  .p2{
    padding:2px;
  }
  .pl5{
    padding-left:5px;
  }

  .totales td{
    padding-right:3px;
    font-size:12px;
    font-weight:bold;
  }
  .totales tr:first-child{ border-top: 2px solid black; }
  .totales tr:last-child{ border-bottom: 2px solid black;border-top: 2px solid black; }
  .long { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
  .split {
  page-break-inside: auto;
  break-inside: auto;          /* alias moderno */
  overflow: visible;
}

  </style>
</head>
<body>
  {{-- HEADER: se repite en cada página --}}
  <header>
    <div class="row">
      <div class="tabla">
        <div class="celda c65">
          <div class="tabla">
            <div class="celda c30">
              <img src="https://pedidos.owari.com.mx/images/logo_owari.png" style="width:100%;">
            </div>
            <div class="celda">
              <b>R.F.C.:</b> LOCI780206BK1<br>
              <b>REGIMEN FISCAL:</b>Personas Físicas con Actividades Empresariales<br>
              <b>DOMICILIO FISCAL:</b>Puerto Madero MZ 64 LT 10, Col. Jardines de Casa Nueva, CP: 55430, Ecatepec de Morelos, Edo. de México, México.
            </div>  
          </div>
        </div>
        <div class="celda">
          <div class="tabla">
            <div class="celda c40">
              <img src="https://pedidos.owari.com.mx/images/miyali.png" style="width:100%;">
            </div>
            <div class="celda t13">
              <div class="tabla">
                <div class="celdav right c30"><img src="https://pedidos.owari.com.mx/images/phone.jpg" style="width: 25px"></div>
                <div class="celdav right"><b>55-2233-0960</b></div>
              </div>
              <div class="tabla">
                <div class="celdav right c30"><img src="https://owari.com.mx/wa.png" style="width: 25px"></div>
                <div class="celdav right"><b>55-2948-2188</b></div>
              </div>
            </div>  
          </div>
        </div>
      </div>
      <div class="tabla">
         <div class="celda c65">
          <label class="bold l18">({{ $cliente['clave'] }})</label> <b class="l15">{{ $cliente['nombre'] }}</b><br>
          <b>Razón social:</b> {{ $cfdi['Receptor']['@attributes']['Nombre'] }}<br>
          <b>RFC:</b> {{ $cfdi['Receptor']['@attributes']['Rfc'] }}<br>
          <b>DOMICILIO FISCAL:</b> {{ $cfdi['Receptor']['@attributes']['DomicilioFiscalReceptor'] }}<br>
          <b>REGIMEN FISCAL:</b> ({{ $cfdi['Receptor']['@attributes']['RegimenFiscalReceptor'] }}) {{ $regimen[$cfdi['Receptor']['@attributes']['RegimenFiscalReceptor']] }}<br>
          <b>FORMA DE PAGO:</b> ({{ $cfdi['@attributes']['FormaPago'] }}) {{ $forma_pago[$cfdi['@attributes']['FormaPago']] }}

        </div>
        <div class="celda">
          <label class="bold l18">FACTURA {{ $cfdi['@attributes']['Serie'] }}-{{ $cfdi['@attributes']['Folio'] }}</label><br>
           <b>Lugar de expedición:</b> 55430<br>
          Comprobante fiscal digital: <b>(I) Ingreso</b><br>
          <b>Fecha:</b> {{ $cfdi['@attributes']['Fecha'] }}<br>
          Metodo de pago: ({{ $cfdi['@attributes']['MetodoPago'] }}) {{ $metodo_pago[$cfdi['@attributes']['MetodoPago']] }}<br>
          Uso CFDI: ({{ $cfdi['Receptor']['@attributes']['UsoCFDI'] }}) {{ $uso_cfdi[$cfdi['Receptor']['@attributes']['UsoCFDI']] }}
        </div>
      </div>
    </div>
  </header>
  {{-- FOOTER: se repite en cada página (paginación via page_script) --}}
  <footer>
    <div class="row">
      <div class="left muted">
      </div>
      <div class="right" id="page-counter">
        {{-- El número de página se agrega con page_script abajo --}}
      </div>
    </div>
  </footer>

  {{-- CONTENIDO --}}
  <main>
    <table class="zebra">
      <thead>
        <tr>
          <th class="nowrap">PED</th>
          <th class="right nowrap">CANT</th>
          <th class="nowrap">CLAVE</th>
          <th class="nowrap">UND</th>
          <th class="left">DESCRIPCIÓN</th>
          <th class="right nowrap">DESC</th>
          <th class="right nowrap">PRECIO</th>
          <th class="right nowrap">IMPORTE</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $piezas = 0;
        ?>
        @if(isset($cfdi['Conceptos']['Concepto']['@attributes']))
          <?php
              $partida = $cfdi['Conceptos']['Concepto']['@attributes'];
          ?>
          <tr>
            <td class="nowrap">{{ $partidas_sae[0]['PEDIDO'] }}</td>  
            <td class="right nowrap">{{ number_format($partida['Cantidad'],0,'.',',') }}</td>
            <td class="nowrap">{{ !isset($partida['NoIdentificacion']) ? $partidas_sae[0]['CVE_ART'] : $partida['NoIdentificacion'] }}</td>
            <td class="nowrap">{{ $partida['ClaveUnidad'] }}</td>
            <td>{{ mb_strimwidth($partida['Descripcion'], 0, 50, '…', 'UTF-8') }}</td>
            <td class="right nowrap">${{ number_format(0.00, 2,'.',',') }}</td>
            <td class="right nowrap">${{ number_format($partida['ValorUnitario'], 2,'.',',') }}</td>
            <td class="right nowrap">${{ number_format($partida['Importe'], 2,'.',',') }}</td>
          </tr>
          <?php
              $piezas+=$partida['Cantidad'];
          ?>
        @else
          @foreach($cfdi['Conceptos']['Concepto'] as $key => $value)
            <?php
              $partida = $value['@attributes'];
            ?>
            <tr>
              <td class="nowrap">{{ $partidas_sae[$key]['PEDIDO'] }}</td>  
              <td class="right nowrap">{{ number_format($partida['Cantidad'],0,'.',',') }}</td>
              <td class="nowrap">{{ !isset($partida['NoIdentificacion']) ? $partidas_sae[$key]['CVE_ART'] : $partida['NoIdentificacion'] }}</td>
              <td class="nowrap">{{ $partida['ClaveUnidad'] }}</td>
              <td>{{ mb_strimwidth($partida['Descripcion'], 0, 50, '…', 'UTF-8') }}</td>
              <td class="right nowrap">${{ number_format(0.00, 2,'.',',') }}</td>
              <td class="right nowrap">${{ number_format($partida['ValorUnitario'], 2,'.',',') }}</td>
              <td class="right nowrap">${{ number_format($partida['Importe'], 2,'.',',') }}</td>
            </tr>
            <?php
              $piezas+=$partida['Cantidad'];
            ?>
          @endforeach
        @endif
      </tbody>
    </table>
    <div class="row split">
      <div class="tabla">
        <div class="celda c65">
          <div class="tabla">
            <div class="celda">
              <label class="t12">TOTAL DE PARTIDAS = <b>{{ count($cfdi['Conceptos']['Concepto']) }}</b>   TOTAL DE PIEZAS = <b>{{ $piezas }}</b></label>
              <div class="tabla" style="margin-top: 5px;">
                <div class="celda c30">
                  <img src="data:image/png;base64,{{ $qr }}" alt="QR" width="90" height="90">
                </div>
                <div class="celda">
                  <div class="tabla">
                    <div class="celdav left c20"><img src="https://pedidos.owari.com.mx/images/oxxo.png" style="width: 100%"></div>
                    <div class="celdav left pl5">OXXO:<br><b>4152 3142 7824 2873</b></div>
                  </div>
                  <div class="tabla">
                    <div class="celdav left c20"><img src="https://pedidos.owari.com.mx/images/bbva.png" style="width: 100%"></div>
                    <div class="celdav left pl5">BBVA:<br><b>CUENTA: 0463 7053 90</b><br><b>CLABE: 012180 0046 3705 3907</b></div>
                  </div>
                </div>
              </div>
              
            </div>  
          </div>
        </div>
        <div class="celda">
          <div class="tabla">
            <div class="celda">
              <table class="totales">
                <tr>
                  <td>SUBTOTAL</td>
                  <td class="right t13">${{ number_format($cfdi['@attributes']['SubTotal'],2,'.',',') }}</td>
                </tr>
                <tr>
                  <td>DESCUENTO</td>
                  <td class="right t13">$0.00</td>
                </tr>
                <tr>
                  <td>IVA</td>
                  <td class="right t13">${{ number_format($cfdi['@attributes']['Total']-$cfdi['@attributes']['SubTotal'],2,'.',',') }}</td>
                </tr>
                <tr>
                  <td>TOTAL</td>
                  <td class="right t13">${{ number_format($cfdi['@attributes']['Total'],2,'.',',') }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="l7 split long">
          <hr style="height:1px; background:black; ">
          "Este documento es una representación impresa de un CFDI"<br>
          <b>Folio fiscal:</b> {{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'] }}<br>
          <b>Fecha y hora de certificación:</b> {{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['FechaTimbrado'] }}<br>
          <b>Sello digital del CFDI:</b><br>
          {{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['SelloCFD'] }}<br>
          <b>Número de serie del Certificado de Sello Digital del SAT:</b> {{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['NoCertificadoSAT'] }}<br>
          <b>Cadena original del complemento de certificación digital del SAT:</b><br>
          ||{{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['Version'] }}|{{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['UUID'] }}|{{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['FechaTimbrado'] }}|{{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['RfcProvCertif'] }}|{{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['SelloCFD'] }}|{{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['NoCertificadoSAT'] }}||<br>
          <b>Sello digital del SAT:</b><br>
          {{ $cfdi['Complemento']['TimbreFiscalDigital']['@attributes']['SelloSAT'] }}
      </div>
    </div>
  </main>

  {{-- Paginación y texto dinámico en cada página --}}
  <script type="text/php">
    if (isset($pdf)) {
      $pdf->page_script('
        $font = $fontMetrics->get_font("DejaVu Sans","normal");
        $size = 7;
        $w = $pdf->get_width();
        $h = $pdf->get_height();

        // Texto: "Página X de Y" alineado a la derecha del footer
        $text = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
        $text_w = $fontMetrics->get_text_width($text, $font, $size);
        $pdf->text($w - 30 - $text_w, $h - 30, $text, $font, $size, [0,0,0]);
      ');
    }
  </script>
</body>
</html>
