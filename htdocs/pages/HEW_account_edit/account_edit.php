<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザー情報の取得
    $sql = "SELECT username AS nickname, points, icon_path FROM user_account WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // データが存在しない場合
    if (!$user) {
        $user = [
            'nickname' => 'ゲスト',
            'points' => 0,
            'icon_path' => '/assets/img/user/default.png'
        ];
    } else {
        // 文字コード変換
        $user['nickname'] = mb_convert_encoding($user['nickname'], "UTF-8", "SJIS");

        // 初期アイコンの適用
        if (empty($user['icon_path'])) {
            $user['icon_path'] = '/assets/img/user/default.png';
        } else {
            $user['icon_path'] = mb_convert_encoding($user['icon_path'], "UTF-8", "SJIS");
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
    <title>アカウント編集 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="account_edit.css">
    <script src="account_edit.js" defer></script>
</head>
<body>

    <?php include("../../assets/HEW_menu/menu.php"); ?>

    <form id="account-edit-form" method="POST" action="upload.php">
        <div class="account-edit-container">
            <div class="icon-upload">
                <label for="icon-upload-input" class="icon-label">アイコンアップロード</label>
                <input type="file" id="icon-upload-input" accept="image/*">
                <canvas id="icon-preview" width="150" height="150"></canvas>
            </div>

            <input type="hidden" id="icon-data" name="icon-data">
            <div class="user-info">
                <div class="readonly-field">
                    <label for="user-id">会員ID(変更不可)</label>
                    <input type="text" id="user-id" name="user-id" value="<?php echo htmlspecialchars($userId); ?>" readonly>
                </div>
                <div>
                    <label for="nickname">ニックネーム</label>
                    <input type="text" id="nickname" name="nickname" placeholder="ニックネームを入力">
                </div>
                <div>
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" placeholder="example@example.com">
                </div>
                <div>
                    <label for="phone">電話番号</label>
                    <input type="tel" id="phone" name="phone" placeholder="電話番号を入力">
                </div>
                <div>
                    <label for="optional-phone">電話番号(任意)</label>
                    <input type="tel" id="optional-phone" name="optional-phone" placeholder="任意の電話番号">
                </div>
                <div class="newsletter-checkbox">
                    <input type="checkbox" id="newsletter" name="newsletter">
                    <label for="newsletter">メールの配信を受け取る</label>
                </div>
                <div class="register-button">
                    <button type="submit" id="register-button">登録</button>
                </div>
            </div>
        </div>
    </form>
    <?php include("../../assets/HEW_footer/footer.php"); ?>
</body>

</html>