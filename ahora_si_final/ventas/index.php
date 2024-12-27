<?php include('../header.php'); ?>
<?php
require '../database/connection.php';

$ventas = $pdo->query("SELECT * FROM ventas")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas</title>
</head>
<body>
    <h1>Ventas Realizadas</h1>
    <a href="create.php">Nueva Venta</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Fecha</th>
        </tr>
        <?php foreach ($ventas as $venta): ?>
        <tr>
            <td><?= $venta['id'] ?></td>
            <td><?= $venta['cliente'] ?></td>
            <td><?= $venta['total'] ?></td>
            <td><?= $venta['fecha'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
