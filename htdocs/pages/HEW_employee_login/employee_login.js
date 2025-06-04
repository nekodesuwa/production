document.getElementById('password').addEventListener('input', function () {
    const password = this.value;

    // 条件の正規表現
    const lengthCheck = password.length >= 8;
    const letterCheck = /[a-z]/i.test(password) && /\d/.test(password);
    const caseCheck = /[a-z]/.test(password) && /[A-Z]/.test(password);
    const specialCharCheck = /[!@#$%^&*]/.test(password);

    // 条件の更新
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
        window.location.href = '/pages/HEW_product_register_edit/product_register_edit.php';
    } else {
        // 条件を満たしていない場合、アラートを表示
        alert('パスワードの条件を満たしていません。すべての条件を確認してください。');
    }
  });
