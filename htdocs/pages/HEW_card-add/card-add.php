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

    // フォームが送信された場合
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cardNumber = preg_replace("/[^0-9]/", "", $_POST['card_number']); // 数字のみ
        $cardName = mb_convert_encoding($_POST['card_name'], "SJIS", "UTF-8");
        $cardMonth = str_pad($_POST['card_month'], 2, "0", STR_PAD_LEFT);
        $cardYear = $_POST['card_year'];
        $expirationDate = "$cardMonth/$cardYear";

        // バリデーション
        if (empty($cardNumber) || empty($cardName) || empty($cardMonth) || empty($cardYear)) {
            die("エラー: 必須項目が未入力です。");
        }

        // card_listにデータ追加
        $sql = "INSERT INTO card_list (customer_id, card_number, expiration_date, cardholder_name, created_at, updated_at) 
                VALUES (:customer_id, :card_number, :expiration_date, :cardholder_name, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customerId,
            ':card_number' => $cardNumber,
            ':expiration_date' => $expirationDate,
            ':cardholder_name' => $cardName
        ]);

        header('Location: ../HEW_card-list/card-list.php');
        exit;
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
    <title>カードの登録 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <?php include("../../assets/HEW_menu/menu.php"); ?>
    <link rel="stylesheet" href="card-add.css">
    <script src="card-add.js" defer></script>
</head>
<body>
    <div>
        <div class="container">
            <div class="back-button-area">
                <a class="back-button-link" href="../HEW_card-list/card-list.php"><button class="back-button">＜ 戻る</button></a>
            </div>
            <div class="container-area">
                <h2>クレジットカードを登録する</h2>
                <form method="POST" action="card-add.php">
                    <div>
                        <label for="card-number">クレジットカード番号</label>
                        <input type="text" id="card-number" name="card_number" placeholder="例: 0000111122223333" required>
                    </div>
                    <div>
                        <label for="card-name">カード名義</label>
                        <input type="text" id="card-name" name="card_name" placeholder="例: KIMURA TAKUYA" required>
                    </div>
                    <span class="inline-inputs">
                        <div class="card-month-area">
                            <label for="card-month">月</label>
                            <input type="number" id="card-month" name="card_month" required>
                        </div>
                        <div class="space"></div>
                        <div class="card-year-area">
                            <label for="card-year">年</label>
                            <input type="number" id="card-year" name="card_year" required>
                        </div>
                    </span>
                    <div>
                        <label for="card-cvv">CVV</label>
                        <input type="number" id="card-cvv" name="card_cvv">
                    </div>
                    <div class="add-button">
                        <button type="submit">このカードを追加する</button>
                </form>
            </div>
        </div>
    </div>
</body>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</html>
