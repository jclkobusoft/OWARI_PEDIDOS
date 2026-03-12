<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermisosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        Permission::create(['name' => 'usuarios_ver']);
        Permission::create(['name' => 'usuarios_crear']);
        Permission::create(['name' => 'usuarios_editar']);
        Permission::create(['name' => 'usuarios_eliminar']);

        Permission::create(['name' => 'clientes_ver']);
        Permission::create(['name' => 'clientes_crear']);
        Permission::create(['name' => 'clientes_editar']);
        Permission::create(['name' => 'clientes_eliminar']);

        Permission::create(['name' => 'pedidos_ver']);
        Permission::create(['name' => 'pedidos_crear']);
        Permission::create(['name' => 'pedidos_editar']);
        Permission::create(['name' => 'pedidos_eliminar']);


        Permission::create(['name' => 'etiquetas_producto']);
        Permission::create(['name' => 'etiquetas_paquetes']);
        Permission::create(['name' => 'etiquetas_compras']);

        echo "Listo";
    }
}
