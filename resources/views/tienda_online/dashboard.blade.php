@extends('tienda_online.base.base')
@section('contenido')
 <section class="my-account-area ptb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="login-form mb-30" style="width: 100%;">
                            <h2>Bienvenido a nuestro nuevo servicio de venta en linea.</h2>
                            <small>Sigue las instrucciones y haznos llegar tu pedido directamente a nuestra sucursal.</small><br><br>
                            <ol>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Usa nuestro buscador para encontrar el producto que buscas. Utiliza palabras especificas para darte un mejor resultado.
                                    <img src="{{ asset('images/a.gif') }}" width="100%">
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		¡Ya encontre mi producto!: dale click al boton ver detalles.
                                    <img src="{{ asset('images/b.gif') }}" width="100%">
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Espera un momento para obtener el stock y el precio de nuestro producto, escribe o selecciona la cantidad de producto que necesitas y da click en agregar al carrito.
                                    <img src="{{ asset('images/c.gif') }}" width="100%">
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Llena tu carrito con todos los productos que quieras en tu pedido.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Para ver tu carrito, dale click al icono de carrito <i class="bi bi-cart-fill"></i> en la parte superior derecha.
                                    <img src="{{ asset('images/d.gif') }}" width="100%">
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                                    En el carrito estaran tus productos seleccionados, aun puedes cambiar la cantidad de producto que necesitas.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Si quieres quitar un producto de tu carrito, simplemente presiona en el simbolo <i class="bi bi-x-circle"></i> que se encuentra del lado izquierdo.
                            		</p>
                            	</li>

                            	<li>
                            		<p style="font-weight: normal;">
                            		Cuando tu carrito este listo presiona el boton de generar pedido. Espera unos instantes y recibiras una notificación de que tu pedido fue creado.
                                    <img src="{{ asset('images/e.gif') }}" width="100%">
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		 ¡Listo! El pedido a sido recibido en nuestra bodega y comenzara a ser atentido, revisa constantemente el estado del pedido para saber en que situación se encuentra.
                            		</p>
                            	</li>
                            </ol>
                            
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="login-form mb-30" style="width: 100%;">
                            <h2>Una guia rapida</h2>
                            <video width="100%" controls>
                              <source src="/owari.mp4" type="video/mp4">
                              Your browser does not support the video tag.
                            </video>

                            <h2 style="margin-top: 30px;">¿Como va mi pedido?</h2>
                            <small>Revisa la sección de mis pedidos para poder saber como va tu pedido.</small><br><br>
                            <ol>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Ingresa en la sección de mis pedidos.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Dale click al pedido del cual quieres saber información.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Revisa la información de tu pedido y el estado en el que se encuentra.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Si tienes dudas, comunicate a los telefonos que tenemos en la parte superior o inferior de la pagina y pregunta por tu pedido con la clave CW que tienes en el sistema.
                            		</p>
                            	</li>
                            	
                            </ol>

                            <h2 style="margin-top: 30px;">Mis favoritos</h2>
                            <small>Guarda o elimina tus productos en la seccion de favoritos.</small><br><br>
                            <ol>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Ingresa a la sección de productos y busca el producto que deseas guardar.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Da click en el boton azul y podras guardarlo como producto favorito.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Tambien puedes agregar tu agregar o quitar tu producto favorito desde el detalle de un producto.
                            		</p>
                            	</li>
                            	<li>
                            		<p style="font-weight: normal;">
                            		Visualiza todos tus productos favoritos en la seccion de productos favoritos. Puedes quitarlos si alguno deja de ser tu favorito.
                            		</p>
                            	</li>
                            	
                            </ol>
                            
                        </div>
                    </div>
                     
                    </div>
                </div>
            </div>
        </section>
@endsection
@section('css')

@endsection
@section('js')

@endsection