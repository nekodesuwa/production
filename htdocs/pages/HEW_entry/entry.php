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

    // キャラステ取得
    $sql = "SELECT attack, health, speed, battle_available FROM game WHERE customer_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $character = $stmt->fetch(PDO::FETCH_ASSOC);

    // もしキャラクターが存在しなければ新規作成
    if (!$character) {
        $sql = "INSERT INTO game (customer_id, attack, health, speed, match_count, win_count, battle_available, created_at, updated_at) 
                VALUES (:user_id, 10, 100, 1, 0, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        // 再取得
        $stmt = $pdo->prepare("SELECT attack, health, speed, battle_available FROM game WHERE customer_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $character = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {

    $alert = "<script type='text/javascript'>alert('プレイヤーデータがありません');
    setTimeout(() => {
            window.location.href = `../HEW_index/index.php`;
        });
    </script>";
    echo $alert;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ゲームエントリー | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="entry.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const battleButton = document.getElementById('start-battle');
    const homeButton = document.getElementById('home-button');

    // 戦闘ボタンの動作
    battleButton.addEventListener('click', () => {
        window.location.href = '../HEW_game/game.php';
    });

    // ホームへ戻るボタンの動作
    homeButton.addEventListener('click', () => {
        window.location.href = '../HEW_index/index.php';
    });

    // 外部から受け取ったステータス情報を更新する
    // PHPの変数使いたかったからこっちに書く
    const playerStatus = {
        hp: <?php echo htmlspecialchars($character['health']); ?>,
        attack: <?php echo htmlspecialchars($character['attack']); ?>,
        speed: <?php echo htmlspecialchars($character['speed']); ?>
    };

    // ステータスをHTMLに反映
    document.getElementById('hp').textContent = playerStatus.hp;
    document.getElementById('attack').textContent = playerStatus.attack;
    document.getElementById('speed').textContent = playerStatus.speed;
});

    </script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="entry-container">
    <h1>ゲームへエントリー！</h1>
    <p>あなたの冒険が今、始まる……！</p>

    <div class="status-container">
        <div class="player-image">
            <img src="../../assets/img/game/player-image.png" alt="プレイヤーキャラクター">
        </div>
        <div class="status">
            <p>HP: <span id="hp"></span></p>
            <p>攻撃力: <span id="attack"></span></p>
            <p>素早さ: <span id="speed"></span></p>
            <p>戦闘可能回数: <span id="battle"><?php echo htmlspecialchars($character['battle_available']); ?></span></p>
        </div>
    </div>

    <button id="start-battle" class="battle-button">戦闘開始</button>
    <button id="home-button" class="home-button">ホームへ戻る</button>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
