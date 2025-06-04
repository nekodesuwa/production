// 「もう一度対戦する」ボタンのクリックイベント
document.getElementById("retry-button").addEventListener("click", function() {
    location.href = "../HEW_entry/entry.php";
});

// 「ホームへ戻る」ボタンのクリックイベント
document.getElementById("home-button").addEventListener("click", function() {
    location.href = "../HEW_index/index.php";
});

// ページが読み込まれたときにアニメーションを適用
document.addEventListener("DOMContentLoaded", function() {
    const winSection = document.querySelector(".game-win-section");
    winSection.style.opacity = 0; // 初期状態で透明に

    setTimeout(() => {
        winSection.style.transition = "opacity 1s ease-in-out, transform 1s ease";
        winSection.style.opacity = 1;
        winSection.style.transform = "scale(1)";
    }, 100); // ページロード後に少し遅れて表示
});

document.addEventListener("DOMContentLoaded", () => {
    const turnCount = localStorage.getItem("turnCount");

    if (turnCount) {
        // 反比例方式でポイントを計算
        const points = calculatePoints(turnCount);
        document.getElementById("turn-count").textContent = `${turnCount}`;
        document.getElementById("points").textContent = `${points.toFixed(0)}`;
        localStorage.removeItem("turnCount");
    }
});
