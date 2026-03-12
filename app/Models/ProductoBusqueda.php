<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoBusqueda extends Model {
	//
	use SoftDeletes;
	 protected $connection= 'mysql';

	protected $table = 'productos_busqueda';
	/**
	 * Fields that can be mass assigned.
	 *
	 * @var array
	 */
	protected $fillable = [
		'marca_comercial',
		'codigo_nikko',
		'grupo',
		'subgrupo',
		'descripcion_1',
		'descripcion_2',
		'descripcion_3',
		'caracteristicas_1',
		'caracteristicas_2',
		'caracteristicas_3',
		'caracteristicas_4',
		'equivalencia_1',
		'equivalencia_2',
		'equivalencia_3',
		'equivalencia_4',
		'equivalencia_5',
		'oem',
		'armadora',
		'modelo',
		'generacion_mexico',
		'version',
		'ano_inicial',
		'ano_final',
		'litros',
		'unidad_litros',
		'cilindros',
		'unidad_cilindros',
		'bloqueo_motor',
		'motor',
		'aspiracion',
		'arbol_levas',
		'valvulas',
		'eje_transmision',
		'traccion_operacion',
		'especificacion',
		'anos',
		'buscador',
		'nuevo',
		'vendido',
		'promocion',
		'precio_normal',
		'precio_final',
		'extra',
		'pagina_principal',
		'invocacion',
		'pruebas_ilc',
		'existencias',
		'minimo_compra_oferta',
		'fecha_promocion_inicio',
		'fecha_promocion_final',
		'extra_clave_1',
		'extra_marca_1',
		'extra_clave_2',
		'extra_marca_2',
		'extra_clave_3',
		'extra_marca_3',
		'codigo_barras',
		'proveedor',
		'lo_mas_nuevo',
		'clave_producto_proveedor',
		'linea',
		'utilidad',
		'subfijo',
		'multiplo_compra',
		'ventas'
	];

}
