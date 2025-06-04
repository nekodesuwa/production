<?php
session_start();

// すでにログインしている場合はマイページへリダイレクト
if (isset($_SESSION['user_id'])) {
    header('Location: ../HEW_index/index.php');
    exit;
}

$error = '';

// フォームが送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = trim($_POST['username'] ?? '');
    $inputPassword = $_POST['password'] ?? '';

    if (empty($inputUsername) || empty($inputPassword)) {
        $error = "ユーザー名とパスワードを入力してください。";
    } else {
        $databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
        if (!file_exists($databasePath)) {
            die('指定されたデータベースファイルが存在しません。');
        }
        $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

        try {
            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // ユーザー情報の取得
            $sql = "SELECT user_id, username, password FROM user_account WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$inputUsername]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 取得したデータを Shift_JIS → UTF-8 に変換
                $dbUsername = mb_convert_encoding($user['username'], "UTF-8", "SJIS");
                $dbPasswordHash = $user['password'];

                if ($inputUsername === $dbUsername && password_verify($inputPassword, $dbPasswordHash)) {
                    // ログイン成功でセッションにユーザーIDを保存
                    $_SESSION['user_id'] = $user['user_id'];

                    // ログイン後はマイページへリダイレクト
                    header('Location: ../HEW_mypage/mypage.php');
                    exit;
                } else {
                    $error = "ユーザー名またはパスワードが正しくありません。";
                }
            } else {
                $error = "ユーザー名またはパスワードが正しくありません。";
            }
        } catch (PDOException $e) {
            die("データベースエラー: " . $e->getMessage());
        }
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
    <link rel="stylesheet" href="login.css">
    <script src="login.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="login-container">
    <h1>ログイン</h1>
    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
	<form method="POST" action="login.php" id="loginForm">
        <div class="form-group">
            <label for="username">ユーザー名</label>
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
        <button type="submit" id="loginButton" class="login-button">ログイン</button>
    </form>
    <p>アカウントをお持ちでない場合は<a href="../HEW_new_account_edit/new_account_edit.php">新規登録</a></p>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
