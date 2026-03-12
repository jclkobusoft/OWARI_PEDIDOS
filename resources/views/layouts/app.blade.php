<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Scripts -->
    <style>
        

.dropdown .dropdown-menu {
  display: none;
}
.dropdown:hover > .dropdown-menu,
.dropend:hover > .dropdown-menu {
  display: block;
  margin-top: 0;
  margin-left: 0.125em;
}
@media screen and (min-width: 769px) {
  .dropend:hover > .dropdown-menu {
    position: absolute;
    top: 0;
    left: 100%;
  }
  .dropend .dropdown-toggle {
    margin-top: 0;
    margin-left: 0.5em;
  }
}
    </style>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="/images/logo_owari.png" width="100px">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                @guest
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>&nbsp;{{ __('Iniciar sesión') }}</a>
                        </li>
                        @endif
                        @if (Route::has('register'))
                        <!--<li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Registrar') }}</a>
                                </li>-->
                        @endif
                    </ul>
                </div>
                @else
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav">
                        <!-- Authentication Links -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-building-gear"></i>&nbsp;{{ __('Administración') }}
                            </a>
                            <ul class="dropdown-menu">
                                @can('usuarios_ver')
                                <li class="nav-item dropend">
                                    
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-bounding-box"></i>&nbsp;{{ __('Usuarios') }}
                                    </a>
                                     <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('usuarios.agregar') }}"><i class="bi bi-plus"></i>&nbsp;Crear usuario</a></li>
                                        <li><a class="dropdown-item" href="{{ route('usuarios.index') }}"><i class="bi bi-table"></i>&nbsp;Ver usuarios</a></li>
                                    </ul>
                                </li>
                                @endcan
                               
                                <li class="nav-item dropend">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-people"></i>&nbsp;{{ __('Clientes') }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        @can('clientes_crear')
                                        <li><a class="dropdown-item" href="{{ route('clientes.agregar') }}"><i class="bi bi-plus"></i>&nbsp;Crear cliente</a></li>
                                        @endcan
                                         @can('clientes_ver')
                                        <li><a class="dropdown-item" href="{{ route('clientes.index') }}"><i class="bi bi-table"></i>&nbsp;Ver clientes</a></li>
                                        @endcan
                                        @can('clientes_ver_ventas')
                                          <li><a class="dropdown-item" href="{{ route('clientes.ventas') }}"><i class="bi bi-coin"></i>&nbsp;Productos/clientes</a></li>
                                        @endcan
                                    </ul>
                                </li>
                                
                                
                                <li class="nav-item dropend">
                                    
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-boxes"></i>&nbsp;{{ __('Productos') }}
                                    </a>
                               
                                     <ul class="dropdown-menu">
                                        @can('productos_reporte_inventario')
                                        <li><a class="dropdown-item" href="{{ route('productos.reporte_inventario') }}"><i class="bi bi-file-earmark-spreadsheet"></i>&nbsp;Reporte de inventario</a></li>
                                        @endcan
                                        @can('productos_reporte_negados')
                                        <li><a class="dropdown-item" href="{{ route('productos.reporte_negados') }}" target="_blank"><i class="bi bi-file-earmark-spreadsheet"></i>&nbsp;Reporte de productos negados</a></li>
                                        @endcan
                                        @can('productos_reporte_agotados_disponibles')
                                        <li><a class="dropdown-item" href="{{ route('productos.larga_venta') }}"><i class="bi bi-emoji-dizzy"></i>&nbsp;Agotados ya disponibles</a></li>
                                        @endcan
                                         @can('ver_reporte_conteo')
                                        <li><a target="_blank" class="dropdown-item" href="{{ route('conteo.form') }}"><i class="bi bi-123"></i>&nbsp;SAE/Conteos</a></li>
                                        @endcan
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="bi bi-aspect-ratio"></i>&nbsp;{{ __('Etiquetas') }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                 @can('etiquetas_producto')
                                <a class="dropdown-item" href="{{ route('etiquetas.crear') }}">
                                    <i class="bi bi-aspect-ratio-fill"></i>&nbsp;{{ __('Crear etiqueta productos') }}
                                </a>
                                @endcan
                                 @can('etiquetas_paquetes')
                                <a class="dropdown-item" href="{{ route('etiquetas.crear_paquetes') }}">
                                    <i class="bi bi-box-seam"></i>&nbsp;{{ __('Crear etiqueta pedidos/paquetes') }}
                                </a>
                                @endcan
                                 @can('etiquetas_compras')
                                <a class="dropdown-item" href="{{ route('etiquetas.etiquetas_compra') }}">
                                    <i class="bi bi-bag-check"></i>&nbsp;{{ __('Crear etiqueta desde compra') }}
                                </a>
                                @endcan
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="bi bi-list-columns-reverse"></i>&nbsp;{{ __('Pedidos') }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                 @can('pedidos_ver')
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person-lines-fill"></i>&nbsp;{{ __('Ver pedidos') }}
                                </a>
                                @endcan
                                @can('pedidos_ver')
                                <a class="dropdown-item" href="{{ route('pedidos_especiales.index') }}">
                                    <i class="bi bi-exclude"></i>&nbsp;{{ __('Ver pedidos especiales') }}
                                </a>
                                @endcan
                                @can('pedidos_ver')
                                <a class="dropdown-item" href="{{ route('pedidos_pendientes.index') }}">
                                    <i class="bi bi-cart-check"></i>&nbsp;{{ __('Ver pedidos pendientes de aprobación') }}
                                </a>
                                @endcan
                                 @can('pedidos_crear')
                                <a class="dropdown-item" href="{{ route('pedidos.crear') }}">
                                    <i class="bi bi-file-earmark-plus-fill"></i>&nbsp;{{ __('Crear pedido') }}
                                </a>
                                @endcan
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="bi bi-bag"></i>&nbsp;{{ __('Compras') }}
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                @can('compras_plantilla_productos_nuevos')
                                <a class="dropdown-item" href="{{ route('compras.plantilla.productos_nuevos') }}">
                                    <i class="bi bi-cart-plus"></i>&nbsp;{{ __('Plantilla de productos nuevos') }}
                                </a>
                                @endcan
                                @can('compras_plantilla_requisiciones')
                                <a class="dropdown-item" href="{{ route('compras.plantilla.captura_compra') }}">
                                    <i class="bi bi-journal-plus"></i>&nbsp;{{ __('Plantilla de requisiciones') }}
                                </a>
                                @endcan
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="d-flex position-relative">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="bi bi-person-bounding-box"></i>&nbsp;{{ ucwords(strtolower(\Auth::user()->name)) }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            <i class="bi bi-door-open"></i>&nbsp;{{ __('Cerrar sesión') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
                @endguest
            </div>
        </nav>
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
     
</body>

</html>