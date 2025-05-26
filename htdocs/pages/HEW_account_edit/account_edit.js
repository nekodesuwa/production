document.getElementById("register-button").addEventListener("click", function () {
    window.location.href = '/pages/HEW_complete/account_edit_complete.php';
});

document.getElementById("icon-upload-input").addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            // Create a square canvas
            const canvas = document.getElementById("icon-preview");
            const ctx = canvas.getContext("2d");

            // Determine the smallest side to make a square
            const size = Math.min(img.width, img.height);
            const startX = (img.width - size) / 2;
            const startY = (img.height - size) / 2;

            // Clear canvas and draw the cropped image
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, startX, startY, size, size, 0, 0, canvas.width, canvas.height);

            // Convert canvas to Base64 and set it to the hidden input field
            const base64Data = canvas.toDataURL("image/png");
            document.getElementById("icon-data").value = base64Data;
        };
        img.src = e.target.result;
    };

    reader.readAsDataURL(file);
});

// 初期画像のURL
const initialImageURL = "/assets/img/user/default.png";

// 初期画像をCanvasに設定
const canvas = document.getElementById("icon-preview");
const ctx = canvas.getContext("2d");
const initialImage = new Image();
initialImage.src = initialImageURL;
initialImage.onload = function () {
    const canvasWidth = canvas.width;
    const canvasHeight = canvas.height;
    const imgAspectRatio = initialImage.width / initialImage.height;
    const canvasAspectRatio = canvasWidth / canvasHeight;

    let renderWidth, renderHeight;
    if (imgAspectRatio > canvasAspectRatio) {
        renderWidth = canvasWidth;
        renderHeight = canvasWidth / imgAspectRatio;
    } else {
        renderHeight = canvasHeight;
        renderWidth = canvasHeight * imgAspectRatio;
    }
    const xOffset = (canvasWidth - renderWidth) / 2;
    const yOffset = (canvasHeight - renderHeight) / 2;

    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
    ctx.drawImage(initialImage, xOffset, yOffset, renderWidth, renderHeight);
};
