-- Skema Database untuk Aplikasi E-Learning
-- Versi 1.0

-- Tabel untuk menyimpan peran pengguna (Guru, Siswa)
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE
);

-- Menambahkan peran dasar
INSERT INTO `roles` (`name`) VALUES ('teacher'), ('student');

-- Tabel untuk menyimpan data pengguna
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

-- Tabel untuk mata pelajaran
CREATE TABLE `subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `teacher_id` INT NOT NULL,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`)
);

-- Tabel untuk tugas
CREATE TABLE `assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `due_date` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`)
);

-- Tabel untuk pengumpulan tugas oleh siswa
CREATE TABLE `submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assignment_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `grade` INT,
  FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)
);
