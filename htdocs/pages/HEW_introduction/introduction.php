<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}
$customerId = $_SESSION['user_id'];

// 商品IDの取得とバリデーション
if (!isset($_GET['product_id']) || !ctype_digit($_GET['product_id'])) {
    die("不正なアクセスです。");
}
$productId = (int)$_GET['product_id'];

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 商品詳細を取得
    $sql = "SELECT product_id, product_name, description, price_excluding_tax, shipping_info, image_url, image_url_2, image_url_3, genre_id 
            FROM product WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':product_id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("商品が見つかりませんでした。");
    }

    // 文字化け防止
    foreach ($product as $key => $value) {
        if (!is_numeric($value) && strpos($key, 'image_url') === false) {
            $product[$key] = mb_convert_encoding($value, "UTF-8", "SJIS");
        }
    }

    // おすせめ商品の取得（4件に制限）
    $sql = "SELECT TOP 4 product_id, product_name, price_excluding_tax, image_url
            FROM product
            WHERE genre_id = :genre_id AND product_id <> :product_id
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':genre_id' => $product['genre_id'],
        ':product_id' => $productId
    ]);
    $recommendedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recommendedProducts as &$recommended) {
        foreach ($recommended as $key => $value) {
            if (!is_numeric($value) && strpos($key, 'image_url') === false) {
                $recommended[$key] = mb_convert_encoding($value, "UTF-8", "SJIS");
            }
        }
    }
    unset($recommended);

    // カートへの追加処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
        $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        // カートに追加（image_url は保存せず、cart.php で product テーブルから取得）
        $sql = "INSERT INTO cart_item (customer_id, product_id, quantity, created_at)
                VALUES (:customer_id, :product_id, :quantity, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customerId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
        ]);

        // カートページへリダイレクト
        header('Location: ../HEW_cart/cart.php');
        exit;
    } else {
        echo "<script>alert('選択した数量が無効です。');</script>";
    }
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
    <title>商品詳細 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="../../assets/scripts/splide.min.css">
    <link rel="stylesheet" href="introduction.css">
    <script src="../../assets/scripts/jquery-3.6.0.min.js" defer></script>
    <script src="../../assets/scripts/splide.min.js" defer></script>
    <script src="introduction.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="main-area">
    <div id="image-slider" class="splide">
        <div class="splide__track">
            <ul class="splide__list">
                <?php foreach (['image_url', 'image_url_2', 'image_url_3'] as $key): ?>
                    <?php if (!empty($product[$key])): ?>
                        <li class="splide__slide"><img src="<?php echo htmlspecialchars($product[$key]); ?>" alt=""></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="content-text">
        <h1 class="title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
        <div class="content-text-body">
            <p class="price">￥<?php echo number_format(floor($product['price_excluding_tax'])); ?>（税込）</p>
            <p class="shipping"><?php echo htmlspecialchars($product['shipping_info']); ?></p>
            <form method="POST">
                <label class="item-select">
                    <select name="quantity" id="quantity-select">
                        <?php for ($i = 1; $i <= 9; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <div class="cart-star-button">
                    <button type="submit" name="add_to_cart" class="cart-button" id="cart-button">
                        <img src="../../assets/img/icon/cart.png" alt=""><span>カートに入れる</span>
                    </button>
                </div>
            </form>
            <p class="info"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
    </div>
</div>

<div class="info-area">
    <section class="product-section">
        <h2>あなたへのおすすめ</h2>
        <div class="product-grid">
            <?php if (!empty($recommendedProducts)): ?>
                <?php foreach ($recommendedProducts as $recommended): ?>
                    <div class="product-card">
                        <a href="introduction.php?product_id=<?php echo $recommended['product_id']; ?>">
                            <img class="product-image" src="<?php echo htmlspecialchars($recommended['image_url']); ?>" alt="">
                            <h3><?php echo htmlspecialchars(mb_strimwidth($recommended['product_name'], 0, 20, "…", "UTF-8")); ?></h3>
                            <p class="price">¥<?php echo number_format(floor($recommended['price_excluding_tax'])); ?>（税込）</p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>おすすめの商品はありません。</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
