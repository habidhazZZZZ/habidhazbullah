<?php
session_start();

// Cek apakah pengguna sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Atur path root dan inklusi file
$root_path = realpath(dirname(__FILE__) . '/..');
include_once($root_path . '/src/views/partials/header.php');
require_once($root_path . '/config/database.php');

// Inisialisasi koneksi database
$database = new Database();
$db = $database->connect();

?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
        Dashboard
    </h1>

    <?php
    // Router untuk menampilkan dashboard berdasarkan peran pengguna
    $role_id = $_SESSION['role_id'];

    if ($role_id == 1) { // Peran Guru
        // Tampilkan dashboard untuk guru
        include_once($root_path . '/src/views/partials/dashboard_teacher.php');
    } elseif ($role_id == 2) { // Peran Siswa
        // Tampilkan dashboard untuk siswa
        include_once($root_path . '/src/views/partials/dashboard_student.php');
    } else {
        // Jika peran tidak dikenali
        echo "<p>Peran pengguna tidak dikenali.</p>";
    }
    ?>

</div>

<?php
include_once($root_path . '/src/views/partials/footer.php');
?>
