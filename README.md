# Website Tes Psikologi Papikostik Berbasis PHP

Ini adalah aplikasi web berbasis PHP dan MySQL yang dirancang untuk menyelenggarakan tes kepribadian Papikostik secara online. Aplikasi ini memiliki panel admin untuk mengelola peserta, soal, dan melihat hasil, serta antarmuka yang ramah pengguna bagi peserta untuk mengerjakan tes.

## Fitur Utama

- **Panel Admin Lengkap**:
  - Login yang aman untuk administrator.
  - Tambah dan kelola data peserta tes.
  - Kelola 90 soal Papikostik dan 20 deskripsi aspek kepribadian.
  - Lihat daftar hasil tes dari semua peserta.
- **Antarmuka Tes Peserta**:
  - Akses tes menggunakan token unik yang aman.
  - Halaman instruksi yang jelas sebelum memulai tes.
  - Antarmuka pengerjaan 90 soal yang bersih dan interaktif dengan progress bar.
- **Laporan Hasil Tes**:
  - Halaman hasil yang dapat diakses oleh admin dan peserta.
  - Visualisasi skor dalam bentuk **grafik radar (jaring laba-laba)** menggunakan Chart.js.
  - Laporan terperinci yang berisi skor dan deskripsi untuk setiap aspek kepribadian.

## Panduan Instalasi

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi di server lokal Anda (misalnya XAMPP, WAMP).

### 1. Persiapan Database

- Buka phpMyAdmin atau klien database lainnya.
- Buat database baru. Anda bisa menamainya `papikostik_db` (atau nama lain, namun Anda harus mengubahnya di file konfigurasi).

### 2. Impor Struktur Tabel

- Di database yang baru saja Anda buat, pilih tab **"Import"**.
- Klik **"Choose File"** dan pilih file `config/database.sql` dari direktori proyek ini.
- Klik **"Go"** atau **"Kirim"**. Ini akan membuat semua tabel yang diperlukan (`admins`, `peserta`, `soal_papikostik`, dll.).

### 3. Konfigurasi Koneksi

- Buka file `config/config.php` di editor teks Anda.
- Sesuaikan nilai-nilai berikut dengan pengaturan server database Anda:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USERNAME', 'root');
  define('DB_PASSWORD', ''); // Isi password database Anda jika ada
  define('DB_NAME', 'papikostik_db'); // Pastikan nama ini sama dengan nama database yang Anda buat
  ```

### 4. Isi Data Awal (Soal dan Deskripsi)

Aplikasi ini dilengkapi dengan data awal untuk soal dan deskripsi aspek. Anda perlu mengimpornya ke database.

- Buka kembali database Anda di phpMyAdmin.
- Impor file `config/seed_soal.sql` untuk mengisi 90 soal Papikostik.
- Setelah itu, impor file `config/seed_deskripsi.sql` untuk mengisi 20 deskripsi aspek.

### 5. Buat Akun Admin Pertama Anda

Untuk bisa masuk ke panel admin, Anda perlu membuat akun admin pertama.

- Buka browser dan arahkan ke file `config/create_admin.php`. Contoh: `http://localhost/nama_folder_proyek/config/create_admin.php`.
- Isi username dan password yang Anda inginkan, lalu klik **"Buat Admin"**.
- Setelah Anda mendapatkan pesan sukses, akun admin Anda siap digunakan.

> **PERINGATAN KEAMANAN PENTING!**
> Setelah berhasil membuat akun admin, **SEGERA HAPUS** file `config/create_admin.php` dari server Anda untuk mencegah penyalahgunaan.

## Cara Penggunaan

### Panel Admin

- Akses panel admin melalui `http://localhost/nama_folder_proyek/admin/`.
- Login menggunakan username dan password yang baru saja Anda buat.
- Di **Dashboard**, Anda dapat menavigasi ke:
  - **Tambah Peserta**: Untuk mendaftarkan peserta baru. Setelah ditambahkan, Anda akan mendapatkan link tes unik untuk dibagikan kepada peserta.
  - **Daftar Peserta**: Untuk melihat semua peserta yang terdaftar.
  - **Kelola Konten**: Untuk melihat atau mengedit soal dan deskripsi aspek jika diperlukan.
  - **Lihat Hasil Tes**: Untuk melihat daftar peserta yang sudah menyelesaikan tes dan mengakses laporan hasil mereka.

### Alur Peserta

- Peserta menerima link tes unik dari admin (misalnya: `.../user/mulai_tes.php?token=xxxxxxxx`).
- Peserta membuka link, membaca instruksi, lalu memulai tes.
- Peserta menjawab 90 soal.
- Setelah selesai, peserta akan langsung diarahkan ke halaman hasil yang menampilkan grafik dan deskripsi skor mereka.
