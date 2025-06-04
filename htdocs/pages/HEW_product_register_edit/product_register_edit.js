document.getElementById("image-upload").addEventListener("change", function (event) {
    const file = event.target.files[0];

    if (!file) {
        alert("画像が選択されていません。");
        return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
        const img = new Image();
        img.src = e.target.result;

        img.onload = function () {
            // Canvasに画像を描画
            const canvas = document.getElementById("image-preview");
            const ctx = canvas.getContext("2d");

            // 正方形に切り取るためのサイズ計算
            const size = Math.min(img.width, img.height);
            const sx = (img.width - size) / 2;
            const sy = (img.height - size) / 2;

            // 正方形に切り取り、Canvasに描画
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, sx, sy, size, size, 0, 0, canvas.width, canvas.height);
        };
    };

    reader.readAsDataURL(file);
});

document.getElementById("next-button").addEventListener("click", function () {
    window.location.href = "../../HEW_product_upload_complete/product_upload_complete.php"; // 遷移先のURLを設定
});
