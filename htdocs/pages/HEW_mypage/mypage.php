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

    // ユーザー情報の取得
    $sql = "SELECT username AS nickname, points, icon_path FROM user_account WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // データが存在しない場合の処理
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

    // ステータス情報の取得
    $sql = "SELECT health, attack, speed FROM game WHERE customer_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $status = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$status) {
        $status = ['health' => 100, 'attack' => 10, 'speed' => 1]; // デフォルト値
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
    <title>マイページ | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="mypage.css">
    <script src="mypage.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="mypage">
    <h1>マイページ</h1>
    <div class="profile-section">
        <div class="icon">
            <img src="<?php echo htmlspecialchars($user['icon_path']); ?>" alt="ユーザーアイコン" />
        </div>
        <div class="user-info">
            <p><strong>会員ID:</strong> <span><?php echo htmlspecialchars($userId); ?></span></p>
            <p><strong>ニックネーム:</strong> <span><?php echo htmlspecialchars($user['nickname']); ?></span></p>
            <p><strong>ポイント残数:</strong> <span><?php echo htmlspecialchars($user['points']); ?></span></p>
        </div>
    </div>
    <div class="status-section">
        <p>現在のステータス</p>
        <ul>
            <li><strong>HP:</strong> <span><?php echo htmlspecialchars($status['health']); ?></span></li>
            <li><strong>攻撃力:</strong> <span><?php echo htmlspecialchars($status['attack']); ?></span></li>
            <li><strong>素早さ:</strong> <span><?php echo htmlspecialchars($status['speed']); ?></span></li>
        </ul>
        <button id="battle-button" onclick="navigateTo('/pages/HEW_record/record.php')">戦績</button>
    </div>
    <div class="menu-section">
        <button class="menu-button" onclick="navigateTo('../HEW_order-history/order-history.php')">注文履歴</button>
        <button class="menu-button" onclick="navigateTo('../HEW_account_edit/account_edit.php')">アカウント情報の編集</button>
        <button class="menu-button" onclick="navigateTo('../HEW_password_change/password_change.php')">パスワードの変更</button>
        <button class="menu-button" onclick="navigateTo('../HEW_card-list/card-list.php')">クレジットカード情報の追加・変更</button>
        <button class="menu-button" onclick="navigateTo('../HEW_shipping-list/shipping-list.php')">配送先の追加・変更</button>
    </div>
    <div class="game-banner">
        <a href="../HEW_entry/entry.php">
            <img src="/assets/img/banner/banner_3.png" alt="ゲームバナー" />
        </a>
    </div>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }
</script>

</body>
</html>
