document.addEventListener("DOMContentLoaded", () => {
    // URLSearchParams でクエリパラメータを取得
    const params = new URLSearchParams(window.location.search);

    // 各パラメータを取得
    const memberId = params.get("memberId") || "未入力";
    const email = params.get("email") || "未入力";
    const category = params.get("category") || "未選択";
    const message = params.get("message") || "未入力";

    // 取得した値をHTMLに反映
    document.getElementById("confirm-member-id").textContent = memberId;
    document.getElementById("confirm-email").textContent = email;
    document.getElementById("confirm-category").textContent = category;
    document.getElementById("confirm-message").textContent = message;

    // 「戻る」ボタンの動作を設定
    document.getElementById("back-button").addEventListener("click", () => {
        window.history.back();
    });

    // 「送信」ボタンのダミー処理（ここにサーバー送信処理を追加可能）
    document.getElementById("submit-button").addEventListener("click", () => {
        window.location.href = '/pages/HEW_complete/contact_complete.php';
    });
});
