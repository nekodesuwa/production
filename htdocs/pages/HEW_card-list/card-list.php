<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // カード削除処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_card_id'])) {
        $deleteCardId = $_POST['delete_card_id'];
        $deleteSql = "DELETE FROM card_list WHERE card_id = :card_id AND customer_id = :customer_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':card_id' => $deleteCardId, ':customer_id' => $customerId]);
    }

    // カード情報取得
    $sql = "SELECT card_id, card_number, expiration_date, cardholder_name FROM card_list WHERE customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cards as &$card) {
        $card['cardholder_name'] = mb_convert_encoding($card['cardholder_name'], "UTF-8", "SJIS");
    }
    unset($card);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

$previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'mypage.php';

if (strpos($previousPage, 'mypage.php') !== false) {
    $backUrl = '../HEW_mypage/mypage.php';

} elseif (strpos($previousPage, 'payment-method.php') !== false) {
    $backUrl = '../HEW_payment-method/payment-method.php';

} elseif (strpos($previousPage, 'card-add.php') !== false) {
    $backUrl = '../HEW_payment-method/payment-method.php';

} else {
    $backUrl = '../HEW_mypage/mypage.php'; // バグ回避
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録済みクレジットカード一覧 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="card-list.css">
</head>

<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container">
    <div class="back-button-area">
        <a class="back-button-link" href="<?php echo htmlspecialchars($backUrl); ?>"><button class="back-button">＜ 戻る</button></a>
    </div>
    <div class="container-area">
        <h1>登録済みクレジットカード一覧</h1>

        <?php if (!empty($cards)): ?>
            <?php foreach ($cards as $card): ?>
                <div class="card-area">
                    <button class="edit-button">
                        <a href="../HEW_card-edit/card-edit.php?card_id=<?= htmlspecialchars($card['card_id']); ?>">編集</a>
                    </button>

                    <div class="card-number">
                        <label>カード番号(下4ケタ)</label>
                        <p>****...<span><?= htmlspecialchars(substr($card['card_number'], -4)); ?></span></p>
                    </div>

                    <div class="card-name">
                        <label>カード名義</label>
                        <p><?= htmlspecialchars($card['cardholder_name']); ?></p>
                    </div>

                    <div class="card-month-year">
                        <label>有効期限</label>
                        <p><?= htmlspecialchars($card['expiration_date']); ?></p>
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="delete_card_id" value="<?= htmlspecialchars($card['card_id']); ?>">
                        <button type="submit" class="delete-button">削除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>登録済みのクレジットカードがありません。</p>
        <?php endif; ?>

        <button class="add-button" onclick="location.href='../HEW_card-add/card-add.php'">クレジットカードを追加</button>
    </div>
</div>
</body>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</html>
