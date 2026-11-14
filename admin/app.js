(() => {
    const sidebar = document.getElementById('sidebar');
    const mobileMenu = document.getElementById('mobileMenu');
    const sidebarClose = document.getElementById('sidebarClose');

    if (!sidebar || !mobileMenu || !sidebarClose) {
        return;
    }

    const setSidebarOpen = (isOpen) => {
        sidebar.classList.toggle('open', isOpen);
        mobileMenu.setAttribute('aria-expanded', String(isOpen));
        mobileMenu.setAttribute(
            'aria-label',
            isOpen ? 'Close navigation menu' : 'Open navigation menu'
        );
    };

    mobileMenu.addEventListener('click', () => {
        setSidebarOpen(!sidebar.classList.contains('open'));
    });

    sidebarClose.addEventListener('click', () => {
        setSidebarOpen(false);
    });

    sidebar.querySelectorAll('.nav-item').forEach((item) => {
        item.addEventListener('click', () => {
            setSidebarOpen(false);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });
})();
