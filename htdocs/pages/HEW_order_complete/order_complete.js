document.addEventListener("DOMContentLoaded", () => {
    const container = document.querySelector(".fade-in");
    // ページ読み込み後、フェードインクラスを追加
    setTimeout(() => {
        container.classList.add("show");
    }, 100);
});

function goToOrderHistory() {
    window.location.href = "/pages/HEW_order-history/order-history.php"; // 注文履歴ページのURL
}

function goToHome() {
    window.location.href = "/pages/HEW_index/index.php"; // トップページのURL
}
