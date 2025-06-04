<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

// `shipping-method.php` で選択した配送先
$deliveryAddressId = $_SESSION['selected_address'] ?? null;

// `payment-method.php` で選択した決済方法
$paymentMethod = $_SESSION['selected_payment_method'] ?? null;

// `payment-method.php` で選択したポイント利用
$usedPoints = $_SESSION['used_points'] ?? 0;

if (!$deliveryAddressId || !$paymentMethod) {
    die('配送先または決済方法が選択されていません。');
}

// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ 配送先情報の取得
    $sql = "
        SELECT d.street, d.city_id, d.prefecture_id, p.prefecture_name, c.city_name, d.building_name, d.postal_code
        FROM ((delivery_address AS d
        INNER JOIN prefecture AS p ON d.prefecture_id = p.prefecture_id)
        INNER JOIN cities AS c ON d.city_id = c.city_id)
        WHERE d.delivery_address_id = :delivery_address_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':delivery_address_id' => $deliveryAddressId]);
    $deliveryAddress = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deliveryAddress) {
        die("配送先情報が見つかりません。");
    }

    // ✅ 文字化け対策
    foreach ($deliveryAddress as $key => $value) {
        if (isset($value)) {
            $deliveryAddress[$key] = mb_convert_encoding($value, "UTF-8", "SJIS");
        }
    }

    // ✅ カート情報の取得
    $sql = "
        SELECT p.product_name, p.image_url, c.quantity, p.price_excluding_tax
        FROM cart_item AS c
        INNER JOIN product AS p ON c.product_id = p.product_id
        WHERE c.customer_id = :customer_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) {
        die("カートが空です。");
    }

    // ✅ 税込み価格の計算（消費税10%適用）
    $totalAmount = 0;
    foreach ($cartItems as &$item) {
        $item['product_name'] = mb_convert_encoding($item['product_name'], "UTF-8", "SJIS"); // 文字化け防止
        $item['price_including_tax'] = floor($item['price_excluding_tax']);
        $totalAmount += $item['price_including_tax'] * $item['quantity'];
    }
    unset($item);

    // ✅ ポイント適用
    $usedPoints = min($totalAmount, $usedPoints);
    $finalAmount = max(0, $totalAmount - $usedPoints);

    // ✅ セッションに金額情報を保存
    $_SESSION['order_total'] = $totalAmount;
    $_SESSION['order_final_amount'] = $finalAmount;
    $_SESSION['order_used_points'] = $usedPoints;

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文情報の確認</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="order-check.css">
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>
<div class="container">
<div class="back-button-area">
    <a class="back-button-link" href="../HEW_payment-method/payment-method.php"><button class="back-button">＜ 戻る</button></a>
</div>
<main>
    <!-- 領収書セクション -->
    <section class="receipt">
        <h2>注文情報</h2>
        <div class="receipt-details">
            <p><strong>注文日:</strong> <?php echo date("Y-m-d H:i:s"); ?></p>
            <p><strong>合計金額:</strong> ¥<?php echo number_format($totalAmount); ?></p>
            <p><strong>使用ポイント:</strong> <?php echo number_format($usedPoints); ?> P</p>
            <p><strong>最終支払額:</strong> ¥<?php echo number_format($finalAmount); ?></p>
        </div>
    </section>

    <!-- 配送先セクション -->
    <section class="shipping-info">
        <h2>配送情報</h2>
        <p><strong>配送先:</strong> 〒<?php echo htmlspecialchars($deliveryAddress['postal_code']); ?></p>
        <p><?php echo htmlspecialchars($deliveryAddress['prefecture_name'] . " " . $deliveryAddress['city_name']); ?></p>
        <p><?php echo htmlspecialchars($deliveryAddress['street'] . " " . ($deliveryAddress['building_name'] ?? '')); ?></p>
    </section>

    <!-- 決済方法セクション -->
    <section class="payment-method">
        <h2>決済方法</h2>
        <p><strong>選択された方法:</strong> <?php echo htmlspecialchars($paymentMethod); ?></p>
    </section>

    <!-- カート内容セクション -->
    <section class="cart-section">
        <h2>カート内の商品</h2>
        <?php foreach ($cartItems as $item): ?>
            <div class="cart-items">
                <div class="cart-items-left">
                    <a href="/"><img class="product-image" src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></a>
                    <p class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                </div>
                <div class="cart-items-right">
                    <p class="item-price">¥<?php echo number_format($item['price_including_tax']); ?></p>
                    <div class="item-quantity">
                        <p>数量:<span><?php echo htmlspecialchars($item['quantity']); ?></span></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- 注文確定ボタン -->
    <div class="confirm-order">
        <form method="POST" action="../HEW_order_complete/order_complete.php">
            <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customerId); ?>">
            <input type="hidden" name="delivery_address_id" value="<?php echo htmlspecialchars($deliveryAddressId); ?>">
            <input type="hidden" name="payment_method" value="<?php echo htmlspecialchars($paymentMethod); ?>">
            <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($_SESSION['order_final_amount']); ?>">
            <button type="submit" id="confirm-button">注文を確定する</button>
        </form>
    </div>
</main>
</div>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
