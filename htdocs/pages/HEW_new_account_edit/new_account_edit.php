<?php
session_start();

// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// POST処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 文字コード変換
    $name = mb_convert_encoding($name, "SJIS", "UTF-8");
    $email = mb_convert_encoding($email, "SJIS", "UTF-8");

    // 入力チェック
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        die("全ての項目を入力してください。");
    }
    if (!filter_var(mb_convert_encoding($email, "UTF-8", "SJIS"), FILTER_VALIDATE_EMAIL)) {
        die("正しいメールアドレスを入力してください。");
    }
    if ($password !== $confirmPassword) {
        die("パスワードが一致しません。");
    }

    // 既存のメールアドレスか
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_account WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            die("このメールアドレスは既に登録されています。");
        }
    } catch (PDOException $e) {
        die("データベースエラー（メールチェック）: " . $e->getMessage());
    }

    // パスワードのハッシュ化
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ユーザー情報の登録
    try {
        $sql = "INSERT INTO user_account (username, email, password, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $hashedPassword]);

        // 登録成功 → 登録完了ページへリダイレクト
        header('Location: ../HEW_login/login.php');
        exit;
    } catch (PDOException $e) {
        die("データベースエラー（登録処理）: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="new_account_edit.css">
    <script src="new_account_edit.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<!-- フォーム -->
<div class="form-container">
    <form action="" method="POST" class="form">
        <div class="form-item">
            <label for="name">名前:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-item">
            <label for="email">メールアドレス:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-item">
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required>
            <ul id="passwordCheckList">
                <li id="lengthCheck" class="invalid">8文字以上</li>
                <li id="letterCheck" class="invalid">英字と数字を含む</li>
                <li id="caseCheck" class="invalid">大文字と小文字を含む</li>
                <li id="specialCharCheck" class="invalid">特殊文字を含む（!@#$%^&*など）</li>
            </ul>
        </div>

        <div class="form-item">
            <label for="confirm_password">パスワード確認:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <p id="matchCheck" class="invalid">パスワード一致</p>
        </div>

        <button type="submit" class="submit-btn">確認に進む</button>
    </form>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

<script>
    document.getElementById("password").addEventListener("input", validatePassword);
    document.getElementById("confirm_password").addEventListener("input", checkPasswordMatch);

    function validatePassword() {
        let password = document.getElementById("password").value;
        document.getElementById("lengthCheck").classList.toggle("valid", password.length >= 8);
        document.getElementById("letterCheck").classList.toggle("valid", /[a-zA-Z]/.test(password) && /[0-9]/.test(password));
        document.getElementById("caseCheck").classList.toggle("valid", /[a-z]/.test(password) && /[A-Z]/.test(password));
        document.getElementById("specialCharCheck").classList.toggle("valid", /[!@#$%^&*]/.test(password));
    }

    function checkPasswordMatch() {
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirm_password").value;
        document.getElementById("matchCheck").classList.toggle("valid", password === confirmPassword);
    }
</script>

</body>
</html>