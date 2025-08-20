<?php
// Atur path root untuk inklusi file yang konsisten
$root_path = realpath(dirname(__FILE__) . '/..');
include_once($root_path . '/src/views/partials/header.php');
?>

<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <h1 class="text-2xl font-bold text-center text-gray-900 dark:text-white">Login ke Akun Anda</h1>

        <!-- Menampilkan pesan error atau sukses -->
        <?php
        session_start();
        if (isset($_SESSION['message'])) {
            $message_type = $_SESSION['message_type'] ?? 'info';
            $color_class = ($message_type === 'error') ? 'red' : 'green';
            echo "<div class='p-4 mb-4 text-sm text-{$color_class}-700 bg-{$color_class}-100 rounded-lg dark:bg-{$color_class}-200 dark:text-{$color_class}-800' role='alert'>";
            echo htmlspecialchars($_SESSION['message']);
            echo "</div>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form action="login_process.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                <input type="text" name="username" id="username" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="username" required>
            </div>
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
            </div>
            <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                Login
            </button>
            <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                Belum punya akun? <a href="register.php" class="font-medium text-primary-600 hover:underline dark:text-primary-500">Register di sini</a>
            </p>
        </form>
    </div>
</div>

<?php
// Path relatif dari public ke src
include_once($root_path . '/src/views/partials/footer.php');
?>
