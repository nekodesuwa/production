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

    // `order`テーブルから注文履歴を取得
    $sql = "
        SELECT
            o.order_id,
            o.created_at,
            o.total_amount,
            oi.product_id,
            p.product_name,
            p.image_url,
            oi.quantity,
            oi.price_including_tax
        FROM
            ([order] AS o
            INNER JOIN order_item AS oi
                ON o.order_id = oi.order_id)
            INNER JOIN product AS p
                ON oi.product_id = p.product_id
        WHERE
            o.customer_id = :user_id
        ORDER BY
            o.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 注文がない場合は空の配列を設定
    if (!$orders) {
        $orders = [];
    }

    // 注文データをグループ化
    $groupedOrders = [];
    foreach ($orders as $order) {
        $orderId = $order['order_id'];
        if (!isset($groupedOrders[$orderId])) {
            $groupedOrders[$orderId] = [
                'created_at' => $order['created_at'],
                'total_amount' => $order['total_amount'],
                'items' => [],
            ];
        }
        $groupedOrders[$orderId]['items'][] = [
            'product_name' => mb_convert_encoding($order['product_name'], "UTF-8", "SJIS"),
            'image_url' => $order['image_url'],
            'quantity' => $order['quantity'],
            'price' => $order['price_including_tax'],
        ];
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
    <title>注文履歴 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="order-history.css">
    <script src="order-history.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<!-- 注文履歴セクション -->
<section class="order-history-section">
    <h1 class="order-history-title">注文履歴</h1>
    <div class="order-history-list">
        <?php if (empty($groupedOrders)): ?>
            <p>注文履歴がありません。</p>
        <?php else: ?>
            <?php foreach ($groupedOrders as $orderId => $order): ?>
                <div class="order-block">
                    <h2 class="order-number">注文番号: #<?php echo htmlspecialchars($orderId); ?></h2>
                    <p class="order-date"><?php echo htmlspecialchars($order['created_at']); ?></p>
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="order-item-left">
                                <img class="product-image" src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <p class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                            </div>
                            <div class="order-item-right">
                                <p class="item-quantity">個数: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                <p class="item-price">¥<?php echo number_format($item['price']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <p class="order-total-amount">合計金額: ¥<?php echo number_format($order['total_amount']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
