@extends('layouts.app') @section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form>
                            <div class="row">
                                <div class="col-md-6 offset-md-1">
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
                buscarProducto(clave_cliente, fila_seleccionada.getData().codigo, 1, fila_seleccionada.getData().equivalencias);
                e.preventDefault();
            }
            if (e.key === 'b' && e.ctrlKey) {
                // Add your code here
                $("#palabras_clave").val("").focus();
            }
        }, false);

        $('#guardar').click(function () {

            var partidas_formulario = table_partidas.getData();

            if (($('#su_pedido').val() == "" || $('#su_pedido').val() == null) && partidas_formulario.length > 0) {
                alert("Confirma si es mostrador o ingresa la hora de entrega");
                $('#su_pedido').focus();
            }
            else {
                guardar_pedido();
            }

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
            buscarProducto(clave_cliente, fila_seleccionada.getData().codigo, 1, fila_seleccionada.getData().equivalencias, fila_seleccionada.getData().precio_normal);
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

            var disponible = fila_seleccionada.getData().disponibilidad;
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

            let a_especial = 0
            if (cantidad > obj.existencia) {

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

            if (cantidad > 0) {
                console.log(partidas);
                $.each(partidas, function (i, v) {
                    if (v.clave == producto_partida.clave) {
                        if (confirm("Este producto ya fue capturado, presiona OK para sumarle esta cantidad o presiona Cancelar para sustituir.")) {

                            var cantidad_final = parseInt(cantidad);
                            var total_partidas = table_partidas.getData();
                            $.each(total_partidas, function (j, k) {
                                if (k.codigo == v.clave)
                                    cantidad_final += parseInt(k.cantidad)
                            });
                            actualizarPrecio(cantidad_final, producto_partida.clave);
                        }
                        else {
                            actualizarPrecio(cantidad, producto_partida.clave);

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

                table_partidas.addData([{
                    "codigo": obj.clave,
                    "descripcion": obj.name,
                    "cantidad": cantidad,
                    "precio": parseFloat(precio).toFixed(2),
                    "precio_iva": parseFloat(precio_iva).toFixed(2),
                    "total": (cantidad * precio).toFixed(2)
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
                            'disponibilidad': val.disponibilidad

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

        function buscarProducto($cliente, $clave, $cantidad, $equivalencias, $precio_normal) {
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


        function guardar_pedido() {
            //armamos el data del formulario

            $('#guardar').attr('disabled', 'disabled');
            var alertas = "";

            if ($("#cliente").val() < 0) {
                alertas += "<h5>Selecciona un cliente valido</h5>";
                $(".texto_modal").html(alertas);
                $("#modal").modal("show");
                $('#guardar').removeAttr('disabled');
                return false;
            }

            var partidas_formulario = table_partidas.getData();
            var partidas_formulario_especial = table_partidas_especiales.getData();

            if (partidas_formulario.length <= 0 && partidas_formulario_especial.length <= 0) {
                alertas += "<h5>Ingresa por lo menos una partida en el pedido</h5>";
                $(".texto_modal").html(alertas);
                $("#modal").modal("show");
                $('#guardar').removeAttr('disabled');
                return false;
            }

            $("#cargando").css('display', 'block');
            $("#pedidos_finales").css('display', 'block');



            var partidas_soma_especial = [];

            for (var i = 0; i < partidas_formulario_especial.length; i++) {
                partidas_soma_especial.push({
                    "clave": partidas_formulario_especial[i].codigo,
                    "cantidad": partidas_formulario_especial[i].cantidad,
                    "precio": partidas_formulario_especial[i].precio,
                    "total": partidas_formulario_especial[i].total
                });
            }

            var soma_especial = {
                "clave_cliente": clientes[$("#cliente").val()],
                "clave_sucursal": "E01",
                "tipo_serie": "PE",
                "partidas": partidas_soma_especial
            }

            if (partidas_formulario_especial.length > 0) {
                var data = {
                    cliente: clientes[$("#cliente").val()],
                    '_token': "{{ csrf_token() }}",
                    partidas: partidas_formulario_especial
                };

                $.post("{{ route('pedidos.guardar_especial') }}", data,
                    function (data, textStatus, jqXHR) {
                        $.post("https://owari.appsoma.online/somma/v2.0/pedidos/especial/externo", soma_especial,
                            function (data, textStatus, jqXHR) {
                                if (partidas_formulario.length <= 0) {
                                    if (data.code) {
                                        $(".texto_modal").html("<h5>Tu pedido especial fue guardado, tiene que ser validado.</h5>");
                                        $(".modal-footer-especiales").show();
                                        table_partidas_especiales.setData([]);
                                    }
                                    else {

                                    }
                                }
                            },
                            "json"
                        );
                    },
                    "json"
                );
            }



            var data = {
                usuario: '{{ \Auth::user()->name }}',
                cliente: clientes[$("#cliente").val()],
                '_token': "{{ csrf_token() }}",
                partidas: partidas_formulario,
                partidas_detalle: partidas,
                'su_pedido': $('#su_pedido').val(),
                'tipo': $("input:radio[name ='tipo_pedido']:checked").val()
            };



            $(".texto_modal").html("<h5>Tu pedido esta guardandose...</h5>");
            $(".modal-footer").hide();
            $("#modal").modal("show");

            //GUARDAMOS EL PEDIDO EN POSTGRES



            $.post("{{ route('pedidos.guardar') }}", data,
                function (data, textStatus, jqXHR) {
                    if (data.code) {
                        $(".texto_modal").html("<h5>Tus pedidos se esta guardando espera un momento.</h5>");
                        $(".modal-footer").show();


                        partidas_formulario = table_partidas.getData();

                        var partes_empresa_uno = dividirEnPartes(data.partidas_a, 30);
                        var partes_empresa_tres = dividirEnPartes(data.partidas_b, 30);

                        console.log(partes_empresa_uno, partes_empresa_tres);

                        partes_empresa_uno.forEach(function (arreglo) {

                            var auxiliar_uno = [];
                            arreglo.forEach(function (value) {
                                partidas_formulario.forEach(function (valor) {
                                    if (valor['codigo'] == value['clave']) {
                                        auxiliar_uno.push(valor);
                                        return false;
                                    }
                                });
                            });
                            partidas_partidas_uno.push(auxiliar_uno);
                        });

                        partes_empresa_tres.forEach(function (arreglo) {
                            var auxiliar_tres = [];
                            arreglo.forEach(function (value) {
                                partidas_formulario.forEach(function (valor) {
                                    if (valor['codigo'] == value['clave']) {
                                        auxiliar_tres.push(valor);
                                        return false;
                                    }
                                });
                            });
                            partidas_partidas_tres.push(auxiliar_tres);
                        });

                        partidas_partidas_uno.forEach(function (data, index) {
                            console.log("INDICE 1: " + index)


                            var partidas_soma_pedido = [];

                            for (var i = 0; i < partidas_partidas_uno[index].length; i++) {
                                partidas_soma_pedido.push({
                                    "clave": partidas_partidas_uno[index][i].codigo,
                                    "cantidad": partidas_partidas_uno[index][i].cantidad,
                                    "precio": partidas_partidas_uno[index][i].precio,
                                    "total": partidas_partidas_uno[index][i].total
                                });
                            }


                            var soma_pedido = {
                                "clave_cliente": clientes[$("#cliente").val()],
                                "clave_sucursal": "E01",
                                "tipo_serie": "P",
                                "partidas": partidas_soma_pedido
                            }

                            $.post("https://owari.appsoma.online/somma/v2.0/pedidos/externo", soma_pedido,
                                function (data, textStatus, jqXHR) {

                                },
                                "json"
                            );



                            guardar_pedido_sae(1, index);
                        });

                        partidas_partidas_tres.forEach(function (data, index) {
                            console.log("INDICE 3: " + index)

                            var partidas_soma_pedido = [];

                            for (var i = 0; i < partidas_partidas_tres[index].length; i++) {
                                partidas_soma_pedido.push({
                                    "clave": partidas_partidas_tres[index][i].codigo,
                                    "cantidad": partidas_partidas_tres[index][i].cantidad,
                                    "precio": partidas_partidas_tres[index][i].precio,
                                    "total": partidas_partidas_tres[index][i].total
                                });
                            }


                            var soma_pedido = {
                                "clave_cliente": clientes[$("#cliente").val()],
                                "clave_sucursal": "E03",
                                "tipo_serie": "P",
                                "partidas": partidas_soma_pedido
                            }

                            $.post("https://owari.appsoma.online/somma/v2.0/pedidos/externo", soma_pedido,
                                function (data, textStatus, jqXHR) {

                                },
                                "json"
                            );

                            guardar_pedido_sae(3, index);
                        });

                        total_pedidos = partidas_partidas_uno.length + partidas_partidas_tres.length;

                        var texto = "NO hay PEDIDOS para la empresa 1";
                        if (partidas_partidas_uno.length > 0)
                            texto = "Se GUARDAN " + partidas_partidas_uno.length + " pedidos para la empresa 1";

                        $("#guardar_pedido_1").html('<label>' + texto + '</label>');

                        var texto = "NO hay PEDIDOS para la empresa 3";
                        if (partidas_partidas_tres.length > 0)
                            texto = "Se GUARDAN " + partidas_partidas_tres.length + " pedidos para la empresa 3";

                        $("#guardar_pedido_3").html('<label>' + texto + '</label>');





                    }
                    else {
                        $(".texto_modal").html("<h5>Tu pedido no se guardo, ocurrio un error general, vuelve a capturarlo.</h5>");
                        $(".modal-footer").show();
                        $('#guardar').removeAttr('disabled');
                    }
                },
                "json"
            ).fail(function () {
                $(".texto_modal").html("<h5>Tu pedido no se guardo, ocurrio un error general, vuelve a capturarlo.</h5>");
                $('#guardar').removeAttr('disabled');
                $(".modal-footer").show();
            });


        }

        var guardado_ok = 0;
        var desaparece = 0;

        function guardar_pedido_sae(empresa, numero_index) {
            desaparece++;
            if (empresa == 1)
                var partidas_finales = partidas_partidas_uno[numero_index];
            else
                var partidas_finales = partidas_partidas_tres[numero_index];

            var data = {
                usuario: '{{ \Auth::user()->name }}',
                cliente: clientes[$("#cliente").val()],
                '_token': "{{ csrf_token() }}",
                partidas: partidas_finales,
                partidas_detalle: partidas,
                'su_pedido': $('#su_pedido').val(),
                'empresa_seleccionada': empresa,
                tipo: $("input:radio[name ='tipo_pedido']:checked").val()
            };

            console.log(partidas_finales);

            $.post("https://sistemasowari.com:8443/catalowari/api/guardar", data,
                function (data, textStatus, jqXHR) {
                    if (data.code) {
                        if (data.pedido != "N/A")
                            $("#guardar_pedido_" + empresa).append("<h5>Tu pedido " + (numero_index + 1) + " de E" + empresa + " fue el: " + data.pedido + "</h5>");

                        guardado_ok++;

                        if (guardado_ok >= total_pedidos) {
                            $('.reiniciar_pantalla').removeClass('d-none');
                            $('.reiniciar_pantalla').click(function (e) {
                                e.preventDefault();
                                table_partidas.setData([]);
                                location.reload();
                            });
                        }
                    }
                    else {
                        $("#guardar_pedido_" + empresa).append('<div class="click_desaparece">Uno de tus pedidos no se guardo <button href="#" onclick="guardar_pedido_sae(' + empresa + ',' + numero_index + ')" class="bnt btn-warning desaparece_' + desaparece + '">Intenta guardar nuevamente E' + empresa + ' Pedido ' + (numero_index + 1) + '</button></div>');
                        $('.desaparece_' + desaparece).click(function () {
                            $(this).closest('.click_desaparece').hide();
                        })
                    }
                },
                "json"
            ).fail(function () {
                $("#guardar_pedido_" + empresa).append('<div class="click_desaparece">Uno de tus pedidos no se guardo <button href="#" onclick="guardar_pedido_sae(' + empresa + ',' + numero_index + ')" class="bnt btn-warning desaparece_' + desaparece + '">Intenta guardar nuevamente E' + empresa + ' Pedido ' + (numero_index + 1) + '</button></div>');
                $('.desaparece_' + desaparece).click(function () {
                    $(this).closest('.click_desaparece').hide();
                })
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

        $(".tipo_cliente").click(function () {
            reiniciarVenta()
            var tipo_cliente = $(this).val();
            var url = "";
            if (tipo_cliente == 'factura') {
                url = "https://sistemasowari.com:8443/catalowari/api/clientes_factura"
            }
            if (tipo_cliente == "normal") {
                url = "https://sistemasowari.com:8443/catalowari/api/clientes"
            }
            $('.seleccionar_cliente').css('display', 'none');
            $.get(
                url, { vendedor: '' },
                function (data) {
                    var obj = jQuery.parseJSON(data);
                    clientes = obj;

                    $("#cliente").html('<option value="-1">Selecciona o busca un cliente</option>')
                    $.each(obj, function (i, val) {
                        $("#cliente").append(
                            '<option value="' +
                            i +
                            '">' +
                            val.clave +
                            " " +
                            val.nombre +
                            "</option>"
                        );
                    });




                    $('.seleccionar_cliente').css('display', 'block');
                    $("#cliente").val('-1')
                        .chosen({ no_results_text: "Oops, no hay resultados!" })
                        .change(function () {
                            seleccion = false;
                            var indice = $(this).val();
                            clave_cliente = clientes[indice].clave;
                            empresa_cliente = clientes[indice].EMPRESA;

                            reiniciarVenta();


                            $("#datos_cliente").html(
                                `
                                            <div>
                                                <label>Clave:</label>
                                                <small>` +
                                clientes[indice].clave +
                                `</small>
                                            </div>
                                            <div>
                                                <label>Nombre:</label>
                                                <small>` +
                                clientes[indice].nombre +
                                `</small>
                                            </div>
                                            `
                            );
                            $(".mostrar_busqueda,.partidas").show();
                            setTimeout(() => {
                                $("#palabras_clave").val("").focus();
                            }, 500);
                        }).trigger('chosen:updated').trigger('chosen:activate');






                }
            );
        });


        function dividirEnPartes(arr, n) {
            var resultado = [];
            for (var i = 0; i < arr.length; i += n) {
                resultado.push(arr.slice(i, i + n));
            }
            return resultado;
        }

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

        .mostrar_busqueda,
        .seleccionar_cliente {
            display: none;
        }

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