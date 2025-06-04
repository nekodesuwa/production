<?php
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;AutoTranslate=No;";

try {
    // データベース接続
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 検索処理
    $searchQuery = trim($_GET['search'] ?? '');
    if ($searchQuery !== '') {
        $searchQuery = mb_convert_encoding($searchQuery, "SJIS", "UTF-8");
        $searchQuery = '%' . $searchQuery . '%';

        $sql = "SELECT product_id, product_name, price, image_url
                FROM product
                WHERE product_name LIKE :searchQuery
                ORDER BY product_id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':searchQuery' => $searchQuery]);
    } else {
        // 検索ワードが空の場合、全商品を取得
        $sql = "SELECT product_id, product_name, price, image_url FROM product ORDER BY product_id DESC";
        $stmt = $pdo->query($sql);
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // データの文字コードをUTF-8に変換
    foreach ($products as &$product) {
        $product['product_name'] = mb_convert_encoding($product['product_name'], "UTF-8", "SJIS");
    }
    unset($product); // 参照変数を解除

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品検索 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="products.css">
    <script src="products.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<main>
    <section class="product-section">
        <h2>検索結果</h2>
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="../HEW_introduction/introduction.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        </a>
                        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p class="price">¥<?php echo number_format($product['price_excluding_tax']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>検索結果が見つかりませんでした。</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
