@if(!empty($flyers))
{{-- Modal de flyers: por visita muestra 1 slide (escritorio 2x2 = 4 flyers, movil apilados = 2).
     Rota sin repetir usando una cookie con los IDs ya mostrados; al agotar los ~120 reinicia.
     Se muestra SIEMPRE al cargar la pagina (cada carga avanza al siguiente lote). Orden fijo (el backend ya ordena por 'orden'). --}}
<style>
    #flyers-overlay { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center;
        background:rgba(0,0,0,0.82); backdrop-filter:blur(3px); -webkit-backdrop-filter:blur(3px); padding:18px; }
    #flyers-overlay .flyers-box { position:relative; display:flex; flex-direction:row; flex-wrap:wrap;
        gap:14px; justify-content:center; align-items:center; max-width:800px; max-height:92vh; }
    #flyers-overlay .flyer-cell { flex:0 0 auto; width:min(370px,40vh); height:min(370px,40vh);
        border-radius:12px; overflow:hidden; box-shadow:0 8px 28px rgba(0,0,0,0.45); background:#fff; display:block; }
    #flyers-overlay .flyer-cell img { width:100%; height:100%; object-fit:cover; display:block; }
    #flyers-close { position:absolute; top:-14px; right:-14px; z-index:3; width:40px; height:40px; border:none;
        border-radius:50%; background:#fff; color:#222; font-size:24px; line-height:38px; cursor:pointer;
        box-shadow:0 2px 10px rgba(0,0,0,0.35); }
    @media (max-width:767px){
        #flyers-overlay .flyers-box { flex-direction:column; flex-wrap:nowrap; max-width:100%; }
        #flyers-overlay .flyer-cell { width:min(86vw,43vh); height:min(86vw,43vh); }
        #flyers-close { top:-6px; right:-6px; }
    }
</style>

<div id="flyers-overlay" role="dialog" aria-modal="true" aria-label="Promociones">
    <div class="flyers-box">
        <button id="flyers-close" type="button" aria-label="Cerrar">&times;</button>
    </div>
</div>

<script>
(function () {
    var FLYERS = @json($flyers);
    if (!Array.isArray(FLYERS) || FLYERS.length === 0) return;

    var overlay = document.getElementById('flyers-overlay');
    var box = overlay ? overlay.querySelector('.flyers-box') : null;
    if (!overlay || !box) return;

    // Cuantos por dispositivo: escritorio 4 (2x2), movil 2 (apilados)
    var esMovil = window.matchMedia('(max-width: 767px)').matches;
    var N = esMovil ? 2 : 4;

    function getCookie(name) { var m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)'); return m ? m.pop() : ''; }
    function setCookie(name, val, dias) { var d = new Date(); d.setTime(d.getTime() + dias * 86400000); document.cookie = name + '=' + val + '; expires=' + d.toUTCString() + '; path=/'; }

    // IDs ya mostrados (rotacion sin repetir)
    var vistos = (getCookie('flyers_vistos') || '').split(',').filter(Boolean);
    var noVistos = FLYERS.filter(function (f) { return vistos.indexOf(String(f.id)) === -1; });

    // Si ya vio todos, reinicia el ciclo
    if (noVistos.length === 0) { vistos = []; noVistos = FLYERS.slice(); }

    var lote = noVistos.slice(0, N); // orden fijo (el backend ya viene ordenado)
    if (lote.length === 0) return;

    // Registrar los mostrados (cookie 1 año) + marcar la visita
    lote.forEach(function (f) { vistos.push(String(f.id)); });
    setCookie('flyers_vistos', vistos.join(','), 365);

    // Pintar las celdas del lote (solo estas imagenes se cargan)
    lote.forEach(function (f) {
        var cell = document.createElement(f.enlace ? 'a' : 'div');
        cell.className = 'flyer-cell';
        if (f.enlace) { cell.href = f.enlace; cell.target = '_blank'; cell.rel = 'noopener'; cell.style.cursor = 'pointer'; }
        var img = document.createElement('img');
        img.src = f.url;
        img.alt = f.titulo || 'Promocion';
        cell.appendChild(img);
        box.appendChild(cell);
    });

    function cerrar() { overlay.style.display = 'none'; document.removeEventListener('keydown', onKey); }
    function onKey(e) { if (e.key === 'Escape') cerrar(); }
    document.getElementById('flyers-close').addEventListener('click', cerrar);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) cerrar(); });
    document.addEventListener('keydown', onKey);

    overlay.style.display = 'flex';
})();
</script>
@endif
