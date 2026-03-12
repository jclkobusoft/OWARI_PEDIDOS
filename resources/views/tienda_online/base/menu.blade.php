<div class="container">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                @if(isset(\Auth::user()->clienteData))
                    @if((\Auth::user()->clienteData->logotipo != "" ||\Auth::user()->clienteData->logotipo != null) && \Auth::user()->clienteData->tiendita)
                          <img src="{{ '/logos/'.\Auth::user()->clienteData->logotipo}}" width="200">
                    @else
                          <img src="{{'https://owari.com.mx/upload/gral/'.$general->logotipo_general}}" width="100">
                    @endif
                @else
                    <img src="{{'https://owari.com.mx/upload/gral/'.$general->logotipo_general}}" width="100">
                @endif   
               
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"><i class="bi bi-three-dots-vertical"></i></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="{{ route('tienda_online.dashboard') }}"><i class="bi bi-house-door-fill"></i>&nbsp;Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tienda_online.favoritos') }}"><i class="bi bi-bookmark-star"></i>&nbsp;Favoritos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tienda_online.productos') }}?q=lo_mas_nuevo&p=1"><i class="bi bi-patch-plus"></i>&nbsp;Lo nuevo</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="{{ route('tienda_online.descuentos') }}"><i class="bi bi-tags"></i>&nbsp;Descuentos</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="{{ route('tienda_online.liquidacion') }}?p=1"><i style="color:red" class="bi bi-bookmark-star-fill"></i>&nbsp;Liquidacion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tienda_online.productos') }}?q=&p=1"><i class="bi bi-car-front"></i>&nbsp;Productos</a>
                    </li>
                    @if(\Auth::user()->clave_cliente != "M014M")
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tienda_online.pedidos') }}"><i class="bi bi-box-seam-fill"></i>&nbsp;Mis pedidos</a>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="d-none d-md-flex">
            <div class="d-flex">
                <a href="{{ route('tienda_online.carrito') }}" class="d-flex">
                <i class="bi bi-cart-fill" style="font-size: 18px; margin-top: 10px;"></i><span style="margin-top: 10px;height: 22px;" class="badge rounded-pill bg-danger cantidad_carrito">
                     @if(Session::has('cart'))
                        @if(count(Session::get('cart')) >0)
                          {{ count(Session::get('cart')) }}
                        @else
                          0
                        @endif
                      @else
                        0
                    @endif

                </span>
            </a>
            </div>
            <div class="d-flex dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ \Auth::user()->name}}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(\Auth::user()->clave_cliente != "M014M")
                        <li><a class="dropdown-item" href="{{ route('tienda_online.generar_catalogo') }}"><i class="bi bi-list-columns"></i>&nbsp;Lista precios completa</a></li>
                    @endif
                    <li><a class="dropdown-item" href="{{ route('tienda_online.editar_cliente') }}"><i class="bi bi-person-circle"></i>&nbsp;Editar información</a></li>
                    <li><a class="dropdown-item" href="{{ route('tienda_online.logout') }}"><i class="bi bi-box-arrow-left"></i>&nbsp;Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
        </div>
    </nav>
    <div class="col-12 d-md-none">
     <div class="d-flex d-md-node">
            <div class="d-flex col">
                <a href="{{ route('tienda_online.carrito') }}" class="d-flex">
                <i class="bi bi-cart-fill" style="font-size: 18px; margin-top: 10px;"></i><span style="margin-top: 10px;height: 22px;" class="badge rounded-pill bg-danger cantidad_carrito">
                     @if(Session::has('cart'))
                        @if(count(Session::get('cart')) >0)
                          {{ count(Session::get('cart')) }}
                        @else
                          0
                        @endif
                      @else
                        0
                    @endif

                </span>
            </a>
            </div>
            <div class="d-flex dropdown col">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ \Auth::user()->name}}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(\Auth::user()->clave_cliente != "M014M")
                        <li><a class="dropdown-item" href="{{ route('tienda_online.generar_catalogo') }}"><i class="bi bi-list-columns"></i>&nbsp;Lista precios completa</a></li> 
                    @endif
                    <li><a class="dropdown-item" href="{{ route('tienda_online.editar_cliente') }}"><i class="bi bi-person-circle"></i>&nbsp;Editar información</a></li>
                    <li><a class="dropdown-item" href="{{ route('tienda_online.logout') }}"><i class="bi bi-box-arrow-left"></i>&nbsp;Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<style>
  .nav-link{
    font-size:16px;
    padding-right:20px;
  }
</style>