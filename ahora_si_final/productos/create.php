<?php include('../header.php'); ?>
<?php
require '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $stock]);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto</title>
    <!-- Enlazar Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

    <div class="container mx-auto p-4">
        <div class="bg-white shadow rounded-lg">
            <!-- Encabezado -->
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-200 rounded-t-lg">
                <h1 class="text-xl font-semibold text-white">Crear Producto</h1>
            </div>
            
            <!-- Contenido -->
            <form action="create.php" method="POST" class="p-4 space-y-4">
                <!-- Campo: Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input 
                        type="text" 
                        name="nombre" 
                        id="nombre" 
                        placeholder="Nombre del producto" 
                        required 
                        class="block w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-800 bg-gray-50 text-sm"
                    >
                </div>
                
                <!-- Campo: Descripción -->
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea 
                        name="descripcion" 
                        id="descripcion" 
                        placeholder="Descripción del producto" 
                        required 
                        class="block w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-800 bg-gray-50 text-sm"
                    ></textarea>
                </div>
                
                <!-- Campo: Precio -->
                <div>
                    <label for="precio" class="block text-sm font-medium text-gray-700 mb-1">Precio</label>
                    <input 
                        type="number" 
                        name="precio" 
                        id="precio" 
                        placeholder="Precio" 
                        step="0.01" 
                        required 
                        class="block w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-800 bg-gray-50 text-sm"
                    >
                </div>
                
                <!-- Campo: Stock -->
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <input 
                        type="number" 
                        name="stock" 
                        id="stock" 
                        placeholder="Cantidad en stock" 
                        required 
                        class="block w-full px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-gray-800 bg-gray-50 text-sm"
                    >
                </div>
                
                <!-- Botones -->
                <div class="flex justify-between items-center">
                    <a href="index.php" class="inline-block px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 text-sm transition">
                        Volver
                    </a>
                    <button 
                        type="submit" 
                        class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm transition"
                    >
                        Crear
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
