document.getElementById('logout-button').addEventListener('click', function () {
    alert('ログアウトしました。');
    // ここにログアウト処理を追加してください
    window.location.href = '/pages/HEW_index/index.php'; // ログアウト後の遷移先を指定
});

document.getElementById('upload-again-button').addEventListener('click', function () {
    alert('もう一度アップロードページに移動します。');
    // アップロードページへの遷移
    window.location.href = '/pages/HEW_product_register_edit/product_register_edit.php'; // 再アップロードページのURLを指定
});
