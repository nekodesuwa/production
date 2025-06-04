document.addEventListener('DOMContentLoaded', () => {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const lengthCheck = document.getElementById('lengthCheck');
    const letterCheck = document.getElementById('letterCheck');
    const caseCheck = document.getElementById('caseCheck');
    const specialCharCheck = document.getElementById('specialCharCheck');
    const matchCheck = document.getElementById('matchCheck');

    const passwordForm = document.getElementById('passwordForm');

    // パスワードの入力チェック
    newPassword.addEventListener('input', () => {
        const value = newPassword.value;

        // 8文字以上
        if (value.length >= 8) {
            lengthCheck.classList.add('valid');
            lengthCheck.classList.remove('invalid');
        } else {
            lengthCheck.classList.add('invalid');
            lengthCheck.classList.remove('valid');
        }

        // 英字と数字を含む
        if (/[a-zA-Z]/.test(value) && /\d/.test(value)) {
            letterCheck.classList.add('valid');
            letterCheck.classList.remove('invalid');
        } else {
            letterCheck.classList.add('invalid');
            letterCheck.classList.remove('valid');
        }

        // 大文字と小文字を含む
        if (/[A-Z]/.test(value) && /[a-z]/.test(value)) {
            caseCheck.classList.add('valid');
            caseCheck.classList.remove('invalid');
        } else {
            caseCheck.classList.add('invalid');
            caseCheck.classList.remove('valid');
        }

        // 特殊文字を含む
        if (/[!@#$%^&*]/.test(value)) {
            specialCharCheck.classList.add('valid');
            specialCharCheck.classList.remove('invalid');
        } else {
            specialCharCheck.classList.add('invalid');
            specialCharCheck.classList.remove('valid');
        }
    });

    // パスワードチェック
    confirmPassword.addEventListener('input', () => {
        if (newPassword.value === confirmPassword.value) {
            matchCheck.classList.add('valid');
            matchCheck.classList.remove('invalid');
            matchCheck.textContent = "パスワードが一致しています";
        } else {
            matchCheck.classList.add('invalid');
            matchCheck.classList.remove('valid');
            matchCheck.textContent = "パスワードが一致していません";
        }
    });

    // フォーム送信時の確認
    passwordForm.addEventListener('submit', (e) => {
        if (document.querySelectorAll('#passwordCheckList .invalid').length > 0 || 
            !matchCheck.classList.contains('valid')) {
            e.preventDefault();
            alert('条件を満たすパスワードを入力してください');
        }
    });
});
