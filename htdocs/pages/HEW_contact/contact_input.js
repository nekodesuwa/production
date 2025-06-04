document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("next-button").addEventListener("click", () => {
        const memberId = document.getElementById("member-id").value.trim();
        const email = document.getElementById("email").value.trim();
        const category = document.getElementById("category").value.trim();
        const message = document.getElementById("message").value.trim();

        // バリデーション
        if (!memberId || !email || !category || !message) {
            alert("すべての項目を入力してください。");
            return;
        }

        const query = `memberId=${encodeURIComponent(memberId)}&email=${encodeURIComponent(email)}&category=${encodeURIComponent(category)}&message=${encodeURIComponent(message)}`;

        window.location.href = `contact_confirmation.php?${query}`;
    });
});
