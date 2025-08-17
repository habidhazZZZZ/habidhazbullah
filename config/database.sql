-- Skema Database untuk Website Tes Papikostik
-- Versi: 1.0
-- Author: Jules

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `papikostik_db`
--

-- --------------------------------------------------------

--
-- Struktur Tabel `admins`
--
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur Tabel `peserta`
--
CREATE TABLE `peserta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `status_tes` enum('Belum Mengerjakan','Selesai') NOT NULL DEFAULT 'Belum Mengerjakan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur Tabel `soal_papikostik`
--
CREATE TABLE `soal_papikostik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_soal` int(3) NOT NULL,
  `pernyataan_a` text NOT NULL,
  `aspek_a` char(1) NOT NULL,
  `pernyataan_b` text NOT NULL,
  `aspek_b` char(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur Tabel `deskripsi_aspek`
--
CREATE TABLE `deskripsi_aspek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aspek` char(1) NOT NULL,
  `nama_aspek` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aspek` (`aspek`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur Tabel `jawaban`
--
CREATE TABLE `jawaban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peserta_id` int(11) NOT NULL,
  `soal_id` int(11) NOT NULL,
  `jawaban_dipilih` char(1) NOT NULL,
  `aspek_terpilih` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `peserta_id` (`peserta_id`),
  KEY `soal_id` (`soal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur Tabel `hasil_tes`
--
CREATE TABLE `hasil_tes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `peserta_id` int(11) NOT NULL,
  `skor_g` tinyint(4) NOT NULL DEFAULT 0,
  `skor_l` tinyint(4) NOT NULL DEFAULT 0,
  `skor_i` tinyint(4) NOT NULL DEFAULT 0,
  `skor_t` tinyint(4) NOT NULL DEFAULT 0,
  `skor_v` tinyint(4) NOT NULL DEFAULT 0,
  `skor_s` tinyint(4) NOT NULL DEFAULT 0,
  `skor_r` tinyint(4) NOT NULL DEFAULT 0,
  `skor_d` tinyint(4) NOT NULL DEFAULT 0,
  `skor_c` tinyint(4) NOT NULL DEFAULT 0,
  `skor_e` tinyint(4) NOT NULL DEFAULT 0,
  `skor_n` tinyint(4) NOT NULL DEFAULT 0,
  `skor_a` tinyint(4) NOT NULL DEFAULT 0,
  `skor_p` tinyint(4) NOT NULL DEFAULT 0,
  `skor_x` tinyint(4) NOT NULL DEFAULT 0,
  `skor_b` tinyint(4) NOT NULL DEFAULT 0,
  `skor_o` tinyint(4) NOT NULL DEFAULT 0,
  `skor_k` tinyint(4) NOT NULL DEFAULT 0,
  `skor_f` tinyint(4) NOT NULL DEFAULT 0,
  `skor_w` tinyint(4) NOT NULL DEFAULT 0,
  `skor_z` tinyint(4) NOT NULL DEFAULT 0,
  `tanggal_tes` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `peserta_id` (`peserta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jawaban`
--
ALTER TABLE `jawaban`
  ADD CONSTRAINT `jawaban_ibfk_1` FOREIGN KEY (`peserta_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jawaban_ibfk_2` FOREIGN KEY (`soal_id`) REFERENCES `soal_papikostik` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hasil_tes`
--
ALTER TABLE `hasil_tes`
  ADD CONSTRAINT `hasil_tes_ibfk_1` FOREIGN KEY (`peserta_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE;

COMMIT;
