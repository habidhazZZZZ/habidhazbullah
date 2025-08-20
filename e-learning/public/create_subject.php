<?php
session_start();

// Proteksi halaman: hanya guru yang bisa mengakses
if (!isset($_SESSION['loggedin']) || $_SESSION['role_id'] != 1) { // 1 = teacher
    $_SESSION['message'] = "Anda tidak memiliki hak akses untuk halaman ini.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
include_once($root_path . '/src/views/partials/header.php');
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Buat Mata Pelajaran Baru</h1>

        <!-- Form untuk membuat mata pelajaran -->
        <form action="create_subject_process.php" method="POST" class="space-y-6">
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Mata Pelajaran</label>
                <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Contoh: Kalkulus Lanjutan" required>
            </div>
            <div>
                <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi Singkat</label>
                <textarea name="description" id="description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Jelaskan secara singkat tentang mata pelajaran ini..."></textarea>
            </div>
            <div class="flex items-center justify-end space-x-4">
                <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:underline">Batal</a>
                <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Simpan Mata Pelajaran
                </button>
            </div>
        </form>
    </div>
</div>

<?php
include_once($root_path . '/src/views/partials/footer.php');
?>
