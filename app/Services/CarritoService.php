<?php

namespace App\Services;

use App\Models\CarritoItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Acceso al carrito de la tienda en linea, ahora persistido en la tabla
 * carrito_items (antes en la sesion de archivo). Expone la MISMA forma de
 * arreglo que usaba la sesion, por lo que los controladores/vistas siguen
 * trabajando con el mismo shape:
 *
 *   [ ['numero_parte'=>..., 'cantidad'=>..., 'partida'=>..., 'sustituto'=>...], ... ]
 *
 * Todo se liga al usuario autenticado (Auth::id()). Los endpoints del carrito
 * estan bajo middleware 'auth', asi que siempre hay usuario.
 */
class CarritoService
{
    public const NORMAL   = 'normal';
    public const ESPECIAL = 'especial';

    private function userId(): ?int
    {
        return Auth::id();
    }

    /** Items del tipo dado, en el mismo shape que tenia el carrito de sesion. */
    public function obtener(string $tipo): array
    {
        $uid = $this->userId();
        if (!$uid) return [];

        return CarritoItem::where('user_id', $uid)
            ->where('tipo', $tipo)
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => $row->datos)
            ->all();
    }

    /** Numero de productos (filas) del tipo dado; para el badge del carrito. */
    public function contar(string $tipo): int
    {
        $uid = $this->userId();
        if (!$uid) return 0;

        return CarritoItem::where('user_id', $uid)->where('tipo', $tipo)->count();
    }

    /** ¿Hay al menos un item de ese tipo? (equivale a Session::has). */
    public function tiene(string $tipo): bool
    {
        $uid = $this->userId();
        if (!$uid) return false;

        return CarritoItem::where('user_id', $uid)->where('tipo', $tipo)->exists();
    }

    /**
     * Reemplaza TODOS los items de ese tipo por los del arreglo dado
     * (equivale a Session::put('cart'|'cartEspecial', $items)). Deduplica por
     * numero_parte (gana el ultimo) y respeta huecos que deja unset().
     */
    public function guardar(string $tipo, array $items): void
    {
        $uid = $this->userId();
        if (!$uid) return;

        $porClave = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $clave = $it['numero_parte'] ?? null;
            if ($clave === null || $clave === '') continue;
            $porClave[(string) $clave] = $it;
        }

        DB::transaction(function () use ($uid, $tipo, $porClave) {
            CarritoItem::where('user_id', $uid)->where('tipo', $tipo)->delete();
            foreach ($porClave as $clave => $it) {
                CarritoItem::create([
                    'user_id'      => $uid,
                    'tipo'         => $tipo,
                    'numero_parte' => (string) $clave,
                    'cantidad'     => (int) ($it['cantidad'] ?? 0),
                    'datos'        => $it,
                ]);
            }
        });
    }

    /** Vacia un solo tipo (normal o especial). */
    public function vaciarTipo(string $tipo): void
    {
        $this->guardar($tipo, []);
    }

    /** Vacia por completo el carrito del usuario (normal + especial). */
    public function vaciarTodo(): void
    {
        $uid = $this->userId();
        if (!$uid) return;

        CarritoItem::where('user_id', $uid)->delete();
    }
}
