<?php
session_start();
// Jika sudah login, langsung lempar ke dalam aplikasi
if (isset($_SESSION['status']) && $_SESSION['status'] === 'login') {
    header('Location: public/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MikroTik Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-200 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center mb-2">MikroTik Monitor</h2>
        <p class="text-center text-gray-400 mb-6">Silakan login untuk melanjutkan.</p>
        
        <?php if(isset($_GET['pesan'])): ?>
        <div class="mb-4 p-3 rounded text-white <?php echo ($_GET['pesan'] == 'gagal' || $_GET['pesan'] == 'belum_login' || $_GET['pesan'] == 'akses_ditolak') ? 'bg-red-500' : 'bg-green-500'; ?>">
            <?php
                if($_GET['pesan'] == 'gagal') echo 'Login gagal! Username atau password salah.';
                if($_GET['pesan'] == 'belum_login') echo 'Anda harus login untuk mengakses halaman tersebut.';
                if($_GET['pesan'] == 'logout') echo 'Anda telah berhasil logout.';
                if($_GET['pesan'] == 'sukses') echo 'Operasi berhasil dilakukan.';
                if($_GET['pesan'] == 'akses_ditolak') echo 'Akses ditolak ke sumber daya tersebut.';
            ?>
        </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300">Username</label>
                <input type="text" id="username" name="username" class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login
                </button>
            </div>
        </form>
    </div>
</body>
</html>
