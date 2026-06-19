@extends('layouts.app') @section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12 mt-2 seleccionar_cliente">
                                            <div class="mb-3 row">
                                                <label for="cliente" class="col-sm-2 col-form-label">Cliente:</label>
                                                <div class="col-sm-10 pt-2">
                                                    <select class="form-select form-select-lg" id="cliente" name="cliente">
                                                        <option value="-1">Selecciona o busca un cliente</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="datos_cliente"></div>

                                            {{-- Radios — ocultos al inicio. Aparecen debajo de los datos del cliente solo si CLASIFIC NO tiene W en pos.4. Asi el flujo de lectura es natural: cliente → sus datos → tipo de pedido. --}}
                                            <div class="row mt-3" id="radios_tipo_pedido" style="display:none;">
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="form-check-input tipo_cliente" type="radio" name="tipo_pedido"
                                                            id="pedido_normal" style="width: 20px; height: 20px;" value="normal">
                                                        <label class="form-check-label" for="pedido_normal"
                                                            style="font-size: 15px;margin:3px 0 0 5px;">
                                                            Pedido normal
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input tipo_cliente" type="radio" name="tipo_pedido"
                                                            id="pedido_todo_factura" style="width: 20px; height: 20px;" value="factura">
                                                        <label class="form-check-label" for="pedido_todo_factura"
                                                            style="font-size: 15px;margin:3px 0 0 5px;">
                                                            Pedido todo a factura
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <div class="card mostrar_busqueda">
                                                <div class="card-body">
                                                    <h5 class="card-title">Buscar producto</h5>
                                                    <p class="card-text">
                                                        Escribe la clave o descripción del producto y presiona
                                                        enter para buscar
                                                    </p>
                                                    <div class="input-group mb-3">
                                                        <input type="text" class="form-control" placeholder="Clave articulo"
                                                            aria-label="Palabras o clave articulo"
                                                            aria-describedby="boton-buscar" id="palabras_clave" />
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="boton-buscar">
                                                            Buscar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3 mostrar_resultados">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Productos</h5>
                                                    <div id="resultados_busqueda"></div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                                <div class="col-md-2">
                                    <div class="row">
                                        <div class="col-md-12" id="detalles_producto">
                                            <div id="resultados_producto"></div>
                                            <div class="agregar-cantidad">
                                                <div class="input-group mb-3">
                                                    <input type="number" class="form-control" placeholder="Cantidad"
                                                        aria-label="Cantidad de producto" aria-describedby="boton-buscar"
                                                        id="cantidad" />
                                                </div>
                                                <a href="#" class="btn btn-primary" id="agregar_partida">Agregar partida</a>
                                            </div>

                                        </div>
                                        <div id="galeria" class="mt-4"></div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="row partidas">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Partidas con existencia en almacen</h5>
                                                    <div class="row">
                                                        <div class="col-md-12 mt-3 mb-3">
                                                            <div class="input-group">
                                                                <input type="file" class="form-control" id="excel"
                                                                    aria-describedby="inputGroupFileAddon04"
                                                                    aria-label="Upload" />
                                                                <button class="btn btn-outline-secondary" type="button"
                                                                    id="subir_excel">Agregar partidas</button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div id="partidas"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row totales_estilos mt-5">
                                                        <div class="col-8">
                                                            <span class="float-end">Gran total:</span>
                                                        </div>
                                                        <div class="col-4">
                                                            <span class="gran_total_final float-end">$0.00</span>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-5">
                                                        <div class="col-12">
                                                            <label>Hora de entrega: (MOSTRADOR U HORA DE ENTREGA)</label>
                                                            <div class="input-group mb-3 col-md-6">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Su pedido" name="su_pedido" maxlength="20"
                                                                    id="su_pedido" />
                                                            </div>
                                                            <div class="d-flex justify-content-end">
                                                                <button type="button" class="btn btn-primary float-right"
                                                                    id="guardar">Generar pedido</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row partidas mt-3">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Pedido especial (se solicita a proveedor)</h5>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div id="partidas_especiales"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row totales_estilos mt-5">
                                                        <div class="col-8">
                                                            <span class="float-end">Gran total:</span>
                                                        </div>
                                                        <div class="col-4">
                                                            <span class="gran_total_final_especiales float-end">$0.00</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body texto_modal">
                </div>
                <div class="modal-footer-especiales" style="display: none;">
                    <button type="button" class="btn btn-secondary terminar d-none"
                        data-bs-dismiss="modal">Terminar</button>
                </div>
                <div style="width: 100%; text-align: center;display: none;" id="cargando">
                    <img src="/images/loading.gif" style="width: 100px;">
                </div>
                <div class="modal-footer">
                    <div style="width: 100%; text-align: center; display: none;" id="pedidos_finales">
                        <label id="guardar_pedido_1">Estamos guardando tu pedido E1.</label><br><br><br>
                        <label id="guardar_pedido_3">Estamos guardando tu pedido E3.</label><br><br>
                    </div>
                    <label>En caso de error no recaptures, baja tu excel y vuelve a subirlo</label>
                    <button type="button" class="btn btn-success" onclick="descargarExcel()">Obtener excel de mi
                        pedido</button>
                    <button type="button" class="btn btn-secondary reiniciar_pantalla d-none"
                        data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script src="/assets/chosen/docsupport/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="/assets/chosen/chosen.jquery.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.8.0/jszip.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.8.0/xlsx.js"></script>
    <script src=" https://cdnjs.cloudflare.com/ajax/libs/xls/0.7.4-a/xls.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script type="text/javascript" src="/assets/excel/excel-builder.dist.js"></script>
    <script type="text/javascript" src="/assets/downloadify/js/swfobject.js"></script>
    <script type="text/javascript" src="/assets/downloadify/js/downloadify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script>




        var resultados = [];
        var fila_seleccionada;
        var seleccion = false;
        var indice_seleccion;
        var producto_partida;
        var partidas = [];
        var gran_total = 0;
        var partidas_partidas_uno = [];
        var partidas_partidas_tres = [];
        var total_pedidos = 0;
        var partidas_especiales = [];
        var special_price = 0;


        $("#palabras_clave").focus(function (e) {
            seleccion = false;
        });

        window.addEventListener("keydown", function (e) {
            //console.log(fila_seleccionada);
            if (["ArrowUp"].indexOf(e.code) > -1) {
                if (fila_seleccionada.getPrevRow()) {
                    fila_seleccionada.getPrevRow().toggleSelect();
                    table.scrollToRow(fila_seleccionada.getPrevRow().getIndex(), "top", false);

                    seleccion = true;
                    indice_seleccion = fila_seleccionada.getPrevRow().getIndex();
                    reiniciarCantidad();
                }
                e.preventDefault();
            }
            if (["ArrowDown"].indexOf(e.code) > -1) {
                if (fila_seleccionada.getNextRow()) {
                    fila_seleccionada.getNextRow().toggleSelect();
                    table.scrollToRow(fila_seleccionada.getNextRow().getIndex(), "bottom", false);

                    seleccion = true;
                    indice_seleccion = fila_seleccionada.getNextRow().getIndex();
                    reiniciarCantidad();
                }
                e.preventDefault();
            }
            if (["Enter"].indexOf(e.code) > -1 && seleccion && e.target.id != "cantidad") {
                console.log(e);
                buscarProducto(clave_cliente, fila_seleccionada.getData().codigo, 1, fila_seleccionada.getData().equivalencias, fila_seleccionada.getData().precio_normal, fila_seleccionada.getData().clave_proveedor);
                e.preventDefault();
            }
            if (e.key === 'b' && e.ctrlKey) {
                // Add your code here
                $("#palabras_clave").val("").focus();
            }
        }, false);

        $('#guardar').click(function () {
            // Fase 12: el click ahora invoca el flujo v2 (orquestador async/await
            // con chunks, retry, regalos via SOMA, abstraccion de proveedores
            // especiales, etc.). El viejo guardar_pedido() fue eliminado.
            // La validacion de su_pedido se hace dentro de validarFormulario().
            guardar_pedido_v2();
        });

        var table = new Tabulator("#resultados_busqueda", {
            movableColumns: true,
            height: "350px",
            selectable: 1,
            data: resultados, //assign data to table
            columns: [ //Define Table Columns
                { title: "Marca", field: "marca_comercial", headerSort: false },
                { title: "Codigo", field: "codigo", headerSort: true },
                { title: "Descripción", field: "descripcion", headerSort: true },
                { title: "Grupo", field: "grupo", headerSort: false },
                { title: "Subgrupo", field: "subgrupo", headerSort: false },
                { title: "Marca", field: "marca", headerSort: false },
                { title: "Equivalencias", field: "equivalencias", headerSort: false },
                { title: "Coincidencias", field: "buscar", headerSort: false },
                { title: "Precio", field: "precio_normal", headerSort: false },
                { title: "Disponibilidad", field: "disponibilidad", headerSort: false }
            ],
            rowClick: function (e, row) {
                //console.log("Hola click");
                seleccion = true;
                indice_seleccion = row.getIndex();
                row.select();
                fila_seleccionada = row;
                $("#galeria").html('');
            }
        });

        table.on("rowSelected", function (row) {
            //console.log("hola seleccionada");
            seleccion = true;
            fila_seleccionada = row;
            $("#galeria").html('');
            reiniciarCantidad();
            special_price = fila_seleccionada.getData().precio_normal
            buscarProducto(clave_cliente, fila_seleccionada.getData().codigo, 1, fila_seleccionada.getData().equivalencias, fila_seleccionada.getData().precio_normal, fila_seleccionada.getData().clave_proveedor);
        });


        var table_partidas = new Tabulator("#partidas", {
            index: "codigo",
            data: partidas, //assign data to table
            layout: "fitColumns", //fit columns to width of table (optional)
            columns: [ //Define Table Columns
                { title: "Codigo", field: "codigo", headerSort: true },
                { title: "Descripción", field: "descripcion", headerSort: true },
                { title: "Cantidad", field: "cantidad", editor: "input", headerSort: false },
                { title: "Precio", field: "precio", headerSort: false },
                { title: "PrecioIVA", field: "precio_iva", visible: false },
                { title: "Total", field: "total", headerSort: false },
                {
                    formatter: "buttonCross", width: 40, align: "center", cellClick: function (e, cell) {
                        console.log("Parti antes: ");
                        console.log(partidas);
                        fila = cell.getRow().getData();
                        $.each(partidas, function (i, v) {

                            if (v.clave == fila.codigo) {
                                partidas.splice(i, 1);
                                return false;
                            }
                        });
                        console.log("Parti: ");
                        console.log(partidas);
                        cell.getRow().delete();
                        actualizarGranTotal();
                    }, headerSort: false
                },
            ]
        });


        table_partidas.on("cellEdited", function (cell) {
            console.log(cell.getRow().getData());
            actualizarPrecio(cell.getRow().getData().cantidad, cell.getRow().getData().codigo);
            actualizarGranTotal();
        });


        var table_partidas_especiales = new Tabulator("#partidas_especiales", {
            index: "codigo",
            data: partidas_especiales, //assign data to table
            layout: "fitColumns", //fit columns to width of table (optional)
            columns: [ //Define Table Columns
                { title: "Codigo", field: "codigo", headerSort: true },
                { title: "Cantidad", field: "cantidad", editor: "input", headerSort: false },
                { title: "Precio", field: "precio", headerSort: false },
                { title: "Total", field: "total", headerSort: false },
                { title: "SAE", field: "sae", headerSort: false },
                {
                    formatter: "buttonCross", width: 40, align: "center", cellClick: function (e, cell) {
                        console.log("Parti antes: ");
                        console.log(partidas_especiales);
                        fila = cell.getRow().getData();
                        $.each(partidas_especiales, function (i, v) {

                            if (v.clave == fila.codigo) {
                                partidas_especiales.splice(i, 1);
                                return false;
                            }
                        });
                        console.log("Parti: ");
                        console.log(partidas_especiales);
                        cell.getRow().delete();
                        actualizarGranTotalEspeciales();
                    }, headerSort: false
                },
            ]
        });


        table_partidas_especiales.on("cellEdited", function (cell) {
            console.log(cell.getRow().getData());
            actualizarPrecioEspecial(cell.getRow().getData().cantidad, cell.getRow().getData().codigo);
            actualizarGranTotalEspeciales();
        });


        let clientes, clave_cliente, empresa_cliente;


        $("#palabras_clave").keydown(function (e) {
            if (e.keyCode == 13) {
                $(this).blur();
                reiniciarCantidad();
                buscar(
                    $(this).val()
                );
            }
        });
        $("#boton-buscar").click(function (e) {
            reiniciarCantidad()
            buscar(
                $("#palabras_clave").val()
            );
        });


        $("#cantidad").keydown(function (e) {
            if (e.keyCode == 13) {
                if ($(this).val() != "") {
                    agregarPartida($(this).val());
                }
                else {
                    alert("Ingresa una cantidad para agregar al pedido");
                }
            }
        });

        $("#agregar_partida").click(function (e) {
            if ($('#cantidad').val() != "") {
                agregarPartida($('#cantidad').val());
            }
            else
                alert("Ingresa una cantidad para agregar al pedido");
        });


        function agregarPartida(cantidad) {
            cantidad = parseInt(cantidad);

            var disponible = fila_seleccionada.getData().disponibilidad;
            var claveProveedor = fila_seleccionada.getData().clave_proveedor || '';
            // Data-driven desde SOMA — NUNCA hardcodear claves de proveedor.
            // Si SOMA caido o el proveedor no esta registrado, el producto se
            // trata como normal (sin split por stock ficticio).
            var esSplitStock = esSplitPorStock(claveProveedor);
            var stockFicticio = obtenerStockFicticio(claveProveedor);
            console.log(disponible);
            var ya_existe = false;
            var obj = producto_partida;
            var precio = obj.precio_publico;
            var precio_iva = obj.precio_iva;

            precio = obj.descuentos[0].precio_lista;
            precio_iva = obj.descuentos[0].precio_iva;


            if (parseInt(cantidad) <= 0) {
                alert("Ingresa una cantidad mayor a cero.");
                return false;
            }

            let a_especial = 0;
            let a_syd = 0;   // legacy, conservado por compat — siempre 0 ahora

            if (esSplitStock) {
                // Proveedor con tipo_separacion='split_por_stock' (S227 y similares).
                // obj.existencia ya incluye `stockFicticio` unidades virtuales que
                // SOMA configura (se sumaron en buscarProducto).
                //
                // Regla: lo que cabe en obj.existencia (real + ficticio) va a
                // table_partidas; el excedente va a table_partidas_especiales con
                // clave_proveedor=S227. Luego separarPartidas() hace el split fino
                // dentro de table_partidas: stock_real → SAE, stock_ficticio → especial.
                //
                // Ej. obj.existencia=12 (real=10, fic=2), cantidad=20:
                //   table_partidas = 12, table_partidas_especiales = 8
                //   separarPartidas: SAE=10, especial[S227] = 2 + 8 = 10
                if (cantidad > obj.existencia) {
                    a_especial = cantidad - obj.existencia;
                    cantidad = obj.existencia;
                }
            } else if (cantidad > obj.existencia) {

                if (disponible != 'agotado') {
                    if (obj.cliente == "N/A") {
                        a_especial = cantidad;
                        cantidad = 0;
                    }
                    else {
                        if (confirm("No puedes agregar mas cantidad de la que hay en almacen.¿Deseas agregar la diferencia para un pedido especial?")) {
                            a_especial = cantidad - obj.existencia
                            cantidad = obj.existencia
                        }
                        else {
                            a_especial = 0;
                            cantidad = obj.existencia
                        }
                    }
                }
                else {
                    alert('Este producto está agotado en fábrica y no se agregara a pedido especial. Solo se vendera la cantidad disponible');
                    a_especial = 0
                    cantidad = obj.existencia
                }
            }

            if (a_especial > 0) {
                console.log(partidas_especiales);
                $.each(partidas_especiales, function (i, v) {
                    if (v.clave == producto_partida.clave) {
                        if (confirm("Este producto ya fue capturado, presiona OK para sumarle esta cantidad o presiona Cancelar para sustituir.")) {

                            var cantidad_final = parseInt(a_especial);
                            var total_partidas = table_partidas.getData();
                            $.each(total_partidas, function (j, k) {
                                if (k.codigo == v.clave)
                                    cantidad_final += parseInt(k.cantidad)
                            });
                            actualizarPrecioEspecial(cantidad_final, producto_partida.clave);
                        }
                        else {
                            actualizarPrecioEspecial(cantidad, producto_partida.clave);

                        }
                        ya_existe = true;
                        return false;
                    }
                });

                if (ya_existe)
                    return false;


                precio = obj.precio_publico;

                if (obj.descuentos.length > 1) {
                    for (i = obj.descuentos.length - 1; i >= 1; i--) {
                        //console.log("comenzamos = " + i);
                        if (obj.descuentos[i].tipo == "S") {
                            //console.log(obj.descuentos[i].tipo + " " + i);
                            if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                //console.log("cero unidades");
                                precio = obj.descuentos[i].precio_lista;
                                precio_iva = obj.descuentos[i].precio_iva;
                                break;
                            } else {
                                //console.log('si tiene cantidad');
                                //console.log("Minimas: "+obj.descuentos[i].unidades_minimas)
                                //console.log("Pido: "+ cantidad)
                                if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                    console.log("hay unidades y cubrimos");
                                    precio = obj.descuentos[i].precio_lista;
                                    precio_iva = obj.descuentos[i].precio_iva;
                                    break;
                                } else {
                                    continue;
                                }
                            }
                        }
                    }
                } else { precio = obj.descuentos[0].precio_lista; precio_iva = obj.descuentos[0].precio_iva; }



                let sae = 'EN SAE';
                if (obj.cliente == "N/A")
                    sae = "NO ESTA EN SAE"
                if (sae == "NO ESTA EN SAE") {

                    precio = special_price;
                    const first = obj?.clasificacion?.[0]?.toUpperCase?.() ?? '';
                    const mapa = { A: 0.85, B: 0.9, C: 0.95 };
                    const descuento = mapa[first] ?? 0.95;
                    console.log(descuento);
                    precio = precio * descuento * 1.16;
                }

                table_partidas_especiales.addData([{
                    "codigo": obj.clave,
                    "cantidad": a_especial,
                    "precio": parseFloat(precio).toFixed(2),
                    "total": (a_especial * precio).toFixed(2),
                    "sae": sae
                }]);
                partidas_especiales.push(obj);
                setTimeout(() => {
                    $("#galeria").html('')
                    $('#detalles_producto').hide();
                    $('#palabras_clave').val("").focus();
                }, 500);
                actualizarGranTotalEspeciales();
            }

            // Cantidad que se muestra en table_partidas. Para todos los casos
            // ya es la cantidad que cabe en obj.existencia (cualquier excedente
            // se mando a table_partidas_especiales arriba). a_syd se conserva en
            // 0 por compat — separarPartidas() es ahora quien hace el split fino.
            var cantidad_total_partida = parseInt(cantidad) + parseInt(a_syd);

            if (cantidad_total_partida > 0) {
                console.log(partidas);
                $.each(partidas, function (i, v) {
                    if (v.clave == producto_partida.clave) {
                        if (confirm("Este producto ya fue capturado, presiona OK para sumarle esta cantidad o presiona Cancelar para sustituir.")) {

                            var cantidad_final = parseInt(cantidad_total_partida);
                            var total_partidas = table_partidas.getData();
                            $.each(total_partidas, function (j, k) {
                                if (k.codigo == v.clave)
                                    cantidad_final += parseInt(k.cantidad)
                            });
                            actualizarPrecio(cantidad_final, producto_partida.clave);
                        }
                        else {
                            actualizarPrecio(cantidad_total_partida, producto_partida.clave);
                        }
                        ya_existe = true;
                        return false;
                    }
                });

                if (ya_existe)
                    return false;


                //console.log(obj);
                if (obj.descuentos.length > 1) {
                    for (i = obj.descuentos.length - 1; i >= 1; i--) {
                        //console.log("comenzamos = " + i);
                        if (obj.descuentos[i].tipo == "S") {
                            //console.log(obj.descuentos[i].tipo + " " + i);
                            if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                //console.log("cero unidades");
                                precio = obj.descuentos[i].precio_lista;
                                precio_iva = obj.descuentos[i].precio_iva;
                                break;
                            } else {
                                //console.log('si tiene cantidad');
                                if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad_total_partida)) {
                                    console.log("hay unidades y cubrimos");
                                    precio = obj.descuentos[i].precio_lista;
                                    precio_iva = obj.descuentos[i].precio_iva;
                                    break;
                                } else {
                                    continue;
                                }
                            }
                        }
                    }
                } else { precio = obj.descuentos[0].precio_lista; precio_iva = obj.descuentos[0].precio_iva; }

                // Fallback: si el producto no esta en SAE para el cliente o el precio resulta 0
                // (ej. S227 con existencia 0), usar precio_normal del catalogo con clasificacion
                if (obj.cliente == "N/A" || parseFloat(precio) <= 0) {
                    var precioBase = parseFloat(fila_seleccionada.getData().precio_normal || obj.precio_publico || 0);
                    if (precioBase > 0) {
                        const first = obj?.clasificacion?.[0]?.toUpperCase?.() ?? '';
                        const mapa = { A: 0.85, B: 0.9, C: 0.95 };
                        const descuento = mapa[first] ?? 0.95;
                        precio = precioBase * descuento * 1.16;
                        precio_iva = precio / 1.16;
                    }
                }

                table_partidas.addData([{
                    "codigo": obj.clave,
                    "descripcion": obj.name,
                    "cantidad": cantidad_total_partida,
                    "precio": parseFloat(precio).toFixed(2),
                    "precio_iva": parseFloat(precio_iva).toFixed(2),
                    "total": (cantidad_total_partida * precio).toFixed(2)
                }]);
                partidas.push(obj);

                setTimeout(() => {
                    $("#galeria").html('')
                    $('#detalles_producto').hide();
                    $('#palabras_clave').val("").focus();
                }, 500);
                actualizarGranTotal();
            }
        }

        function actualizarPrecio(cantidad, codigo) {

            if (cantidad <= 0)
                cantidad = 1;

            $.each(partidas, function (i, obj) {
                if (obj.clave == codigo) {
                    if (obj.existencia < cantidad) {
                        alert("No puedes agregar mas cantidad de la que hay en almacen.");
                        return false;
                    }
                    var precio = obj.precio_publico;
                    if (obj.descuentos.length > 1) {
                        for (i = obj.descuentos.length - 1; i >= 1; i--) {
                            //console.log("comenzamos = " + i);
                            if (obj.descuentos[i].tipo == "S") {
                                //console.log(obj.descuentos[i].tipo + " " + i);
                                if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                    //console.log("cero unidades");
                                    precio = obj.descuentos[i].precio_lista;
                                    precio_iva = obj.descuentos[i].precio_iva;
                                    break;
                                } else {
                                    if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                        //console.log("hay unidades y cubrimos");
                                        precio = obj.descuentos[i].precio_lista;
                                        precio_iva = obj.descuentos[i].precio_iva;
                                        break;
                                    } else {
                                        continue;
                                    }
                                }
                            }
                        }
                    } else { precio = obj.descuentos[0].precio_lista; precio_iva = obj.descuentos[0].precio_lista; }

                    table_partidas.updateData([{
                        "codigo": codigo,
                        "cantidad": cantidad,
                        "precio": parseFloat(precio).toFixed(2),
                        "precio_iva": parseFloat(precio_iva).toFixed(2),
                        "total": (cantidad * precio).toFixed(2)
                    }]);
                    actualizarGranTotal();
                    return false;
                }
            });

        }


        function actualizarPrecioEspecial(cantidad, codigo) {

            if (cantidad <= 0)
                cantidad = 1;

            $.each(partidas_especiales, function (i, obj) {
                if (obj.clave == codigo) {
                    var precio = obj.precio_publico;
                    table_partidas_especiales.updateData([{
                        "codigo": codigo,
                        "cantidad": cantidad,
                        "precio": parseFloat(precio).toFixed(2),
                        "total": (cantidad * precio).toFixed(2)
                    }]);
                    actualizarGranTotalEspeciales();
                    return false;
                }
            });

        }


        function reiniciarCantidad() {
            $('#detalles_producto').hide();
            $('#cantidad').val("");
        }

        function buscar($palabras) {
            $("#galeria").html('')
            resultados = [];
            $.getJSON("{{ route('pedidos.busqueda') }}", { q: $palabras },
                function (data, textStatus, jqXHR) {
                    $.each(data, function (i, val) {

                        var equivalencias = (val.extra_clave_1 != null ? val.extra_clave_1 + "--" : '') + (val.extra_clave_2 != null ? val.extra_clave_2 + "--" : '') + (val.extra_clave_3 != null ? val.extra_clave_3 + "--" : '')
                        resultados.push({
                            "marca_comercial": val.marca_comercial,
                            "codigo": val.codigo_nikko,
                            "descripcion": val.descripcion_1,
                            "grupo": val.grupo,
                            "subgrupo": val.subgrupo,
                            "marca": val.marca_comercial,
                            "equivalencias": equivalencias,
                            "buscar": val.buscador,
                            'precio_normal': val.precio_normal,
                            'disponibilidad': val.disponibilidad,
                            'clave_proveedor': val.clave_proveedor || ''

                        });
                    });
                    //console.log(resultados);
                    $('.mostrar_resultados').css('display', 'flex');
                    table.replaceData(resultados);
                    table.redraw(true);
                    setTimeout(() => {
                        $('.tabulator-row').first().trigger("click");
                    }, 500);

                }
            );
        }

        function buscarProducto($cliente, $clave, $cantidad, $equivalencias, $precio_normal, $clave_proveedor) {
            $.get(
                "https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto_vendedores",
                { cliente: $cliente, clave: $clave, tipo: $("input:radio[name ='tipo_pedido']:checked").val() },
                function (data) {
                    //console.log(data);
                    var obj = data;
                    if (data.code == 0) {
                        alert(data.mensaje);
                        return false;
                    }

                    // Proveedores con tipo_separacion='split_por_stock' suman
                    // `stock_ficticio` unidades virtuales al stock SAE (la UI
                    // siempre las muestra como disponibles). Data-driven desde
                    // SOMA (PROVEEDORES_ESPECIALES) — NUNCA hardcodear S227.
                    var stockFicticio = obtenerStockFicticio($clave_proveedor);
                    if (stockFicticio > 0) {
                        obj.existencia = parseInt(obj.existencia || 0) + stockFicticio;
                    }

                    // empresa_buscar_producto_vendedores (SAE) no devuelve
                    // clave_proveedor; la copiamos desde la fila tabulator que
                    // la trae desde apiBusqueda (SOMA). Sin esto, separarPartidas
                    // no detectaria el proveedor especial y la partida caeria en
                    // clasificarPorEmpresa como producto normal.
                    obj.clave_proveedor = $clave_proveedor || '';

                    producto_partida = obj;
                    if (obj.cliente == "N/A")
                        obj.precio_publico = $precio_normal;

                    //analisis para saber que politiva le toca
                    var notas = "";
                    var precio = obj.precio_publico;
                    var precio_iva = obj.precio_iva;
                    var cantidad = $("#cantidad").val() != "" ? $("#cantidad").val() : 1;
                    //console.log("TAMANO:" + obj.descuentos.length);
                    var notas = "";
                    if (obj.descuentos.length > 1) {
                        for (i = obj.descuentos.length - 1; i >= 1; i--) {
                            //console.log("comenzamos = " + i);
                            if (obj.descuentos[i].tipo == "S") {
                                //console.log(obj.descuentos[i].tipo + " " + i);
                                if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                    //console.log("cero unidades");
                                    precio = obj.descuentos[i].precio_lista;
                                    precio_iva = obj.descuentos[i].precio_iva;
                                    break;
                                } else {
                                    if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                        //console.log("hay unidades y cubrimos");
                                        precio = obj.descuentos[i].precio_lista;
                                        precio_iva = obj.descuentos[i].precio_iva;
                                        break;
                                    } else {
                                        notas +=
                                            "Si compras " +
                                            parseFloat(obj.descuentos[i].unidades_minimas).toFixed(0) +
                                            "<br> el precio es de $" +
                                            parseFloat(obj.descuentos[i].precio_lista).toFixed(2) + "<br>";
                                        continue;
                                    }
                                }
                            }
                        }
                    } else { precio = obj.descuentos[0].precio_lista; precio_iva = obj.descuentos[0].precio_iva; }

                    if ($equivalencias != "") {
                        var textos_eq = ' <h5 class="card-title">Equivalencias</h5>';
                        var eq = $equivalencias.split("--");
                        $.each(eq, function (i, val) {
                            if (val != "")
                                textos_eq += `<div class="forzar-busqueda" data-clave="` + val + `">
                                                <label>`+ val + `:</label>
                                                <small class="indice_equivalencia"></small>
                                                </div>`;
                        });
                    }

                    if (obj.cliente == 'N/A') {
                        precio = $precio_normal
                        const first = obj?.clasificacion?.[0]?.toUpperCase?.() ?? '';
                        const mapa = { A: 0.85, B: 0.9, C: 0.95 };
                        const descuento = mapa[first] ?? 0.95;
                        console.log(descuento);
                        precio = precio * descuento * 1.16;
                    }

                    if (obj.paqueteria > 0) {
                        notas += "<br>Precio incluye pago de paqueteria";
                    }

                    $("#resultados_producto").html(
                        `
                                    <h5 class="card-title">Detalles del producto</h5>
                                    `+ (obj.cliente == 'N/A' ? `<div style="color:red;font-size:15px;font-weight:bold;">PEDIDO ESPECIAL</div>` : ``) + `
                                    <div>
                                        <label>Clave:</label>
                                        <small>` +
                        obj.clave +
                        `</small>
                                    </div>
                                    <div>
                                        <label>Linea:</label>
                                        <small>` +
                        obj.linea +
                        `</small>
                                    </div>
                                    <div>
                                        <label>Descripción:</label>
                                        <small>` +
                        obj.name +
                        `</small>
                                    </div>
                                    <div>
                                        <label>Existencia:</label>
                                        <small>` +
                        obj.existencia +
                        `</small>
                                    </div>
                                    <div>
                                        <label>Precio:</label>
                                        <small>$` +
                        parseFloat(precio).toFixed(2) +
                        `</small>
                                    </div>
                                    <div class="mb-3">
                                        <small class="nota_precio">` +
                        notas +
                        `</small>
                                    </div>

                                `
                    ).show();

                    $("#detalles_producto").css('display', 'block');
                    setTimeout(() => {
                        $("#cantidad").focus();
                    }, 500);


                    if ($('#galeria').hasClass('slick-initialized')) {
                        $('#galeria').slick('destroy');
                    }
                    $("#galeria").html('');
                    $.get("https://owari.com.mx/api/imagenes", { "codigo": obj.clave },
                        function (data, textStatus, jqXHR) {
                            data = JSON.parse(data);
                            $.each(data, function (index, val) {
                                /* iterate through array or object */
                                $("#galeria").append('<div><img src="' + val + '" width="100%"></div>');
                            });

                            setTimeout(() => {
                                $("#galeria").slick({
                                    dots: true,
                                    arrows: false
                                });
                            }, "500");

                        }
                    );


                    if ($equivalencias != "") {
                        $("#resultados_producto").append(textos_eq);
                        $('.forzar-busqueda').click(function (e) {
                            var e = jQuery.Event("keydown");
                            e.which = 13; // # Some key code value
                            $('#palabras_clave').val($(this).data('clave')).trigger(e);
                        });
                    }
                    $.each($('.forzar-busqueda'), function (i, val) {
                        $.get("https://sistemasowari.com:8443/catalowari/api/producto-existencia", { "clave": $(val).data('clave') },
                            function (data, textStatus, jqXHR) {
                                data = JSON.parse(data);
                                var existencia = parseInt(data.existencia);
                                $(val).find('.indice_equivalencia').text("").text(existencia);
                            }
                        );
                    });
                    $(".agregar-cantidad").show();
                }
            );
        }

        function actualizarGranTotal() {
            var total_partidas = table_partidas.getData();
            var gran_total = 0;
            $.each(total_partidas, function (i, v) {
                gran_total += parseFloat(v.total);
            });
            console.log(gran_total);
            $('.gran_total_final').html("$" + (gran_total.toFixed(2)));
        }

        function actualizarGranTotalEspeciales() {
            var total_partidas = table_partidas_especiales.getData();
            var gran_total = 0;
            $.each(total_partidas, function (i, v) {
                gran_total += parseFloat(v.precio * v.cantidad);
            });
            console.log(gran_total);
            $('.gran_total_final_especiales').html("$" + (gran_total.toFixed(2)));
        }


        function reiniciarVenta() {
            resultados = [];
            fila_seleccionada;
            seleccion = false;
            indice_seleccion;
            producto_partida;
            partidas = [];
            gran_total = 0;
            $('.gran_total_final').html("$" + gran_total);
            table.replaceData(resultados);
            table.redraw(true);
            table_partidas.replaceData(partidas);
            table_partidas.redraw(true);
            partidas_especiales = [];
            table_partidas_especiales.replaceData(partidas_especiales);
            table_partidas_especiales.redraw(true);
        }


        var oFileIn;



        $("#subir_excel").click(function (e) {
            if ($("#excel")[0].files[0]) {
                var leyenda = "<label>Espera un momento, se esta analizando tu archivo.</label>";
                $(".texto_modal").html(leyenda);
                $(".modal-footer").hide();
                $("#modal").modal("show");
                setTimeout(() => {
                    filePicked();
                }, 500);
            }
            else
                alert('Selecciona primero un archivo de excel');


        })


        function filePicked() {


            // Get The File From The Input
            var oFile = $("#excel")[0].files[0];
            var sFilename = oFile.name;
            // Create A File Reader HTML5
            var reader = new FileReader();

            // Ready The Event For When A File Gets Selected
            reader.onload = function (e) {
                var data = e.target.result;
                var cfb = XLSX.read(data, { type: 'binary' });
                //console.log(cfb)
                cfb.SheetNames.forEach(function (sheetName) {
                    // Obtain The Current Row As CSV
                    var sCSV = XLSX.utils.make_csv(cfb.Sheets[sheetName]);
                    var oJS = XLSX.utils.sheet_to_json(cfb.Sheets[sheetName]);
                    console.log(oJS);

                    var numeroFilas = oJS.length;
                    var ejecutadas = 0;
                    var texto_alerta = "";



                    $.each(oJS, function (i, v) {
                        var ya_existe = false;
                        $.each(partidas, function (j, k) {
                            if (k.clave == v.CLAVE) {
                                actualizarPrecio(v.CANTIDAD, v.CLAVE);
                                ya_existe = true;
                                return false;
                            }
                        });

                        if (ya_existe)
                            return false;


                        $.ajax({
                            type: 'GET',
                            async: false,
                            url: "https://sistemasowari.com:8443/catalowari/api/empresa_buscar_producto",
                            data: { cliente: clave_cliente, clave: v.CLAVE, tipo: $("input:radio[name ='tipo_pedido']:checked").val() },
                            dataType: 'json',
                            success: function (data) {
                                ejecutadas++;
                                console.log(data);

                                var obj = data;

                                if (obj.code) {


                                    producto_partida = obj;

                                    //analisis para saber que politiva le toca
                                    var precio = obj.descuentos[0].precio_lista;
                                    var precio_iva = obj.descuentos[0].precio_iva;
                                    var cantidad = v.CANTIDAD;


                                    if (cantidad > obj.existencia) {
                                        ejecutadas++;
                                        texto_alerta += "<label>Para la clave " + v.CLAVE + " no puedes vender mas cantidad de la que hay en almacen (" + v.CANTIDAD + ")</label><br>";
                                        if (ejecutadas == numeroFilas) {
                                            $(".texto_modal").html(texto_alerta);
                                            $("#modal").modal("show");
                                        }
                                        return false;
                                    }


                                    //console.log("TAMANO:" + obj.descuentos.length);
                                    if (obj.descuentos.length > 1) {
                                        for (i = obj.descuentos.length - 1; i >= 1; i--) {
                                            //console.log("comenzamos = " + i);
                                            if (obj.descuentos[i].tipo == "S") {
                                                //console.log(obj.descuentos[i].tipo + " " + i);
                                                if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(0)) {
                                                    //console.log("cero unidades");
                                                    precio = obj.descuentos[i].precio_lista;
                                                    precio_iva = obj.descuentos[i].precio_iva;
                                                    break;
                                                } else {
                                                    if (parseInt(obj.descuentos[i].unidades_minimas) <= parseInt(cantidad)) {
                                                        //console.log("hay unidades y cubrimos");
                                                        precio = obj.descuentos[i].precio_lista;
                                                        precio_iva = obj.descuentos[i].precio_iva;
                                                        break;
                                                    } else {
                                                        continue;
                                                    }
                                                }
                                            }
                                        }
                                    } else { precio = obj.descuentos[0].precio_lista; precio_iva = obj.descuentos[0].precio_iva; }



                                    table_partidas.addData([{
                                        "codigo": obj.clave,
                                        "descripcion": obj.name,
                                        "cantidad": cantidad,
                                        "precio": parseFloat(precio).toFixed(2),
                                        "precio_iva": parseFloat(precio_iva).toFixed(2),
                                        "total": (cantidad * precio).toFixed(2)
                                    }]);
                                    console.log({ "precio_iva": parseFloat(precio_iva).toFixed(2) })
                                    partidas.push(obj);
                                    actualizarGranTotal();

                                }


                                if (ejecutadas == numeroFilas) {
                                    if (texto_alerta != "") {
                                        $(".texto_modal").html(texto_alerta + "<br><br>Da click fuera de esta ventana para continuar.");
                                        $("#modal").modal("show");
                                    }
                                    else {
                                        $("#modal").modal("hide");
                                    }
                                }
                            },
                            error: function () {

                                ejecutadas++;
                                texto_alerta += "<label>La clave " + v.CLAVE + " no existe.</label><br>";
                                if (ejecutadas == numeroFilas) {
                                    $(".texto_modal").html(texto_alerta);
                                    $("#modal").modal("show");
                                }
                            }
                        });


                    });

                });
            };

            // Tell JS To Start Reading The File.. You could delay this if desired
            setTimeout(() => {
                reader.readAsBinaryString(oFile);
            }, 500);

        }


        // ════════════════════════════════════════════════════════════════════
        // FLUJO DE GUARDADO (v2)
        // ════════════════════════════════════════════════════════════════════
        // El click en #guardar invoca guardar_pedido_v2(). Es el unico flujo
        // activo; el viejo se elimino en Fase 12 del refactor.
        //
        //   guardar_pedido_v2()              ← orquestador (async/await)
        //     ├── validarFormulario()
        //     ├── obtenerClienteSeleccionado()      ← del select, no Auth user
        //     ├── obtenerTipoRadioSiCorresponde()   ← solo si cliente NO tiene W
        //     ├── capturarEnSoma()                  (no bloqueante, fire-and-forget)
        //     ├── separarPartidas()                 ← usa PROVEEDORES_ESPECIALES
        //     ├── consultarRegalo()                 ← origen='telemarketing'
        //     ├── guardarEspecialesGenerales()
        //     ├── clasificarPorEmpresa()            ← W en pos.4 + radio si no tiene W
        //     ├── insertarEnSaeConRetry()           ← CHUNKS DE 30 con reintentos
        //     │     └── insertarChunkConReintentosManuales()
        //     │            └── intentarChunkConRetryInterno() (5 retries auto)
        //     │            └── mostrarOpcionReintentar() (boton del vendedor)
        //     ├── guardarPedidoLocal()              ← modelo Pedido conservado
        //     └── mostrarExito() / mostrarError()
        //
        // Helpers UI:    mostrarCargando, mostrarError, mostrarExito,
        //                mostrarOpcionReintentar
        // Helpers datos: todasLasPartidas, cargarProveedoresEspeciales,
        //                dividirEnChunks, calcularGranTotalActual
        // ════════════════════════════════════════════════════════════════════

        // Catalogo de proveedores especiales — cargado al iniciar la pagina.
        // El mapa se llena al resolverse `proveedoresEspecialesListos` (Promise).
        // Toda funcion que dependa de el debe esperar esa promesa para evitar
        // perder partidas silenciosamente cuando SOMA tarda en responder.
        var PROVEEDORES_ESPECIALES = {};

        var proveedoresEspecialesListos = fetch('https://owari.appsoma.online/somma/v2.0/api/proveedores-especiales')
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                PROVEEDORES_ESPECIALES = Object.fromEntries(
                    (data.proveedores || []).map(function(p) { return [p.clave, p]; })
                );
                return true;
            })
            .catch(function(e) {
                console.warn('No se pudo cargar proveedores_especiales:', e);
                PROVEEDORES_ESPECIALES = {};
                return false;
            });

        // Helpers data-driven: NUNCA hardcodear claves de proveedor (S227, AAAE, etc.).
        // La regla viene de SOMA via PROVEEDORES_ESPECIALES.
        function configProveedor(claveProveedor) {
            if (!claveProveedor) return null;
            return PROVEEDORES_ESPECIALES[claveProveedor] || null;
        }
        function esSplitPorStock(claveProveedor) {
            var cfg = configProveedor(claveProveedor);
            return !!(cfg && cfg.tipo_separacion === 'split_por_stock');
        }
        function obtenerStockFicticio(claveProveedor) {
            var cfg = configProveedor(claveProveedor);
            if (!cfg || cfg.tipo_separacion !== 'split_por_stock') return 0;
            return parseInt(cfg.stock_ficticio) || 0;
        }

        var MAX_INTENTOS_SAE = 5;
        var ESPERA_ENTRE_INTENTOS_MS = 2000;
        var CHUNK_SIZE_SAE = 30;

        function dormir(ms) {
            return new Promise(function(resolve) { setTimeout(resolve, ms); });
        }


        // ──────────────────────────────────────────────────────────────────
        // ORQUESTADOR PRINCIPAL
        // ──────────────────────────────────────────────────────────────────
        async function guardar_pedido_v2() {
            if (!validarFormulario()) return;
            mostrarCargando('Procesando tu pedido...');

            try {
                // 0. Asegurar que la config de proveedores especiales este cargada
                //    desde SOMA antes de tocar partidas. Si SOMA esta caido,
                //    abortamos: prefiero detener el flujo a perder partidas
                //    silenciosamente (S227 caeria en clasificarPorEmpresa.DESCARTADA).
                var cfgOk = await proveedoresEspecialesListos;
                if (!cfgOk) {
                    throw new Error(
                        'No se pudo cargar la configuracion de proveedores especiales desde SOMA. ' +
                        'Recarga la pagina e intenta de nuevo.'
                    );
                }

                // 1. Cliente del select (con CLASIFIC y CAMPLIB3 ya cargados al cambiar select)
                var cliente = obtenerClienteSeleccionado();

                // 2. Determinar tipoRadio: solo aplica si cliente NO tiene W en pos.4.
                //    Si tiene W → null (auto split por existencia).
                //    Si no tiene W → 'normal' o 'factura' del radio.
                var tipoRadio = obtenerTipoRadioSiCorresponde(cliente);

                // destino_sucursal para SOMA:
                //   tipoRadio='factura' → 'E01' (forzar empresa 1)
                //   resto (W o normal)  → 'TODAS' (SOMA decide)
                var destinoSomaSucursal = (tipoRadio === 'factura') ? 'E01' : 'TODAS';

                // 3. (El espejo en SOMA se manda AL FINAL, ya con los folios SAE
                //     reales — ver paso 10b — para poder comparar SOMA vs SAE.)

                // 4. Separar partidas en {sae, especiales}
                var separadas = separarPartidas();

                // 5. Guardar pedidos especiales (uno por proveedor).
                //    Captura los ids de los PedidoEspecial (y su # de partidas) para mandarlos a SOMA.
                var especialesCreados     = await guardarEspecialesGenerales(separadas.especiales, cliente.clave);
                var idsEspeciales         = especialesCreados.map(function (e) { return e.id; });
                var numPartidasEspeciales = especialesCreados.map(function (e) { return e.num; });

                // 6. Clasificar SAE por empresa
                var clasificacion = clasificarPorEmpresa(separadas.sae, cliente.CLASIFIC, tipoRadio, cliente.EXISTE_E3);

                // 7. Consultar regalo SOLO si hay partidas en empresa 1
                //    (factura). Regla: un pedido 100% especial (o solo remision)
                //    no genera regalo — el regalo acompaña una venta real en E01.
                //    Si lo aplica, se agrega a factura para que entre con el resto.
                var regalo = null;
                if (clasificacion.factura.length > 0) {
                    regalo = await consultarRegalo(cliente);
                    if (regalo) clasificacion.factura.push(regalo);
                }

                // 8. Insertar en SAE con CHUNKS de 30 + retry automatico + reintento manual
                var foliosFactura  = await insertarEnSaeConRetry(clasificacion.factura, 1, cliente.clave);
                var foliosRemision = clasificacion.remision.length
                    ? await insertarEnSaeConRetry(clasificacion.remision, 3, cliente.clave)
                    : [];

                // 9. Espejo local (modelo Pedido)
                var idPedido = await guardarPedidoLocal({
                    cliente: cliente.clave,
                    folios_factura:  foliosFactura,
                    folios_remision: foliosRemision,
                    partidas_sae:    clasificacion.factura.concat(clasificacion.remision),
                    especiales:      separadas.especiales,
                    regalo:          regalo,
                    tipo_radio:      tipoRadio,
                });

                // 10. Mostrar exito con resumen de folios
                mostrarExito(idPedido, foliosFactura, foliosRemision);

                // 10b. Espejo en SOMA con los folios SAE reales (fire-and-forget).
                //      Aqui los folios vienen como arrays (chunking).
                capturarEnSoma(cliente, todasLasPartidas(), destinoSomaSucursal, {
                    folio_sae_e01: foliosFactura,
                    folio_sae_e03: foliosRemision,
                    id_especial:   idsEspeciales,
                    partidas_sae_e01:  dividirEnChunks(clasificacion.factura,  CHUNK_SIZE_SAE).map(function (c) { return c.length; }),
                    partidas_sae_e03:  dividirEnChunks(clasificacion.remision, CHUNK_SIZE_SAE).map(function (c) { return c.length; }),
                    partidas_especial: numPartidasEspeciales,
                });

            } catch (err) {
                console.error('guardar_pedido_v2 fallo:', err);
                mostrarError(err.message || 'Ocurrio un error inesperado');
            }
        }


        // ──────────────────────────────────────────────────────────────────
        // HELPERS — UI
        // ──────────────────────────────────────────────────────────────────

        function mostrarCargando(mensaje) {
            // Limpia el modal de cualquier estado previo (errores, folios, etc.),
            // muestra el spinner + mensaje y deshabilita #guardar.
            $('.texto_modal').html('<h5>' + (mensaje || 'Procesando tu pedido...') + '</h5>');
            $('.modal-footer-especiales').hide();
            $('.terminar').addClass('d-none');
            $('#cargando').css('display', 'block');
            $('#pedidos_finales').css('display', 'none');
            $('#guardar_pedido_1').empty();
            $('#guardar_pedido_3').empty();
            $('.reiniciar_pantalla').addClass('d-none');
            $('#modal').modal('show');
            $('#guardar').attr('disabled', 'disabled');
        }

        function mostrarError(mensaje) {
            // Estado de error: oculta spinner, muestra el boton Cerrar (que
            // recarga la pagina) y reactiva #guardar para reintento manual.
            $('.texto_modal').html(
                '<h5 class="text-danger">' + (mensaje || 'Ocurrio un error') + '</h5>'
            );
            $('#cargando').css('display', 'none');
            $('#pedidos_finales').css('display', 'none');
            $('.reiniciar_pantalla').off('click').on('click', function(e) {
                e.preventDefault();
                location.reload();
            }).removeClass('d-none');
            $('#modal').modal('show');
            $('#guardar').removeAttr('disabled');
        }

        function mostrarExito(idPedido, foliosFactura, foliosRemision) {
            // Resumen de folios SAE generados, agrupados por empresa.
            // El boton Cerrar recarga la pagina (limpia formulario para
            // capturar otro pedido).
            var resumenE1 = (foliosFactura && foliosFactura.length > 0)
                ? '<b>Empresa 1 (Factura):</b><br>' +
                    foliosFactura.map(function(f, i) { return '&nbsp;&nbsp;Pedido ' + (i+1) + ': ' + f; }).join('<br>')
                : '<b>Empresa 1 (Factura):</b> <span class="text-muted">sin pedidos</span>';

            var resumenE3 = (foliosRemision && foliosRemision.length > 0)
                ? '<b>Empresa 3 (Remision):</b><br>' +
                    foliosRemision.map(function(f, i) { return '&nbsp;&nbsp;Pedido ' + (i+1) + ': ' + f; }).join('<br>')
                : '<b>Empresa 3 (Remision):</b> <span class="text-muted">sin pedidos</span>';

            $('.texto_modal').html(
                '<h5 class="text-success">Pedido guardado correctamente.</h5>' +
                '<div class="text-start mt-3" style="font-size:13px;">' +
                    '<p class="mb-2">' + resumenE1 + '</p>' +
                    '<p class="mb-0">' + resumenE3 + '</p>' +
                '</div>'
            );
            $('#cargando').css('display', 'none');
            $('#pedidos_finales').css('display', 'none');
            $('.reiniciar_pantalla').off('click').on('click', function(e) {
                e.preventDefault();
                location.reload();
            }).removeClass('d-none');
            $('#modal').modal('show');
        }

        function mostrarOpcionReintentar(empresa, idx, total, errorMsg) {
            // Inserta un bloque inline en el panel de la empresa con el
            // mensaje del error + botones [Reintentar] [Cancelar].
            // Devuelve Promise<boolean> que se resuelve cuando el vendedor
            // hace click en uno de los botones.
            //   resolve(true)  → quiere reintentar
            //   resolve(false) → cancela el flujo
            return new Promise(function(resolve) {
                var divId = 'reintentar_' + empresa + '_' + idx + '_' + Date.now();
                var msgSeguro = String(errorMsg || 'error desconocido')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                var html = '<div id="' + divId + '" class="alert alert-warning mt-2 p-2 text-start">' +
                           '  <small><b>✗ Pedido ' + idx + '/' + total + ' de E' + empresa + '</b> fallo tras ' +
                           MAX_INTENTOS_SAE + ' intentos:<br>' +
                           '  <span class="text-muted">' + msgSeguro + '</span></small><br>' +
                           '  <button type="button" class="btn btn-sm btn-success me-2 mt-2 btn-reintentar-chunk">' +
                           '    <i class="bi bi-arrow-repeat"></i>&nbsp;Reintentar' +
                           '  </button>' +
                           '  <button type="button" class="btn btn-sm btn-danger mt-2 btn-cancelar-chunk">' +
                           '    Cancelar' +
                           '  </button>' +
                           '</div>';

                $('#guardar_pedido_' + empresa).append(html);

                var $div = $('#' + divId);
                $div.find('.btn-reintentar-chunk').on('click', function() {
                    $div.replaceWith(
                        '<div class="text-info"><small>&nbsp;&nbsp;⟳ Reintentando pedido ' +
                        idx + '/' + total + ' de E' + empresa + '...</small></div>'
                    );
                    resolve(true);
                });
                $div.find('.btn-cancelar-chunk').on('click', function() {
                    $div.replaceWith(
                        '<div class="text-danger"><small>&nbsp;&nbsp;✗ Pedido ' +
                        idx + '/' + total + ' cancelado por el vendedor.</small></div>'
                    );
                    resolve(false);
                });
            });
        }


        // ──────────────────────────────────────────────────────────────────
        // HELPERS — DATOS DEL FORMULARIO Y CLIENTE
        // ──────────────────────────────────────────────────────────────────

        function validarFormulario() {
            // Validaciones secuenciales, primer fail muestra alert y termina.
            //   1. Cliente seleccionado (no -1 ni undefined)
            //   2. Al menos una partida (normal o especial)
            //   3. su_pedido capturado si hay partidas normales (heredado del flujo viejo)
            var indiceCliente = $("#cliente").val();
            if (indiceCliente === undefined || parseInt(indiceCliente) < 0) {
                alert('Selecciona un cliente valido');
                return false;
            }

            var partidasNormales   = (typeof table_partidas !== 'undefined') ? table_partidas.getData() : [];
            var partidasEspeciales = (typeof table_partidas_especiales !== 'undefined') ? table_partidas_especiales.getData() : [];

            if (partidasNormales.length === 0 && partidasEspeciales.length === 0) {
                alert('Ingresa por lo menos una partida en el pedido');
                return false;
            }

            if (partidasNormales.length > 0 && (!$('#su_pedido').val() || $('#su_pedido').val().trim() === '')) {
                alert('Confirma si es mostrador o ingresa la hora de entrega');
                $('#su_pedido').focus();
                return false;
            }

            return true;
        }

        function obtenerClienteSeleccionado() {
            // Devuelve el objeto cliente del array global `clientes`. CLASIFIC
            // y CAMPLIB3 los enriquece el onChange del select (Fase 4); si Fase 4
            // aun no esta hecha, vienen vacios y se tratan como "sin W"
            // (default a factura via radio).
            var indice = $("#cliente").val();
            if (indice === undefined || parseInt(indice) < 0) {
                throw new Error('No hay cliente seleccionado');
            }
            var c = clientes[indice];
            if (!c) {
                throw new Error('Cliente no encontrado en el array clientes');
            }
            return {
                clave:     c.clave,
                nombre:    c.nombre || '',
                EMPRESA:   c.EMPRESA || c.empresa || null,
                CLASIFIC:  c.CLASIFIC || '',
                CAMPLIB3:  c.CAMPLIB3 || '',
                EXISTE_E3: (typeof c.EXISTE_E3 === 'boolean') ? c.EXISTE_E3 : undefined,
            };
        }

        function obtenerTipoRadioSiCorresponde(cliente) {
            // Si CLASIFIC tiene W en pos.4 → null (regla auto, no consulta radio).
            // Sino → 'normal' o 'factura' del radio visible. Default 'normal'
            // si por alguna razon no hay seleccionado.
            if (tieneWEnPos4(cliente.CLASIFIC)) {
                return null;
            }
            var seleccionado = $("input:radio[name='tipo_pedido']:checked").val();
            return seleccionado || 'normal';
        }

        function todasLasPartidas() {
            // Junta normales + especiales. Se manda a SOMA capturar tal cual;
            // SOMA aplica sus reglas internas (politicas, descuentos, division
            // normal/especial, etc.).
            var normales   = (typeof table_partidas !== 'undefined') ? table_partidas.getData() : [];
            var especiales = (typeof table_partidas_especiales !== 'undefined') ? table_partidas_especiales.getData() : [];
            return (normales || []).concat(especiales || []);
        }


        // ──────────────────────────────────────────────────────────────────
        // HELPERS — SEPARACION Y CLASIFICACION
        // ──────────────────────────────────────────────────────────────────

        function separarPartidas() {
            // Itera table_partidas (visibles) y table_partidas_especiales y
            // los enriquece con datos del array `partidas`/`partidas_especiales`
            // (los obj crudos de empresa_buscar_producto_vendedores) para
            // conocer clave_proveedor, existencia_factura y existencia_remision.
            //
            // Devuelve { sae: [...], especiales: { claveProveedor: [...] } }.
            //
            // Reglas por proveedor (mismas que carrito):
            //   sin config en PROVEEDORES_ESPECIALES → sae
            //   tipo='todo_especial'                  → especiales[clave]
            //   tipo='split_por_stock':
            //     cantidad <= existencia_real (obj.existencia - stock_ficticio) → sae
            //     existencia_real == 0           → todo a especiales[clave]
            //     cantidad >  existencia_real    → split (real a sae + resto a especial)
            //
            // Las partidas movidas manualmente al cartEspecial (table_partidas_especiales)
            // siempre se agrupan por clave_proveedor, sin importar el tipo.
            var sae = [];
            var especiales = {};

            function pushEspecial(claveProveedor, partida) {
                var clave = (claveProveedor || '').trim() || 'SIN_PROVEEDOR';
                if (!especiales[clave]) especiales[clave] = [];
                especiales[clave].push(partida);
            }

            function ajustarPartida(partida, nuevaCantidad) {
                var copia = Object.assign({}, partida);
                copia.cantidad = nuevaCantidad;
                copia.total = (nuevaCantidad * parseFloat(copia.precio)).toFixed(2);
                return copia;
            }

            function enriquecerDesdeObj(p, obj) {
                if (!obj) return p;
                p.clave_proveedor    = (obj.clave_proveedor || '').trim();
                p.existencia         = parseInt(obj.existencia)         || 0;
                p.existencia_factura = parseInt(obj.existencia_factura);
                p.existencia_remision = parseInt(obj.existencia_remision);
                if (isNaN(p.existencia_factura))  p.existencia_factura  = -1;
                if (isNaN(p.existencia_remision)) p.existencia_remision = -1;
                return p;
            }

            // 1. Partidas normales (table_partidas)
            var visibles = (typeof table_partidas !== 'undefined') ? table_partidas.getData() : [];
            for (var i = 0; i < visibles.length; i++) {
                var p = Object.assign({}, visibles[i]);
                var obj = (typeof partidas !== 'undefined')
                    ? partidas.find(function(o) { return o.clave === p.codigo; })
                    : null;
                p = enriquecerDesdeObj(p, obj);

                var claveProv = p.clave_proveedor || '';
                var config = claveProv ? PROVEEDORES_ESPECIALES[claveProv] : null;

                if (!config) {
                    sae.push(p);
                    continue;
                }

                if (config.tipo_separacion === 'todo_especial') {
                    pushEspecial(claveProv, p);
                    continue;
                }

                if (config.tipo_separacion === 'split_por_stock') {
                    var stockFicticio  = parseInt(config.stock_ficticio) || 0;
                    var existenciaReal = Math.max(0, (p.existencia || 0) - stockFicticio);
                    var cantidad       = parseInt(p.cantidad) || 0;

                    if (cantidad <= existenciaReal) {
                        sae.push(p);
                    } else if (existenciaReal === 0) {
                        pushEspecial(claveProv, p);
                    } else {
                        sae.push(ajustarPartida(p, existenciaReal));
                        pushEspecial(claveProv, ajustarPartida(p, cantidad - existenciaReal));
                    }
                    continue;
                }

                // Tipo desconocido — defensivo
                sae.push(p);
            }

            // 2. Partidas especiales (table_partidas_especiales). La clave_proveedor
            //    solo separa si ese proveedor esta marcado como ESPECIAL en SOMA
            //    (PROVEEDORES_ESPECIALES). Sino, todo va al bucket SIN_PROVEEDOR
            //    para que se genere UN solo PedidoEspecial general.
            var especialesVisibles = (typeof table_partidas_especiales !== 'undefined') ? table_partidas_especiales.getData() : [];
            for (var j = 0; j < especialesVisibles.length; j++) {
                var pe = Object.assign({}, especialesVisibles[j]);
                var objE = (typeof partidas_especiales !== 'undefined')
                    ? partidas_especiales.find(function(o) { return o.clave === pe.codigo; })
                    : null;
                pe = enriquecerDesdeObj(pe, objE);
                var claveProvE = (pe.clave_proveedor || '').trim();
                var configE = claveProvE ? PROVEEDORES_ESPECIALES[claveProvE] : null;
                pushEspecial(configE ? claveProvE : '', pe);
            }

            // 3. Consolidar duplicados dentro de cada bucket especial
            Object.keys(especiales).forEach(function(k) {
                especiales[k] = consolidarPorCodigo(especiales[k]);
            });

            return { sae: sae, especiales: especiales };
        }

        // Suma cantidades y totales de partidas con el mismo codigo,
        // preservando los demas campos del primer registro.
        function consolidarPorCodigo(arr) {
            var mapa = {};
            for (var i = 0; i < arr.length; i++) {
                var p = arr[i];
                var cod = p.codigo;
                if (!mapa[cod]) {
                    mapa[cod] = Object.assign({}, p);
                    mapa[cod].cantidad = parseInt(p.cantidad) || 0;
                    mapa[cod].total    = parseFloat(p.total)  || 0;
                } else {
                    mapa[cod].cantidad += parseInt(p.cantidad) || 0;
                    mapa[cod].total    += parseFloat(p.total)  || 0;
                }
            }
            return Object.values(mapa).map(function(p) {
                p.total = parseFloat(p.total).toFixed(2);
                return p;
            });
        }

        function clasificarPorEmpresa(partidasSae, clasif, tipoRadio, existeE3) {
            // Decide a qué empresa SAE va cada partida (E01 factura, E03 remision).
            //
            // GUARD: si el cliente NO existe en CLIE03 (existeE3 === false), todo
            // va a factura. SAE empresa 3 rechazaria el pedido si el cliente no
            // esta registrado alli. existeE3 === undefined (backend viejo sin
            // EXISTE_E3) NO bloquea — mantiene compat.
            //
            // Reglas:
            //   - Si CLASIFIC tiene W en pos.4 → SIEMPRE split por existencia
            //     (auto, ignora el radio aunque venga seteado).
            //   - Si CLASIFIC NO tiene W:
            //       tipoRadio === 'factura' → todo a E01 (sin importar existencia)
            //       tipoRadio === 'normal'  → split por existencia (igual que con W)
            //
            // Regla del split por existencia (replicada de externos/guardarPedido):
            //   existencia_remision >= 0  → E03 (remision, precio sin IVA)
            //   existencia_factura > 0
            //       AND cantidad <= existencia_factura → E01 (factura, precio con IVA)
            //   resto → DESCARTADA (bug heredado; se preserva con console.warn)
            var factura  = [];
            var remision = [];

            // Guard: cliente no esta en CLIE03 → no puede ir a E03
            if (existeE3 === false) {
                return { factura: partidasSae.slice(), remision: remision };
            }

            // Cliente sin W con radio "factura" → atajo: todo a factura
            if (!tieneWEnPos4(clasif) && tipoRadio === 'factura') {
                return { factura: partidasSae.slice(), remision: remision };
            }

            // En todos los demas casos: split por existencia
            for (var i = 0; i < partidasSae.length; i++) {
                var p        = partidasSae[i];
                var existRem = parseInt(p.existencia_remision);
                var existFac = parseInt(p.existencia_factura);
                var cantidad = parseInt(p.cantidad) || 0;

                if (!isNaN(existRem) && existRem >= 0) {
                    remision.push(p);
                } else if (!isNaN(existFac) && existFac > 0 && cantidad <= existFac) {
                    factura.push(p);
                } else {
                    console.warn(
                        'clasificarPorEmpresa: partida descartada (no cabe en E01 ni E03)',
                        p
                    );
                }
            }

            return { factura: factura, remision: remision };
        }

        function tieneWEnPos4(clasif) {
            // CLIE.CLASIFIC en SAE es VARCHAR(5). Si viene mas corto se padea
            // con espacios; aqui tambien por seguridad. Posicion 4 (1-based)
            // = indice 3 (0-based).
            if (!clasif) return false;
            var c = String(clasif).padEnd(5, ' ');
            return c.charAt(3).toUpperCase() === 'W';
        }


        // ──────────────────────────────────────────────────────────────────
        // HELPERS — ENVIO A ENDPOINTS
        // ──────────────────────────────────────────────────────────────────

        function capturarEnSoma(cliente, partidas, destinoSucursal, foliosSae) {
            // POST al proxy local soma.capturar_proxy, que reenvia a SOMA
            // /api/pedidos/capturar con X-API-Key.
            //
            // Fire-and-forget: no esperamos respuesta. Si SOMA cae, el flujo
            // sigue normal (graceful degradation).
            //
            // SOMA recibe TODAS las partidas (cart + cartEspecial) sin separar
            // y aplica sus propias reglas (politicas, descuentos, division
            // normal/especial, etc.).
            //
            // destino_sucursal:
            //   'E01'    cuando tipoRadio === 'factura' (forzar empresa 1)
            //   'TODAS'  cuando tipoRadio === 'normal' o cliente con W
            //
            // foliosSae (opcional): { folio_sae_e01[], folio_sae_e03[], id_especial[] }
            // con los folios del documento REAL en SAE (aqui pueden ser varios por
            // chunking) para que SOMA los guarde y se compare SOMA vs SAE.
            try {
                var idEnvio = (window.crypto && crypto.randomUUID)
                    ? crypto.randomUUID()
                    : ('env-' + Date.now() + '-' + Math.random().toString(36).slice(2));

                var partidasParaSoma = (partidas || []).map(function(p) {
                    return { clave: p.codigo, cantidad: parseInt(p.cantidad) || 0 };
                });

                var payload = {
                    clave_cliente:     cliente.clave,
                    partidas:          partidasParaSoma,
                    origen:            'TELEMARKETING',
                    id_envio_externo:  idEnvio,
                    destino_sucursal:  destinoSucursal || 'TODAS',
                    gran_total_origen: calcularGranTotalActual(),
                };

                if (foliosSae) {
                    payload.folio_sae_e01 = foliosSae.folio_sae_e01 || [];
                    payload.folio_sae_e03 = foliosSae.folio_sae_e03 || [];
                    payload.id_especial   = foliosSae.id_especial   || [];
                    payload.partidas_sae_e01  = foliosSae.partidas_sae_e01  || [];
                    payload.partidas_sae_e03  = foliosSae.partidas_sae_e03  || [];
                    payload.partidas_especial = foliosSae.partidas_especial || [];
                }

                fetch("{{ route('soma.capturar_proxy') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(payload),
                }).catch(function(err) {
                    console.warn('capturarEnSoma fallo:', err);
                });
            } catch (e) {
                console.warn('capturarEnSoma excepcion:', e);
            }
        }

        async function consultarRegalo(cliente) {
            // POST /somma/v2.0/api/promociones-regalo/evaluar para saber si
            // este pedido aplica a alguna promo de regalo activa.
            //
            // En telemkt mandamos origen='telemarketing'; SOMA mapea a
            // aplica_mostrador (cubre tambien mostrador, son el mismo canal).
            //
            // El caller (este JS) ya tiene CLASIFIC y CAMPLIB3 enriquecidos en
            // el cliente; los pasa al payload para que SOMA matchee audiencia
            // sin volver a consultar SAE.
            //
            // Devuelve la partida_regalo lista para meter al bucket sae si
            // SOMA responde aplica:true. Null en cualquier otro caso —
            // incluyendo timeouts, errores de red o respuestas raras.
            // Graceful degradation: si SOMA truena, el pedido se guarda sin
            // regalo y el vendedor no ve un error.
            try {
                var granTotal = calcularGranTotalActual();

                var payload = {
                    clave_cliente: cliente.clave,
                    gran_total:    granTotal,
                    origen:        'telemarketing',
                    campolibre:    (cliente.CAMPLIB3 || '').toString().trim(),
                    clasificacion: (cliente.CLASIFIC || '').toString().trim(),
                };

                var resp = await fetch(
                    'https://owari.appsoma.online/somma/v2.0/api/promociones-regalo/evaluar',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                        },
                        body: JSON.stringify(payload),
                    }
                );

                if (!resp.ok) {
                    console.warn('consultarRegalo: SOMA respondio HTTP', resp.status);
                    return null;
                }

                var data = await resp.json();
                if (!data || !data.aplica || !data.partida_regalo) {
                    return null;
                }

                var pr = data.partida_regalo;
                var claveRegalo = String(pr.clave || '');

                // Regla "una sola vez por cliente": SAE empresa 1 es la fuente
                // de verdad — buscamos PAR_FACTP01 JOIN FACTP01 con la clave
                // del regalo, cliente, STATUS!=C. Si ya tiene, descartamos.
                // En error devolvemos null (conservador: no regalar dos veces).
                if (claveRegalo) {
                    try {
                        var url = 'https://sistemasowari.com:8443/catalowari/api/regalo_ya_tiene'
                            + '?cliente=' + encodeURIComponent(cliente.clave)
                            + '&clave_regalo=' + encodeURIComponent(claveRegalo);
                        var verif = await fetch(url, { headers: { 'Accept': 'application/json' }});
                        if (!verif.ok) {
                            console.warn('consultarRegalo: SAE verificacion HTTP', verif.status, '— no se aplica');
                            return null;
                        }
                        var vdata = await verif.json();
                        if (vdata && vdata.ya_tiene === true) {
                            console.log('consultarRegalo: cliente ya tiene', claveRegalo, 'en SAE — no se aplica');
                            return null;
                        }
                    } catch (e2) {
                        console.warn('consultarRegalo: verificacion SAE fallo:', e2);
                        return null;
                    }
                }

                // El regalo se trata como partida normal de SAE, dirigida a
                // empresa 1 (factura). existencia_remision=-1 evita que vaya
                // a E03; existencia_factura=999 garantiza que clasificarPorEmpresa
                // la mande a E01.
                return {
                    codigo:               claveRegalo,
                    descripcion:          (data.promocion && data.promocion.nombre) || 'Regalo promocional',
                    cantidad:             parseInt(pr.cantidad) || 1,
                    precio:               parseFloat(pr.precio || 0.01).toFixed(2),
                    precio_iva:           parseFloat(pr.precio || 0.01).toFixed(2),
                    total:                parseFloat(pr.total  || 0.01).toFixed(2),
                    existencia:           999,
                    existencia_factura:   999,
                    existencia_remision: -1,
                    clave_proveedor:     '',
                    es_regalo:           true,
                };
            } catch (e) {
                console.warn('consultarRegalo fallo:', e);
                return null;
            }
        }

        // Suma el total visible de table_partidas + table_partidas_especiales.
        // Usado por consultarRegalo y capturarEnSoma para mandar el monto del
        // pedido a los endpoints de SOMA.
        function calcularGranTotalActual() {
            var total = 0;
            var t1 = (typeof table_partidas !== 'undefined') ? table_partidas.getData() : [];
            var t2 = (typeof table_partidas_especiales !== 'undefined') ? table_partidas_especiales.getData() : [];
            t1.forEach(function(p) { total += parseFloat(p.total) || 0; });
            t2.forEach(function(p) { total += parseFloat(p.total) || 0; });
            return total;
        }

        async function guardarEspecialesGenerales(especialesPorProveedor, claveCliente) {
            // Itera el mapa { claveProveedor: [partidas], ... } y por cada
            // grupo hace UN POST a pedidos.guardar_especial con clave_proveedor.
            //
            // Cubre S227 (SYD), AAAA y cualquier proveedor futuro. El backend
            // distingue por clave_proveedor para el subject del email
            // (Pedido especial SYD vs Pedido especial).
            //
            // Si una llamada truena, loguea warning y continua con las demas
            // — no bloquea el flujo. Vendedor verá los especiales que sí
            // se registraron y los que fallaron quedan en log.
            // Devuelve los ids de los PedidoEspecial creados, para mandarlos a SOMA.
            var idsEspeciales = [];
            if (!especialesPorProveedor) return idsEspeciales;

            var claves = Object.keys(especialesPorProveedor);
            for (var i = 0; i < claves.length; i++) {
                var claveProveedor = claves[i];
                var partidasGrupo  = especialesPorProveedor[claveProveedor] || [];
                if (partidasGrupo.length === 0) continue;

                var data = {
                    '_token':  '{{ csrf_token() }}',
                    cliente:   claveCliente,
                    partidas:  partidasGrupo,
                };
                if (claveProveedor && claveProveedor !== 'SIN_PROVEEDOR') {
                    data.clave_proveedor = claveProveedor;
                }

                var idEsp = await guardarEspecialUnGrupo(data, claveProveedor);
                if (idEsp) idsEspeciales.push({ id: idEsp, num: partidasGrupo.length });
            }
            return idsEspeciales;
        }

        // Wrapper que envuelve $.post en una Promise para usar con await,
        // y no truena el flujo si una falla — solo loguea warning.
        // Resuelve con el id del PedidoEspecial creado (o null si falla).
        function guardarEspecialUnGrupo(data, claveProveedor) {
            return new Promise(function(resolve) {
                $.post("{{ route('pedidos.guardar_especial') }}", data)
                    .done(function(resp) {
                        try {
                            var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            resolve(r && r.id_pedido ? r.id_pedido : null);
                        } catch (e) {
                            resolve(null);
                        }
                    })
                    .fail(function(xhr) {
                        console.warn(
                            'guardarEspecialesGenerales fallo proveedor=' + claveProveedor,
                            xhr && xhr.status,
                            xhr && xhr.responseText
                        );
                        resolve(null);   // continuar con el siguiente proveedor
                    });
            });
        }

        // ──────────────────────────────────────────────────────────────────
        // HELPERS — INSERCION SAE CON CHUNKS Y REINTENTOS
        // ──────────────────────────────────────────────────────────────────

        async function insertarEnSaeConRetry(partidas, empresa, claveCliente) {
            // Inserta en SAE empresa 1 (factura) o 3 (remision).
            // Divide partidas en chunks de CHUNK_SIZE_SAE (30) y procesa
            // secuencial. Cada chunk con retry interno automatico (5 intentos
            // con 2s entre cada uno) y, si todos fallan, pregunta al vendedor
            // si quiere reintentar manualmente.
            //
            // Devuelve array de folios SAE generados (uno por chunk exitoso).
            //
            // Si el vendedor cancela en cualquier chunk, propaga la excepcion
            // al orquestador que la captura en mostrarError.
            if (!partidas || partidas.length === 0) return [];

            // Activar el panel de progreso por chunks en el modal
            $('#pedidos_finales').css('display', 'block');

            var chunks   = dividirEnChunks(partidas, CHUNK_SIZE_SAE);
            var usuario  = '{{ \Auth::user()->name }}';
            var suPedido = $('#su_pedido').val() || '';
            var folios   = [];

            // Anuncio inicial en el panel de la empresa
            $('#guardar_pedido_' + empresa).html(
                '<b>Empresa ' + empresa + ':</b> ' + chunks.length +
                ' pedido' + (chunks.length > 1 ? 's' : '') + ' a procesar<br>'
            );

            for (var i = 0; i < chunks.length; i++) {
                var folio = await insertarChunkConReintentosManuales(
                    chunks[i], empresa, i + 1, chunks.length,
                    claveCliente, usuario, suPedido
                );
                folios.push(folio);
            }
            return folios;
        }

        async function insertarChunkConReintentosManuales(chunk, empresa, idx, total, claveCliente, usuario, suPedido) {
            // Loop infinito: 5 retries automaticos, luego pregunta al vendedor
            // si quiere reintentar. Si el vendedor cancela, throw.
            // Si el chunk pasa (interno o tras reintentar), append exito en
            // el modal y devuelve el folio.
            while (true) {
                var resultado = await intentarChunkConRetryInterno(
                    chunk, empresa, claveCliente, usuario, suPedido
                );
                if (resultado.exito) {
                    $('#guardar_pedido_' + empresa).append(
                        '<div class="text-success"><small>&nbsp;&nbsp;✓ Pedido ' + idx + '/' + total +
                        ': <b>' + resultado.folio + '</b></small></div>'
                    );
                    return resultado.folio;
                }

                // 5 retries internos fallaron → vendedor decide
                var quiereReintentar = await mostrarOpcionReintentar(empresa, idx, total, resultado.error);
                if (!quiereReintentar) {
                    throw new Error(
                        'Cancelado por el vendedor en pedido ' + idx + '/' + total +
                        ' de E' + empresa
                    );
                }
                // Si dijo si, el while loop vuelve a intentar
            }
        }

        async function intentarChunkConRetryInterno(chunk, empresa, claveCliente, usuario, suPedido) {
            // 5 reintentos automaticos con dormir(2s) entre cada uno.
            // Devuelve {exito:true, folio} en caso de exito,
            // {exito:false, error} si todos los reintentos fallan.
            var ultimoError = null;
            for (var intento = 1; intento <= MAX_INTENTOS_SAE; intento++) {
                try {
                    var folio = await intentarInsercionSae(chunk, empresa, claveCliente, usuario, suPedido);
                    if (intento > 1) {
                        console.log('intentarChunkConRetryInterno exito en intento ' + intento + '/' + MAX_INTENTOS_SAE);
                    }
                    return { exito: true, folio: folio };
                } catch (err) {
                    ultimoError = err;
                    console.warn(
                        'intentarChunkConRetryInterno empresa=' + empresa +
                        ' intento=' + intento + '/' + MAX_INTENTOS_SAE +
                        ' fallo:', err.message
                    );
                    if (intento < MAX_INTENTOS_SAE) {
                        await dormir(ESPERA_ENTRE_INTENTOS_MS);
                    }
                }
            }
            return { exito: false, error: ultimoError ? ultimoError.message : 'desconocido' };
        }

        async function intentarInsercionSae(partidas, empresa, claveCliente, usuario, suPedido) {
            // Un solo POST a /catalowari/api/guardar_v2. Sin retry interno.
            // Lanza Error en cualquier falla; el llamador maneja retries.
            var partidasParaSae = (partidas || []).map(function(p) {
                return partidaParaSae(p, empresa);
            });

            var payload = {
                empresa:   empresa,
                cliente:   claveCliente,
                usuario:   usuario || '',
                su_pedido: suPedido || '',
                // origen 'W' = telemarketing. SAE genera serie CAMPLIB13+W
                // (ej. PEDW). El carrito manda 'CW' para distinguirse.
                origen:    'W',
                partidas:  partidasParaSae,
            };

            var resp = await fetch(
                'https://sistemasowari.com:8443/catalowari/api/guardar_v2',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(payload),
                }
            );

            if (!resp.ok) {
                throw new Error('SAE respondio HTTP ' + resp.status);
            }

            var data = await resp.json();
            if (!data || data.code !== 1 || !data.pedido) {
                throw new Error('SAE rechazo: ' + ((data && data.mensaje) || 'sin mensaje'));
            }

            return data.pedido;   // ej. "4W12345"
        }

        function partidaParaSae(p, empresa) {
            // El precio FINAL es el mismo en ambas sucursales (el total no cambia):
            //   empresa 1 (factura): precio unitario SIN IVA; SAE le suma el 16%
            //     (IMPU4) y el total queda con IVA.
            //   empresa 3 (remision): SAE no maneja impuestos, asi que el precio
            //     unitario va YA CON IVA (sin linea de impuesto) para que el total
            //     quede igual que en factura.
            // p.precio = precio con IVA (mostrado); p.precio_iva = precio sin IVA.
            var cantidad     = parseInt(p.cantidad) || 0;
            var precioSinIva = parseFloat(p.precio_iva) || 0;
            var precioConIva = parseFloat(p.precio) || 0;
            var totalConIva  = parseFloat(p.total) || 0;

            return {
                clave:    p.codigo,
                cantidad: cantidad,
                precio:   empresa === 1 ? precioSinIva : precioConIva,
                total:    totalConIva,
            };
        }

        function dividirEnChunks(arr, n) {
            var chunks = [];
            for (var i = 0; i < arr.length; i += n) {
                chunks.push(arr.slice(i, i + n));
            }
            return chunks;
        }

        async function guardarPedidoLocal(payload) {
            // POST al endpoint existente pedidos.guardar para crear el
            // espejo del pedido en la tabla `pedidos` (modelo Pedido).
            //
            // Notas:
            //   - El endpoint guarda el request completo en la columna
            //     `entrada` (JSON), asi que todos los campos v2 viajan ahi.
            //   - El endpoint TAMBIEN intenta clasificar partidas en
            //     partidas_a/partidas_b, pero como nuestro v2 ya hizo la
            //     clasificacion correcta con clasificarPorEmpresa,
            //     ignoramos esos campos de la respuesta.
            //   - Pasamos partidas_detalle vacio para que el endpoint no
            //     duplique trabajo.
            //   - Mantenemos compat (cliente como objeto, tipo del radio).
            //
            // Devuelve el id_pedido local para poder mostrar resumen al
            // vendedor en mostrarExito.
            var data = {
                '_token':           '{{ csrf_token() }}',
                usuario:            '{{ \Auth::user()->name }}',
                cliente:            { clave: payload.cliente },
                partidas:           payload.partidas_sae || [],
                partidas_detalle:   [],   // v2 ya clasifico
                su_pedido:          $('#su_pedido').val() || '',
                tipo:               payload.tipo_radio || 'normal',

                // Campos v2 — quedan registrados en `entrada` JSON para auditoria
                folios_factura:     payload.folios_factura  || [],
                folios_remision:    payload.folios_remision || [],
                especiales_resumen: payload.especiales      || {},
                regalo:             payload.regalo          || null,
                tipo_radio:         payload.tipo_radio      || null,
                es_v2:              true,
            };

            return new Promise(function(resolve, reject) {
                $.post("{{ route('pedidos.guardar') }}", data)
                    .done(function(resp) {
                        try {
                            var r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                            if (r && r.code) {
                                resolve(r.id_pedido);
                            } else {
                                reject(new Error('No se pudo registrar el pedido en el espejo local'));
                            }
                        } catch (e) {
                            reject(new Error('Respuesta invalida del espejo local'));
                        }
                    })
                    .fail(function(xhr) {
                        console.warn('guardarPedidoLocal fallo', xhr && xhr.status);
                        reject(new Error('Error de red al registrar el espejo local'));
                    });
            });
        }




        function descargarExcel() {

            var workbook = ExcelBuilder.Builder.createWorkbook();
            var worksheet = workbook.createWorksheet({ name: 'PEDIDO' });
            var stylesheet = workbook.getStyleSheet();

            var originalData = [
                ['CLAVE', 'CANTIDAD'],
            ];

            var partidas_formulario = table_partidas.getData();
            $.each(partidas_formulario, function (key, value) {
                originalData.push([value.codigo, value.cantidad]);
            });

            worksheet.setData(originalData);
            workbook.addWorksheet(worksheet);





            ExcelBuilder.Builder.createFile(workbook).then(function (data) {
                window.open("data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," + data, '_blank');
            });

        }

        // ──────────────────────────────────────────────────────────────────
        // Inicializacion del formulario (Fase 4 del refactor v2)
        // ──────────────────────────────────────────────────────────────────
        // Antes: el vendedor primero elegia radio (normal/factura) y eso
        // disparaba la carga de clientes. Ahora:
        //   1. Al cargar la pagina, lista UNICA desde /api/clientes_factura
        //   2. Vendedor elige cliente del select
        //   3. Se enriquece con CLASIFIC y CAMPLIB3 (datos_cliente)
        //   4. Si CLASIFIC tiene W en pos.4 → ocultar radios (auto E01/E03)
        //      Si NO tiene W → mostrar radios para elegir normal/factura
        // El handler viejo de .tipo_cliente.click se conserva abajo solo para
        // hacer reiniciarVenta() cuando el vendedor cambia entre los radios.
        // ──────────────────────────────────────────────────────────────────

        function inicializarFormularioPedido() {
            $.get(
                'https://sistemasowari.com:8443/catalowari/api/clientes_factura',
                { vendedor: '' },
                function (data) {
                    var obj = jQuery.parseJSON(data);
                    clientes = obj;

                    $("#cliente").html('<option value="-1">Selecciona o busca un cliente</option>');
                    $.each(obj, function (i, val) {
                        $("#cliente").append(
                            '<option value="' + i + '">' + val.clave + ' ' + val.nombre + '</option>'
                        );
                    });

                    $('.seleccionar_cliente').css('display', 'block');

                    $("#cliente").val('-1')
                        .chosen({ no_results_text: "Oops, no hay resultados!" })
                        .change(onClienteSeleccionadoEnSelect)
                        .trigger('chosen:updated')
                        .trigger('chosen:activate');
                }
            );
        }

        async function onClienteSeleccionadoEnSelect() {
            seleccion = false;
            var indice = $("#cliente").val();
            if (indice === undefined || parseInt(indice) < 0) return;

            var cliente = clientes[indice];
            clave_cliente = cliente.clave;
            empresa_cliente = cliente.EMPRESA;

            reiniciarVenta();

            // Enriquecer con CLASIFIC, CAMPLIB3 y EXISTE_E3 desde SAE.
            // datos_cliente devuelve el row crudo de CLIE01 LEFT JOIN CLIE_CLIB01,
            // y un flag EXISTE_E3 que indica si el cliente esta en CLIE03.
            try {
                var resp = await fetch(
                    'https://sistemasowari.com:8443/catalowari/api/datos_cliente?clave=' +
                    encodeURIComponent(cliente.clave)
                );
                if (resp.ok) {
                    var datos = await resp.json();
                    if (datos && datos.CLAVE) {
                        cliente.CLASIFIC  = (datos.CLASIFIC || '').toString().trim();
                        cliente.CAMPLIB3  = (datos.CAMPLIB3 || '').toString().trim();
                        cliente.EXISTE_E3 = (typeof datos.EXISTE_E3 === 'boolean') ? datos.EXISTE_E3 : undefined;
                        clientes[indice] = cliente;   // persiste enriquecimiento
                    }
                }
            } catch (e) {
                console.warn('No se pudo enriquecer cliente con CLASIFIC/EXISTE_E3:', e);
            }

            // Decidir radios segun W en pos.4 de CLASIFIC + presencia en CLIE03.
            //
            //   - Cliente CON W → ocultar radios (v2 hace split automatico E01/E03).
            //   - Cliente SIN W y SIN CLIE03 → ocultar radios y forzar 'factura'.
            //     Sin CLIE03 no puede recibir remision, asi que "normal" no aplica
            //     y el unico destino posible es E01. Mostrar las opciones seria
            //     confuso porque ambas terminarian igual.
            //   - Cliente SIN W y CON CLIE03 → mostrar radios para elegir normal
            //     (split por existencia) o todo a factura.
            //
            // EXISTE_E3 puede ser undefined si el backend viejo no devuelve el
            // flag — en ese caso preservamos comportamiento anterior (mostrar radios).
            if (tieneWEnPos4(cliente.CLASIFIC || '')) {
                $('#radios_tipo_pedido').hide();
                // Forzar 'normal' (oculto) por compat con guardar_pedido viejo
                // que aun lee el radio. El v2 detecta W e ignora el radio.
                $('#pedido_normal').prop('checked', true);
            } else if (cliente.EXISTE_E3 === false) {
                $('#radios_tipo_pedido').hide();
                $('#pedido_todo_factura').prop('checked', true);
            } else {
                $('#radios_tipo_pedido').show();
                if (!$("input:radio[name='tipo_pedido']:checked").val()) {
                    $('#pedido_normal').prop('checked', true);
                }
            }

            $("#datos_cliente").html(
                '<div><label>Clave:</label><small>&nbsp;' + cliente.clave + '</small></div>' +
                '<div><label>Nombre:</label><small>&nbsp;' + cliente.nombre + '</small></div>' +
                (cliente.CLASIFIC
                    ? '<div><label>Clasificacion:</label><small>&nbsp;' + cliente.CLASIFIC + '</small></div>'
                    : '')
            );

            $(".mostrar_busqueda,.partidas").show();
            setTimeout(function () {
                $("#palabras_clave").val("").focus();
            }, 500);
        }

        // Al cargar la pagina, traer la lista de clientes y montar el select.
        $(document).ready(function () {
            inicializarFormularioPedido();
        });

        // El handler viejo .tipo_cliente.click ya NO carga clientes (lista unica
        // desde init). Solo reinicia las partidas si el vendedor cambia entre
        // los radios visibles (clientes sin W).
        $(".tipo_cliente").click(function () {
            reiniciarVenta();
        });


        $('.terminar').click(function (event) {
            /* Act on the event */
            location.reload();
        });


    </script>
    <link rel="stylesheet" href="/assets/chosen/chosen.css" />
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="/assets/slick/slick-theme.css" />

    <style>
        label {
            font-size: 13px;
            font-weight: bold;
        }

        #datos_cliente label,
        #resultados_producto label {
            font-size: 13px;
            font-weight: bold;
        }

        #datos_cliente small,
        #resultados_producto small {
            font-size: 13px;
            font-weight: lighter;
        }

        .form-label {
            font-weight: bold;
        }

        .mostrar_busqueda {
            display: none;
        }
        /* .seleccionar_cliente ya NO se oculta — el select de cliente esta
           visible desde que carga la pagina. */

        #resultados_producto {
            color: blue;
        }

        .nota_precio {
            color: red;
            font-weight: bold !important;
        }

        #resultados_producto,
        .partidas,
        .mostrar_resultados {
            display: none;
        }

        .agregar-cantidad {
            display: none;
        }

        .forzar-busqueda:hover {
            cursor: pointer;
        }

        .totales_estilos span {
            font-size: 20px;
            font-weight: bold;
        }

        body {
            font-size: 13px;
        }
    </style>
@endsection