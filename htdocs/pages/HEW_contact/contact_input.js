document.addEventListener("DOMContentLoaded", () => {
    // 「次へ」ボタンのクリックイベント
    document.getElementById("next-button").addEventListener("click", () => {
        // 各入力欄の値を取得
        const memberId = document.getElementById("member-id").value.trim();
        const email = document.getElementById("email").value.trim();
        const category = document.getElementById("category").value.trim();
        const message = document.getElementById("message").value.trim();

        // 必須項目のバリデーション
        if (!memberId || !email || !category || !message) {
            alert("すべての項目を入力してください。");
            return;
        }

        // クエリパラメータを生成
        const query = `memberId=${encodeURIComponent(memberId)}&email=${encodeURIComponent(email)}&category=${encodeURIComponent(category)}&message=${encodeURIComponent(message)}`;

        // 確認ページに遷移
        window.location.href = `contact_confirmation.php?${query}`;
    });
});
