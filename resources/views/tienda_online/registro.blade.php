@extends('tienda_online.base.base')
@section('contenido')
<section class="my-account-area ptb-100c">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center pb-70">
                    	 <img src="{{'https://owari.com.mx/upload/gral/'.$general->logotipo_general}}" width="250px" alt="image">
                    </div>
                    <div class="col-lg-6 col-sm-12 offset-lg-3 offset-md-3 col-md-6">
                        <div class="login-form mb-30">
                            <h2>Registrarse</h2>
                            @if (Session::has('message'))
                                <div class="contact-info-box">
                                     <b>{{ Session::get('message') }}</b>
                                </div>
                            @else
                                <div class="contact-info-box">
                                    ¿Quieres formar parte de nuestros clientes? Rellena la información y nos pondremos en contacto contigo.
                                </div>
                            @endif
                        

                          
        				
                            <form action="{{ route('tienda_online.registrar') }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>Nombre completo</label>
                                    <input type="text" class="form-control" placeholder="Nombre completo" name="nombre" required>
                                </div>
                                <div class="form-group">
                                    <label>Celular/No. telefonico</label>
                                    <input type="text" class="form-control" placeholder="Celular/Numero telefonico" name="telefono" required>
                                </div>
        
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" placeholder="Correo electronico" name="email" required>
                                </div>

                                 <div class="form-group">
                                    <label>Clave de cliente</label>
                                    <small>Si conoces tu clave de cliente ingresala, de lo contrario deja vacio el campo</small>
                                    <input type="text" class="form-control" placeholder="Ya soy cliente" name="cliente">
                                </div>

        
                                <button type="submit">Registrarme</button>
                            </form>
                            <a href="{{ route('tienda_online.login') }}" style="width:100%;text-align: center; margin-top: 60px; display: block;">Iniciar sesión</a>
                        </div>
                    </div>

                   
                </div>
            </div>
        </section>
@stop