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
                        
                            @if (Session::has('error_tiendita'))
                                <div class="alert alert-danger" role="alert">
                                    {{ Session::get('error_tiendita') }}
                                </div>
                            @endif
                            <form id="forma_tiendita" action="{{ route('tienda_online.actualizar_tiendita') }}" method="post" enctype="multipart/form-data">
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
                                    <input type="number" class="form-control" id="porcentaje_tiendita" placeholder="Porcentaje ganancia" min="1" name="porcentaje" @if(isset(\Auth::user()->clienteData)) value="{{ \Auth::user()->clienteData->porcentaje }}" @endif>
                                </div>

                                <button type="submit">Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (Session::has('mostrar_modal_tiendita'))
            <div class="modal fade" id="modalTienditaActivada" tabindex="-1" aria-labelledby="modalTienditaActivadaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTienditaActivadaLabel">Tu tiendita esta activada</h5>
                        </div>
                        <div class="modal-body">
                            <p>
                                Desde ahora los precios en pantalla se muestran con un aumento del
                                <b>{{ Session::get('porcentaje_tiendita') }}%</b> sobre el precio de mayoreo.
                                Esos son los precios que se mostrarán mientras la tiendita este activa.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

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

            $('#forma_tiendita').on('submit', function(event) {
                var activa     = $('#checkme').is(':checked');
                var porcentaje = parseFloat($('#porcentaje_tiendita').val() || 0);

                if (activa && (!porcentaje || porcentaje <= 0)) {
                    alert('Para activar tu tiendita debes ingresar un porcentaje mayor a 0.');
                    $('#porcentaje_tiendita').focus();
                    event.preventDefault();
                    return false;
                }
            });

            @if (Session::has('mostrar_modal_tiendita'))
                document.addEventListener('DOMContentLoaded', function () {
                    var modalEl = document.getElementById('modalTienditaActivada');
                    if (modalEl && window.bootstrap && bootstrap.Modal) {
                        new bootstrap.Modal(modalEl).show();
                    }
                });
            @endif
        </script>
@stop