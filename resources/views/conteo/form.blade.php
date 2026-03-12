<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Conteo</title>
    <style>
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 100vh; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <form action="{{ route('conteo.exportar') }}" method="GET">
        <h2>Generar Reporte de Inventario</h2>
        
        <div>
            <label for="conteo">Número de Conteo:</label>
            <input 
                type="number" 
                id="conteo" 
                name="conteo" 
                placeholder="Ej: 45" 
                required>
        </div>
        
        <button type="submit">Generar y Descargar Excel</button>
    </form>

</body>
</html>