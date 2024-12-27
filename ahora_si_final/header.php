<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas</title>
    <!-- Enlazar Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Barra de navegación -->
    <nav class="bg-gray-800 p-4 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Logo o nombre de la aplicación -->
            <a href="#" class="text-white text-2xl font-semibold hover:text-blue-300 transition duration-300">Sistema de Ventas</a>

            <!-- Menú de navegación -->
            <div class="hidden md:flex space-x-6">
                <a href="/ahora_si_final/productos/index.php" class="text-white hover:text-blue-300 transition duration-300">Productos</a>

                <a href="/ahora_si_final/generar_factura.php" class="text-white hover:text-blue-300 transition duration-300">Facturas</a>

            </div>

            <!-- Icono de menú (visible en dispositivos móviles) -->
            <div class="md:hidden flex items-center space-x-4">
                <button id="menuButton" class="text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Menú desplegable para móviles -->
    <div id="mobileMenu" class="hidden md:hidden bg-blue-800 text-white p-4 space-y-4">
        <a href="/ahora_si_final/productos/index.php" class="block">Productos</a>
        <a href="/ahora_si_final/generar_factura.php" class="block">Facturas</a>
    </div>

    <script>
        // Controlar la visibilidad del menú en dispositivos móviles
        const menuButton = document.getElementById('menuButton');
        const mobileMenu = document.getElementById('mobileMenu');

        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>

</body>
</html>
