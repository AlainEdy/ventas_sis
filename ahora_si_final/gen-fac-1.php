<?php
// Incluir el archivo de cabecera
include('header.php');

// Incluir conexión a la base de datos
require 'database/connection.php';

// Inicializar variables de sesión
session_start();

// Obtener productos de la base de datos
try {
    $productos = $pdo->query("SELECT id, nombre, descripcion, precio FROM productos")->fetchAll(PDO::FETCH_ASSOC);

    $productosOptions = '';
    foreach ($productos as $producto) {
        $productosOptions .= '<option value="' . htmlspecialchars($producto['id']) . '" ' .
            'data-descripcion="' . htmlspecialchars($producto['descripcion']) . '" ' .
            'data-precio="' . htmlspecialchars($producto['precio']) . '">' .
            htmlspecialchars($producto['nombre']) . '</option>';
    }
} catch (PDOException $e) {
    echo "Error al obtener los productos: " . $e->getMessage();
    die();
}

// Datos del emisor
$emisor = array(
    'tipodoc'                   => '6',
    'nrodoc'                    => '20123456789',
    'razon_social'              => 'AFAMA SAC',
    'nombre_comercial'          => 'AFAMA',
    'direccion'                 => 'VIRTUAL',
    'ubigeo'                    => '200101',
    'departamento'              => 'PUNO',
    'provincia'                 => 'PUNO',
    'distrito'                  => 'PUNO',
    'pais'                      => 'PE',
    'usuario_secundario'        => 'MODDATOS',
    'clave_usuario_secundario'  => 'MODDATOS'
);

$emisor_tipodoc = isset($emisor['tipodoc']) ? $emisor['tipodoc'] : "No disponible";
$emisor_nrodoc = isset($emisor['nrodoc']) ? $emisor['nrodoc'] : "No disponible";
$emisor_razonsocial = isset($emisor['razon_social']) ? $emisor['razon_social'] : "No disponible";

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = $_POST['cliente'];
    $comprobante = $_POST['comprobante'];
    $comprobante['fecha_emision'] = date('Y-m-d');
    $comprobante['hora'] = date('H:i:s');
    $comprobante['fecha_vencimiento'] = date('Y-m-d');
    $comprobante['total_opgravadas'] = 0;
    $comprobante['total_opexoneradas'] = 0;
    $comprobante['total_opinafectas'] = 0;
    $comprobante['total_impbolsas'] = 0;
    $comprobante['total_opgratuitas_1'] = 0;
    $comprobante['total_opgratuitas_2'] = 0;
    $comprobante['igv'] = 0;
    $comprobante['total'] = 0;

    // Procesar detalle de productos
    $detalle = array();
    foreach ($_POST['detalle'] as $key => $item) {
        $cantidad = floatval($item['cantidad']);
        $precio_unitario = floatval($item['precio_unitario']);
        $tipo_afectacion = $item['tipo_afectacion_igv'];

        // Calcular valores según tipo de afectación
        if ($tipo_afectacion == '10') { // Gravado
            $valor_unitario = round($precio_unitario / 1.18, 2); // Redondear a 2 decimales
            $igv = round(($precio_unitario - $valor_unitario) * $cantidad, 2); // Redondear a 2 decimales
            $porcentaje_igv = 18;
            $codigo_tributo = '1000';
            $tipo_tributo = 'VAT';
            $nombre_tributo = 'IGV';
        } else if ($tipo_afectacion == '20') { // Exonerado
            $valor_unitario = round($precio_unitario, 2); // Redondear a 2 decimales
            $igv = 0;
            $porcentaje_igv = 0;
            $codigo_tributo = '9997';
            $tipo_tributo = 'VAT';
            $nombre_tributo = 'EXO';
        } else { // Inafecto
            $valor_unitario = round($precio_unitario, 2); // Redondear a 2 decimales
            $igv = 0;
            $porcentaje_igv = 0;
            $codigo_tributo = '9998';
            $tipo_tributo = 'FRE';
            $nombre_tributo = 'INA';
        }

        $detalle[] = array(
            'item'                      => $key + 1,
            'codigo'                    => $item['codigo'],
            'descripcion'               => $item['descripcion'],
            'cantidad'                  => $cantidad,
            'precio_unitario'           => $precio_unitario,
            'valor_unitario'            => $valor_unitario,
            'igv'                       => $igv,
            'tipo_precio'               => '01',
            'porcentaje_igv'            => $porcentaje_igv,
            'importe_total'             => round($cantidad * $precio_unitario, 2), // Redondear a 2 decimales
            'valor_total'               => round($cantidad * $valor_unitario, 2), // Redondear a 2 decimales
            'unidad'                    => 'NIU',
            'bolsa_plastica'            => 'NO',
            'total_impuesto_bolsas'     => 0.00,
            'tipo_afectacion_igv'       => $tipo_afectacion,
            'codigo_tipo_tributo'       => $codigo_tributo,
            'tipo_tributo'              => $tipo_tributo,
            'nombre_tributo'            => $nombre_tributo
        );
    }

    // Procesar cuotas si es venta a crédito
    $cuotas = array();
    if ($comprobante['forma_pago'] == 'Credito' && isset($_POST['cuotas'])) {
        foreach ($_POST['cuotas'] as $cuota) {
            $cuotas[] = array(
                'cuota' => $cuota['cuota'],
                'monto' => floatval($cuota['monto']),
                'fecha' => $cuota['fecha']
            );
        }
    }

    // Calcular totales
    $total_opgravadas = 0;
    $total_opexoneradas = 0;
    $total_opinafectas = 0;
    $total_impbolsas = 0;
    $total = 0;
    $igv = 0;

    foreach ($detalle as $item) {
        if ($item['tipo_afectacion_igv'] == '10') {
            $total_opgravadas += $item['valor_total'];
        } else if ($item['tipo_afectacion_igv'] == '20') {
            $total_opexoneradas += $item['valor_total'];
        } else {
            $total_opinafectas += $item['valor_total'];
        }
        $igv += $item['igv'];
        $total += $item['importe_total'];
    }

    // Inicializar variables adicionales faltantes
    $op_gratuitas1 = 0.00;
    $op_gratuitas2 = 0.00;

    $comprobante['total_opgravadas'] = $total_opgravadas;
    $comprobante['total_opexoneradas'] = $total_opexoneradas;
    $comprobante['total_opinafectas'] = $total_opinafectas;
    $comprobante['total_impbolsas'] = $total_impbolsas;
    $comprobante['total_opgratuitas_1'] = $op_gratuitas1;
    $comprobante['total_opgratuitas_2'] = $op_gratuitas2;
    $comprobante['igv'] = $igv;
    $comprobante['total'] = $total;

    // Convertir total a letras
    require_once('cantidad_en_letras.php');
    $comprobante['total_texto'] = CantidadEnLetra($total);

    // Generar XML
    require_once('./api/api_genera_xml.php');
    $obj_xml = new api_genera_xml();

    $nombreXML = $emisor['nrodoc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];
    $rutaXML = 'xml/';

    $obj_xml->crea_xml_invoice($rutaXML . $nombreXML, $emisor, $cliente, $comprobante, $detalle, $cuotas);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Factura Electrónica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Generar Factura Electrónica</h2>

        <!-- Datos del Emisor -->
        <div class="bg-white shadow-lg bg-blue-500 rounded-lg mb-4">
            <div class="bg-gray-800  px-6 py-2 border-b border-gray-200 rounded-t-lg">
                <h3 class="text-xl font-semibold text-white">Datos del Emisor</h3>
            </div>
            <div class="p-4 space-y-2">
                <!-- Primera fila -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Documento</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= $emisor['tipodoc'] == '6' ? 'RUC' : 'DNI'; ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número Documento</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['nrodoc']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Razón Social</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['razon_social']); ?>
                        </p>
                    </div>
                </div>
                <!-- Segunda fila -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['direccion']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ubigeo</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['ubigeo']); ?>
                        </p>
                    </div>
                </div>
                <!-- Tercera fila -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['departamento']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provincia</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['provincia']); ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Distrito</label>
                        <p class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50">
                            <?= htmlspecialchars($emisor['distrito']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form id="facturaForm" method="POST" action="" class="space-y-6">
            <!-- Datos del Cliente -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="bg-gray-800 px-6 py-2 border-b border-gray-200 rounded-t-lg">
                    <h3 class="text-xl font-semibold text-white">Datos del Cliente</h3>
                </div>
                <div class="p-4 space-y-2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Documento</label>
                            <select name="cliente[tipodoc]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 p-3 bg-gray-100 focus:border-blue-500" required>
                                <option value="6">RUC</option>
                                <option value="1">DNI</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número Documento</label>
                            <input type="text" name="cliente[nrodoc]" class="mt-1 block w-full rounded-md border-gray-300 p-3 bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Razón Social</label>
                            <input type="text" name="cliente[razon_social]" class="mt-1 block w-full rounded-md border-gray-300 p-3 bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                            <input type="text" name="cliente[direccion]" class="mt-1 block w-full rounded-md border-gray-300 p-3 bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">País</label>
                            <input type="text" name="cliente[pais]" class="mt-1 block w-full rounded-md border-gray-300 p-3 bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500" value="PE" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos del Comprobante -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="bg-gray-800 px-6 py-2 border-b border-gray-200 rounded-t-lg">
                    <h3 class="text-xl font-semibold text-white">Datos del Comprobante</h3>
                </div>
                <div class="p-4 space-y-2">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Documento</label>
                            <select name="comprobante[tipodoc]" class="mt-1 block w-full rounded-md border-gray-300 p-3 bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="01">Factura</option>
                                <option value="03">Boleta</option>
                                <option value="07">Nota de Crédito</option>
                                <option value="08">Nota de Débito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Serie</label>
                            <select name="comprobante[serie]" class="mt-1 block w-full rounded-md p-3 bg-gray-100 border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="F001">F001</option>
                                <option value="B001">B001</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Correlativo</label>
                            <input type="number" name="comprobante[correlativo]" class="mt-1 block w-full rounded-md border-gray-300 p-3 bg-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500" value="1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Moneda</label>
                            <select name="comprobante[moneda]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 p-3 bg-gray-100 focus:border-blue-500" required>
                                <option value="PEN">Soles</option>
                                <option value="USD">Dólares</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pago</label>
                            <select name="comprobante[forma_pago]" id="formaPago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-3 bg-gray-100 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="Contado">Contado</option>
                                <option value="Credito">Crédito</option>
                            </select>
                        </div>
                        <div id="montoPendienteDiv" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto Pendiente</label>
                            <input type="number" step="0.01" name="comprobante[monto_pendiente]" id="montoPendiente" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuotas -->
            <div id="cuotasSection" class="bg-white shadow-lg rounded-lg hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900">Cuotas</h3>
                    <button type="button" onclick="agregarCuota()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Agregar Cuota
                    </button>
                </div>
                <div id="cuotasContainer" class="p-6 space-y-4">
                    <!-- Las cuotas se agregarán aquí dinámicamente -->
                </div>
            </div>

            <!-- Detalle de Productos -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="bg-gray-800 px-6 pt-2 mt-2 rounded-t-lg overflow-x-scroll md:overflow-hidden">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-xl font-semibold text-white">Detalle de Productos</h3>
                        <button type="button" onclick="agregarProducto()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Agregar Producto
                        </button>
                    </div>
                </div>
                <div class="px-6">
                    <div id="productosContainer" class="pt-6 space-y-1">
                        <table class="min-w-full table-auto border-collapse table-fixed">
                            <thead>
                                <tr class="bg-gray-800 text-white">
                                    <th class="py-1 border-b text-center" style="width: 12%;">Código</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Nombre Prod.</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Descripción del Prod.</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Precio</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Cant.</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Tipo de Afectación</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Subtotal</th>
                                    <th class="py-1 border-b text-center" style="width: 12%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="productosTableBody">
                                <!-- Aquí se agregarán las filas dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabla de Totales -->
                    <div class="mt-8 py-4">
                        <div class="w-full mr-auto">
                            <div class="bg-gray-200 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-800">Op. Gravadas:</span>
                                    <span id="totalOpGravadas" class="text-sm font-medium">0.00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-800">Op. Exoneradas:</span>
                                    <span id="totalOpExoneradas" class="text-sm font-medium">0.00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-800">Op. Inafectas:</span>
                                    <span id="totalOpInafectas" class="text-sm font-medium">0.00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-800">IGV:</span>
                                    <span id="totalIgv" class="text-sm font-medium">0.00</span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                    <span class="text-base font-semibold text-gray-900">Total:</span>
                                    <span id="totalGeneral" class="text-base font-semibold text-gray-900">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de Envío -->
            <div class="mt-6 flex justify-center">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Generar Factura
                </button>
            </div>

            <!-- Template para nuevos productos -->
            <template id="productoTemplate">
                <div class="bg-white border rounded-lg p-4 producto-row">
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-center">
                        <div>
                            <input type="text" name="detalle[INDEX][codigo]" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Código" required>
                        </div>
                        <div>
                            <input type="text" name="detalle[INDEX][descripcion]" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Descripción" required>
                        </div>
                        <div>
                            <input type="number" name="detalle[INDEX][cantidad]" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 cantidad" placeholder="Cant." required>
                        </div>
                        <div>
                            <input type="number" step="0.01" name="detalle[INDEX][precio_unitario]" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 precio" placeholder="Precio" required>
                        </div>
                        <div>
                            <select name="detalle[INDEX][tipo_afectacion_igv]" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 tipo-afectacion" required>
                                <option value="10">Gravado</option>
                                <option value="20">Exonerado</option>
                                <option value="30">Inafecto</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="subtotal text-sm font-medium">0.00</span>
                            <button type="button" onclick="eliminarProducto(INDEX)" class="inline-flex items-center p-2 border border-transparent rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <script>
                let productoCount = 0;

                // Función para agregar un nuevo producto
                function agregarProducto() {
                    const container = document.getElementById('productosContainer');
                    productoCount++;

                    const productoHTML = `
            <div class="producto-row" id="producto${productoCount}">
                <div class="grid grid-cols-1 md:grid-cols-8 gap-4 items-center bg-white p-4 shadow-md">
                    <!-- Código -->
                    <div>
                        <input type="text" name="detalle[${productoCount}][codigo]" id="producto_codigo_${productoCount}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Código" required readonly>
                    </div>
                    <!-- Nombre del Producto (Select para elegir el producto) -->
                    <div>
                        <select name="detalle[${productoCount}][nombre]" id="producto_nombre_${productoCount}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required onchange="rellenarDatosProducto(${productoCount})">
                            <option value="">Selecciona un producto</option>
                            ${productosOptions}
                        </select>
                    </div>
                    <!-- Descripción -->
                    <div class="md:col-span-1">
                        <textarea name="detalle[${productoCount}][descripcion]" 
                                  id="producto_descripcion_${productoCount}" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                  placeholder="Descripción" 
                                  required 
                                  rows="3" 
                                  style="resize: vertical;" 
                                  oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';"></textarea>
                    </div>
                    <!-- Precio -->
                    <div>
                        <input type="number" step="0.01" name="detalle[${productoCount}][precio_unitario]" id="producto_precio_${productoCount}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 precio" placeholder="Precio" required readonly>
                    </div>
                    <!-- Cantidad -->
                    <div>
                        <input type="number" name="detalle[${productoCount}][cantidad]" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 cantidad" placeholder="Cant." required min="0" step="any">
                    </div>
                    <!-- Tipo de Afectación -->
                    <div>
                        <select name="detalle[${productoCount}][tipo_afectacion_igv]" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 tipo-afectacion" required>
                            <option value="10">Gravado</option>
                            <option value="20">Exonerado</option>
                            <option value="30">Inafecto</option>
                        </select>
                    </div>
                    <!-- Subtotal -->
                    <div class="flex justify-center items-center">
                        <span class="subtotal text-xl font-medium">0.00</span>
                    </div>
                    <!-- Botón Eliminar en la misma fila -->
                    <div class="flex justify-center items-center">
                        <button type="button" class="bg-red-600 text-white hover:bg-red-900 p-2 rounded-md focus:outline-none" onclick="eliminarProducto(${productoCount})">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>`;
                    container.insertAdjacentHTML('beforeend', productoHTML);
                    actualizarEventListeners();
                }

                // Función para eliminar un producto
                function eliminarProducto(id) {
                    const elemento = document.getElementById(`producto${id}`);
                    elemento.remove();
                    calcularTotales();
                }

                // Función para rellenar los campos de descripción, precio y código según el producto seleccionado
                function rellenarDatosProducto(id) {
                    const selectProducto = document.getElementById(`producto_nombre_${id}`);
                    const descripcion = document.getElementById(`producto_descripcion_${id}`);
                    const precio = document.getElementById(`producto_precio_${id}`);
                    const codigo = document.getElementById(`producto_codigo_${id}`);

                    const productoSeleccionado = selectProducto.options[selectProducto.selectedIndex];

                    if (productoSeleccionado.value) {
                        descripcion.value = productoSeleccionado.getAttribute('data-descripcion');
                        precio.value = productoSeleccionado.getAttribute('data-precio');
                        codigo.value = productoSeleccionado.getAttribute('value');
                    } else {
                        descripcion.value = '';
                        precio.value = '';
                        codigo.value = '';
                    }
                }

                // Función para calcular totales
                function calcularTotales() {
                    let totalOpGravadas = 0;
                    let totalOpExoneradas = 0;
                    let totalOpInafectas = 0;
                    let totalIgv = 0;

                    document.querySelectorAll('.producto-row').forEach(row => {
                        const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
                        const precio = parseFloat(row.querySelector('.precio').value) || 0;
                        const tipoAfectacion = row.querySelector('.tipo-afectacion').value;
                        const subtotal = cantidad * precio;

                        row.querySelector('.subtotal').textContent = subtotal.toFixed(2);

                        if (tipoAfectacion === '10') { // Gravado
                            const valorSinIgv = subtotal / 1.18;
                            totalOpGravadas += valorSinIgv;
                            totalIgv += subtotal - valorSinIgv;
                        } else if (tipoAfectacion === '20') { // Exonerado
                            totalOpExoneradas += subtotal;
                        } else if (tipoAfectacion === '30') { // Inafecto
                            totalOpInafectas += subtotal;
                        }
                    });

                    document.getElementById('totalOpGravadas').textContent = totalOpGravadas.toFixed(2);
                    document.getElementById('totalOpExoneradas').textContent = totalOpExoneradas.toFixed(2);
                    document.getElementById('totalOpInafectas').textContent = totalOpInafectas.toFixed(2);
                    document.getElementById('totalIgv').textContent = totalIgv.toFixed(2);
                    document.getElementById('totalGeneral').textContent = (totalOpGravadas + totalOpExoneradas + totalOpInafectas + totalIgv).toFixed(2);
                }

                // Función para actualizar event listeners
                function actualizarEventListeners() {
                    document.querySelectorAll('.cantidad, .precio, .tipo-afectacion').forEach(input => {
                        input.removeEventListener('change', calcularTotales);
                        input.addEventListener('change', calcularTotales);
                    });
                }

                // Agregar primer producto al cargar la página
                window.addEventListener('load', agregarProducto);
            </script>

            <script>
                const productosOptions = `<?php echo $productosOptions; ?>`;
            </script>
</body>

</html>

<?php
// Función para generar el mensaje con estilo según el estado
function mostrarMensaje($estado_envio)
{
    // Determinar el color del cuadro según el estado
    $mensaje = $estado_envio['estado_mensaje'];
    $esExitoso = strpos($mensaje, 'PROCESO TERMINADO') !== false;

    // Estilo según el estado de éxito o error
    $estilo = $esExitoso ? 'bg-green-500 text-white' : 'bg-red-500 text-white';
    $borde = $esExitoso ? 'border-green-700' : 'border-red-700';

    // Mostrar el mensaje con el estilo adecuado
    echo "<div class='p-6 rounded-lg border-2 $borde mt-6'>";
    echo '</br> PARTE 01: XML DE FACTURA CREADO SATISFACTORIAMENTE';
    echo '</br> PARTE 2: ENVIO CPE-SUNAT';
    echo '</br> Estado de envío: ' . htmlspecialchars($estado_envio['estado']);
    echo '</br> Mensaje: ' . htmlspecialchars($estado_envio['estado_mensaje']);
    echo '</br> HASH_CPE: ' . htmlspecialchars($estado_envio['hash_cpe']);
    echo '</br> Descripción: ' . htmlspecialchars($estado_envio['descripcion']);
    echo '</br> Nota: ' . htmlspecialchars($estado_envio['nota']);
    echo '</br> Código de error: ' . htmlspecialchars($estado_envio['codigo_error']);
    echo '</br> Mensaje de error: ' . htmlspecialchars($estado_envio['mensaje_error']);
    echo '</br> HTTP CODE: ' . htmlspecialchars($estado_envio['http_code']);
    echo '</br> OUTPUT: ' . htmlspecialchars($estado_envio['output']);
    echo "</div>";
}

// Enviar a SUNAT
require_once('./api/api_cpe.php');

// Asegurarse de que $nombreXML esté definido
if (isset($nombreXML) && !empty($nombreXML)) {
    $objEnvio = new api_cpe();
    $estado_envio = $objEnvio->enviar_invoice($emisor, $nombreXML, 'certificado_digital/', 'xml/', 'cdr/');

    // Mostrar el mensaje de éxito o error
    mostrarMensaje($estado_envio);
} else {
    echo "<div class='p-6 rounded-lg border-2 border-red-700 mt-6'>Error: No se pudo generar el nombre del archivo XML.</div>";
}

// Asegurarse de que las variables $comprobante, $cliente, y $detalle estén definidas y no sean nulas
if (!isset($comprobante) || !is_array($comprobante)) {
    $comprobante = array(
        'serie' => '',
        'correlativo' => '',
        'total_opgravadas' => 0,
        'total_opexoneradas' => 0,
        'total_opinafectas' => 0,
        'igv' => 0,
        'total' => 0,
        'total_texto' => '',
        'forma_pago' => ''
    );
}

if (!isset($cliente) || !is_array($cliente)) {
    $cliente = array(
        'razon_social' => '',
        'nrodoc' => '',
        'direccion' => ''
    );
}

if (!isset($detalle) || !is_array($detalle)) {
    $detalle = array();
}

// Generar HTML de la factura
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .factura-titulo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .info-section h3 {
            margin-bottom: 10px;
            font-size: 18px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .totales {
            float: right;
            width: 300px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .totales .total-row {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .totales .total-row strong {
            font-weight: bold;
        }
        .footer {
            clear: both;
            margin-top: 30px;
            font-size: 16px;
        }
        .cuotas-section {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="factura-titulo">FACTURA ELECTRÓNICA</div>
        <div>' . htmlspecialchars($comprobante['serie']) . '-' . str_pad(htmlspecialchars($comprobante['correlativo']), 8, "0", STR_PAD_LEFT) . '</div>
    </div>

    <div class="info-section">
        <h3>DATOS DEL EMISOR</h3>
        <p><strong>Razón Social:</strong> ' . (isset($emisor['razon_social']) ? htmlspecialchars($emisor['razon_social']) : 'No especificado') . '</p>
        <p><strong>RUC:</strong> ' . (isset($emisor['nrodoc']) ? htmlspecialchars($emisor['nrodoc']) : 'No especificado') . '</p>
        <p><strong>Dirección:</strong> ' . (isset($emisor['direccion']) ? htmlspecialchars($emisor['direccion']) : 'No especificado') . '</p>
    </div>

    <div class="info-section">
        <h3>DATOS DEL CLIENTE</h3>
        <p><strong>Razón Social:</strong> ' . htmlspecialchars($cliente['razon_social']) . '</p>
        <p><strong>RUC:</strong> ' . htmlspecialchars($cliente['nrodoc']) . '</p>
        <p><strong>Dirección:</strong> ' . htmlspecialchars($cliente['direccion']) . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ITEM</th>
                <th>DESCRIPCIÓN</th>
                <th>CANT.</th>
                <th>P.UNIT</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>';

foreach ($detalle as $item) {
    $html .= '
            <tr>
                <td>' . htmlspecialchars($item['item']) . '</td>
                <td>' . htmlspecialchars($item['descripcion']) . '</td>
                <td>' . htmlspecialchars($item['cantidad']) . '</td>
                <td>S/ ' . number_format($item['precio_unitario'], 2) . '</td>
                <td>S/ ' . number_format($item['importe_total'], 2) . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="totales">
        <div class="total-row">
            <strong>Op. Gravadas:</strong> <span>S/ ' . number_format($comprobante['total_opgravadas'], 2) . '</span>
        </div>
        <div class="total-row">
            <strong>Op. Exoneradas:</strong> <span>S/ ' . number_format($comprobante['total_opexoneradas'], 2) . '</span>
        </div>
        <div class="total-row">
            <strong>Op. Inafectas:</strong> <span>S/ ' . number_format($comprobante['total_opinafectas'], 2) . '</span>
        </div>
        <div class="total-row">
            <strong>IGV:</strong> <span>S/ ' . number_format($comprobante['igv'], 2) . '</span>
        </div>
        <div class="total-row">
            <strong>Total:</strong> <span>S/ ' . number_format($comprobante['total'], 2) . '</span>
        </div>
    </div>

    <div class="footer">
        <strong>Son:</strong> ' . htmlspecialchars($comprobante['total_texto']) . '
    </div>';

if ($comprobante['forma_pago'] == 'Credito') {
    $html .= '
    <div class="cuotas-section">
        <h3>INFORMACIÓN DE CUOTAS:</h3>';

    foreach ($cuotas as $cuota) {
        $html .= '
        <p>' . htmlspecialchars($cuota['cuota']) . ' - <strong>Monto:</strong> S/ ' . number_format(htmlspecialchars($cuota['monto']), 2) .
            ' - <strong>Fecha:</strong> ' . htmlspecialchars($cuota['fecha']) . '</p>';
    }

    $html .= '</div>';
}

$html .= '
</body>
</html>';

// Guardar el HTML como archivo
if (isset($nombreXML) && !empty($nombreXML)) {
    $nombreArchivo = 'facturas/' . $nombreXML . '.html';
    file_put_contents($nombreArchivo, $html);

    // Contenedor principal con alineación izquierda y espaciado
    echo '<div class="text-left p-6">';

    // Mensajes con estilo Tailwind
    echo '<div class="flex items-center justify-center my-6 space-x-4">';

    // Mensaje de éxito
    echo '<p class="text-green-600 font-medium">PARTE 3: FACTURA HTML CREADA SATISFACTORIAMENTE</p>';

    // Botón en verde
    echo '<a href="http://localhost/ahora_si_final/' . htmlspecialchars($nombreArchivo) . '" 
        class="inline-block px-6 py-3 bg-green-600 text-white font-medium rounded-lg
        hover:bg-green-700 transition duration-300 ease-in-out focus:outline-none focus:ring-2 
        focus:ring-green-500 focus:ring-opacity-50 transform hover:scale-105 text-center
        shadow-md" 
        target="_blank">
        Ver Factura Generada
    </a>';

    echo '</div>';
} else {
    echo "<div class='text-left p-6'>";
    echo "<div class='flex items-center justify-center my-6 space-x-4'>";
    echo "<p class='text-red-600 font-medium'>Error: No se pudo guardar la factura HTML. El nombre del archivo XML no está definido.</p>";
    echo "</div>";
    echo "</div>";
}
?>