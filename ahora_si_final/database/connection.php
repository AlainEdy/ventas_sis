<?php
$host = 'localhost';      // Dirección del servidor de base de datos
$dbname = 'ventas';       // Nombre de la base de datos
$username = 'root';       // Usuario de la base de datos
$password = '';           // Contraseña del usuario

try {
    // Crear la conexión utilizando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Establecer el modo de error a excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obtener todos los productos
    $sql = "SELECT * FROM productos";
    $stmt = $pdo->query($sql);
    
    // Obtenemos todos los productos
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertimos los productos a formato JSON para pasarlos al frontend
    $productosJson = json_encode($productos);
} catch (PDOException $e) {
    // Manejo de excepciones y errores en la conexión
    echo "Error de conexión: " . $e->getMessage();
}
?>
