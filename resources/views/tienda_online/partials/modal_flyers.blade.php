@if(!empty($flyers))
<div id="flyers-overlay" role="dialog" aria-modal="true" aria-label="Promociones"
     style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.78); backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px); align-items:center; justify-content:center; padding:20px;">
    <div style="position:relative; width:auto; max-width:92vw; text-align:center;">

        <button id="flyers-close" type="button" aria-label="Cerrar"
                style="position:absolute; top:-16px; right:-16px; z-index:2; width:40px; height:40px; border:none; border-radius:50%; background:#fff; color:#222; font-size:24px; line-height:38px; cursor:pointer; box-shadow:0 2px 10px rgba(0,0,0,0.35);">&times;</button>

        <div id="flyers-carousel" style="position:relative; display:inline-block;">
            @foreach($flyers as $i => $f)
                <div class="flyer-slide" data-index="{{ $i }}" style="display:{{ $i === 0 ? 'block' : 'none' }};">
                    @if(!empty($f['enlace']))
                        <a href="{{ $f['enlace'] }}" target="_blank" rel="noopener" style="display:inline-block;">
                            <img src="{{ $f['url'] }}" alt="{{ $f['titulo'] ?? 'Promocion' }}"
                                 style="display:block; max-width:92vw; max-height:84vh; width:auto; height:auto; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.5); cursor:pointer;" />
                        </a>
                    @else
                        <img src="{{ $f['url'] }}" alt="{{ $f['titulo'] ?? 'Promocion' }}"
                             style="display:block; max-width:92vw; max-height:84vh; width:auto; height:auto; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,0.5);" />
                    @endif
                </div>
            @endforeach

            @if(count($flyers) > 1)
                <button id="flyers-prev" type="button" aria-label="Anterior"
                        style="position:absolute; top:50%; left:10px; transform:translateY(-50%); width:44px; height:44px; border:none; border-radius:50%; background:rgba(255,255,255,0.9); color:#222; font-size:26px; line-height:40px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.3);">&lsaquo;</button>
                <button id="flyers-next" type="button" aria-label="Siguiente"
                        style="position:absolute; top:50%; right:10px; transform:translateY(-50%); width:44px; height:44px; border:none; border-radius:50%; background:rgba(255,255,255,0.9); color:#222; font-size:26px; line-height:40px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.3);">&rsaquo;</button>
            @endif
        </div>

        @if(count($flyers) > 1)
            <div id="flyers-dots" style="margin-top:14px; display:flex; gap:8px; justify-content:center;">
                @foreach($flyers as $i => $f)
                    <span class="flyer-dot" data-index="{{ $i }}"
                          style="width:10px; height:10px; border-radius:50%; background:{{ $i === 0 ? '#fff' : 'rgba(255,255,255,0.45)' }}; cursor:pointer; display:inline-block;"></span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    function getCookie(name) {
        var m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return m ? m.pop() : '';
    }
    // Si ya cerro los flyers, no se muestran hasta que expire la cookie (24 h).
    // --- PRUEBAS: desactivado para que el modal aparezca SIEMPRE al entrar. ---
    // Para reactivar el ciclo de 24 h, descomenta la siguiente linea:
    // if (getCookie('flyers_vistos') === '1') return;

    var overlay = document.getElementById('flyers-overlay');
    if (!overlay) return;

    var slides = [].slice.call(overlay.querySelectorAll('.flyer-slide'));
    var dots   = [].slice.call(overlay.querySelectorAll('.flyer-dot'));
    var idx = 0;

    function show(i) {
        idx = (i + slides.length) % slides.length;
        slides.forEach(function (s, j) { s.style.display = (j === idx ? 'block' : 'none'); });
        dots.forEach(function (d, j) { d.style.background = (j === idx ? '#fff' : 'rgba(255,255,255,0.45)'); });
    }

    function cerrar() {
        overlay.style.display = 'none';
        var d = new Date();
        d.setTime(d.getTime() + 24 * 60 * 60 * 1000); // 24 horas
        document.cookie = 'flyers_vistos=1; expires=' + d.toUTCString() + '; path=/';
        document.removeEventListener('keydown', onKey);
    }

    function onKey(e) {
        if (e.key === 'Escape') cerrar();
        else if (e.key === 'ArrowLeft' && slides.length > 1) show(idx - 1);
        else if (e.key === 'ArrowRight' && slides.length > 1) show(idx + 1);
    }

    document.getElementById('flyers-close').addEventListener('click', cerrar);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) cerrar(); });
    document.addEventListener('keydown', onKey);

    var prev = document.getElementById('flyers-prev');
    var next = document.getElementById('flyers-next');
    if (prev) prev.addEventListener('click', function () { show(idx - 1); });
    if (next) next.addEventListener('click', function () { show(idx + 1); });
    dots.forEach(function (d) {
        d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-index'), 10)); });
    });

    overlay.style.display = 'flex';
})();
</script>
@endif
