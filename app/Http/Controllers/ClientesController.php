<?php

namespace App\Http\Controllers;

use App\DataTables\ClientesDataTable;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\ProductoMasVendido;


class ClientesController extends Controller
{
    //
    public function index(ClientesDataTable $dataTable)
    {
        if(!\Auth::user()->can('clientes_ver'))
            abort(403, 'No tienes autorizacion');
        return $dataTable->render('clientes.index');
    }

    public function agregar(){
        if(!\Auth::user()->can('clientes_crear'))
            abort(403, 'No tienes autorizacion');
        

        return view('clientes.agregar');
    }

    public function crear(Request $r){
        if(!\Auth::user()->can('clientes_crear'))
            abort(403, 'No tienes autorizacion');

        extract($r->all());

        $cliente = User::where('email',$email)->first();
        if($cliente){
            return \Redirect::back()->withInput()->withErrors(['msg' => 'El email ingresado ya esta registrado con otro cliente.']);
        }
        $cliente = User::create([
            'name' => $name,
            'email' => $email,
            'password' => \Hash::make($password),
            'cliente' => true,
            'clave_cliente' => $clave_cliente,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
        $cliente->givePermissionTo([]);

        return redirect()->back()->with('success', 'El cliente fue creado con exito.');

    }

    public function editar($cliente){
        if(!\Auth::user()->can('clientes_editar'))
            abort(403, 'No tienes autorizacion');


        $cliente = User::find($cliente);
        return view('clientes.editar',compact('cliente'));
    }

    public function actualizar(Request $r,$cliente){
        if(!\Auth::user()->can('clientes_editar'))
            abort(403, 'No tienes autorizacion');    

        extract($r->all());
        $cliente = User::find($cliente);
        $cliente->fill([
            'name' => $name,
            'email' => $email,
            'clave_cliente' => $clave_cliente
        ])->save();

    
        if(!is_null($password))
            $cliente->fill(['password' => \Hash::make($password)])->save();

        return redirect()->back()->with('success', 'Los cambios se guardaron con exito.');

    }

    public function eliminar(Request $r){
         if(!\Auth::user()->can('clientes_eliminar'))
            abort(403, 'No tienes autorizacion');

        extract($r->all());
        $cliente=User::find($cliente);
        $cliente->syncPermissions([]);
        $cliente->delete();

        return json_encode([
            'code' => 1,
            'mensaje' => 'El usuario fue eliminado correctamente'
        ]);
    }

    public function ventas(){
        if(!\Auth::user()->can('clientes_ver_ventas'))
            abort(403, 'No tienes autorizacion');
        $productos = ProductoMasVendido::get()->pluck('clave')->toArray();
        //dd($productos);
        return view('clientes.ventas',compact('productos'));
    }

}
