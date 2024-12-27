<?php include('../header.php'); ?>
<?php
require '../database/connection.php';

$productos = $pdo->query("SELECT * FROM productos")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <!-- Enlazar Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

    <div class="container mx-auto p-4">
        <div class="bg-white shadow rounded-lg">
            <!-- Encabezado -->
            <div class="bg-gray-800 px-4 py-3 border-b border-gray-200 rounded-t-lg flex justify-between items-center">
                <h1 class="text-xl font-semibold text-white">Gestión de Productos</h1>
                <!-- Botón para crear producto alineado a la derecha -->
                <div>
                    <a href="create.php" class="inline-block px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-300 text-sm">
                        Crear Producto
                    </a>
                </div>
            </div>
            
            <!-- Contenido -->
            <div class="p-4 space-y-4">

                <!-- Tabla de productos -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700 text-left">ID</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700 text-left">Nombre</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700 text-left">Descripción</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700 text-left">Precio</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700 text-left">Stock</th>
                                <th class="px-4 py-2 text-sm font-medium text-gray-700 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($productos as $producto): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700"><?= $producto['id'] ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($producto['descripcion']) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($producto['precio']) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= htmlspecialchars($producto['stock']) ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <a href="edit.php?id=<?= $producto['id'] ?>" class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-900 mr-2 text-sm">Editar</a>
                                    <a href="delete.php?id=<?= $producto['id'] ?>" class="bg-red-600 text-white px-3 py-1 rounded-md hover:bg-red-900 text-sm" onclick="return confirm('¿Seguro que quieres eliminar este producto?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
