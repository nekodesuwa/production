<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 戦闘結果を受け取って更新する
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['battle_result'])) {
        $battleResult = $_POST['battle_result'];

        // 戦闘回数を増やす
        $sql = "UPDATE game SET match_count = match_count + 1 WHERE customer_id = :customer_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':customer_id' => $customerId]);

        // 勝利数を増やす
        if ($battleResult === 'win') {
            $sql = "UPDATE game SET win_count = win_count + 1 WHERE customer_id = :customer_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':customer_id' => $customerId]);
        }
    }

    // 戦績取得
    $sql = "SELECT win_count, match_count FROM game WHERE customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    $winCount = $record['win_count'] ?? 0;
    $battleCount = $record['match_count'] ?? 0;

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>戦績表 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="record.css">
    <script src="record.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container">
    <div class="stats-box">
        <div class="stats-title">戦績表</div>
        <div class="character-image">
            <img src="/assets/img/game/player-image.png" alt="自キャラ画像">
        </div>
        <div class="stats">
            <div class="stat-item">
                <p>～勝利数～ <br><span id="win-count"><?php echo htmlspecialchars($winCount); ?></span></p>
            </div>
            <div class="stat-item">
                <p>～戦闘数～ <br><span id="battle-count"><?php echo htmlspecialchars($battleCount); ?></span></p>
            </div>
        </div>
    </div>

    <button id="back-button" onclick="window.location.href='../pages/HEW_mypage/mypage.php'">マイページへ戻る</button>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>