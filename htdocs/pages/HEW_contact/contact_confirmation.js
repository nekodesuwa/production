document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);

    // 変数の値取得
    const memberId = params.get("memberId") || "未入力";
    const email = params.get("email") || "未入力";
    const category = params.get("category") || "未選択";
    const message = params.get("message") || "未入力";

    // 確認画面に値を設定
    document.getElementById("confirm-member-id").textContent = memberId;
    document.getElementById("confirm-email").textContent = email;
    document.getElementById("confirm-category").textContent = category;
    document.getElementById("confirm-message").textContent = message;

    // 戻るボタン
    document.getElementById("back-button").addEventListener("click", () => {
        window.history.back();
    });

    // 送信ボタン
    document.getElementById("submit-button").addEventListener("click", () => {
        window.location.href = '/pages/HEW_complete/contact_complete.php';
    });
});
