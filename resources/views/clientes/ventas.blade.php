@extends('layouts.app')
@section('content')
<style>
    #tablaNoComp,
    #tablaComp{
        font-size:9px;
    }
</style>
<div class="container">
    <div class="card">
        <div class="card-header">Ventas cliente / Productos mas vendidos</div>
        <div class="card-body">
            <h4>Selecciona un cliente para ver la información</h4>
            <div class="container py-3">
  <h5>Clientes</h5>

  <div id="alerta" class="alert alert-danger d-none"></div>

  <div id="loading" class="d-flex align-items-center">
    <div class="spinner-border me-2" role="status" aria-hidden="true"></div>
    <span>Cargando…</span>
  </div>

  <div class="table-responsive d-none" id="tablaWrap">
    <table class="table table-striped table-hover table-sm" id="tablaClientes">
      <thead><tr><th>Clave</th><th>Nombre</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="clienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="clienteModalTitle">Detalle del cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="modalError" class="alert alert-danger d-none"></div>

        <div id="modalLoading" class="d-flex align-items-center">
          <div class="spinner-border me-2" role="status" aria-hidden="true"></div>
          <span>Cargando…</span>
        </div>

        <div class="table-responsive d-none" id="modalTablaWrap">
          <div id="modalTablas" class="d-none">
            <div class="row g-3">
                <div class="col-md-12 justify-end">
                    <button id="btnPDF" class="btn btn-sm btn-danger">
                        Descargar PDF
                    </button>
                    <button onclick="toExcel()" class="btn btn-sm btn-success">Descargar Excel</button>
                    <script>
                        function toExcel() {
                            const wb = XLSX.utils.table_to_book(document.getElementById('tablaNoComp'), {sheet: 'Hoja1'});
                            XLSX.writeFile(wb, $('#clienteModal').data('clave')+'.xlsx');
                        }
                        
                    </script>
                </div>
                <div class="col-md-6">
                <h4 class="mb-2">Sin compras</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="tablaNoComp">
                    <thead><tr><th>Clave</th><th>Descripción</th><th>Precio</th></tr></thead>
                    <tbody></tbody>
                    </table>
                </div>
                </div>
                <div class="col-md-6">
                <h4 class="mb-2">Con compras</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="tablaComp">
                    <thead>
                        <tr><th>Clave</th><th>Descripción</th><th>Última fecha</th><th>Última cant.</th><th>Precio</th></tr>
                    </thead>
                    <tbody></tbody>
                    </table>
                </div>
                </div>
            </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>



       <script>
       $(function () {
  const LIST_URL   = 'https://sistemasowari.com:8443/catalowari/api/clientes_factura?vendedor=';               // cambia a tu endpoint
  const DETAIL_URL = 'https://sistemasowari.com:8443/catalowari/api/venta_productos';       // GET ?clave=C083M (ajústalo)

  const $tbody  = $('#tablaClientes tbody');
  const $wrap   = $('#tablaWrap');
  const $load   = $('#loading');
  const $alerta = $('#alerta');

  // Carga inicial: lista de clientes
  $.ajax({
    url: LIST_URL,
    method: 'GET',
    dataType: 'json',
    cache: false
  })
  .done(function (resp) {
    const rows = Array.isArray(resp) ? resp : (resp?.data || []);
    const frag = document.createDocumentFragment();

    rows.forEach(it => {
      const tr  = document.createElement('tr');

      const tdC = document.createElement('td');
      tdC.textContent = it?.clave ?? '';

      const tdN = document.createElement('td');
      const a   = document.createElement('a');
      a.href = '#';
      a.className = 'cliente-link';
      a.dataset.clave  = it?.clave ?? '';
      a.dataset.nombre = it?.nombre ?? '';
      a.textContent = it?.nombre ?? '';
      tdN.appendChild(a);

      tr.appendChild(tdC);
      tr.appendChild(tdN);
      frag.appendChild(tr);
    });

    $tbody.empty()[0].appendChild(frag);
    $wrap.removeClass('d-none');
  })
  .fail(function (xhr) {
    const msg = xhr.responseJSON?.message || xhr.statusText || 'Error al cargar';
    $alerta.text(`Error ${xhr.status || ''} ${msg}`).removeClass('d-none');
  })
  .always(function () { $load.addClass('d-none'); });

  // Delegación: abre modal y carga detalle
  

  $(document).on('click', '.cliente-link', function (e) {
    e.preventDefault();
    const clave  = this.dataset.clave || '';
    const nombre = this.dataset.nombre || '';

    $('#clienteModalTitle').text(`Cliente ${clave} — ${nombre}`);
    $('#clienteModal').data('clave', clave);
    showModal('#clienteModal');

    // reset UI modal
    $('#modalError').addClass('d-none').empty();
    $('#modalTablaWrap').addClass('d-none');
    $('#modalLoading').removeClass('d-none');
    $('#tablaDetalle thead tr').empty();
    $('#tablaDetalle tbody').empty();

    // pide detalle
    $.ajax({
      url: DETAIL_URL,
      method: 'GET',
      dataType: 'json',
      cache: false,
      data: { cliente: clave } // ajusta si tu API usa otra forma
    })
    .done(function (resp) {
        const data = Array.isArray(resp) ? resp : (resp?.data || []);
        renderDosTablas(data);
        $('#modalTablaWrap').removeClass('d-none');
        $('#modalTablas').removeClass('d-none');
    })
    .fail(function (xhr) {
      const msg = xhr.responseJSON?.message || xhr.statusText || 'Error';
      $('#modalError').text(`Error ${xhr.status || ''} ${msg}`).removeClass('d-none');
    })
    .always(function () {
      $('#modalLoading').addClass('d-none');
    });
  });

  // Helpers
  function renderTable(selector, arr, columns) {
    const $table = $(selector);
    const $thead = $table.find('thead tr').empty();
    const $tbody = $table.find('tbody').empty();

    const cols = (Array.isArray(columns) && columns.length)
      ? columns
      : inferColumns(arr);

    cols.forEach(c => $thead.append($('<th>').text(c)));

    const frag = document.createDocumentFragment();
    arr.forEach(row => {
      const tr = document.createElement('tr');
      cols.forEach(c => {
        const td = document.createElement('td');
        const v  = row?.[c];
        td.textContent = v == null ? '' : String(v);
        tr.appendChild(td);
      });
      frag.appendChild(tr);
    });
    $tbody[0].appendChild(frag);
  }

  function inferColumns(arr) {
    if (!Array.isArray(arr) || !arr.length) return [];
    // Une todas las llaves presentes, mantén orden estable
    const seen = new Set();
    arr.forEach(o => Object.keys(o || {}).forEach(k => seen.add(k)));
    return Array.from(seen);
  }

  function showModal(sel) {

    
    const el = document.querySelector(sel);
    if (window.bootstrap?.Modal) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    } else {
      $(sel).modal('show'); // Bootstrap 4
    }
  }

  function renderDosTablas(arr) {
  const no = [], si = [];
  arr.forEach(o => (Number(o?.COMPRADO) === 1 ? si : no).push(o));

  fill('#tablaNoComp tbody', no, ['CVE_ART','DESCR','PRECIO']);
  fill('#tablaComp tbody', si, ['CVE_ART','DESCR','ULTIMA_FECHA','ULTIMA_CANTIDAD','PRECIO']);
}

function fill(sel, rows, keys) {
  const tbody = document.querySelector(sel);
  const frag  = document.createDocumentFragment();

  if (!rows.length) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = keys.length;
    td.className = 'text-muted';
    td.textContent = 'Sin datos';
    tr.appendChild(td);
    frag.appendChild(tr);
  } else {
    rows.forEach(r => {
      const tr = document.createElement('tr');
      keys.forEach(k => {
        const td = document.createElement('td');
        let v = r?.[k];
        if (k === 'ULTIMA_FECHA') v = fmtFecha(v);
        if (k === 'ULTIMA_CANTIDAD') v = fmtCant(v);
        if (k === 'PRECIO') v = fmtMoney(v);
        td.textContent = v == null ? '' : String(v).trim();
        tr.appendChild(td);
      });
      frag.appendChild(tr);
    });
  }

  tbody.innerHTML = '';
  tbody.appendChild(frag);
}

function fmtFecha(s) {
  if (!s) return '';
  // "YYYY-MM-DD hh:mm:ss" -> "DD/MM/YYYY" sin zonas
  const d = s.slice(0,10).split('-'); // [YYYY,MM,DD]
  return `${d[2]}/${d[1]}/${d[0]}`;
}
function fmtCant(v) {
  if (v == null) return '';
  const n = Number(v);
  return Number.isFinite(n) ? n.toLocaleString('es-MX') : String(v);
}
function fmtMoney(v, opts = {}) {
  if (v == null || v === '') return '';
  const n = Number(v);
  if (!Number.isFinite(n)) return String(v);

  const {
    currency = 'MXN',
    minimumFractionDigits = 2,
    maximumFractionDigits = 2,
    useGrouping = true,
  } = opts;

  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency,
    minimumFractionDigits,
    maximumFractionDigits,
    useGrouping,
  }).format(n);
}


});

$(function () {
  $('#btnPDF').on('click', async function () {
    await document.fonts?.ready;

    const el  = document.getElementById('tablaNoComp');
    const opt = {
      margin: [20,12,20,12],                 // mm: top,right,bottom,left
      filename: $('#clienteModal').data('clave')+'.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true }, // mejor calidad y CORS
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['css','legacy'], avoid: ['.no-split','tr','td'] }
    };

    html2pdf().set(opt).from(el).save();
  });
});
    </script>
   
@endpush