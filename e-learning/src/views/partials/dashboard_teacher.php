<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Kolom Kiri: Aksi Cepat -->
    <div class="md:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Aksi Cepat</h2>
        <div class="space-y-4">
            <a href="create_subject.php" class="block w-full text-center text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5">
                <i class="fas fa-plus-circle mr-2"></i>Buat Mata Pelajaran
            </a>
            <a href="create_assignment.php" class="block w-full text-center text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5">
                <i class="fas fa-file-alt mr-2"></i>Buat Tugas Baru
            </a>
        </div>
    </div>

    <!-- Kolom Kanan: Daftar Mata Pelajaran -->
    <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Mata Pelajaran Anda</h2>
        <div class="space-y-4">
            <?php
            // Memuat model Subject dan mengambil data
            require_once($root_path . '/src/models/Subject.php');
            $subject_model = new Subject($db);
            $subject_model->teacher_id = $_SESSION['user_id'];
            $subjects = $subject_model->getByTeacher();

            $num = $subjects->rowCount();

            if ($num > 0) {
                while ($row = $subjects->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    echo "<div class='p-4 border rounded-lg dark:border-gray-700 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200'>";
                    echo "<h3 class='font-bold text-lg text-primary-600 dark:text-primary-400'>" . htmlspecialchars($name) . "</h3>";
                    echo "<p class='text-gray-600 dark:text-gray-300'>" . htmlspecialchars($description) . "</p>";
                    // Tambahkan tombol aksi di sini di masa depan, misal: lihat detail, edit, hapus
                    echo "</div>";
                }
            } else {
                echo "<div class='text-center py-4'>";
                echo "<p class='text-gray-500 dark:text-gray-400'>Anda belum membuat mata pelajaran apapun.</p>";
                echo "<a href='create_subject.php' class='mt-4 inline-block text-white bg-primary-600 hover:bg-primary-700 font-medium rounded-lg text-sm px-5 py-2.5'>Buat Mata Pelajaran Pertama Anda</a>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</div>
