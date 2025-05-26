// パスワード入力イベントをリッスン
document.getElementById('password').addEventListener('input', function () {
  const password = this.value;

  // 条件の正規表現
  const lengthCheck = password.length >= 8;
  const letterCheck = /[a-z]/i.test(password) && /\d/.test(password);
  const caseCheck = /[a-z]/.test(password) && /[A-Z]/.test(password);
  const specialCharCheck = /[!@#$%^&*]/.test(password);

  // 各条件の更新
  updateChecklist('lengthCheck', lengthCheck);
  updateChecklist('letterCheck', letterCheck);
  updateChecklist('caseCheck', caseCheck);
  updateChecklist('specialCharCheck', specialCharCheck);
});

// チェックリストの状態を更新する関数
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

// フォーム送信時の処理
document.getElementById('loginForm').addEventListener('submit', function (event) {
  event.preventDefault(); // フォームのデフォルト動作を防止
  const password = document.getElementById('password').value;

  // パスワード条件を再確認
  const lengthCheck = password.length >= 8;
  const letterCheck = /[a-z]/i.test(password) && /\d/.test(password);
  const caseCheck = /[a-z]/.test(password) && /[A-Z]/.test(password);
  const specialCharCheck = /[!@#$%^&*]/.test(password);

  if (lengthCheck && letterCheck && caseCheck && specialCharCheck) {
      // 条件を満たしている場合、index.phpへ移動
      window.location.href = '/pages/HEW_index/index.php';
  } else {
      // 条件を満たしていない場合、アラートを表示
      alert('パスワードの条件を満たしていません。すべての条件を確認してください。');
  }
});

document.getElementById('password').addEventListener('input', function () {
    const password = this.value;
    
    // パスワードの条件をチェック
    const lengthCheck = document.getElementById('lengthCheck');
    const letterCheck = document.getElementById('letterCheck');
    const caseCheck = document.getElementById('caseCheck');
    const specialCharCheck = document.getElementById('specialCharCheck');

    // 8文字以上
    if (password.length >= 8) {
        lengthCheck.classList.remove('invalid');
        lengthCheck.classList.add('valid');
    } else {
        lengthCheck.classList.remove('valid');
        lengthCheck.classList.add('invalid');
    }

    // 英字と数字を含む
    if (/[a-zA-Z]/.test(password) && /\d/.test(password)) {
        letterCheck.classList.remove('invalid');
        letterCheck.classList.add('valid');
    } else {
        letterCheck.classList.remove('valid');
        letterCheck.classList.add('invalid');
    }

    // 大文字と小文字を含む
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) {
        caseCheck.classList.remove('invalid');
        caseCheck.classList.add('valid');
    } else {
        caseCheck.classList.remove('valid');
        caseCheck.classList.add('invalid');
    }

    // 特殊文字を含む
    if (/[!@#$%^&*]/.test(password)) {
        specialCharCheck.classList.remove('invalid');
        specialCharCheck.classList.add('valid');
    } else {
        specialCharCheck.classList.remove('valid');
        specialCharCheck.classList.add('invalid');
    }
});
