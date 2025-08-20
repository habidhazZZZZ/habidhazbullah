<?php
// Memuat model yang diperlukan
require_once($root_path . '/src/models/Subject.php');
require_once($root_path . '/src/models/Assignment.php');

// Inisialisasi model
$subject_model = new Subject($db);
$assignment_model = new Assignment($db);

// Ambil data
$subjects = $subject_model->getAll();
$active_assignments = $assignment_model->getAllActive();

$num_subjects = $subjects->rowCount();
$num_assignments = $active_assignments->rowCount();
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Kolom Utama: Tugas Mendatang -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Tugas Mendatang</h2>
        <div class="space-y-4">
            <?php
            if ($num_assignments > 0) {
                while ($row = $active_assignments->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    echo "<a href='assignment.php?id=" . htmlspecialchars($id) . "' class='block p-4 border rounded-lg dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200'>";
                    echo "<div class='flex justify-between items-center'>";
                    echo "<div>";
                    echo "<h3 class='font-bold text-lg text-primary-700 dark:text-primary-400'>" . htmlspecialchars($title) . "</h3>";
                    echo "<p class='text-sm text-gray-500 dark:text-gray-400'>Mata Pelajaran: " . htmlspecialchars($subject_name) . "</p>";
                    echo "</div>";
                    echo "<div class='text-right flex-shrink-0 ml-4'>";
                    echo "<p class='text-sm font-semibold text-red-600 dark:text-red-400'>Tenggat:</p>";
                    echo "<p class='text-sm text-gray-800 dark:text-gray-200'>" . date('d M Y, H:i', strtotime($due_date)) . "</p>";
                    echo "</div>";
                    echo "</div>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<div class='text-center py-6'>";
                echo "<i class='fas fa-check-circle text-5xl text-green-500'></i>";
                echo "<p class='mt-4 text-gray-600 dark:text-gray-300'>Tidak ada tugas yang akan datang. Kerja bagus!</p>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- Kolom Samping: Daftar Mata Pelajaran -->
    <div class="lg:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Mata Pelajaran</h2>
        <div class="space-y-3">
            <?php
            if ($num_subjects > 0) {
                while ($row = $subjects->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    echo "<div class='p-3 border rounded-lg dark:border-gray-700'>";
                    echo "<h3 class='font-semibold text-md'>" . htmlspecialchars($name) . "</h3>";
                    echo "<p class='text-xs text-gray-500 dark:text-gray-400'>Oleh: " . htmlspecialchars($teacher_name) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p class='text-gray-500 dark:text-gray-400'>Belum ada mata pelajaran yang tersedia.</p>";
            }
            ?>
        </div>
    </div>
</div>
