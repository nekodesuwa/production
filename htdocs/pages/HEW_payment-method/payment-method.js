document.addEventListener("DOMContentLoaded", function () {
    const accordionButtons = document.querySelectorAll('.accordion-button');

    accordionButtons.forEach(button => {
        button.addEventListener('click', function () {
            const content = this.nextElementSibling;
            const isOpen = content.style.display === 'block';

            document.querySelectorAll('.accordion-content').forEach(item => {
                item.style.display = 'none';
            });

            if (!isOpen) {
                content.style.display = 'block';
            }
        });
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const pointCheckbox = document.getElementById("use-point");
    const pointInput = document.getElementById("point-input");

    pointCheckbox.addEventListener("change", () => {
        pointInput.disabled = !pointCheckbox.checked;
        if (!pointCheckbox.checked) {
            pointInput.value = "";
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const pointCheckbox = document.getElementById("use-point");
    const pointInput = document.getElementById("point-input");
    const nextButton = document.querySelector(".next-button");
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');

    // 初期状態でボタンを無効化
    nextButton.disabled = true;

    // 決済方法の選択状態をチェック
    function checkSelection() {
        let selected = false;
        paymentMethods.forEach(method => {
            if (method.checked) {
                selected = true;
            }
        });
        nextButton.disabled = !selected;
    }

    // ラジオボタンが選択されたときにボタンを有効化
    paymentMethods.forEach(method => {
        method.addEventListener("change", checkSelection);
    });

    // ポイントチェックボックスの制御
    pointCheckbox.addEventListener("change", () => {
        pointInput.disabled = !pointCheckbox.checked;
        if (!pointCheckbox.checked) {
            pointInput.value = "";
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const proceedButton = document.getElementById("proceed-button");
    const radioButtons = document.querySelectorAll("input[name='selected_address']");

    // 初期状態でボタンを無効化
    proceedButton.disabled = true;
    proceedButton.classList.add("disabled-button");

    // ラジオボタン選択時にボタンを有効化
    radioButtons.forEach(function (radio) {
        radio.addEventListener("change", function () {
            proceedButton.disabled = false; // ボタンを有効化
            proceedButton.classList.remove("disabled-button");
            proceedButton.classList.add("active-button");
        });
    });
});

