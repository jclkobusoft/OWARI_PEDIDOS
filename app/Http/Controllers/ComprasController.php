<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CarritoExcelImport;

use App\Models\DatosGenerales;
use App\Models\User;
use App\Models\Registrado;
use App\Models\ProductoBusqueda;
use App\Models\Favorito;
use App\Models\PedidoWeb;
use App\Models\PedidoPartida;
use App\Models\Cliente;
use App\Models\PedidoEspecial;
use App\Models\PedidoEspecialSae;
use App\Models\PedidoEspecialPartida;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PlantillaNuevosProductosExport;
use App\Exports\PlantillaRequisicionExport;
use Maatwebsite\Excel\Facades\Excel;

class ComprasController extends Controller
{
    
    public function plantillaProductosNuevos(){
        return view('compras.plantilla_nuevos');
    }
    public function plantillaCapturaCompra(){
        return view('compras.plantilla_requisicion');
    }
    public function excelProductosNuevos(Request $request){

        $request->validate([
            'archivo' => 'required|file|mimes:xml|max:5120', // 5MB máximo
        ]);

        if ($request->file('archivo')->isValid()) {
            $file = $request->file('archivo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $filename, 'public');
            $cfdiArray = $this->leerCfdi40(\Storage::disk('public')->path($path));
            $generado = $this->generarPlantillaNuevosProductos($cfdiArray);

            $archivo = "PN_".$cfdiArray["Emisor"]["Rfc"]."_".$cfdiArray["Comprobante"]["Folio"].".xlsx";
            $archivo_excel = "compras/".$archivo;

            
            $export = new PlantillaNuevosProductosExport($generado);
        	Excel::store($export, $archivo_excel);

            

            return response()->json(['archivo' => $archivo,'message' => 'Tu archivo se descargara automaticamente']);
        }

        return response()->json(['message' => 'Archivo no válido'], 422);

    }

    public function excelRequisicion(Request $request){

        $request->validate([
            'archivo' => 'required|file|mimes:xml|max:5120', // 5MB máximo
        ]);

        if ($request->file('archivo')->isValid()) {
            $file = $request->file('archivo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $filename, 'public');
            $cfdiArray = $this->leerCfdi40(\Storage::disk('public')->path($path));
            $generado = $this->generaPlantillaRequisicion($cfdiArray);

            $archivo = "R_".$cfdiArray["Emisor"]["Rfc"]."_".$cfdiArray["Comprobante"]["Folio"].".xlsx";
            $archivo_excel = "compras/".$archivo;
            
            $export = new PlantillaRequisicionExport($generado);
        	Excel::store($export, $archivo_excel);

            

            return response()->json(['archivo' => $archivo,'message' => 'Tu archivo se descargara automaticamente']);
        }

        return response()->json(['message' => 'Archivo no válido'], 422);

    }


    private function generaPlantillaRequisicion($cfdiArray){
        
        $rfc = $cfdiArray["Emisor"]["Rfc"];

        $urlProveedor = 'https://sistemasowari.com:8443/catalowari/api/proveedor?rfc='.$rfc;
        $urlProducto = 'https://sistemasowari.com:8443/catalowari/api/producto?clave=';

        // Obtener los datos
        $data = file_get_contents($urlProveedor);

        
        // Verificar si se obtuvieron datos correctamente
        $proveedor = json_decode($data);
        if(isset($proveedor->error))
            return ['message' => 'No existe el proveedor en SAE'];

        $arreglo = [[
            'Clave OWARI',
            'Cantidad',
            'Precio',
            'Factura',
            'Proveedor',
            'Fecha',
        ]];

        foreach($cfdiArray["Conceptos"] as $key => $value){
            
            $productoBusqueda = ProductoBusqueda::where('clave_producto_proveedor',$value["NoIdentificacion"])->where('proveedor',$proveedor->CLAVE)->first();
            if($productoBusqueda)
                $dataSAE = file_get_contents($urlProducto.urlencode($productoBusqueda->codigo_nikko));
            else
                $dataSAE = file_get_contents($urlProducto.urlencode($value['NoIdentificacion']));
            
            
            $productoSAE = json_decode($dataSAE);
            // Verificar si se obtuvieron datos correctamente
            if(isset($productoSAE->error))
                $productoSAE = false;

            if($productoSAE && $productoBusqueda){
                $data = [
                    'Clave OWARI' => $productoBusqueda->codigo_nikko,
                    'Cantidad' => $value['Cantidad'],
                    'Precio' => $value['ValorUnitario'],
                    'Factura' => $cfdiArray['Comprobante']['Folio'],
                    'Proveedor' => $proveedor->CLAVE,
                    'Fecha' => date('d/m/Y'),
                ];
            }
            elseif($productoSAE){
                $data = [
                    'Clave OWARI' => $productoSAE->CVE_ART,
                    'Cantidad' => $value['Cantidad'],
                    'Precio' => $value['ValorUnitario'],
                    'Factura' => $cfdiArray['Comprobante']['Folio'],
                    'Proveedor' => $proveedor->CLAVE,
                    'Fecha' => date('d/m/Y'),
                ];
            }
            else{
                $data = [
                    'Clave OWARI' => $value['NoIdentificacion'].' (NO EXISTE EN SAE)',
                    'Cantidad' => $value['Cantidad'],
                    'Precio' => $value['ValorUnitario'],
                    'Factura' => $cfdiArray['Comprobante']['Folio'],
                    'Proveedor' => $rfc,
                    'Fecha' => date('d/m/Y'),
                ];
            }

            array_push($arreglo,$data);
        }

        return $arreglo;

    }

    private function generarPlantillaNuevosProductos($cfdiArray){
        $rfc = $cfdiArray["Emisor"]["Rfc"];

        $urlProveedor = 'https://sistemasowari.com:8443/catalowari/api/proveedor?rfc='.$rfc;
        $urlProducto = 'https://sistemasowari.com:8443/catalowari/api/producto?clave=';

        // Obtener los datos
        $data = file_get_contents($urlProveedor);

        
        // Verificar si se obtuvieron datos correctamente
        $proveedor = json_decode($data);
        if(isset($proveedor->error))
            return ['message' => 'No existe el proveedor en SAE'];

        $arreglo = [[
            'Clave',
            'Descripcion',
            'Linea',
            'Ubicacion',
            'Codigo de barras',
            'Clave SAT',
            'Unidad SAT',
            'Costo',
            'Precio publico',
            'Precio 2',
            'Precio 3',
            'Precio 11',
            'Precio 12',
            'Precio 13',
            'Precio 14',
            'Precio 15',
            'Precio 16',
            'Precio 17',
        ]];

        foreach($cfdiArray["Conceptos"] as $key => $value){
            
            $productoBusqueda = ProductoBusqueda::where('clave_producto_proveedor',$value["NoIdentificacion"])->where('proveedor',$proveedor->CLAVE)->first();
            if($productoBusqueda)
                $dataSAE = file_get_contents($urlProducto.urlencode($productoBusqueda->codigo_nikko));
            else
                $dataSAE = file_get_contents($urlProducto.urlencode($value['NoIdentificacion']));
            
            
            $productoSAE = json_decode($dataSAE);
            // Verificar si se obtuvieron datos correctamente
            if(isset($productoSAE->error))
                $productoSAE = false;


            if($productoBusqueda && !$productoSAE){
                //producto esta en web pero no existe en sae

                $costo = $value["ValorUnitario"];
                $publico = $value["ValorUnitario"] * ($productoBusqueda->utilidad ? 1+($productoBusqueda->utilidad/100) : 1);

                $precio_menos_20 = $publico * 0.8;
                $precio_menos_15 = $publico * 0.85;
                $precio_menos_10 = $publico * 0.9;
                $precio_menos_17 = $publico * 0.83;
                $precio_menos_15_menos_5 = ($publico * 0.85) * 0.95;

                $precio_2 = $precio_menos_20;
                $precio_3 = $precio_menos_20;
                $precio_11 = $precio_menos_20;
                $precio_12 = $precio_menos_15;
                $precio_13 = $precio_menos_20;
                $precio_14 = $precio_menos_20;
                $precio_15 = $precio_menos_10;
                $precio_16 = $precio_menos_17;
                $precio_17 = $precio_menos_15_menos_5;




                $data = [
                    'Clave'=> substr($productoBusqueda->codigo_nikko.($productoBusqueda->subfijo ? '-'.$productoBusqueda->subfijo: ''),0,16),
                    'Descripcion'=> substr($value['Descripcion'],0,40),
                    'Linea'=> $productoBusqueda->linea,
                    'Ubicacion'=> '',
                    'Codigo de barras'=> $productoBusqueda->codigo_nikko.($productoBusqueda->subfijo ? '-'.$productoBusqueda->subfijo: ''),
                    'Clave SAT'=> $value['ClaveProdServ'],
                    'Unidad SAT'=> $value['ClaveUnidad'],
                    'Costo' => number_format($costo,2,'.',''),
                    'Precio publico'=> number_format($publico,2,'.',''),
                    'Precio 2' => number_format($precio_2,2,'.',''),
                    'Precio 3' => number_format($precio_3,2,'.',''),
                    'Precio 11' => number_format($precio_11,2,'.',''),
                    'Precio 12' => number_format($precio_12,2,'.',''),
                    'Precio 13' => number_format($precio_13,2,'.',''),
                    'Precio 14' => number_format($precio_14,2,'.',''),
                    'Precio 15' => number_format($precio_15,2,'.',''),
                    'Precio 16' => number_format($precio_16,2,'.',''),
                    'Precio 17' => number_format($precio_17,2,'.',''),
                ];
            }
            elseif ($productoBusqueda && $productoSAE) {
                # code...
                //producto esta en web y tambien esta en SAE
                $data = [
                    'Clave'=> $productoBusqueda->codigo_nikko,
                    'Descripcion'=> 'SAE',
                    'Linea'=> 'SAE',
                    'Ubicacion'=> 'SAE',
                    'Codigo de barras'=> 'SAE',
                    'Clave SAT'=> 'SAE',
                    'Unidad SAT'=> 'SAE',
                    'Costo'=> 'SAE',
                    'Precio publico'=> 'SAE',
                    'Precio 2' => 'SAE',
                    'Precio 3' => 'SAE',
                    'Precio 11' => 'SAE',
                    'Precio 12' => 'SAE',
                    'Precio 13' => 'SAE',
                    'Precio 14' => 'SAE',
                    'Precio 15' => 'SAE',
                    'Precio 16' => 'SAE',
                    'Precio 17' => 'SAE',
                ];
            }
            elseif(!$productoBusqueda){
                //agregarlo pero poniendole de nota que no existe ni en web ni en sae

                $costo = $value["ValorUnitario"];
                $publico = $value["ValorUnitario"];

                $precio_menos_20 = $publico * 0.8;
                $precio_menos_15 = $publico * 0.85;
                $precio_menos_10 = $publico * 0.9;
                $precio_menos_17 = $publico * 0.83;
                $precio_menos_15_menos_5 = ($publico * 0.85) * 0.95;

                $precio_2 = $precio_menos_20;
                $precio_3 = $precio_menos_20;
                $precio_11 = $precio_menos_20;
                $precio_12 = $precio_menos_15;
                $precio_13 = $precio_menos_20;
                $precio_14 = $precio_menos_20;
                $precio_15 = $precio_menos_10;
                $precio_16 = $precio_menos_17;
                $precio_17 = $precio_menos_15_menos_5;

                $data = [
                    'Clave'=> substr($value['NoIdentificacion'],0,16),
                    'Descripcion'=> substr($value['Descripcion'],0,40),
                    'Linea'=> 'NO EXISTE EN EXCEL WEB',
                    'Ubicacion'=> 'NO EXISTE EN EXCEL WEB',
                    'Codigo de barras'=> $value['NoIdentificacion'],
                    'Clave SAT'=> $value['ClaveProdServ'],
                    'Unidad SAT'=> $value['ClaveUnidad'],
                    'Costo' => number_format($costo,2,'.',''),
                    'Precio publico'=> number_format($publico,2,'.',''),
                    'Precio 2' => number_format($precio_2,2,'.',''),
                    'Precio 3' => number_format($precio_3,2,'.',''),
                    'Precio 11' => number_format($precio_11,2,'.',''),
                    'Precio 12' => number_format($precio_12,2,'.',''),
                    'Precio 13' => number_format($precio_13,2,'.',''),
                    'Precio 14' => number_format($precio_14,2,'.',''),
                    'Precio 15' => number_format($precio_15,2,'.',''),
                    'Precio 16' => number_format($precio_16,2,'.',''),
                    'Precio 17' => number_format($precio_17,2,'.',''),
                    
                ];
            }
            

            array_push($arreglo,$data);
        }

        return $arreglo;
    }



    private function leerCfdi40($rutaArchivo) {
        // Cargar el archivo XML
        $xml = simplexml_load_file($rutaArchivo);
        
        if ($xml === false) {
            throw new Exception("Error al leer el archivo XML");
        }
        
        // Registrar los namespaces del CFDI 4.0
        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('cfdi', $namespaces['cfdi']);
        $xml->registerXPathNamespace('tfd', $namespaces['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital');
        
        // Convertir a arreglo
        $arregloCfdi = [
            'Comprobante' => [
                'Version' => (string)$xml['Version'],
                'Serie' => (string)$xml['Serie'],
                'Folio' => (string)$xml['Folio'],
                'Fecha' => (string)$xml['Fecha'],
                'Sello' => (string)$xml['Sello'],
                'FormaPago' => (string)$xml['FormaPago'],
                'NoCertificado' => (string)$xml['NoCertificado'],
                'Certificado' => (string)$xml['Certificado'],
                'CondicionesDePago' => (string)$xml['CondicionesDePago'],
                'SubTotal' => (float)$xml['SubTotal'],
                'Descuento' => (float)$xml['Descuento'],
                'Moneda' => (string)$xml['Moneda'],
                'TipoCambio' => (float)$xml['TipoCambio'],
                'Total' => (float)$xml['Total'],
                'TipoDeComprobante' => (string)$xml['TipoDeComprobante'],
                'MetodoPago' => (string)$xml['MetodoPago'],
                'LugarExpedicion' => (string)$xml['LugarExpedicion'],
                'Confirmacion' => (string)$xml['Confirmacion']
            ],
            'Emisor' => [
                'Rfc' => (string)$xml->xpath('//cfdi:Emisor')[0]['Rfc'],
                'Nombre' => (string)$xml->xpath('//cfdi:Emisor')[0]['Nombre'],
                'RegimenFiscal' => (string)$xml->xpath('//cfdi:Emisor')[0]['RegimenFiscal']
            ],
            'Receptor' => [
                'Rfc' => (string)$xml->xpath('//cfdi:Receptor')[0]['Rfc'],
                'Nombre' => (string)$xml->xpath('//cfdi:Receptor')[0]['Nombre'],
                'DomicilioFiscalReceptor' => (string)$xml->xpath('//cfdi:Receptor')[0]['DomicilioFiscalReceptor'],
                'RegimenFiscalReceptor' => (string)$xml->xpath('//cfdi:Receptor')[0]['RegimenFiscalReceptor'],
                'UsoCFDI' => (string)$xml->xpath('//cfdi:Receptor')[0]['UsoCFDI']
            ],
            'Conceptos' => [],
            'Impuestos' => [],
            'Complemento' => [
                'TimbreFiscalDigital' => null
            ]
        ];
        
        // Procesar conceptos
        foreach ($xml->xpath('//cfdi:Conceptos/cfdi:Concepto') as $concepto) {
            $arregloCfdi['Conceptos'][] = [
                'ClaveProdServ' => (string)$concepto['ClaveProdServ'],
                'NoIdentificacion' => (string)$concepto['NoIdentificacion'],
                'Cantidad' => (float)$concepto['Cantidad'],
                'ClaveUnidad' => (string)$concepto['ClaveUnidad'],
                'Unidad' => (string)$concepto['Unidad'],
                'Descripcion' => (string)$concepto['Descripcion'],
                'ValorUnitario' => (float)$concepto['ValorUnitario'],
                'Importe' => (float)$concepto['Importe'],
                'Descuento' => (float)$concepto['Descuento']
            ];
        }
        
        // Procesar impuestos
        if ($xml->xpath('//cfdi:Impuestos')) {
            $impuestos = $xml->xpath('//cfdi:Impuestos')[0];
            
            $arregloCfdi['Impuestos'] = [
                'TotalImpuestosTrasladados' => (float)$impuestos['TotalImpuestosTrasladados'],
                'TotalImpuestosRetenidos' => (float)$impuestos['TotalImpuestosRetenidos'],
                'Traslados' => [],
                'Retenciones' => []
            ];
            
            // Traslados
            foreach ($xml->xpath('//cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado') as $traslado) {
                $arregloCfdi['Impuestos']['Traslados'][] = [
                    'Base' => (float)$traslado['Base'],
                    'Impuesto' => (string)$traslado['Impuesto'],
                    'TipoFactor' => (string)$traslado['TipoFactor'],
                    'TasaOCuota' => (float)$traslado['TasaOCuota'],
                    'Importe' => (float)$traslado['Importe']
                ];
            }
            
            // Retenciones
            foreach ($xml->xpath('//cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion') as $retencion) {
                $arregloCfdi['Impuestos']['Retenciones'][] = [
                    'Base' => (float)$retencion['Base'],
                    'Impuesto' => (string)$retencion['Impuesto'],
                    'TipoFactor' => (string)$retencion['TipoFactor'],
                    'TasaOCuota' => (float)$retencion['TasaOCuota'],
                    'Importe' => (float)$retencion['Importe']
                ];
            }
        }
        
        // Procesar complemento (Timbre Fiscal Digital)
        $tfd = $xml->xpath('//tfd:TimbreFiscalDigital');
        if ($tfd) {
            $tfd = $tfd[0];
            $arregloCfdi['Complemento']['TimbreFiscalDigital'] = [
                'Version' => (string)$tfd['Version'],
                'UUID' => (string)$tfd['UUID'],
                'FechaTimbrado' => (string)$tfd['FechaTimbrado'],
                'RfcProvCertif' => (string)$tfd['RfcProvCertif'],
                'SelloCFD' => (string)$tfd['SelloCFD'],
                'NoCertificadoSAT' => (string)$tfd['NoCertificadoSAT'],
                'SelloSAT' => (string)$tfd['SelloSAT']
            ];
        }
        
        return $arregloCfdi;
    }


    public function descargarPlantilla(Request $r){
        extract($r->all());
        $rutaArchivo = storage_path('app/compras/'.$archivo);
        
        // Verificar si el archivo existe
        if (!file_exists($rutaArchivo)) {
            abort(404, 'El archivo no existe');
        }
        
        // Descargar con el nombre original
        return response()->download($rutaArchivo);
    }
    
}
