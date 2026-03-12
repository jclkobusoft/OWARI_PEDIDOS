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
                            <h2>Iniciar sesión</h2>
                            @if (Session::has('message'))
                                <div class="contact-info-box">
                                     <strong>¡Error!</strong>&nbsp;{{ Session::get('message') }}
                                </div>
                            @endif
        				
                            <form action="{{ route('tienda_online.iniciar_sesion') }}" method="post">
                                @csrf
        
                                <div class="form-group">
                                    <label>E-mail o Celular</label>
                                    <input type="text" class="form-control" placeholder="E-mail/Celular" name="email" required>
                                </div>
        
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                                </div>
        
                                <div class="row align-items-center">
                                    <div class="col-lg-6 col-md-6 col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkme" name="recuerdame" value="1">
                                            <label class="form-check-label" for="checkme">Recordarme</label>
                                        </div>
                                    </div>
        
                                    <div class="col-lg-6 col-md-6 col-sm-6 lost-your-password">
                                        <a href="#" class="lost-your-password">¿Olvidaste tu contraseña?</a>
                                    </div>
                                </div>
            
                                <button type="submit">Iniciar sesion</button>
                            </form>
                            <a href="{{ route('tienda_online.registro') }}" style="width:100%;text-align: center; margin-top: 60px; display: block;">Registrate</a>
                        </div>

                      
                    </div>

                   
                </div>
            </div>
        </section>
@stop