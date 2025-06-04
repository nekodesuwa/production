<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// ターン数を取得
$turnCount = isset($_GET['turnCount']) ? (int)$_GET['turnCount'] : 1;

// ポイント計算関数
function calculatePoints($turnCount) {
    return 10;
}

$earnedPoints = calculatePoints($turnCount);

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

    if ($user) {
        // ポイント加算
        $newPoints = $user['points'] + $earnedPoints;
        $updateSql = "UPDATE user_account SET points = :points WHERE user_id = :user_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':points' => $newPoints, ':user_id' => $userId]);
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
    <title>敗北 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="result.css">
    <script src="result.js" defer></script>
</head>
<body>

    <?php include("../../assets/HEW_menu/menu.php"); ?>

<!-- 敗北したときのテキスト -->
<div class="game-lose-section">
    <h1>残念！敗北しました……</h1>
    <p>終了までにかかったターン数: <span><?= htmlspecialchars($turnCount) ?></span>ターン</p>
    <p>獲得ポイント: <span><?= htmlspecialchars($earnedPoints) ?></span>ポイント</p>
    <p>現在の総ポイント: <span><?= htmlspecialchars($newPoints) ?></span>ポイント</p>
    <div class="lose-buttons">
        <button id="retry-button">もう一度対戦する</button>
        <button id="home-button">ホームへ戻る</button>
    </div>
</div>

</body>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</html>