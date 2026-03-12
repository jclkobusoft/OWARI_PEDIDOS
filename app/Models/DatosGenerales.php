<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosGenerales extends Model
{
    protected $connection= 'mysql';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'datos_generales';

    /**
     * Fields that can be mass assigned.
     *
     * @var array
     */
    protected $fillable = [
        'contenido_empresa',
        'logotipo_general',
        'logotipo_email',
        'icono_facebook',
        'url_facebook',
        'icono_instagram',
        'url_instagram',
        'icono_twitter',
        'url_twitter',
        'icono_youtube',
        'url_youtube',
        'icono_pinterest',
        'url_pinterest',
        'email_contacto',
        'telefono_1',
        'marcar_1',
        'telefono_2',
        'marcar_2',
        'telefono_3',
        'marcar_3',
        'direccion_1',
        'direccion_2',
        'direccion_3',
        'horarios',
        'aviso_privacidad',
        'terminos_uso',
        'habilitar_pop_up',
        'pop_up',
        'inicio_adicional_arriba',
        'habilitar_nuevos_lanzamientos',
        'habilitar_mas_vendido',
        'habilitar_promociones',
        'habilitar_notinikko',
        'habilitar_boletines',
        'inicio_adicional_abajo',
        'soporte_boletines',
        'soporte_videos',
        'soporte_buzon',
        'soporte_buzon_email',
        'soporte_habilitar_chat',
        'bolsa_trabajo',
        'bolsa_trabajo_email',
        'contacto_telefono_1',
        'contacto_telefono_2',
        'contacto_telefono_3',
        'contacto_direccion_1',
        'contacto_direccion_2',
        'contacto_direccion_3',
        'contacto_email_1',
        'contacto_email_2',
        'contacto_email_3',
        'contacto_horario_1',
        'contacto_horario_2',
        'contacto_horario_3',
        'contacto_latitud_marcador',
        'contacto_longitud_marcador',
        'contacto_latitud_centrado',
        'contacto_longitud_centrado',
        'contacto_zoom_centrado',
        'contacto_email_envio',
        'promociones',
        'notinikko',
        'color_pagina',
        'titulo_bienvenida',
        'subtitulo_bienvenida',
        'texto_bienvenida',
        'imagen_bienvenida',
        'titulo_marcas',
        'texto_marcas',
        'titulo_boletines',
        'texto_boletines',
        'titulo_catalogos',
        'texto_catalogos',
        'titulo_productos',
        'texto_productos',
        'imagen_conviertete_distribuidor',
        'nosotros_banner',
        'nosotros_historia_titulo',
        'nosotros_historia_texto',
        'nosotros_imagen_video_historia',
        'nosotros_url_video_historia',
        'nosotros_titulo_video_historia',
        'mision',
        'vision',
        'valores',
        'nosotros_numeros_experiencia',
        'nosotros_numeros_productos',
        'nosotros_numeros_socios',
        'nosotros_numeros_marcas',
        'nosotros_numeros_empleados',
        'nosotros_numeros_almacenes',
        'nosotros_lema',
        'nosotros_imagen_lema',
        'distribuidor',
        'descripcion_footer',
        'imagen_footer'
    ];
}
