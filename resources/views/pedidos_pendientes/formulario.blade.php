<div class="row">
    <div class="col-md-4">
        <h4>Datos generales</h4>
        <div class="mb-3">
            {{ Form::label('name', 'Nombre',['class' => 'form-label']) }}
            {{ Form::text('name',null,['class' => 'form-control form-control-sm','placeholder' => 'Nombre del cliente','required' => 'required' ]) }}
        </div>
        <div class="mb-3 row">
            <label for="cliente" class="form-label">Cliente SAE:</label>
            <select class="form-select" id="clave_cliente" name="clave_cliente">
                <option value="-1">Selecciona o busca un cliente</option>
            </select>
        </div>
        <div class="mb-3">
            {{ Form::label('email', 'E-mail',['class' => 'form-label']) }}
            {{ Form::email('email',null,['class' => 'form-control form-control-sm','placeholder' => 'Email del cliente','required' => 'required' ]) }}
        </div>
        <div class="mb-3">
             {{ Form::label('password', 'Contraseña',['class' => 'form-label']) }}
             @if(isset($cliente))
                {{ Form::password('password',['class' => 'form-control form-control-sm','placeholder' => 'Contraseña cliente']) }}
             @else
                {{ Form::password('password',['class' => 'form-control form-control-sm','placeholder' => 'Contraseña cliente','required' => 'required' ]) }}
             @endif
        </div>
    </div>
</div>