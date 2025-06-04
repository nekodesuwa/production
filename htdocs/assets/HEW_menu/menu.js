document.addEventListener("DOMContentLoaded", function() {
    const menuIcon = document.getElementById('menu-menuIcon');
    const sideMenu = document.getElementById('menu-menu');
    const closeMenu = document.getElementById('menu-closeMenu');
    const overlay = document.getElementById('menu-overlay');

    menuIcon.addEventListener('click', function() {
        sideMenu.classList.add('menu-show');
        overlay.classList.add('show');
    });

    closeMenu.addEventListener('click', function() {
        sideMenu.classList.remove('menu-show');
        overlay.classList.remove('show');
    });

    overlay.addEventListener('click', function() {
        sideMenu.classList.remove('menu-show');
        overlay.classList.remove('show');
    });
});




window.addEventListener('load', () => {
    const loadingScreen = document.getElementById('loading-screen');
    const mainContent = document.getElementById('main-content');
    const progressBar = document.querySelector('.progress-fill');

    setTimeout(() => {
        progressBar.style.width = '100%';
    }, 100);

    setTimeout(() => {
        loadingScreen.style.display = 'none';
        mainContent.style.display = 'block';

        document.body.style.overflow = 'auto';
    }, 500);
});
