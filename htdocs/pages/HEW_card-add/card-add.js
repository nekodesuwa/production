
document.addEventListener("DOMContentLoaded", () => {
    const cardNumberInput = document.getElementById("card-number");
    const cardNameInput = document.getElementById("card-name");
    const cardYearInput = document.getElementById("card-year");
    const cardMonthInput = document.getElementById("card-month");
    const cardCvvInput = document.getElementById("card-cvv");

    // クレカ
    cardNumberInput.addEventListener("input", (e) => {
        let input = e.target.value.replace(/[^0-9]/g, ""); // 文字制限
        if (input.length > 16) input = input.slice(0, 16); // 文字数制限

            // ハイフン
            const formattedInput = input.match(/.{1,4}/g)?.join("-") || "";
            e.target.value = formattedInput;
        });

        // カード名義
        cardNameInput.addEventListener("input", (e) => {
            let input = e.target.value.toUpperCase().replace(/[^A-Z\s]/g, "");
            e.target.value = input;
        });

        // 月
        cardMonthInput.addEventListener("input", (e) => {
            let input = e.target.value.replace(/[^0-9]/g, "");
            if (input.length > 2) input = input.slice(0, 2);
            e.target.value = input;
        });

        // 年
        cardYearInput.addEventListener("input", (e) => {
            let input = e.target.value.replace(/[^0-9]/g, "");
            if (input.length > 2) input = input.slice(0, 2);
            e.target.value = input;
        });

        // CVV
        cardCvvInput.addEventListener("input", (e) => {
            let input = e.target.value.replace(/[^0-9]/g, "");
            if (input.length > 3) input = input.slice(0, 3);
            e.target.value = input;
        });
    });