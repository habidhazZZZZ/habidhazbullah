<!DOCTYPE html>
<html lang="id" class=""> <!-- class 'dark' akan ditambahkan di sini oleh JS -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Platform</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi tambahan untuk Tailwind
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    }
                }
            }
        }
    </script>
    <!-- CSS Kustom -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome untuk ikon (opsional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <div id="app" class="min-h-screen">
        <!-- Navigasi Utama -->
        <nav class="bg-white dark:bg-gray-800 shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            E-Learn
                        </a>
                    </div>
                    <div class="flex items-center">
                        <!-- Tombol Dark/Light Mode -->
                        <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                            <i class="fa-solid fa-moon" id="theme-toggle-dark-icon"></i>
                            <i class="fa-solid fa-sun" id="theme-toggle-light-icon" style="display: none;"></i>
                        </button>

                        <!-- Menu Pengguna -->
                        <div class="ml-4">
                            <?php
                            // Mulai session jika belum ada, untuk header yang modular
                            if (session_status() == PHP_SESSION_NONE) {
                                session_start();
                            }

                            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) :
                            ?>
                                <!-- Tampilan jika sudah login -->
                                <div class="flex items-center space-x-4">
                                    <span class="font-medium text-gray-800 dark:text-gray-200">
                                        Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                                    </span>
                                    <a href="dashboard.php" class="text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400" title="Dashboard">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </a>
                                    <a href="logout.php" class="text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400" title="Logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </a>
                                </div>
                            <?php else : ?>
                                <!-- Tampilan jika belum login -->
                                <div>
                                    <a href="login.php" class="text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 font-medium">Login</a>
                                    <a href="register.php" class="ml-4 text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 text-center">Register</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Konten Utama -->
        <main>
            <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
