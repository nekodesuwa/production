<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];
$deliveryAddressId = $_SESSION['selected_address'] ?? null;
$paymentMethod = $_SESSION['selected_payment_method'] ?? null;
$usedPoints = $_SESSION['used_points'] ?? 0;
$totalAmount = $_SESSION['order_total'] ?? 0;
$finalAmount = $_SESSION['order_final_amount'] ?? 0;

if (!$deliveryAddressId || !$paymentMethod || $finalAmount <= 0) {
    die('注文情報が正しくありません。');
}

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $orderStatus = "注文受付中";
    $currentDateTime = date("Y-m-d H:i:s");

    // `order` にデータ挿入
    $sql = "INSERT INTO [order] (customer_id, delivery_address_id, total_amount, payment_method, points_used, order_status, order_date, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $customerId,
        $deliveryAddressId,
        (int) $finalAmount,
        mb_convert_encoding($paymentMethod, "SJIS", "UTF-8"),
        (int) $usedPoints,
        mb_convert_encoding($orderStatus, "SJIS", "UTF-8"),
        $currentDateTime,
        $currentDateTime,
        $currentDateTime
    ]);

    // `order_id` を取得
    $sql = "SELECT TOP 1 order_id FROM [order] WHERE customer_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $orderId = $order['order_id'] ?? null;

    if (!$orderId) {
        die("注文IDの取得に失敗しました。");
    }

    // カート内の商品取得
    $sql = "SELECT c.product_id, p.product_name, p.price_excluding_tax, c.quantity
            FROM cart_item c
            INNER JOIN product p ON c.product_id = p.product_id
            WHERE c.customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) {
        die("カートが空です。");
    }

    // `order_item` にデータ挿入
    $totalQuantity = 0;
    foreach ($cartItems as $item) {
        $priceIncludingTax = floor($item['price_excluding_tax']);
        $totalQuantity += (int) $item['quantity'];

        $sql = "INSERT INTO order_item (order_id, product_id, price_including_tax, quantity, product_info)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $orderId,
            $item['product_id'],
            (int) $priceIncludingTax,
            (int) $item['quantity'],
            $item['product_name']
        ]);
    }

    // `order_history` にデータ挿入
    $sql = "INSERT INTO order_history (order_id, customer_id, product_details, total_amount, payment_method, order_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $orderId,
        $customerId,
        json_encode($cartItems, JSON_UNESCAPED_UNICODE),
        (int) $finalAmount,
        mb_convert_encoding($paymentMethod, "SJIS", "UTF-8"),
        mb_convert_encoding($orderStatus, "SJIS", "UTF-8"),
        $currentDateTime,
        $currentDateTime
    ]);

    // カートを空にする
    $sql = "DELETE FROM cart_item WHERE customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);

    // `game` テーブルの更新
    $healthIncrease = floor($finalAmount / 1000) * 10;
    $attackIncrease = $totalQuantity;
    $speedIncrease = 1;
    $battleIncrease = floor($finalAmount / 1000); // 1000円ごとに+1

    // `game` テーブルのデータ取得
    $sql = "SELECT attack, health, speed, battle_available FROM game WHERE customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);
    $gameData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gameData) {
        // 既存データがある場合、更新
        $sql = "UPDATE game
                SET health = health + ?,
                    attack = attack + ?,
                    speed = speed + ?,
                    battle_available = battle_available + ?,
                    updated_at = ?
                WHERE customer_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $healthIncrease,
            $attackIncrease,
            $speedIncrease,
            $battleIncrease,
            $currentDateTime,
            $customerId
        ]);
    } else {
        // データがない場合、新規作成
        $sql = "INSERT INTO game (customer_id, attack, health, speed, battle_available, match_count, win_count, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $customerId,
            $attackIncrease,
            $healthIncrease,
            $speedIncrease,
            $battleIncrease,
            $currentDateTime,
            $currentDateTime
        ]);
    }

    // ユーザー情報の取得
    $sql = "SELECT username AS nickname, points, icon_path FROM user_account WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $customerId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 使用したポイントを減らす
        $remainingPoints = $user['points'] - $usedPoints;

        if ($remainingPoints < 0) {
            die("ポイントが不足しています。");
        }

        // ポイントを更新
        $sql = "UPDATE user_account SET points = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$remainingPoints, $customerId]);
    } else {
        die("ユーザー情報の取得に失敗しました。");
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
    <title>注文完了 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="order_complete.css">
    <script src="order_complete.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container fade-in">
    <div class="order-number">注文番号: <?php echo htmlspecialchars($orderId); ?></div>
    <div class="order-thanks">
        <h1>ご注文ありがとうございました</h1>
        <p style="font-size: 13px; margin-bottom: 5px;" >注文情報はマイページの注文履歴から確認できます。</p>
    </div>

    <div class="status-up">
        <h2>ステータス上昇</h2>
        <p>HP: <?php echo $gameData['health'] ?? 0; ?> + <?php echo $healthIncrease; ?> = <?php echo ($gameData['health'] ?? 0) + $healthIncrease; ?></p>
        <p>攻撃力: <?php echo $gameData['attack'] ?? 0; ?> + <?php echo $attackIncrease; ?> = <?php echo ($gameData['attack'] ?? 0) + $attackIncrease; ?></p>
        <p>素早さ: <?php echo $gameData['speed'] ?? 0; ?> + <?php echo $speedIncrease; ?> = <?php echo ($gameData['speed'] ?? 0) + $speedIncrease; ?></p>
        <p style="margin-bottom: 5px;">バトル回数: <?php echo $gameData['battle_available'] ?? 0; ?> + <?php echo $battleIncrease; ?> = <?php echo ($gameData['battle_available'] ?? 0) + $battleIncrease; ?></p>
    </div>


    <div class="banner">
        <a href="../HEW_entry/entry.php">
            <img src="../../assets/img/banner/banner_3.png" alt="ゲームバナー" class="banner-image">
        </a>
    </div>

    <div class="buttons">
        <button class="history-btn" onclick="goToOrderHistory()">注文履歴へ移動</button>
        <button class="home-btn" onclick="goToHome()">トップページに戻る</button>
    </div>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>