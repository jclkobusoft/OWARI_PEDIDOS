@extends('layouts.app')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header"><b>Generar plantilla de requisicion</b></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger" role="alert">
              <h4 class="alert-heading">Error!</h4>
              <p>{{$errors->first()}}</p>
            </div>
            @endif

            @if(\Session('success'))
            <div class="alert alert-success" role="alert">
                  <h4 class="alert-heading">Bien!</h4>
                  <p>{{\Session('success')}}</p>
                </div>
            @endif
            <form id="formulario">
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <label for="formFileLg" class="form-label">XML de compra</label>
                            <input class="form-control" name="archivo" type="file">
                            @csrf
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 d-flex justify-content-end">
                         <button type="submit" class="btn btn-dark mt-2">Generar plantilla de requisición</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div id="response"></div>
                        <div id="progress" style="width: 100%; background: #f3f3f3; display: none;">
                            <div id="progress-bar" style="height: 20px; background: #4CAF50; width: 0%"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>

        <script>
            $(document).ready(function() {
                $('#formulario').on('submit', function(e) {
                    e.preventDefault();
                    $('button[type="submit"]').attr('disabled', 'disabled');
                    
                    var formData = new FormData(this);
            
                    $('#progress').show();
                    
                    $.ajax({
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener('progress', function(e) {
                                if (e.lengthComputable) {
                                    var percent = Math.round((e.loaded / e.total) * 100);
                                    $('#progress-bar').width(percent + '%');
                                    $('#progress-bar').html(percent + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        url: "{{ route('compras.excel.requisicion') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            $('#response').html('Subiendo archivo...');
                            $('#progress-bar').width('0%');
                        },
                        success: function(response) {
                            $('#response').html('El archivo se descargara automaticamente.');
                            window.open('{{ route('compras.plantilla.descargar') }}?archivo='+response.archivo);
                        },
                        error: function(xhr) {
                            var error = xhr.responseJSON && xhr.responseJSON.message 
                                ? xhr.responseJSON.message 
                                : 'Error al subir el archivo';
                            $('#response').html('<strong>Error:</strong> ' + error);
                        },
                        complete: function() {
                            $('#progress').hide();
                        }
                    });



                });
            });        
    </script>
  
@endpush