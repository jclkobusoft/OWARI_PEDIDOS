@extends('tienda_online.base.base')
@section('contenido')
<section class="my-account-area ptb-100c">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-md-6">
                        <div class="login-form mb-30">
                            <h2>Actualiza tu contraseña</h2>
                            @if (Session::has('message'))
                                <div class="contact-info-box">
                                     <b>{{ Session::get('message') }}</b>
                                </div>
                            @else
                                <div class="contact-info-box">
                                    Cambia tu contraseña y validala correctamente.
                                </div>
                            @endif
                        
                            <form action="{{ route('tienda_online.actualizar_password') }}" method="post" id="pass">
                                @csrf
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="text" class="form-control" readonly="readonly" value="{{  \Auth::user()->email }}">
                                </div>
                                <div class="form-group">
                                    <label>Password nuevo:</label>
                                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label>Confirmar password:</label>
                                    <input type="password" class="form-control" placeholder="Confirmar Password" name="confirmacion" required>
                                </div>
        
                                <button type="submit">Actualizar password</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-md-6">
                        <div class="login-form mb-30">
                            <h2>Mi tienda</h2>
                            @if (Session::has('message'))
                                <div class="contact-info-box">
                                     <b>{{ Session::get('message') }}</b>
                                </div>
                            @else
                                <div class="contact-info-box">
                                    Habilita tu tiendita en linea, sube tu logo y escribe el % que quieres aumentar a los precios.
                                </div>
                            @endif
                        
                            <form action="{{ route('tienda_online.actualizar_tiendita') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="checkme" name="activar_tiendita" @if(isset(\Auth::user()->clienteData)) @if(\Auth::user()->clienteData->tiendita) checked @endif @endif>
                                    <label class="form-check-label" for="checkme">Activar mi tiendita</label>
                                </div>
                                <div class="form-group">
                                    <label>Logotipo</label>
                                    <input type="file" placeholder="Logotipo" name="logotipo" accept="image/png, image/gif, image/jpeg">
                                    @if(isset(\Auth::user()->clienteData)) 
                                      <img src="/logos/{{ \Auth::user()->clienteData->logotipo }}" width="250px" class="mt-3 mb-3">
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>% para precios</label>
                                    <input type="number" class="form-control" placeholder="Porcentaje ganancia" min="1" name="porcentaje" @if(isset(\Auth::user()->clienteData)) value="{{ \Auth::user()->clienteData->porcentaje }}" @endif>
                                </div>
        
                                <button type="submit">Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script type="text/javascript">
            $('#pass').submit(function(event) {

                if($('input[name="password"]').val() == '')
                    return false;
                if($('input[name="password"]').val() != $('input[name="confirmacion"]').val()){
                    alert('Las contraseñas no coinciden.');
                    return false;
                }
                else
                    return true;

            });
                
        </script>
@stop