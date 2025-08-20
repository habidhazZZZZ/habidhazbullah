<?php
session_start();

// Proteksi halaman
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
include_once($root_path . '/src/views/partials/header.php');
require_once($root_path . '/config/database.php');
require_once($root_path . '/src/models/Assignment.php');

// Validasi ID Tugas
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='text-center text-red-500'>ID Tugas tidak valid.</div>";
    include_once($root_path . '/src/views/partials/footer.php');
    exit;
}

// Ambil detail tugas
$database = new Database();
$db = $database->connect();
$assignment = new Assignment($db);
$assignment->id = $_GET['id'];

if (!$assignment->getById()) {
    echo "<div class='text-center text-red-500'>Tugas tidak ditemukan.</div>";
    include_once($root_path . '/src/views/partials/footer.php');
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Detail Tugas -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md mb-8">
            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($assignment->subject_name); ?></p>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mt-2"><?php echo htmlspecialchars($assignment->title); ?></h1>
            <p class="text-md text-red-600 dark:text-red-400 font-semibold mt-2">
                Tenggat: <?php echo date('l, d F Y, H:i', strtotime($assignment->due_date)); ?>
            </p>
            <div class="prose dark:prose-invert max-w-none mt-6 text-gray-700 dark:text-gray-300">
                <?php echo nl2br(htmlspecialchars($assignment->description)); ?>
            </div>
        </div>

        <!-- Form Pengumpulan Tugas -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Kumpulkan Tugas Anda</h2>

            <!-- Menampilkan pesan error/sukses -->
            <?php
            if (isset($_SESSION['message'])) {
                $color_class = ($_SESSION['message_type'] === 'error') ? 'red' : 'green';
                echo "<div class='p-4 mb-4 text-sm text-{$color_class}-700 bg-{$color_class}-100 rounded-lg dark:bg-gray-700 dark:text-{$color_class}-400' role='alert'>";
                echo htmlspecialchars($_SESSION['message']);
                echo "</div>";
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <form action="submit_assignment_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($assignment->id); ?>">

                <div>
                    <label for="file_submission" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pilih File</label>
                    <input type="file" name="file_submission" id="file_submission" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" required>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">Tipe file yang diizinkan: DOC, DOCX, PDF, JPG, PNG, MP4, XLS, XLSX (Maks: 25MB)</p>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full sm:w-auto text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-8 py-3 text-center">
                        <i class="fas fa-upload mr-2"></i>Kirim Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once($root_path . '/src/views/partials/footer.php');
?>
