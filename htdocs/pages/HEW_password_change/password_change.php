<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // 入力チェック
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "全ての項目を入力してください。";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "新しいパスワードが一致しません。";
        } elseif (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[\W_]/', $newPassword)) {
            $error = "パスワードは8文字以上で、英字・数字・特殊文字を含む必要があります。";
        } else {
            // 現在のパスワードを確認
            $sql = "SELECT password FROM user_account WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                $error = "現在のパスワードが正しくありません。";
            } else {
                // 新しいパスワードをハッシュ化して更新
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE user_account SET password = :password, password_last_updated = NOW() WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':password' => $hashedPassword,
                    ':user_id' => $userId,
                ]);

                $success = "パスワードが変更されました。";
            }
        }
    }
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード変更 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="password_change.css">
    <script src="password_change.js" defer></script>
</head>
<body>
<?php include("../../assets/HEW_menu/menu.php"); ?>
<div class="password-change-container">
    <div class="title">パスワード変更</div>

    <?php if (!empty($error)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif (!empty($success)): ?>
        <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form id="passwordForm" method="POST" action="password_change.php">
        <div class="form-group">
            <label for="currentPassword">現在のパスワード</label>
            <input type="password" id="currentPassword" name="currentPassword" required>
        </div>
        <div class="form-group">
            <label for="newPassword">新しいパスワード</label>
            <input type="password" id="newPassword" name="newPassword" required>
            <ul id="passwordCheckList">
                <li id="lengthCheck" class="invalid">8文字以上</li>
                <li id="letterCheck" class="invalid">英字と数字を含む</li>
                <li id="caseCheck" class="invalid">大文字と小文字を含む</li>
                <li id="specialCharCheck" class="invalid">特殊文字を含む（!@#$%^&*など）</li>
            </ul>
        </div>
        <div class="form-group">
            <label for="confirmPassword">パスワード確認</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
            <p id="matchCheck" class="invalid">パスワードが一致していません</p>
        </div>
        <button type="submit" class="submit-button">変更する</button>
    </form>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
