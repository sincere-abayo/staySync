</div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Handle responsive sidebar
        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 1024) {
                sidebar.classList.add('fixed', 'h-full', 'z-40');
                sidebar.classList.remove('relative');
            } else {
                sidebar.classList.remove('fixed', 'h-full', 'z-40', '-translate-x-full');
                sidebar.classList.add('relative');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
</body>
</html>
