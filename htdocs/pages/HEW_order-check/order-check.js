document.addEventListener("DOMContentLoaded", function() {
    const confirmButton = document.getElementById("confirm-button");

    confirmButton.addEventListener("click", function() {
        window.location.href = "/pages/HEW_order_complete/order_complete.php"; // 完了ページのURLを指定
    });
});
