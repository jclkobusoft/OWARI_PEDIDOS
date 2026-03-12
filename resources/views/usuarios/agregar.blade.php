@extends('layouts.app')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Crear usuario</div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger" role="alert">
              <h4 class="alert-heading">Error!</h4>
              <p>{{$errors->first()}}</p>
            </div>
            @endif

            @if(\Session('success'))
            <div class="alert alert-success" role="alert">
                  <h4 class="alert-heading">Bien!</h4>
                  <p>{{\Session('success')}}</p>
                </div>
            @endif
            {{ Form::open(['route' => 'usuarios.crear', 'method' => 'post','id' => 'formulario']) }}
                @include('usuarios.formulario')
                <div class="row">
                    <div class="col-md-12 d-flex justify-content-end">
                         <button type="submit" class="btn btn-dark">Crear usuario</button>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script>

        $('#formulario').submit(function(e){
            $('button[type="submit"]').attr('disabled', 'disabled');
            return true;
        });
    </script>
@endpush