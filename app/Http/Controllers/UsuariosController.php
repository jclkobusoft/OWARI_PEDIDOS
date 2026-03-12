<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use Illuminate\Http\Request;

use App\Models\User;

class UsuariosController extends Controller
{
    //
    public function index(UsersDataTable $dataTable)
    {
        if(!\Auth::user()->can('usuarios_ver'))
            abort(403, 'No tienes autorizacion');

        return $dataTable->render('usuarios.index');
    }

    public function agregar(){
        if(!\Auth::user()->can('usuarios_crear'))
            abort(403, 'No tienes autorizacion');
        

        return view('usuarios.agregar');
    }

    public function crear(Request $r){
        if(!\Auth::user()->can('usuarios_crear'))
            abort(403, 'No tienes autorizacion');

        extract($r->all());

        $usuario = User::where('email',$email)->first();
        if($usuario){
            return \Redirect::back()->withInput()->withErrors(['msg' => 'El email ingresado ya esta registrado con otro usuario.']);
        }
        $usuario = User::create([
            'name' => $name,
            'email' => $email,
            'password' => \Hash::make($password),
            'vendedor_sae' => $vendedor_sae,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
        $usuario->givePermissionTo($permisos);

        return redirect()->back()->with('success', 'El usuario fue creado con exito.');



    }

    public function editar($usuario){
        if(!\Auth::user()->can('usuarios_editar'))
            abort(403, 'No tienes autorizacion');


        $usuario = User::find($usuario);
        return view('usuarios.editar',compact('usuario'));
    }

    public function actualizar(Request $r,$usuario){
        if(!\Auth::user()->can('usuarios_editar'))
            abort(403, 'No tienes autorizacion');    

        extract($r->all());
        $usuario = User::find($usuario);
        $usuario->fill([
            'name' => $name,
            'email' => $email,
            'vendedor_sae' => $vendedor_sae
        ])->save();

        if(!isset($permisos))
            $permisos = [];

        $usuario->syncPermissions($permisos);

        if(!is_null($password))
            $usuario->fill(['password' => \Hash::make($password)])->save();

        return redirect()->back()->with('success', 'Los cambios se guardaron con exito.');

    }

    public function eliminar(Request $r){
         if(!\Auth::user()->can('usuarios_eliminar'))
            abort(403, 'No tienes autorizacion');

        extract($r->all());
        $usuario=User::find($usuario);
        $usuario->syncPermissions([]);
        $usuario->delete();

        return json_encode([
            'code' => 1,
            'mensaje' => 'El usuario fue eliminado correctamente'
        ]);
    }

}
