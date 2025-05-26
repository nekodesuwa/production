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

// ページが読み込まれた後、0.5秒間ローディング画面を表示
window.addEventListener('load', () => {
    const loadingScreen = document.getElementById('loading-screen');
    const mainContent = document.getElementById('main-content');
    const progressBar = document.querySelector('.progress-fill');

    // プログレスバーを満タンにする
    setTimeout(() => {
        progressBar.style.width = '100%';
    }, 100); // 進捗のアニメーションを0.1秒後に開始

    // 0.5秒後にロード画面を非表示にし、メインコンテンツを表示
    setTimeout(() => {
        loadingScreen.style.display = 'none';
        mainContent.style.display = 'block';

        // 再度スクロールを許可
        document.body.style.overflow = 'auto';
    }, 500); // 500ミリ秒（0.5秒）
});
