@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('AGOTADOS YA DISPONIBLES') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @php
                         $items = $rows;
                         function fmtFecha($s){ return $s ? date('d/m/Y', strtotime($s)) : ''; }
                         function fmtNum($v){ return $v === null ? '' : number_format((float)$v, 2, '.', ','); }
                    @endphp

                    <div class="table-responsive">
                         <table class="table table-sm table-striped table-hover align-middle">
                              <thead class="table-light">
                                   <tr class="text-nowrap">
                                   <th>CLAVE</th>
                                   <th>DESCRIPCION</th>
                                   <th class="text-end">EXISTENCIA</th>
                                   <th>PRECIO</th> 
                                   <th>CLIENTE</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   @foreach($items as $r)
                                   <tr>
                                   <td>{{ $r['CVE_ART'] }}</td>
                                   <td class="text-wrap">{{ $r['DESCR'] }}</td>
                                   <td class="text-end">{{ fmtNum($r['EXIST_REAL'] ?? null) }}</td>
                                   <td class="text-end">${{ number_format($r['PRECIO'],2,'.',',') }}</td>
                                   <td class="text-end">{{ $r['ULT_CLIENTE'] ?? '—' }}</td>
                                   </tr>
                                   @endforeach
                              </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
