document.addEventListener("DOMContentLoaded", () => {
    const backToTopButton = document.getElementById("back-to-top");

    // スクロール時にボタンを表示/非表示
    window.addEventListener("scroll", () => {
        if (window.scrollY > 200) {
            backToTopButton.classList.add("visible"); // ボタンをスライド表示
        } else {
            backToTopButton.classList.remove("visible"); // ボタンをスライド非表示
        }
    });

    // ボタンをクリックしてページトップへ移動
    backToTopButton.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" }); // スムーズスクロール
    });
});
