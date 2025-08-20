</div>
        </main>
    </div>

    <footer class="bg-white dark:bg-gray-800 shadow-inner mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> E-Learning Platform. Dibuat dengan ❤️.</p>
        </div>
    </footer>

    <!-- Script Kustom -->
    <script>
    // Handler untuk Theme Toggle
    const themeToggleBtn = document.getElementById('theme-toggle');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const lightIcon = document.getElementById('theme-toggle-light-icon');

    // Cek tema saat halaman dimuat
    if (localStorage.getItem('color-theme') === 'dark' ||
        (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        lightIcon.style.display = 'block';
        darkIcon.style.display = 'none';
    } else {
        document.documentElement.classList.remove('dark');
        lightIcon.style.display = 'none';
        darkIcon.style.display = 'block';
    }

    themeToggleBtn.addEventListener('click', function() {
        // Toggle class 'dark' pada <html>
        document.documentElement.classList.toggle('dark');

        // Simpan preferensi tema di localStorage
        const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        localStorage.setItem('color-theme', theme);

        // Toggle ikon
        if (theme === 'dark') {
            lightIcon.style.display = 'block';
            darkIcon.style.display = 'none';
        } else {
            lightIcon.style.display = 'none';
            darkIcon.style.display = 'block';
        }
    });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>
