<?php
require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = $_POST['cliente'];
    $productos = $_POST['productos']; // Aquí deberías manejar la relación con los productos
    $total = $_POST['total'];

    $stmt = $pdo->prepare("INSERT INTO ventas (cliente, total) VALUES (?, ?)");
    $stmt->execute([$cliente, $total]);

    $venta_id = $pdo->lastInsertId();
    // Insertar detalles de la venta (productos, cantidad, precio)
    foreach ($productos as $producto_id => $cantidad) {
        $stmt = $pdo->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad) VALUES (?, ?, ?)");
        $stmt->execute([$venta_id, $producto_id, $cantidad]);
    }

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Venta</title>
</head>
<body>
    <h1>Crear Venta</h1>
    <form action="create.php" method="POST">
        <input type="text" name="cliente" placeholder="Cliente" required>
        <input type="text" name="productos" placeholder="ID Productos y cantidades" required> <!-- Este campo debe manejarse más dinámicamente -->
        <input type="number" name="total" placeholder="Total" required>
        <button type="submit">Crear Venta</button>
    </form>
    <a href="index.php">Volver</a>
</body>
</html>