<?php
session_start();

// Proteksi halaman: hanya guru yang bisa mengakses
if (!isset($_SESSION['loggedin']) || $_SESSION['role_id'] != 1) {
    $_SESSION['message'] = "Anda tidak memiliki hak akses untuk halaman ini.";
    $_SESSION['message_type'] = "error";
    header("Location: dashboard.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
include_once($root_path . '/src/views/partials/header.php');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/Subject.php');

// Ambil daftar mata pelajaran milik guru untuk dropdown
$database = new Database();
$db = $database->connect();
$subject = new Subject($db);
$subject->teacher_id = $_SESSION['user_id'];
$subjects_list = $subject->getByTeacher();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Buat Tugas Baru</h1>

        <!-- Form untuk membuat tugas -->
        <form action="create_assignment_process.php" method="POST" class="space-y-6">
            <div>
                <label for="subject_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih Mata Pelajaran</label>
                <select name="subject_id" id="subject_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php
                    if ($subjects_list->rowCount() > 0) {
                        while ($row = $subjects_list->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Judul Tugas</label>
                <input type="text" name="title" id="title" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Contoh: Latihan Bab 3" required>
            </div>
            <div>
                <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi Tugas</label>
                <textarea name="description" id="description" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Jelaskan instruksi tugas di sini..."></textarea>
            </div>
            <div>
                <label for="due_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tenggat Waktu</label>
                <input type="datetime-local" name="due_date" id="due_date" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
            </div>
            <div class="flex items-center justify-end space-x-4">
                <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:underline">Batal</a>
                <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>

<?php
include_once($root_path . '/src/views/partials/footer.php');
?>
