@extends('layouts.app') 
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <form id="forma">
                 <div class="mb-3">
                   <label for="exampleInputEmail1" class="form-label">Empresa</label>
                   <select name="empresa" id="empresa">
                       <option value="1">E1 Factura</option>
                       <option value="3">E3 Remision</option>
                   </select>
                </div>
                <div class="mb-3">
                   <label for="exampleInputEmail1" class="form-label">Ingresa tu no. de compra</label>
                   <input type="text" class="form-control" id="numero_compra" name="numero_compra">
                </div>
                <div id="partidas"  class="mb-3"></div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">¿Quien las esta haciendo?</label>
                    <input type="text" class="form-control" id="quien" maxlength="4" style="text-transform:uppercase" name="quien">
                </div>
                <button type="button" class="btn btn-primary" id="crear">Generar etiquetas</button>
            
            </form>
        </div>
        <div class="col-md-6" id="frame">
            <img style="display:none;" class="imagen_girando" src="https://owari.com.mx/upload/gral/general-Owari_007.png">
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
     var partidas = [];
     var table_partidas = new Tabulator("#partidas", {
            index:"codigo",
            data:partidas, //assign data to table
            layout:"fitColumns", //fit columns to width of table (optional)
            columns:[ //Define Table Columns
                {title:"Clave", field:"clave",headerSort:true},
                {title:"CodigoBarras", field:"codigo_barras",headerSort:false,visible:false},
                {title:"Descrp", field:"descripcion",headerSort:true},
                {title:"Cant", field:"CANTIDAD", editor:"input",headerSort:false},
                {formatter:"buttonCross", width:40, align:"center", cellClick:function(e, cell){
                    cell.getRow().delete();
                },headerSort:false},
            ]
        });
     $("#numero_compra").focus();
     var descripcion, barras, clave;

     $("#numero_compra").keypress(function(e) {
         /* Act on the event */
        var resultados = [];
        if(e.keyCode == 13){
            $.get('https://sistemasowari.com:8443/catalowari/api/productos_compra?empresa='+$("#empresa").val()+'&numero_compra='+$("#numero_compra").val(), function(data) {
                /*optional stuff to do after success */
                data = $.parseJSON(data);
                if(data.code){
                    table_partidas.replaceData(data.partidas);
                    table_partidas.redraw(true);
                }
                else{
                    alert("Esa compra no existe en la empresa seleccionada");
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

        $(this).attr('disabled', 'disabled');
        $(".imagen_girando").show();
        var productos = table_partidas.getData();
        var data = {
            compra : $('#numero_compra').val(),
            quien : $('#quien').val(),
            productos: productos
        };

        $.get('{{ route('etiquetas.pdf_compra') }}', data , function(data, textStatus, xhr) {
            /*optional stuff to do after success */
            data = jQuery.parseJSON(data);
            $('#frame').html('');
            $('#frame').html('<iframe src="'+data.archivo+'" title="Etiquetas" width="100%" height="800px"></iframe>');
            $("#numero_compra").val("").focus();
        });
     });
   
</script>
<style type="text/css">
    .imagen_girando {
      width: 250px;
      position: relative;
      animation: example 4s infinite;
    }

@keyframes example {
  0%   {left:0px; top:0px;}
  25%  {left:250px; top:0px;}
  50%  {left:250px; top:250px;}
  75%  {left:0px; top:250px;}
  100% {left:0px; top:0px;}
}
</style>
@endsection