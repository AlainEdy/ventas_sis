<?php
require '../database/connection.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id = ?");
    $stmt->execute([$nombre, $descripcion, $precio, $stock, $id]);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
</head>
<body>
    <h1>Editar Producto</h1>
    <form action="edit.php?id=<?= $producto['id'] ?>" method="POST">
        <input type="text" name="nombre" value="<?= $producto['nombre'] ?>" required>
        <input type="text" name="descripcion" value="<?= $producto['descripcion'] ?>" required>
        <input type="number" name="precio" value="<?= $producto['precio'] ?>" step="0.01" required>
        <input type="number" name="stock" value="<?= $producto['stock'] ?>" required>
        <button type="submit">Actualizar</button>
    </form>
    <a href="index.php">Volver</a>
</body>
</html>
