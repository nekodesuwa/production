<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // データベース接続
    $databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
    if (!file_exists($databasePath)) {
        die('指定されたデータベースファイルが存在しません。');
    }
    $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";
    $username = "";
    $password = "";

    // 社員情報の取得
    $sql = "SELECT employee_id, password_hash, role FROM employee WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee && password_verify($password, $employee['password_hash'])) {
        // 認証成功
        $_SESSION['employee_id'] = $employee['employee_id'];
        $_SESSION['role'] = $employee['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        // 認証失敗
        $error = "社員IDまたはパスワードが正しくありません。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="employee_login.css">
    <script src="employee_login.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="login-container">
    <h1>社員用ログイン</h1>
    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form id="loginForm" method="POST" action="employee_login_process.php">
        <div class="form-group">
            <label for="username">社員ID</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
            <ul id="passwordCheckList">
                <li id="lengthCheck" class="invalid">8文字以上</li>
                <li id="letterCheck" class="invalid">英字と数字を含む</li>
                <li id="caseCheck" class="invalid">大文字と小文字を含む</li>
                <li id="specialCharCheck" class="invalid">特殊文字を含む（!@#$%^&*など）</li>
            </ul>
        </div>
        <button type="submit" id="loginButton">ログイン</button>
    </form>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
