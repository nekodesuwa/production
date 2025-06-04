// ページが読み込まれた時の処理
document.addEventListener('DOMContentLoaded', () => {
    // コンテンツのフェードインをトリガー
    const content = document.getElementById('content');
    content.classList.add('show');

    // 10秒後に自動遷移
    setTimeout(() => {
        window.location.href = '/pages/HEW_index/index.php';
    }, 10000);
});

  // 「トップページに戻る」ボタンのクリックイベント
    document.getElementById('return-btn').addEventListener('click', () => {
    window.location.href = '/pages/HEW_index/index.php';
});
