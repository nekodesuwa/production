function updateChecklist(id, isValid) {
    const element = document.getElementById(id);
    if (isValid) {
        element.classList.remove('invalid');
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
    }
}

// パスワードの条件を判定し、チェックリストを更新
function validatePassword(password) {
    const lengthCheck = password.length >= 8;
    const letterCheck = /[a-z]/i.test(password) && /\d/.test(password);
    const caseCheck = /[a-z]/.test(password) && /[A-Z]/.test(password);
    const specialCharCheck = /[!@#$%^&*]/.test(password);

    updateChecklist('lengthCheck', lengthCheck);
    updateChecklist('letterCheck', letterCheck);
    updateChecklist('caseCheck', caseCheck);
    updateChecklist('specialCharCheck', specialCharCheck);

    return lengthCheck && letterCheck && caseCheck && specialCharCheck;
}

document.getElementById('password').addEventListener('input', function () {
    validatePassword(this.value);
});

// フォーム送信時にパスワード条件をチェックして、NGなら送信しない
document.getElementById('loginForm').addEventListener('submit', function (event) {
    const password = document.getElementById('password').value;

    if (!validatePassword(password)) {
        event.preventDefault(); // 送信中止
        alert('パスワードの条件を満たしていません。すべての条件を確認してください。');
    }
});
