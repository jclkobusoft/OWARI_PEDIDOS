@extends('layouts.app') 
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <form id="forma">
                <div class="mb-3">
                   <label for="exampleInputEmail1" class="form-label">Selecciona tu producto</label>
                   <input type="text" class="form-control" id="entrada" name="entrada">
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">¿Quien las esta haciendo?</label>
                    <input type="text" class="form-control" id="quien" maxlength="4" style="text-transform:uppercase" name="quien">
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">¿Cuantas etiquetas?</label>
                    <input type="number" class="form-control" id="exampleInputPassword1" id="cuantas" value="1" name="cuantas">
                </div>
                <button type="button" class="btn btn-primary" id="crear">Crear etiquetas</button>
                 <div class="mt-3" id="info">
                    
                  
                </div>
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
     $("#entrada").focus();
     var descripcion, barras, clave;

     $("#entrada").keypress(function(e) {
         /* Act on the event */
        if(e.keyCode == 13){
            $.get('https://sistemasowari.com:8443/catalowari/api/producto_etiqueta?clave='+$("#entrada").val(), function(data) {
                /*optional stuff to do after success */
                data = $.parseJSON(data);
                if(data.producto){
                    
                    clave = data.producto.CVE_ART;
                    descripcion = data.producto.DESCR;
                    barras = data.producto.CAMPLIB20;
                    $("#info").html(`
                            <b>Clave:`+clave+`</b><br>
                            <label>Descripción:`+descripcion+`</label>
                    `);
                    $("#quien").focus();
                }
                else{
                    $("#info").html(`
                            <label>Sin información/No ubicado</label>
                    `);
                    clave = ""
                    barras = ""
                    descripcion = ""
                }

            });
            return false;   
        }

     });
     
     $("#cuantas,#quien").keypress(function(e) {
        if(e.keyCode == 13){
            $("#crear").trigger('click');
            return false;
        }
     })

     $("#crear").click(function(){
        if($("#quien").val() == ""){
            alert("Ingresa quien esta haciendo las etiquetas");
            return false;
        }

        if(descripcion == ""){
            alert("No hay información valida para crear la etiqueta");
            return false;
        }

        $.get('{{ route('etiquetas.pdf') }}', $('#forma').serialize()+"&descripcion="+descripcion+"&barras="+barras+"&clave="+clave , function(data, textStatus, xhr) {
            /*optional stuff to do after success */
            clave = ""
            barras = ""
            descripcion = ""
            data = jQuery.parseJSON(data);
            $('#frame').html('');
            $('#frame').html('<iframe src="'+data.archivo+'" title="Etiquetas" width="100%" height="800px"></iframe>');

            $("#entrada").val("").focus();
        });
     });
   
</script>
@endsection