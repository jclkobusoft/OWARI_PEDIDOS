
<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="refacciones autopartes vw chevrolet ecatepec miyali tsuru motos nissan mayoreo nikko quimica ecom kabuto axpro kem kba gates fp safety best cooling syd spartan aremi masuda hella matsuma osram ngk tecfa denso tmk estado mexico jardines casanueva tucson calidad buen precio fastoys mcallen cavi devcon marvill restore lucas new shine wd40 pinitos aromatizantes clutch frenos suspension motor enfriamiento afinacion eletricas partes filtros aire aceite gasolina cabina colision champion iluminacion motor moto automovil autos mantenimiento automotriz economicas tomco econoflow uniflow miscelanea chachara importacion dodge chrysler toyota kia mitsubishi mg eur eko baleros honda bujias versa np300 aveo rio sentra mg5 march seltos general motors gm volswagen mazda hyundai renault suzuki chirey seat jac mercedez benz peugeot audi bmw fiat omoda volvo mini subaru lincoln acura infinity bsk rebotes abrazadera terminales">
    <meta name="robots" content="index,follow">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="Spanish">
    <meta name="revisit-after" content="1 day">
    <meta name="author" content="pro-tic.mx">
    <!-- Primary Meta Tags -->
    <meta name="title" content="Owari Autopartes {{ $titulo }}" />
    <meta name="description" content="Owari Autopartes, empresa dedica a la venta de autopartes al mayoreo con mas de 10 años de experiencia" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Owari Autopartes {{ $titulo }}" />
    <meta property="og:description" content="Owari Autopartes, empresa dedica a la venta de autopartes al mayoreo con mas de 10 años de experiencia" />
    <meta property="og:image" content="https://www.owari.com.mx/upload/gral/general-Owari_007.png" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="{{ url()->current() }}" />
    <meta property="twitter:title" content="Owari Autopartes {{ $titulo }}" />
    <meta property="twitter:description" content="Owari Autopartes, empresa dedica a la venta de autopartes al mayoreo con mas de 10 años de experiencia" />
    <meta property="twitter:image" content="https://www.owari.com.mx/upload/gral/general-Owari_007.png" />



    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/bootstrap.min.css")}}">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/animate.min.css")}}">
    <!-- Meanmenu CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/meanmenu.css")}}">
    <!-- Boxicons CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/boxicons.min.css")}}">
    <!-- Flaticon CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/flaticon.css")}}">
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/owl.carousel.min.css")}}">
    <!-- Owl Theme Default CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/owl.theme.default.min.css")}}">
    <!-- Odometer CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/odometer.min.css")}}">
    <!-- Nice Select CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/nice-select.min.css")}}">
    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/magnific-popup.min.css")}}">
    <!-- Imagelightbox CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/imagelightbox.min.css")}}">
    <!-- Style CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/style.css")}}">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{asset("tienda_online/css/responsive.css")}}">
    <link rel="stylesheet" href="{{asset("tienda_online/chosen/chosen.css")}}">
    <link rel="stylesheet" href="{{asset("tienda_online/css/kobusoft.css")}}">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <title>Owari Autopartes {{ $titulo }}</title>

    <link rel="icon" type="image/png" href="https://owari.com.mx/cms/images/favicon.png">
    <!-- Google tag (gtag.js) -->
    <script src="{{asset("tienda_online/js/jquery.min.js")}}"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TW7793KBQG"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-TW7793KBQG');
    </script>
</head>

<body>

   

    <!-- Start Top Header Area -->
    
    <div class="top-header-area d-sm-block d-none">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-12">
                    <b style="color:white;">Solo venta al mayoreo</b>
                </div>
                <div class="col-lg-5 col-md-12">
                    <ul class="top-header-information">
                        <li>
                            <i class="flaticon-pin"></i>
                            <a target="_blank" style="color:white;" href="https://waze.com/ul?q=Owari%20Autopartes&navigate=yes">Waze: {{$general->direccion_1." ".$general->direccion_2." ".$general->direccion_3}}</a>
                        </li>
                        <li>
                            <i class="flaticon-clock"></i>
                            Horario: {{$general->horarios}}
                        </li>
                    </ul>
                </div>

                
                <div class="col-lg-5 col-md-12">
                    <ul class="top-header-optional">
                        <li>Telefonos: <b>
                                @if($general->telefono_1 != "")
                                    <a class="boton-rojo" href="tel:{!! $general->marcar_1 !!}">{!! $general->telefono_1 !!}</a><br>
                                @endif
                                @if($general->telefono_2 != "")
                                    <a class="boton-rojo" href="https://api.whatsapp.com/send?phone={!! trim($general->marcar_2,'+') !!}&text=Hola, soy un cliente de la pagina web" target="_blank">{!! $general->telefono_2 !!}</a><br>
                                @endif
                                @if($general->telefono_3 != "")
                                    <a class="boton-rojo" href="tel:{!! $general->marcar_3 !!}">{!! $general->telefono_3 !!}</a>
                                @endif
                            </b>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="top-header-area d-block d-sm-none">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-9 col-md-12">
                    <ul class="top-header-information">
                        <li>
                            <i class="flaticon-pin"></i>
                            <a target="_blank" style="color:#d31531;" href="https://waze.com/ul?q=Owari%20Autopartes&navigate=yes">Click aqui para llegar con Waze</a>
                        </li>
                        <li>
                            <i class="flaticon-clock"></i>
                             {{$general->horarios}}
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-12">
                    <ul class="top-header-optional">
                        <li>Telefonos: <b>
                                @if($general->telefono_1 != "")
                                    <a  style="line-height:17px;" class="boton-rojo" href="tel:{!! $general->marcar_1 !!}">{!! $general->telefono_1 !!}</a><br>
                                @endif
                                @if($general->telefono_2 != "")
                                    <a  style="line-height:17px;" class="boton-rojo" href="https://api.whatsapp.com/send?phone={!! trim($general->marcar_2,'+') !!}&text=Hola, soy un cliente de la pagina web" target="_blank">{!! $general->telefono_2 !!}</a><br>
                                @endif
                            </b>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Top Header Area -->

    @if(\Request::route()->getName() != 'tienda_online.login' && \Request::route()->getName() != 'tienda_online.registro' && \Request::route()->getName() != 'tienda_online.registro_nuevo')
        @include('tienda_online.base.menu')
    @endif
    <div class="container">
        @if(\Auth::check())
        <div class="row">
            <div class="col-12 col-md-6 text-right offset-md-3 mt-3">
                <form action="{{ route('tienda_online.productos') }}" method="get">
                    <input type="hidden" value="1" name="p">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="q" placeholder="Buscar: Clave, Marca, Modelo, Año" value="">
                        <button class="btn btn-outline-secondary" style="color:white; background-color:rgb(43,57,145)" type="submit" id="button-addon2"><i class="bi bi-search"></i>&nbsp;Buscar</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    <!-- End Navbar Area -->
    @yield('contenido')        
    <!-- Start Footer Area -->
    <section class="footer-area pt-100 pb-70" style="background-image: url('{{'https://owari.com.mx/upload/gral/'.$general->imagen_footer }}');">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-sm-6">
                    <div class="single-footer-widget">
                        <a target="_blank" href="https://owari.com.mx">
                            <img src="{{'https://owari.com.mx/upload/gral/'.$general->logotipo_general}}" width="150px" alt="image">
                        </a>

                        <p>{!!$general->descripcion_footer !!}</p>

                        <ul class="footer-social">
                            @if($general->url_facebook != "")
                                <li>
                                    <a href="{{$general->url_facebook}}" target="_blank">
                                        <i class='bx bxl-facebook'></i>
                                    </a>
                                </li>
                            @endif
                            @if($general->url_twitter != "")
                                <li>
                                    <a href="{{$general->url_twitter}}" target="_blank">
                                        <i class='bx bxl-twitter'></i>
                                    </a>
                                </li>
                            @endif
                            @if($general->url_instagram != "")
                                <li>
                                    <a href="{{$general->url_instagram}}" target="_blank">
                                        <i class='bx bxl-instagram-alt'></i>
                                    </a>
                                </li>
                            @endif
                            @if($general->url_youtube != "")
                                <li>
                                    <a href="{{$general->url_youtube}}" target="_blank">
                                        <i class='bx bxl-youtube'></i>
                                    </a>
                                </li>
                            @endif
                            @if($general->url_pinterest != "")
                                <li>
                                    <a href="{{$general->url_pinterest}}" target="_blank">
                                        <i class='bx bxl-pinterest-alt'></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

             

                
                <div class="col-lg-6 col-sm-6">
                    <div class="single-footer-widget">
                        <h2>Contactanos</h2>

                        <ul class="footer-contact-info">
                            <li>
                                <i class='bx bxs-phone'></i>
                                <span>Telefono(s):</span>

                                @if($general->telefono_1 != "")
                                    <a href="tel:{!! $general->marcar_1 !!}">{!! $general->telefono_1 !!}</a>
                                @endif
                                @if($general->telefono_2 != "")
                                <a href="https://api.whatsapp.com/send?phone={!! trim($general->marcar_2,'+') !!}&text=Hola, soy un cliente de la pagina web" target="_blank">{!! $general->telefono_2 !!}</a><br>
                                @endif
                                @if($general->telefono_3 != "")
                                    <a href="tel:{!! $general->marcar_3 !!}">{!! $general->telefono_3 !!}</a>
                                @endif
                            </li>
                            <li>
                                <i class='bx bx-envelope'></i>
                                <span>Correo electronico</span>
                                <a href="mailto:{{$general->email_contacto}}">{{$general->email_contacto}}</a>
                            </li>
                            <li>
                                <i class='bx bx-map'></i>
                                <span>Ubicación</span>
                                @if($general->direccion_1 != "")
                                    {{$general->direccion_1}},
                                @endif
                                @if($general->direccion_2 != "")
                                    {{$general->direccion_2}},
                                @endif
                                @if($general->direccion_3 != "")
                                    {{$general->direccion_3}}
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Footer Area -->
    <div class="alert alert-light alert-dismissible fade" role="alert" id="alerta_carrito">
            <p class="texto_alerta">Tu producto fue agregado correctamente.</p>
            <button type="button" class="btn-close cerrar_alerta" style="background:none;"> <i class="bi bi-x"></i></button>
        </div>


    <!-- Start Copy Right Area -->
    <div class="copyright-area">
        <div class="container">
            <div class="copyright-area-content">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6">
                        <p>
                            Copyright © {{date('Y')}} <label class="esconder_tiendita">Owari Autopartes</label>. Derechos reservados
                            <a href="https://kobusoft.com/" target="_blank">
                                Desarrollado x PRO-TIC
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Copy Right Area -->

    <!-- Start Go Top Area -->
    <div class="go-top">
        <i class='bx bx-up-arrow-alt'></i>
    </div>
    <!-- End Go Top Area -->

    <!-- Jquery Slim JS -->
   
    <!-- Popper JS -->
    <script src="{{asset("tienda_online/js/popper.min.js")}}"></script>
    <!-- Bootstrap JS -->
    <script src="{{asset("tienda_online/js/bootstrap.min.js")}}"></script>
    <!-- Meanmenu JS -->
    <script src="{{asset("tienda_online/js/jquery.meanmenu.js")}}"></script>
    <!-- Owl Carousel JS -->
    <script src="{{asset("tienda_online/js/owl.carousel.min.js")}}"></script>
    <!-- Magnific Popup JS -->
    <script src="{{asset("tienda_online/js/jquery.magnific-popup.min.js")}}"></script>
    <!-- Imagelightbox JS -->
    <script src="{{asset("tienda_online/js/imagelightbox.min.js")}}"></script>
    <!-- Odometer JS -->
    <script src="{{asset("tienda_online/js/odometer.min.js")}}"></script>
    <!-- Jquery Nice Select JS -->
    <script src="{{asset("tienda_online/js/jquery.nice-select.min.js")}}"></script>
    <!-- Jquery Appear JS -->
    <script src="{{asset("tienda_online/js/jquery.appear.min.js")}}"></script>
    <!-- Ajaxchimp JS -->
    <script src="{{asset("tienda_online/js/jquery.ajaxchimp.min.js")}}"></script>
    <!-- Form Validator JS -->
    <script src="{{asset("tienda_online/js/form-validator.min.js")}}"></script>
    <!-- Contact JS -->
    <script src="{{asset("tienda_online/js/contact-form-script.js")}}"></script>
    <!-- Wow JS -->
    <script src="{{asset("tienda_online/js/wow.min.js")}}"></script>
    <!-- Autocompletar JS -->
    <script src="{{asset("tienda_online/js/jquery.autocomplete.js")}}"></script>
    <!-- Custom JS -->
    <script src="{{asset("tienda_online/chosen/chosen.jquery.js")}}"></script>
    <script src="{{asset("tienda_online/js/mainw.js")}}"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script>
    $('input[name="q"]').devbridgeAutocomplete({
        serviceUrl: '{{route('tienda_online.autocompletar')}}',
        minChars: 2,
        noCache: true,
        onSelect: function(suggestion) {
            window.location.href = '{{route('tienda_online.detalles_producto', '')}}/' + suggestion.data;
        }
    });
    </script>

  <style>
    @if(isset(\Auth::user()->clienteData))
        @if(\Auth::user()->clienteData->tiendita)
            .top-header-area{
                display:none !important;       
            }
            .footer-area{
                display:none !important;
            }
            .esconder_tiendita{
                display:none !important;
            }
           
        @endif
    @endif   
  
    .autocomplete-suggestions {
        border: 1px solid #999;
        background: #FFF;
        overflow: auto;
    }

    .autocomplete-suggestion {
        padding: 2px 5px;
        white-space: nowrap;
        overflow: hidden;
    }

    .autocomplete-selected {
        background: #F0F0F0;
    }

    .autocomplete-suggestions strong {
        font-weight: normal;
        color: #3399FF;
    }

    .autocomplete-group {
        padding: 2px 5px;
    }

    .autocomplete-group strong {
        display: block;
        border-bottom: 1px solid #000;
    }
    </style>

    <style>

        #alerta_carrito {
        position: fixed;
        top: 10px;
        width: 300px;
        display: none;
        right: 10px;
        background-color: #eee;
        border: #000 1px solid;
        z-index: 999999;
}
        @media only screen and (max-width: 768px) {
        .middle-header-area{
            position: sticky;
            top: 48px;
            background-color: #FFF;
            padding: 10px 0;
            z-index:9999;
        }
        .middle-header-search{
            margin:0;
        }
        .navbar-area{
            position: fixed !important;
            top:0px;
            width:100%;
        }
    }

    
    </style>

    @yield('js')        

    @yield('css')        
</body>

</html>