<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php'); // ログインページにリダイレクト
    exit;
}

$customerId = $_SESSION['user_id'];

// ODBC接続設定
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 数量変更
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
        $cartItemId = intval($_POST['cart_item_id']);
        $quantity = intval($_POST['quantity']);

        $sql = "UPDATE [cart_item] SET quantity = :quantity, updated_at = NOW() WHERE cart_item_id = :cart_item_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':quantity' => $quantity, ':cart_item_id' => $cartItemId]);

        header('Location: cart.php'); // ページをリロード
        exit;
    }

    // 商品削除
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
        $cartItemId = intval($_POST['cart_item_id']);

        $sql = "DELETE FROM [cart_item] WHERE cart_item_id = :cart_item_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cart_item_id' => $cartItemId]);

        header('Location: cart.php'); // ページをリロード
        exit;
    }

    // カート情報の取得
    $sql = "SELECT ci.cart_item_id, ci.quantity, p.product_name, p.price_excluding_tax, p.image_url, p.product_id
            FROM [cart_item] AS ci
            INNER JOIN [product] AS p ON ci.product_id = p.product_id
            WHERE ci.customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 合計金額計算
    $totalPrice = 0;
    foreach ($cartItems as &$item) {
        if (!is_numeric($item['product_name'])) {
            $item['product_name'] = mb_convert_encoding($item['product_name'], "UTF-8", "SJIS");
        }
        if (!is_numeric($item['image_url'])) {
            $item['image_url'] = mb_convert_encoding($item['image_url'], "UTF-8", "SJIS");
        }
        $totalPrice += $item['price_excluding_tax'] * $item['quantity']; // 合計金額 = 税込み価格 × 数量
    }
    unset($item);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カートページ | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="cart.css">
    <script src="cart.js" defer></script>
</head>
<body>
<?php include("../../assets/HEW_menu/menu.php"); ?>
    <section class="cart-section">
        <h1 class="cart-title">カートに入っている商品</h1>
        <?php if (empty($cartItems)): ?>
            <p>カートに商品がありません。</p>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-items">
                    <div class="cart-items-left">
                        <a href="../HEW_introduction/introduction.php?product_id=<?php echo htmlspecialchars($item['product_id']); ?>">
                            <img class="product-image" src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        </a>
                        <p class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                    </div>
                    <div class="cart-items-right">
                        <p class="item-price">¥<?php echo number_format($item['price_excluding_tax']); ?></p>

                        <!-- 数量変更フォーム -->
                        <form method="POST" class="item-form">
                            <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                            <label for="quantity">数量:</label>
                            <select name="quantity" class="item-quantity-number" onchange="this.form.submit()">
                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $item['quantity'] ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </form>

                        <!-- 商品削除ボタン -->
                        <form method="POST" class="item-delete-form">
                            <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                            <button type="submit" name="delete_item" class="item-delete">削除</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- 合計金額と購入ボタン -->
            <div class="cart-summary">
                <p>合計金額: <span class="total-price">¥<?php echo number_format($totalPrice); ?></span></p>
                <button id="checkout-button">購入画面に進む</button>
            </div>
        <?php endif; ?>
    </section>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
