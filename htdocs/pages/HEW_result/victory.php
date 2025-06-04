<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// フォームからターン数を取得
$turnCount = isset($_GET['turnCount']) ? (int)$_GET['turnCount'] : 1;

// ポイント計算関数
function calculatePoints($turnCount) {
    $basePoints = 1000; // 基本ポイント
    if ($turnCount === 0) {
        return $basePoints;
    }
    $points = $basePoints / $turnCount;
    return ($points < 100) ? 100 : (int)$points; // 最低100ポイント
}

// 計算したポイント
$earnedPoints = calculatePoints($turnCount);

$basePoints = 100;
$bonusPoints = $earnedPoints - $basePoints;
$finalPoints = $earnedPoints; // 最終的な獲得ポイント

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
        $newPoints = $user['points'] + $finalPoints;
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
    <title>勝利 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="result.css">
    <script src="result.js" defer></script>
</head>
<body>

    <?php include("../../assets/HEW_menu/menu.php"); ?>

<!-- 勝利テキスト -->
<div class="game-win-section">
    <h1>おめでとうございます！勝利しました！</h1>
    <p>勝利までにかかったターン数: <span><?= htmlspecialchars($turnCount) ?></span>ターン</p>
    <p>獲得ポイント: <span><?= htmlspecialchars($basePoints) ?> + <?= htmlspecialchars($bonusPoints) ?> = <?= htmlspecialchars($finalPoints) ?></span>ポイント</p>
    <p>現在の総ポイント: <span><?= htmlspecialchars($newPoints) ?></span>ポイント</p>
    <div class="win-buttons">
        <button id="retry-button">もう一度対戦する</button>
        <button id="home-button">ホームへ戻る</button>
    </div>
</div>

</body>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</html>
