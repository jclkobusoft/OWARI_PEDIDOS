@extends('layouts.app') 
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <form id="forma">
               <div class="mb-3 row">
                    <label for="cliente" class="col-sm-2 col-form-label">Cliente:</label>
                    <div class="col-sm-10 pt-2">
                        <select class="form-select" id="cliente" name="cliente">
                            <option value="-1">Selecciona o busca un cliente</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row" id="ver_pedido" style="display:none;">
                    <label for="cliente" class="col-sm-2 col-form-label">Pedido:</label>
                    <div class="col-sm-10 pt-2">
                        <select class="form-select" id="pedido" name="pedido">
                            <option value="-1">Selecciona o busca un pedido</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row" id="ver_empacador" style="display:none;">
                    <label for="cliente" class="col-sm-2 col-form-label">Empaca:</label>
                    <div class="col-sm-10 pt-2">
                        <select class="form-select" id="empaca" name="empaca">
                            @foreach($empacadores as $empacador)
                              <option value="{{  $empacador->iniciales }}">{{ $empacador->iniciales }} -- {{ $empacador->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row" id="ver_elementos" style="display:none;">
                    <label for="cliente" class="col-sm-2 col-form-label">Cajas:</label>
                    <div class="col-sm-10 pt-2">
                        <input type="number" class="form-control elementos" id="cajas" name="cajas" >
                    </div>
                    <label for="cliente" class="col-sm-2 col-form-label">Atados:</label>
                    <div class="col-sm-10 pt-2">
                        <input type="number" class="form-control elementos" id="atados" name="atados" >
                    </div>
                    <label for="cliente" class="col-sm-2 col-form-label">Bolsas:</label>
                    <div class="col-sm-10 pt-2">
                        <input type="number" class="form-control elementos" id="bolsas" name="bolsas" >
                    </div>
                  <div class="row">
                        <div class="col-md-12 d-flex justify-content-end">
                               <button type="button" class="btn btn-primary mt-3" id="crear">Crear etiquetas</button>
                        </div>
                  </div>
                </div>
               

                <div id="datos_cliente" class="mb-3"></div>
                <div id="datos_pedido" class="mb-3"></div>
            </form>
        </div>
        <div class="col-md-8" id="frame">
            
        </div>
    </div>
</div>
<script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
<script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.8.0/jszip.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.8.0/xlsx.js"></script>
<script src=" https://cdnjs.cloudflare.com/ajax/libs/xls/0.7.4-a/xls.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="/assets/excel/excel-builder.dist.js"></script>
<script type="text/javascript" src="/assets/downloadify/js/swfobject.js"></script>
<script type="text/javascript" src="/assets/downloadify/js/downloadify.min.js"></script>
<link rel="stylesheet" href="/assets/chosen/chosen.css" />
<link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="/assets/slick/slick-theme.css"/>
<script>
       var clientes, clave_cliente, clave_pedido;
  url = "https://sistemasowari.com:8443/catalowari/api/clientes_caja"
  $.get(
      url,
      function(data) {
          var obj = jQuery.parseJSON(data);
          clientes = obj;
          $("#cliente").html('<option value="-1">Selecciona o busca un cliente</option>')
          $.each(obj, function(i, val) {
              $("#cliente").append(
                  '<option value="' +
                  i +
                  '">' +
                  val.clave +
                  " -- " +
                  (val.nombre_comercial != null ? val.nombre_comercial:val.nombre) +
                  "</option>"
              );
          });
          $("#cliente").val('-1')
              .chosen({ no_results_text: "Oops, no hay resultados!" })
              .change(function() {
                  seleccion = false;
                  var indice = $(this).val();
                  clave_cliente = clientes[indice].clave;
                  $("#datos_cliente").html(
                      `
                        <div>
                            <label>Clave:</label>
                            <small>` +
                      clientes[indice].clave +
                      `</small>
                            </div>
                            <div>
                                <label>Nombre:</label>
                                <small>` +
                            (clientes[indice].nombre_comercial != null ? clientes[indice].nombre_comercial : clientes[indice].nombre) +
                                                  `</small>
                            </div>
                            `
                  );
                  buscarPedidos(clave_cliente);
              }).trigger('chosen:updated').trigger('chosen:activate');
      }
  );

  function buscarPedidos(cliente) {
    $.get('https://sistemasowari.com:8443/catalowari/api/pedidos_clientes_horas', {clave: cliente} ,function(data) {
        /*optional stuff to do after success */
        data = jQuery.parseJSON(data);
        console.log(data);
        $("#pedido").html('<option value="-1">Selecciona o busca un pedido</option>')
        $.each(data.pedidos, function(i, val) {
              $("#pedido").append(
                  '<option value="E' +
                  val.EMPRESA+`--`+ val.CVE_DOC +
                  '">E' +
                  val.EMPRESA+`-- `+ val.CVE_DOC +
                  "</option>"
              );
          });
         $("#ver_pedido").show();
         $("#pedido").val('-1')
              .chosen({ no_results_text: "Oops, no hay resultados!" })
              .trigger("chosen:updated")
              .change(function() {
                  $("#datos_pedido").html(
                      `
                        <div>
                            <label>Pedido:</label>
                            <small>` +
                      $(this).val() +
                      `</small>
                            </div>
                            `
                  );
                  $("#ver_elementos,#ver_empacador").show();
              })
    });

  }

  $('.elementos').blur(function(event) {
        /* Act on the event */
      if($(this).val() == "")
            $(this).val('0');
  });

  $("#crear").click(function(event) {
        /* Act on the event */
      $.get('{{ route('etiquetas.pdf_paquetes') }}', $('#forma').serialize()+"&nombre_cliente="+$( "#cliente option:selected" ).text() ,function(data) {
            /*optional stuff to do after success */
            $('#frame').html('');
            data = jQuery.parseJSON(data);
            if(!data.code){
                  alert(data.mensaje)
            }
            else{

                  $('#frame').html('<iframe src="'+data.archivo+'" title="Etiquetas" width="100%" height="800px"></iframe>');
            }
            
      });
  });
</script>
@endsection