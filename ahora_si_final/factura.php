<?php

$emisor = array(
    'tipodoc'                   =>  '6',
    'nrodoc'                    =>  '20123456789',
    'razon_social'              =>  'CETI ORG',
    'nombre_comercial'          =>  'CETI',
    'direccion'                 =>  'VIRTUAL',
    'ubigeo'                    =>  '130101',
    'departamento'              =>  'LAMBAYEQUE',
    'provincia'                 =>  'CHICLAYO',
    'distrito'                  =>  'CHICLAYO',
    'pais'                      =>  'PE',
    'usuario_secundario'        =>  'MODDATOS',
    'clave_usuario_secundario'  =>  'MODDATOS'
);

$cliente = array(
    'tipodoc'                   =>  '6',
    'nrodoc'                    =>  '10123456789',
    'razon_social'              =>  'CLIENTE CON RUC',
    'direccion'                 =>  'VIRTUAL',
    'pais'                      =>  'PE',
);

$comprobante = array(
    'tipodoc'                   =>  '01', //FACTURA: 01, BOLETA: 03, NC: 07, ND: 08
    'serie'                     =>  'FACT',
    'correlativo'               =>  1,
    'fecha_emision'             =>  date('Y-m-d'),
    'hora'                      =>  '00:00:00',
    'fecha_vencimiento'         =>  date('Y-m-d'),
    'moneda'                    =>  'PEN',
    'total_opgravadas'          =>  0.00,
    'total_opexoneradas'        =>  0.00,
    'total_opinafectas'         =>  0.00,
    'total_impbolsas'           =>  0.00,
    'total_opgratuitas1'        =>  0.00,
    'total_opgratuitas2'        =>  0.00,
    'igv'                       =>  0.00,
    'total'                     =>  0.00,
    'total_texto'               =>  '',
    'forma_pago'                =>  'Credito', //Contado o Credito, si hay cuotas cuando es credito
    'monto_pendiente'           =>  200.00 //Contado: 0 y no hay cuotas
);

$cuotas = array(
    array(
        'cuota'                 =>  'Cuota001',
        'monto'                 =>  100.00,
        'fecha'                 =>  '2025-08-28'
    ),
    array(
        'cuota'                 =>  'Cuota002',
        'monto'                 =>  100.00,
        'fecha'                 =>  '2025-09-28'
    )
);

$detalle = array(
    array(
        'item'                      =>  1,
        'codigo'                    =>  'PRO001',
        'descripcion'               =>  'IMPRESORA EPSON WIFI',
        'cantidad'                  =>  1,
        'precio_unitario'           =>  800, //incluido  impuestos IGV
        'valor_unitario'            =>  677.97, //no incluye impuestos IGV=0
        'igv'                       =>  122.30, //Cantidad * (Precio unitario - Valor unitario)
        'tipo_precio'               =>  '01', //01: Lucra con el servicio, 02: SiN lucro
        'porcentaje_igv'            =>  18,
        'importe_total'             =>  800, //cantidad * precio unitario
        'valor_total'               =>  677.97, //cantidad * valor unitario
        'unidad'                    =>  'NIU',
        'bolsa_plastica'            =>  'NO', //impuesto ICBPER    
        'total_impuesto_bolsas'     =>  0.00,
        //Gravados: 10, Exonerados: 20, Inafectos: 30
        'tipo_afectacion_igv'       =>  '10',
        'codigo_tipo_tributo'       =>  '1000', //Catalogho Nro 5. Codito de tipos de tributos
        'tipo_tributo'              =>  'VAT',
        'nombre_tributo'            =>  'IGV'
    ),
    array(
        'item'                      =>  2,
        'codigo'                    =>  'PRO002',
        'descripcion'               =>  'LIBRO LA DIVINA COMEDIA',
        'cantidad'                  =>  1,
        'precio_unitario'           =>  140.00, //incluido  impuestos IGV
        'valor_unitario'            =>  140.00, //no incluye impuestos IGV=0
        'igv'                       =>  0.00, //Cantidad * (Precio unitario - Valor unitario)
        'tipo_precio'               =>  '01', //01: Lucra con el servicio, 02: Si lucro
        'porcentaje_igv'            =>  0.00,
        'importe_total'             =>  140.00, //cantidad * precio unitario
        'valor_total'               =>  140.00, //cantidad * valor unitario
        'unidad'                    =>  'NIU',
        'bolsa_plastica'            =>  'NO', //impuesto ICBPER    
        'total_impuesto_bolsas'     =>  0.00,
        //Gravados: 10, Exonerados: 20, Inafectos: 30
        'tipo_afectacion_igv'       =>  '20',
        'codigo_tipo_tributo'       =>  '9997', //Catalogho Nro 5. Codito de tipos de tributos
        'tipo_tributo'              =>  'VAT',
        'nombre_tributo'            =>  'EXO'
    ),
    array(
        'item'                      =>  3,
        'codigo'                    =>  'PRO003',
        'descripcion'               =>  'MANZANA ROJA IMPORTADA',
        'cantidad'                  =>  10,
        'precio_unitario'           =>  2.50, //incluido  impuestos IGV
        'valor_unitario'            =>  2.50, //no incluye impuestos IGV=0
        'igv'                       =>  0.00, //Cantidad * (Precio unitario - Valor unitario)
        'tipo_precio'               =>  '01', //01: Lucra con el servicio, 02: Si lucro
        'porcentaje_igv'            =>  0.00,
        'importe_total'             =>  25.00, //cantidad * precio unitario
        'valor_total'               =>  25.00, //cantidad * valor unitario
        'unidad'                    =>  'NIU',
        'bolsa_plastica'            =>  'NO', //impuesto ICBPER    
        'total_impuesto_bolsas'     =>  0.00,
        //Gravados: 10, Exonerados: 20, Inafectos: 30
        'tipo_afectacion_igv'       =>  '30',
        'codigo_tipo_tributo'       =>  '9998', //Catalogho Nro 5. Codito de tipos de tributos
        'tipo_tributo'              =>  'FRE',
        'nombre_tributo'            =>  'INA'
    ),
);

//inicializar varibles totales
$total_opgravadas = 0.00;
$total_opexoneradas = 0.00;
$total_opinafectas = 0.00;
$total_opimpbolsas = 0.00;
$total = 0.00;
$igv = 0.00;
$op_gratuitas1 = 0.00;
$op_gratuitas2 = 0.00;

foreach ($detalle as $key => $value) {
    
    if ($value['tipo_afectacion_igv'] == 10) { //op gravadas
        $total_opgravadas += $value['valor_total'];
    }

    if ($value['tipo_afectacion_igv'] == 20) { //op exoneradas
        $total_opexoneradas += $value['valor_total'];
    }

    if ($value['tipo_afectacion_igv'] == 30) { //op inafectas
        $total_opinafectas += $value['valor_total'];
    }

    $igv += $value['igv'];
    $total_opimpbolsas = $value['total_impuesto_bolsas'];
    $total += $value['importe_total'] + $total_opimpbolsas;
}

$comprobante['total_opgravadas'] = $total_opgravadas;
$comprobante['total_opexoneradas'] = $total_opexoneradas;
$comprobante['total_opinafectas'] = $total_opinafectas;
$comprobante['total_impbolsas'] = $total_opimpbolsas;
$comprobante['total_opgratuitas_1'] = $op_gratuitas1;
$comprobante['total_opgratuitas_2'] = $op_gratuitas2;
$comprobante['igv'] = $igv;
$comprobante['total'] = $total;

require_once('cantidad_en_letras.php');
$comprobante['total_texto'] = CantidadEnLetra($total);

//PARTE 1: CREAR EL XML DE FACTURA
require_once('./api/api_genera_xml.php');
$obj_xml = new api_genera_xml();

//nombre del XML segun SUNAT
$nombreXML = $emisor['nrodoc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];
$rutaXML = 'xml/';

$obj_xml->crea_xml_invoice($rutaXML . $nombreXML, $emisor, $cliente, $comprobante, $detalle, $cuotas);

echo '</br> PARTE 01: XML DE FACTURA CREADO SATISFACTORIAMENTE';

//PARTE 2: ENVIO CPE A SUNAT
require_once('./api/api_cpe.php');
$objEnvio = new api_cpe();
$estado_envio = $objEnvio->enviar_invoice($emisor, $nombreXML, 'certificado_digital/', 'xml/', 'cdr/');

echo '</br> PARTE 2: ENVIO CPE-SUNAT';
echo '</br> Estado de envío: ' . $estado_envio['estado'];
echo '</br> Mensaje: ' . $estado_envio['estado_mensaje'];
echo '</br> HASH_CPE: ' . $estado_envio['hash_cpe'];
echo '</br> Descripción: ' . $estado_envio['descripcion'];
echo '</br> Nota: ' . $estado_envio['nota'];
echo '</br> Código de error: ' . $estado_envio['codigo_error'];
echo '</br> Mensaje de error: ' . $estado_envio['mensaje_error'];
echo '</br> HTTP CODE: ' . $estado_envio['http_code'];
echo '</br> OUTPUT: ' . $estado_envio['output'];


?>