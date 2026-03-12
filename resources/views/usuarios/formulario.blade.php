<div class="row">
    <div class="col-md-4">
        <h4>Datos generales</h4>
        <div class="mb-3">
            {{ Form::label('name', 'Nombre',['class' => 'form-label']) }}
            {{ Form::text('name',null,['class' => 'form-control form-control-sm','placeholder' => 'Nombre del usuario del sistema','required' => 'required' ]) }}
        </div>
        <div class="mb-3">
            {{ Form::label('email', 'E-mail',['class' => 'form-label']) }}
            {{ Form::email('email',null,['class' => 'form-control form-control-sm','placeholder' => 'Email del usuario','required' => 'required' ]) }}
        </div>
        <div class="mb-3">
             {{ Form::label('password', 'Contraseña',['class' => 'form-label']) }}
             @if(isset($usuario))
                {{ Form::password('password',['class' => 'form-control form-control-sm','placeholder' => 'Contraseña usuario']) }}
             @else
                {{ Form::password('password',['class' => 'form-control form-control-sm','placeholder' => 'Contraseña usuario','required' => 'required' ]) }}
             @endif
        </div>
        <div class="mb-3">
            {{ Form::label('vendedor_sae', 'Clave de vendedor en SAE',['class' => 'form-label']) }}
            {{ Form::text('vendedor_sae',null,['class' => 'form-control form-control-sm','placeholder' => 'Clave vendedor SAE' ]) }}
            <small>Si quieres que el usuario vea solo sus clientes escribe aqui su clave de cliente de SAE empresa 1, si quieres que vea todos los clientes dejalo vacio.</small>
        </div>
    </div>
    <div class="col-md-8">
        <h4>Permisos</h4>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <p class="h5">Usuarios</p>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'usuarios_ver', isset($usuario) ? $usuario->can('usuarios_ver') : null ,['class' => 'form-check-input','id' => 'usuarios_ver']) }}
                      {{ Form::label('usuarios_ver', 'Ver usuarios',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'usuarios_crear', isset($usuario) ? $usuario->can('usuarios_crear') : null ,['class' => 'form-check-input','id' => 'usuarios_crear']) }}
                      {{ Form::label('usuarios_crear', 'Agregar usuarios',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'usuarios_editar', isset($usuario) ? $usuario->can('usuarios_editar') : null ,['class' => 'form-check-input','id' => 'usuarios_editar']) }}
                      {{ Form::label('usuarios_editar', 'Editar usuarios',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'usuarios_eliminar', isset($usuario) ? $usuario->can('usuarios_eliminar') : null ,['class' => 'form-check-input','id' => 'usuarios_eliminar']) }}
                      {{ Form::label('usuarios_eliminar', 'Eliminar usuarios',['class' => 'form-check-label']) }}
                    </div>
                   

                </div>
                <div class="mb-3">
                    <p class="h5">Productos</p>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'productos_reporte_inventario', isset($usuario) ? $usuario->can('productos_reporte_inventario') : null ,['class' => 'form-check-input','id' => 'productos_reporte_inventario']) }}
                      {{ Form::label('productos_reporte_inventario', 'Ver reporte de inventario',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'productos_reporte_negados', isset($usuario) ? $usuario->can('productos_reporte_negados') : null ,['class' => 'form-check-input','id' => 'productos_reporte_negados']) }}
                      {{ Form::label('productos_reporte_negados', 'Ver reporte de productos negados',['class' => 'form-check-label']) }}
                    </div>
                      <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'productos_reporte_agotados_disponibles', isset($usuario) ? $usuario->can('productos_reporte_agotados_disponibles') : null ,['class' => 'form-check-input','id' => 'productos_reporte_agotados_disponibles']) }}
                      {{ Form::label('productos_reporte_agotados_disponibles', 'Ver agotados ya disponibles',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','ver_reporte_conteo', isset($usuario) ? $usuario->can('ver_reporte_conteo') : null ,['class' => 'form-check-input','id' => 'ver_reporte_conteo']) }}
                      {{ Form::label('ver_reporte_conteo', 'Ver SAE/Conteos',['class' => 'form-check-label']) }}
                    </div>
                    
                </div>
                <div class="mb-3">
                    <p class="h5">Etiquetas</p>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'etiquetas_producto', isset($usuario) ? $usuario->can('etiquetas_producto') : null ,['class' => 'form-check-input', 'id' => 'etiquetas_producto']) }}
                      {{ Form::label('etiquetas_producto', 'Generar etiquetas de producto',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'etiquetas_paquetes', isset($usuario) ? $usuario->can('etiquetas_paquetes') : null ,['class' => 'form-check-input', 'id' => 'etiquetas_paquetes']) }}
                      {{ Form::label('etiquetas_paquetes', 'Generar etiquetas de paquetes',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]', 'etiquetas_compras', isset($usuario) ? $usuario->can('etiquetas_compras') : null ,['class' => 'form-check-input', 'id' => 'etiquetas_compras']) }}
                      {{ Form::label('etiquetas_compras', 'Generar etiquetas de compra',['class' => 'form-check-label']) }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
               <div class="mb-3">
                    <p class="h5">Clientes</p>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','clientes_ver', isset($usuario) ? $usuario->can('clientes_ver') : null ,['class' => 'form-check-input','id' => 'clientes_ver']) }}
                      {{ Form::label('clientes_ver', 'Ver clientes',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','clientes_crear', isset($usuario) ? $usuario->can('clientes_crear') : null ,['class' => 'form-check-input','id' => 'clientes_crear']) }}
                      {{ Form::label('clientes_crear', 'Agregar clientes',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','clientes_editar', isset($usuario) ? $usuario->can('clientes_editar') : null ,['class' => 'form-check-input','id' => 'clientes_editar']) }}
                      {{ Form::label('clientes_editar', 'Editar clientes',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','clientes_eliminar', isset($usuario) ? $usuario->can('clientes_eliminar') : null ,['class' => 'form-check-input','id' => 'clientes_eliminar']) }}
                      {{ Form::label('clientes_eliminar', 'Eliminar clientes',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','clientes_ver_ventas', isset($usuario) ? $usuario->can('clientes_ver_ventas') : null ,['class' => 'form-check-input','id' => 'clientes_ver_ventas']) }}
                      {{ Form::label('clientes_ver_ventas', 'Ver ventas/producto',['class' => 'form-check-label']) }}
                    </div>
                </div>
                <div class="mb-3">
                    <p class="h5">Pedidos</p>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','pedidos_ver', isset($usuario) ? $usuario->can('pedidos_ver') : null ,['class' => 'form-check-input','id' => 'pedidos_ver']) }}
                      {{ Form::label('pedidos_ver', 'Ver pedidos',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','pedidos_crear', isset($usuario) ? $usuario->can('pedidos_crear') : null ,['class' => 'form-check-input','id' => 'pedidos_crear']) }}
                      {{ Form::label('pedidos_crear', 'Agregar pedidos',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','pedidos_editar', isset($usuario) ? $usuario->can('pedidos_editar') : null ,['class' => 'form-check-input','id' => 'pedidos_editar']) }}
                      {{ Form::label('pedidos_editar', 'Editar pedidos',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','pedidos_eliminar', isset($usuario) ? $usuario->can('pedidos_eliminar') : null ,['class' => 'form-check-input','id' => 'pedidos_eliminar']) }}
                      {{ Form::label('pedidos_eliminar', 'Eliminar pedidos',['class' => 'form-check-label']) }}
                    </div>
                </div>
                <div class="mb-3">
                    <p class="h5">Compras</p>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','compras_plantilla_productos_nuevos', isset($usuario) ? $usuario->can('compras_plantilla_productos_nuevos') : null ,['class' => 'form-check-input','id' => 'compras_plantilla_productos_nuevos']) }}
                      {{ Form::label('compras_plantilla_productos_nuevos', 'Ver plantilla productos nuevos',['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check form-switch">
                      {{ Form::checkbox('permisos[]','compras_plantilla_requisiciones', isset($usuario) ? $usuario->can('compras_plantilla_requisiciones') : null ,['class' => 'form-check-input','id' => 'compras_plantilla_requisiciones']) }}
                      {{ Form::label('compras_plantilla_requisiciones', 'Ver plantilla requisicion',['class' => 'form-check-label']) }}
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
</div>